<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownOption extends Model
{
    use HasFactory;

    protected $table = 'dropdown_options';

    protected $fillable = [
        'category',
        'label',
        'form_target',
    ];
}