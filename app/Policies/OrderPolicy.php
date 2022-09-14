<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;


    /**
     * Create a new policy instance.
     *
     * @return void
     */

    public function create()
    {
        return false;
    }

    public function view()
    {
        return true;
    }

    public function update()
    {
        return true;
    }
}
