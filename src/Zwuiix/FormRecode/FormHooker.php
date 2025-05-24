<?php

namespace Zwuiix\FormRecode;

use Erodia\Engine;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use Zwuiix\FormRecode\inventory\FakeInventoryWindow;
use ReflectionClass;
use Exception;

class FormHooker implements Listener
{
    private static bool $isRegistered = false;
    private static self $instance;

    /**
     * Returns whether the form hooker is already registered.
     * @return bool
     */
    public static function isRegistered(): bool {
        return self::$isRegistered;
    }

    /**
     * Registers the form hooker with the plugin manager.
     * @param Plugin $registrant
     * @return void
     * @throws Exception
     */
    public static function register(Plugin $registrant): void {
        if (self::$isRegistered) {
            throw new Exception("FormHooker is already registered.");
        }

        self::$instance = new self();
        Server::getInstance()->getPluginManager()->registerEvents(self::$instance, $registrant);
        self::$isRegistered = true;
    }

    /**
     * Handles player form responses.
     * @param DataPacketReceiveEvent $event
     * @return void
     * @throws \ReflectionException
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $origin = $event->getOrigin();
        $packet = $event->getPacket();

        if (!$packet instanceof ModalFormResponsePacket || !$origin->isConnected()) {
            return;
        }

        if ($packet->formData === null && $packet->cancelReason === null) {
            $origin->disconnectWithError("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
            return;
        }

        $player = $origin->getPlayer();
        if (!$player instanceof Player) {
            $event->cancel();
            return;
        }

        $forms = (new ReflectionClass(Player::class))->getProperty("forms")->getValue($player);
        $form = $forms[$packet->formId] ?? null;

        if (!$form instanceof IForm) {
            return;
        }

        $event->cancel();
        $form->handle($player, $packet);
    }

    /**
     * Intercepts outgoing form requests and blocks the player's inventory using a fake window.
     * @param DataPacketSendEvent $event
     * @return void
     * @throws \ReflectionException
     */
    public function onDataPacketSend(DataPacketSendEvent $event): void {
        $targets = $event->getTargets();
        $packets = $event->getPackets();

        if (count($targets) !== 1 || count($packets) !== 1) return;

        $origin = array_shift($targets);
        $packet = array_shift($packets);

        if (!$packet instanceof ModalFormRequestPacket || !$origin->isConnected()) {
            return;
        }

        $player = $origin->getPlayer();
        if (!$player instanceof Player) {
            return;
        }

        $forms = (new ReflectionClass(Player::class))->getProperty("forms")->getValue($player);
        $form = $forms[$packet->formId] ?? null;

        if (!$form instanceof IForm) {
            return;
        }

        // Close any open inventory and apply a fake one to block access during the form
        $player->removeCurrentWindow();
        (new ReflectionClass(Player::class))->getProperty("currentWindow")->setValue(
            $player,
            new FakeInventoryWindow($form->getUniqueId())
        );
    }
}
