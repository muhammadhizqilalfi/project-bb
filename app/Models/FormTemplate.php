<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    use HasFactory;

    protected $table = 'form_templates';

    protected $fillable = [
        'form_type',
        'name',
        'month',
        'year',
        'satuanKerja' => 'required|string|max:255',
        'cases',
        'latest_case_summary',
        'latest_case_saved_at',
    ];

    protected $casts = [
        'cases' => 'array',
        'latest_case_summary' => 'array',
        'latest_case_saved_at' => 'datetime',
    ];
}
