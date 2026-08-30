<?php

namespace App\Policies;

use App\Models\RoomChecklist;

class RoomChecklistPolicy extends BasePolicy
{
    protected string $viewGate = 'checkin.view';
    protected string $manageGate = 'checkin.manage';
}
