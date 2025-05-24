<?php

namespace Zwuiix\FormRecode\inventory;

use pocketmine\inventory\Inventory;
use pocketmine\inventory\SimpleInventory;

class FakeInventoryWindow extends SimpleInventory
{
    public function __construct(
        private readonly string $uniqueId,
    )
    {
        parent::__construct(0);
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}