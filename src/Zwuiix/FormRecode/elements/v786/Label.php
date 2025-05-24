<?php

namespace Zwuiix\FormRecode\elements\v786;

use Zwuiix\FormRecode\elements\Element;

class Label extends Element
{
    public function __construct(
        private string $text,
    ) {}

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return ["type" => "label", "text" => $this->text];
    }
}