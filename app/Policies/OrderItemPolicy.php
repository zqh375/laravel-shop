<?php


namespace App\Policies;


use Illuminate\Auth\Access\HandlesAuthorization;

class OrderItemPolicy
{
    use HandlesAuthorization;

    public function delete()
    {
        return false;
    }

    public function forceDelete()
    {
        return false;
    }

}
