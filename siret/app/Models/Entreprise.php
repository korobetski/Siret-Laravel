<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'siret',
        'siren',
        'tva',
        'nom',
        'numeroVoie',
        'typeVoie',
        'libelleVoie',
        'codePostal',
        'libelleCommune',
        'dateCreation'
    ];
}
