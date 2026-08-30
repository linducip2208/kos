<?php

namespace App\Policies;

use App\Models\Occupant;

class OccupantPolicy extends BasePolicy
{
    protected string $viewGate = 'tenant.view';
    protected string $manageGate = 'tenant.manage';
}
