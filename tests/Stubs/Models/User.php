<?php

namespace Yoeunes\Voteable\Tests\Stubs\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Yoeunes\Voteable\Traits\CanVote;

class User extends Authenticatable
{
    use CanVote;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
