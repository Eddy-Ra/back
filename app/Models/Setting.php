<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'emailDefaut',
        'nomExpediteur',
        'frequenceEnvoi',
        'delaiEntreLots',
        'autoValidation',
        'notificationsEmail',
        'sauvegardeAuto',
        'apiKeyN8n',
    ];

    protected $casts = [
        'autoValidation' => 'boolean',
        'notificationsEmail' => 'boolean',
        'sauvegardeAuto' => 'boolean',
    ];
}