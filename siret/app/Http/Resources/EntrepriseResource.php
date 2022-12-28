<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EntrepriseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'siret' => $this->siret,
            'siren' => $this->siren,
            'tva' => $this->tva,
            'nom' => $this->nom,
            'numeroVoie' => $this->numeroVoie,
            'typeVoie' => $this->typeVoie,
            'libelleVoie' => $this->libelleVoie,
            'codePostal' => $this->codePostal,
            'libelleCommune' => $this->libelleCommune,
            'dateCreation' => $this->dateCreation,
        ];
    }
}
