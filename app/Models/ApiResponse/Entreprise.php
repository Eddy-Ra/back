<?php

namespace App\Models\ApiResponse;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $table = 'entreprisesEmailReponse'; 

    protected $fillable = [
        'nom',
        'email_contact',
        'secteur',
    ];

    public function responses()
    {
        return $this->hasMany(Response::class, 'entreprise_id');
    }
}
