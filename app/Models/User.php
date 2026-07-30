<?php

namespace App\Models;

// Jika menggunakan paket official mongodb/laravel-mongodb:
use MongoDB\Laravel\Eloquent\Model as Eloquent;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends Eloquent implements AuthenticatableContract
{
    use Authenticatable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    protected $fillable = [
        'name',
        'nip',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}