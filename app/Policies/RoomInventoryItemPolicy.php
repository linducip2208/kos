<?php

namespace App\Policies;

use App\Models\RoomInventoryItem;

class RoomInventoryItemPolicy extends BasePolicy
{
    protected string $viewGate = 'inventory.view';
    protected string $manageGate = 'inventory.manage';
}
