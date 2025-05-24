<?php

namespace Zwuiix\FormRecode\elements;

readonly class Image implements \JsonSerializable
{
    public function __construct(
        private ImageType $type,
        private string    $path,
    ) {}

    /**
     * @return ImageType
     */
    public function getType(): ImageType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return ["type" => $this->type->value, "data" => $this->path];
    }
}