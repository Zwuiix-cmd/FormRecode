<?php

namespace Zwuiix\FormRecode;

use Closure;
use \pocketmine\form\Form as FormPM;
use pocketmine\network\mcpe\protocol\ClientboundCloseFormPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use Zwuiix\FormRecode\inventory\FakeInventoryWindow;

/**
 * @internal
 */
abstract class IForm implements FormPM
{
    private const MAX_RESPONSE_LENGTH = 2048;

    private ?Closure $onClose = null;
    private string $uniqueId;

    public function __construct(
        private string $title = ""
    ) {
        $this->uniqueId = uniqid();
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    abstract public function getResponseDepth(): int;
    abstract public function process(Player $player, mixed $responseData): void;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param Player $player
     * @param $data
     * @return void
     */
    final public function handleResponse(Player $player, $data): void
    {
        // Fuck PMMP
    }

    /**
     * @param Closure $closure
     * @return IForm
     */
    final public function onClose(Closure $closure): IForm
    {
        $this->onClose = $closure;
        return $this;
    }

    /**
     * @param Player $player
     * @param ModalFormResponsePacket $pk
     * @return void
     */
    final public function handle(Player $player, ModalFormResponsePacket $pk): void
    {
        if(!$player->isConnected()) return;

        $reflect = new \ReflectionClass(Player::class);
        $reflectForms = $reflect->getProperty("forms");
        $forms = $reflectForms->getValue($player);

        $currentWindow = $player->getCurrentWindow();
        if(!$currentWindow instanceof FakeInventoryWindow || $currentWindow->getUniqueId() !== $this->uniqueId) {
            var_dump($currentWindow);
            $player->getNetworkSession()->disconnectWithError("Failed to handle form");
            return;
        }

        if($pk->cancelReason !== null) {
            if($this->onClose !== null) {
                ($this->onClose)($player);
            }
        } else if($pk->formData !== null) {
            if(strlen($pk->formData) > self::MAX_RESPONSE_LENGTH) {
                $player->getNetworkSession()->disconnectWithError("Failed to decode form response data");
                return;
            }

            try{
                $responseData = json_decode($pk->formData, true, $this->getResponseDepth(), JSON_THROW_ON_ERROR);
            }catch(\JsonException $e){
                $player->getNetworkSession()->disconnectWithError("Failed to decode form response data");
                return;
            }

            $this->process($player, $responseData);
        }

        unset($forms[$pk->formId]);
        $reflectForms->setValue($player, $forms);
        $player->getNetworkSession()->sendDataPacket(ClientboundCloseFormPacket::create());
    }

    /**
     * @param Player $player
     * @return void
     * @throws \JsonException
     */
    final public function send(Player $player)
    {
        if(!$player->isConnected()) return;

        $reflect = new \ReflectionClass(Player::class);
        $reflectId = $reflect->getProperty("formIdCounter");
        $reflectForms = $reflect->getProperty("forms");

        $id = $reflectId->getValue($player);
        ++$id;
        $reflectId->setValue($player, $id);

        $forms = $reflectForms->getValue($player);
        $forms[$id] = $this;
        $reflectForms->setValue($player, $forms);

        $player->getNetworkSession()->onFormSent($id, $this);
    }
}