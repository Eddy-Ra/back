<?php

namespace App\Models\ApiResponse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Response extends Model
{
    use HasFactory;

    protected $table = 'reponsesEmailReponse'; 

    protected $fillable = [
        'expediteur',
        'sujet',
        'contenu',
        'mail_original',
        'statut',
        'date_reponse',
        'entreprise_id',
        'categorie_id'
    ];

    protected $casts = [
        'date_reponse' => 'datetime',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
}
