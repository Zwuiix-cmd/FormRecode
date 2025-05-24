<?php

namespace Zwuiix\FormRecode\types;

use pocketmine\player\Player;
use Zwuiix\FormRecode\elements\Button;
use Zwuiix\FormRecode\elements\Element;
use Zwuiix\FormRecode\elements\Image;
use Zwuiix\FormRecode\IForm;

class Form extends IForm
{
    private ?\Closure $onSubmit = null;

    /**
     * @param string $title
     * @param string $description
     * @param Button[] $buttons
     * @param Element[] $elements
     */
    private function __construct(
        string $title,
        private string $description,
        private array $buttons,
        private array $elements,
    )
    {
        parent::__construct($title);
    }

    /**
     * @param string $title
     * @param string $description
     * @param Button[] $buttons
     * @param Element[] $elements
     * @return self
     */
    public static function create(string $title = "", string $description = "", array $buttons = [], array $elements = []): Form
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
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param \Closure $closure
     * @return Form
     */
    public function onSubmit(\Closure $closure): self
    {
        $this->onSubmit = $closure;
        return $this;
    }

    /**
     * @param Button ...$buttons
     * @return void
     */
    public function addButtons(Button ...$buttons): void
    {
        $this->buttons += $buttons;
    }

    /**
     * @param string $text
     * @param string|int|null $label
     * @param Image|null $image
     * @return void
     */
    public function addButton(string $text, string|int|null $label = null, ?Image $image = null): void
    {
        $id = count($this->buttons);
        $this->buttons[] = new Button($id, $label !== null ? $label : $id, $text, $image);
    }

    /**
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * @param Element ...$elements
     * @return void
     */
    public function setElements(Element ...$elements): void
    {
        $this->elements = $elements;
    }

    /**
     * @return int
     */
    public function getResponseDepth(): int
    {
        return 1;
    }

    public function process(Player $player, mixed $responseData): void
    {
        if(!is_int($responseData)) return;
        if(!isset($this->buttons[$responseData])) return;
        $button = $this->buttons[$responseData];
        if($this->onSubmit !== null) {
            ($this->onSubmit)($player, $button);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return ["type" => "form", "title" => $this->getTitle(), "content" => $this->description, "buttons" => $this->buttons, "elements" => $this->elements];
    }
}