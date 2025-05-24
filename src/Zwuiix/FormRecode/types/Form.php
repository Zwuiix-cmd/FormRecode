<?php

namespace Zwuiix\FormRecode\types;

use Closure;
use pocketmine\player\Player;
use Zwuiix\FormRecode\elements\Button;
use Zwuiix\FormRecode\elements\Element;
use Zwuiix\FormRecode\elements\Image;
use Zwuiix\FormRecode\IForm;

class Form extends IForm
{
    /** @var Closure|null Callback called when a button is clicked */
    private ?Closure $onSubmit = null;

    /**
     * @param string $title
     * @param string $description
     * @param Button[] $buttons
     * @param Element[] $elements
     */
    private function __construct(
        string $title,
        private string $description,
        private array $buttons = [],
        private array $elements = [],
    ) {
        parent::__construct($title);
    }

    /**
     * Factory method to create a new Form.
     * @param string $title
     * @param string $description
     * @param Button[] $buttons
     * @param Element[] $elements
     * @return Form
     */
    public static function create(string $title = "", string $description = "", array $buttons = [], array $elements = []): self
    {
        return new self($title, $description, $buttons, $elements);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Sets a callback for when a button is clicked.
     * @param Closure $closure
     * @return $this
     */
    public function onSubmit(Closure $closure): self
    {
        $this->onSubmit = $closure;
        return $this;
    }

    /**
     * Adds multiple buttons to the form.
     * @param Button ...$buttons
     * @return void
     */
    public function addButtons(Button ...$buttons): void
    {
        $this->buttons = array_merge($this->buttons, $buttons);
    }

    /**
     * Adds a single button to the form.
     * @param string $text
     * @param string|int|null $label
     * @param Image|null $image
     */
    public function addButton(string $text, string|int|null $label = null, ?Image $image = null): void
    {
        $id = count($this->buttons);
        $this->buttons[] = new Button($id, $label ?? $id, $text, $image);
    }

    /**
     * Returns all associated UI elements.
     * @return Element[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * Sets all elements for this form.
     * @param Element ...$elements
     * @return void
     */
    public function setElements(Element ...$elements): void
    {
        $this->elements = $elements;
    }

    public function getResponseDepth(): int
    {
        return 1;
    }

    /**
     * Handles the player's response (button click).
     * @param Player $player
     * @param mixed $responseData
     * @return void
     */
    public function process(Player $player, mixed $responseData): void
    {
        if (!is_int($responseData) || !isset($this->buttons[$responseData])) return;

        $button = $this->buttons[$responseData];
        if ($this->onSubmit !== null) {
            ($this->onSubmit)($player, $button);
        }
    }

    /**
     * Serializes the form to JSON.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "form",
            "title" => $this->getTitle(),
            "content" => $this->description,
            "buttons" => $this->buttons,
            "elements" => $this->elements,
        ];
    }
}
