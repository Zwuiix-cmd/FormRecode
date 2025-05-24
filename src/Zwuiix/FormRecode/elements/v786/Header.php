<?php

namespace Zwuiix\FormRecode\elements\v786;

use Zwuiix\FormRecode\elements\Element;

class Header extends Element
{
    /**
     * @param string $text The text displayed in the header.
     */
    public function __construct(
        private string $text,
    ) {}

    /**
     * Gets the header text.
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Sets the header text.
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Serializes the header element to JSON.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "header",
            "text" => $this->text
        ];
    }
}
