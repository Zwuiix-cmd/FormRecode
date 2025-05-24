<?php

namespace Zwuiix\FormRecode\elements;

use JsonSerializable;

readonly class Image implements JsonSerializable
{
    public function __construct(
        private ImageType $type,
        private string $path,
    ) {}

    /**
     * Gets the image type (GAME or URL).
     * @return ImageType
     */
    public function getType(): ImageType
    {
        return $this->type;
    }

    /**
     * Gets the image path (relative resource path or URL).
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Serializes the image to a format compatible with Minecraft's form system.
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            "type" => $this->type->value,
            "data" => $this->path
        ];
    }
}
