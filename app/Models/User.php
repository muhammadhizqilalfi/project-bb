<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'nip',
        'name',
        'password',
        'role', // example: 'admin', 'staff'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}