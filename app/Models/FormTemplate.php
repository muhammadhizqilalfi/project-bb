<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as Eloquent;

class FormTemplate extends Eloquent
{
    protected $connection = 'mongodb';

    protected $collection = 'form_templates';

    protected $fillable = [
        'form_type',
        'name',
        'month',
        'year',
        'latest_case_summary',
        'latest_case_saved_at',
    ];
}
