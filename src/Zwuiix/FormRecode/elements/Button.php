<?php

namespace Zwuiix\FormRecode\elements;

use pocketmine\nbt\tag\CompoundTag;

class Button extends Element
{
    protected CompoundTag $namedTag;

    /**
     * @param int $id Unique identifier for the button.
     * @param string|int $label Label for the button, can be string or int.
     * @param string $text Text displayed on the button.
     * @param Image|null $image Optional image shown on the button.
     */
    public function __construct(
        private readonly int        $id,
        private readonly string|int $label,
        private readonly string     $text,
        private readonly ?Image     $image,
    ) {}

    /**
     * Gets the button's unique ID.
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the button's label.
     * @return int|string
     */
    public function getLabel(): int|string
    {
        return $this->label;
    }

    /**
     * Gets the button's display text.
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Gets the optional image associated with the button.
     * @return Image|null
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * Gets the NBT named tag associated with this button.
     * @return CompoundTag
     */
    public function getNamedTag(): CompoundTag
    {
        return $this->namedTag;
    }

    /**
     * Serializes the button to JSON for sending in the form.
     * @return array
     */
    public function jsonSerialize(): array
    {
        $values = ["text" => $this->text];
        if ($this->image !== null) {
            $values["image"] = $this->image;
        }
        return $values;
    }
}