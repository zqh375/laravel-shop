<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Vyuldashev\NovaPermission\PermissionPolicy;
use Vyuldashev\NovaPermission\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, DefaultDatetimeFormat;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function before(User $user, $ability)
    {
//        if ($user->isAdministrator()) {
//            return true;
//        }
    }


    /**
     *
     * 获取用户地址
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Product::class, 'user_favorite_products')
            ->withTimestamps()
            ->orderBy('user_favorite_products.created_at', 'desc');
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function roles()
    {
        return $this->hasOne(Role::class,'role_id','id');
    }


    public function permissions()
    {
        return $this->hasOne(PermissionPolicy::class,'permission_id','id');
    }
}
