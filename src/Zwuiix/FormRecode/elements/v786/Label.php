<?php

namespace Zwuiix\FormRecode\elements\v786;

use Zwuiix\FormRecode\elements\Element;

class Label extends Element
{
    /**
     * @param string $text The text content of the label.
     */
    public function __construct(
        private string $text,
    ) {}

    /**
     * Gets the label text.
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Sets the label text.
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Serializes the label element to JSON.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "label",
            "text" => $this->text
        ];
    }
}
