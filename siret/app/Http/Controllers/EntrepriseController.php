<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EntrepriseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entreprises = Entreprise::all();
        return [
            'statut' => 1,
            'datas' => $entreprises
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
        */
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $siret
     * @return \Illuminate\Http\Response
     */
    public function show($siret)
    {
        $entreprise = Entreprise::query()->where('siret', $siret)->first();

        if ($entreprise == null) {
            return [
                'statut' => 2,
                'error' => 'STR_SIRET_NOT_FOUND_IN_DATABASE'
            ];
        }

        return [
            'statut' => 1,
            'datas' => $entreprise,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $siret
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $siret)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $siret
     * @return \Illuminate\Http\Response
     */
    public function destroy($siret)
    {
        //
    }


    /**
     * Retourne le code TVA d'après le siren (9 chiffres).
     *
     * @param  int  $siren
     * @return string $tvaCode
     */
    public function getTVACode($siren):string
    {
        // https://fr.wikipedia.org/wiki/Code_Insee#Num%C3%A9ro_de_TVA_intracommunautaire
        $tvaKey = (12 + 3 * ($siren % 97)) % 97;
        return sprintf('FR%02d%d', $tvaKey, $siren);
    }


    public function insee($siret)
    {
        // clé perso de l'api Insee
        $token = "cf20aa72-d7a3-3995-b7d0-196f7f805e1b";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->withOptions(['verify' => false])->get('https://api.insee.fr/entreprises/sirene/V3/siret/'.$siret);
        
        $inseeJson = $response->json();
        
        if ($inseeJson['header']['statut'] != 200) {
            return [
                'statut' => 2,
                'error' => 'STR_SIRET_NOT_FOUND_IN_INSEE_API'
            ];
        }

        $datas = [
            'siret' => $siret,
            'siren' => $inseeJson['etablissement']['siren'],
            'tva' => $this->getTVACode($inseeJson['etablissement']['siren']),
            'numeroVoie' => $inseeJson['etablissement']['adresseEtablissement']['numeroVoieEtablissement'],
            'typeVoie' => $inseeJson['etablissement']['adresseEtablissement']['typeVoieEtablissement'],
            'libelleVoie' => $inseeJson['etablissement']['adresseEtablissement']['libelleVoieEtablissement'],
            'codePostal' => $inseeJson['etablissement']['adresseEtablissement']['codePostalEtablissement'],
            'libelleCommune' => $inseeJson['etablissement']['adresseEtablissement']['libelleCommuneEtablissement'],
            'dateCreation' => $inseeJson['etablissement']['dateCreationEtablissement'],
        ];
        return [
            'statut' => 1,
            'datas' => $datas,
        ];
    }
}
