<?php

namespace App\Policies;

use App\Models\RoomType;

class RoomTypePolicy extends BasePolicy
{
    protected string $viewGate = 'room.view';
    protected string $manageGate = 'room.manage';
}
