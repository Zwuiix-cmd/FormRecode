<?php

namespace Zwuiix\FormRecode\inventory;

use pocketmine\inventory\SimpleInventory;

class FakeInventoryWindow extends SimpleInventory
{
    /**
     * @param string $uniqueId A unique identifier associated with this fake inventory window.
     */
    public function __construct(
        private readonly string $uniqueId,
    ) {
        parent::__construct(0); // Zero slots: not meant to hold items.
    }

    /**
     * Returns the unique ID tied to this fake inventory instance.
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}
