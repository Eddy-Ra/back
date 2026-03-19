<?php

namespace App\Models\ApiResponse;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table = 'categoriesEmailReponse'; 

    protected $fillable = ['nom'];

    public function responses()
    {
        return $this->hasMany(Response::class, 'categorie_id');
    }
}
