<?php

namespace App\Policies;

use App\Models\Lease;

class LeasePolicy extends BasePolicy
{
    protected string $viewGate = 'lease.view';
    protected string $manageGate = 'lease.manage';
}
