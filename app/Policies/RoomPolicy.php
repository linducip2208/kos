<?php

namespace App\Policies;

use App\Models\Room;

class RoomPolicy extends BasePolicy
{
    protected string $viewGate = 'room.view';
    protected string $manageGate = 'room.manage';
}
