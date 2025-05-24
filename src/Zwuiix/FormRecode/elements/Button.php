<?php

namespace Zwuiix\FormRecode\elements;

use pocketmine\nbt\tag\CompoundTag;

class Button extends Element
{
    protected CompoundTag $namedTag;

    public function __construct(
        private int    $id,
        private string|int $label,
        private string $text,
        private ?Image  $image,
    ) {}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int|string
     */
    public function getLabel(): int|string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return Image|null
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * @return CompoundTag
     */
    public function getNamedTag(): CompoundTag
    {
        return $this->namedTag;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $values = ["text" => $this->text];
        if($this->image !== null) $values["image"] = $this->image;
        return $values;
    }
}