<?php

namespace App\Policies;

use App\Models\Property;

class PropertyPolicy extends BasePolicy
{
    protected string $viewGate = 'property.view';
    protected string $manageGate = 'property.manage';
}
