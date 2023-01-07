<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Entreprise extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'entreprises';

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

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'nom' => $this->nom,
            'codePostal' => (int) $this->codePostal,
            'libelleCommune' => $this->libelleCommune,
        ];
    }
}
