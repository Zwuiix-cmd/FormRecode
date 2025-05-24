<?php

namespace Zwuiix\FormRecode\elements\v786;

use Zwuiix\FormRecode\elements\Element;

class Divider extends Element
{
    /**
     * @param string $text The text displayed on the divider.
     */
    public function __construct(
        private string $text,
    ) {}

    /**
     * Gets the divider text.
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Sets the divider text.
     * @param string $text
     * @return void
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Serializes the divider to JSON format.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => "divider",
            "text" => $this->text
        ];
    }
}
