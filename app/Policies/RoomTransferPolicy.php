<?php

namespace App\Policies;

use App\Models\RoomTransfer;

class RoomTransferPolicy extends BasePolicy
{
    protected string $viewGate = 'checkin.view';
    protected string $manageGate = 'checkin.manage';
}
