<?php

namespace Zwuiix\FormRecode;

use Closure;
use pocketmine\form\Form as PMForm;
use pocketmine\network\mcpe\protocol\ClientboundCloseFormPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\player\Player;
use Zwuiix\FormRecode\inventory\FakeInventoryWindow;
use ReflectionClass;
use JsonException;

/**
 * Base class for custom forms.
 * Internal usage only.
 *
 * @internal
 */
abstract class IForm implements PMForm
{
    private const MAX_RESPONSE_LENGTH = 2048;

    /** @var string Unique identifier used to match the fake inventory window */
    private string $uniqueId;

    /** @var Closure|null Callback when the form is closed without a response */
    private ?Closure $onClose = null;

    /**
     * @param string $title
     */
    public function __construct(
        private string $title = ""
    ) {
        $this->uniqueId = uniqid();
    }

    /**
     * Returns the form's unique ID (used to verify inventory state).
     * @return string
     */
    public function getUniqueId(): string {
        return $this->uniqueId;
    }

    /**
     * Get the response depth allowed for decoding the form response JSON.
     * @return int
     */
    abstract public function getResponseDepth(): int;

    /**
     * Process the decoded form response.
     * @param Player $player
     * @param mixed $responseData
     * @return void
     */
    abstract public function process(Player $player, mixed $responseData): void;

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    /**
     * Sets a callback to be executed if the player closes the form without answering.
     * @param Closure $closure
     * @return $this
     */
    final public function onClose(Closure $closure): IForm {
        $this->onClose = $closure;
        return $this;
    }

    /**
     * Called when the form response is received.
     * @param Player $player
     * @param ModalFormResponsePacket $pk
     * @return void
     */
    final public function handle(Player $player, ModalFormResponsePacket $pk): void {
        if (!$player->isConnected()) return;

        $reflect = new ReflectionClass(Player::class);
        $forms = $reflect->getProperty("forms")->getValue($player);

        $currentWindow = $player->getCurrentWindow();
        if (!$currentWindow instanceof FakeInventoryWindow || $currentWindow->getUniqueId() !== $this->uniqueId) {
            // Mismatched or missing inventory window — possible spoof or desync
            $player->getNetworkSession()->disconnectWithError("Failed to handle form");
            return;
        }

        if ($pk->cancelReason !== null) {
            // Form was closed without input
            if ($this->onClose !== null) {
                ($this->onClose)($player);
            }
        } elseif ($pk->formData !== null) {
            if (strlen($pk->formData) > self::MAX_RESPONSE_LENGTH) {
                $player->getNetworkSession()->disconnectWithError("Form response too large.");
                return;
            }

            try {
                $responseData = json_decode($pk->formData, true, $this->getResponseDepth(), JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $player->getNetworkSession()->disconnectWithError("Invalid form response.");
                return;
            }

            $this->process($player, $responseData);
        }

        // Unregister the form
        unset($forms[$pk->formId]);
        $reflect->getProperty("forms")->setValue($player, $forms);

        // Close fake inventory lock
        $player->removeCurrentWindow();

        // Close form
        $player->getNetworkSession()->sendDataPacket(ClientboundCloseFormPacket::create());
    }

    /**
     * Sends the form to the given player.
     * @param Player $player
     * @return void
     * @throws JsonException
     */
    final public function send(Player $player): void {
        if (!$player->isConnected()) return;

        $reflect = new ReflectionClass(Player::class);
        $formId = $reflect->getProperty("formIdCounter");
        $forms = $reflect->getProperty("forms");

        $id = $formId->getValue($player);
        $formId->setValue($player, ++$id);

        $formList = $forms->getValue($player);
        $formList[$id] = $this;
        $forms->setValue($player, $formList);

        $player->getNetworkSession()->onFormSent($id, $this);
    }

    /**
     * Useless (PMMP override only).
     */
    final public function handleResponse(Player $player, $data): void {
        // Unused due to custom hooker system
    }
}
