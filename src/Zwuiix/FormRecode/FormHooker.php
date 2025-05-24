<?php

namespace Zwuiix\FormRecode;

use Erodia\Engine;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ClientboundCloseFormPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use ReflectionException;
use Zwuiix\FormRecode\inventory\FakeInventoryWindow;

class FormHooker implements Listener
{
    /** @var bool */
    private static bool $isRegistered = false;
    private static self $hooker;

    public static function isRegistered(): bool {
        return self::$isRegistered;
    }

    /**
     * @param Plugin $registrant
     * @return void
     * @throws \Exception
     */
    public static function register(Plugin $registrant): void {
        if(self::$isRegistered) {
            throw new \Exception("Event listener is already registered by another plugin.");
        }

        self::$hooker = new self();
        Server::getInstance()->getPluginManager()->registerEvents(self::$hooker, $registrant);
        self::$isRegistered = true;
    }

    /**
     * @param DataPacketReceiveEvent $ev
     * @return void
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $ev): void
    {
        $origin = $ev->getOrigin();
        $pk = $ev->getPacket();

        if(!$pk instanceof ModalFormResponsePacket) {
            return; // Ignoring other packet
        }

        if(!$origin->isConnected()) {
            $ev->cancel();
            return;
        }

        if($pk->formData === null && $pk->cancelReason === null) {
            $origin->disconnectWithError("Expected either formData or cancelReason to be set in ModalFormResponsePacket");
            return;
        }

        $player = $origin->getPlayer();
        $reflectPlayer = new \ReflectionClass(Player::class);
        $forms = $reflectPlayer->getProperty("forms")->getValue($player);

        if(!isset($forms[$pk->formId])) {
            $ev->cancel();
            return;
        }

        $form = $forms[$pk->formId];
        if(!$form instanceof IForm) {
            // skip
            return;
        }

        $ev->cancel();
        Engine::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(fn() => $form->handle($player, $pk)), 20 * 5);
    }

    /**
     * @param DataPacketSendEvent $ev
     * @return void
     */
    public function onDataPacketSend(DataPacketSendEvent $ev): void
    {
        $targets = $ev->getTargets();
        $pks = $ev->getPackets();

        if(count($targets) > 1) return;
        if(count($pks) > 1) return;

        $origin = array_shift($targets);
        $pk = array_shift($pks);

        if(!$pk instanceof ModalFormRequestPacket) {
            return;
        }

        if(!$origin->isConnected()) {
            $ev->cancel();
            return;
        }

        $player = $origin->getPlayer();
        $reflectPlayer = new \ReflectionClass(Player::class);
        $forms = $reflectPlayer->getProperty("forms")->getValue($player);

        if(!isset($forms[$pk->formId])) {
            return;
        }

        $form = $forms[$pk->formId];
        if(!$form instanceof IForm) {
            // skip
            return;
        }

        $player->removeCurrentWindow();
        $reflectPlayer->getProperty("currentWindow")->setValue($player, new FakeInventoryWindow($form->getUniqueId()));
    }
}