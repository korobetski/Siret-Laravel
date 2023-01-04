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
        // TODO : mettre la limite de pagination en paramètre
        $entreprises = Entreprise::paginate(2)->withPath('/entreprises');
        return [
            'statut' => 200,
            'pagination' => $entreprises
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
        $entreprise = Entreprise::create($request->input());

        if ($entreprise == null) {
            return [
                'statut' => 400,
                'error' => 'STR_DATABASE_INSERT_ERROR'
            ];
        }

        return [
            'statut' => 201,
            'datas' => $entreprise,
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $entreprise = Entreprise::find($id);

        if ($entreprise == null) {
            return [
                'statut' => 404,
                'error' => 'STR_ID_NOT_FOUND_IN_DATABASE'
            ];
        }

        return [
            'statut' => 200,
            'datas' => $entreprise,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $entreprise = Entreprise::find($id);
        if ($entreprise == null) {
            return [
                'statut' => 404,
                'error' => 'STR_ID_NOT_FOUND_IN_DATABASE'
            ];
        }

        $nouvellesDonnees = $request->input();
        $entreprise->siret = $nouvellesDonnees['siret'];
        $entreprise->siren = $nouvellesDonnees['siren'];
        $entreprise->tva = $nouvellesDonnees['tva'];
        $entreprise->nom = $nouvellesDonnees['nom'];
        $entreprise->numeroVoie = $nouvellesDonnees['numeroVoie'];
        $entreprise->typeVoie = $nouvellesDonnees['typeVoie'];
        $entreprise->libelleVoie = $nouvellesDonnees['libelleVoie'];
        $entreprise->codePostal = $nouvellesDonnees['codePostal'];
        $entreprise->libelleCommune = $nouvellesDonnees['libelleCommune'];
        $entreprise->dateCreation = $nouvellesDonnees['dateCreation'];
        $entreprise->save();

        return [
            'statut' => 200,
            'datas' => $entreprise,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = Entreprise::destroy($id);
        if ($response == 0) {
            return [
                'statut' => 404,
                'error' => 'STR_DELETE_ENTRY_ERROR'
            ];
        }

        return [
            'statut' => 200,
            'datas' => 'STR_ENTRY_DELETED',
        ];
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
        $token = "96b6a150-dc78-3da0-8cf2-9a8a93453bbf";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->withOptions(['verify' => false])->get('https://api.insee.fr/entreprises/sirene/V3/siret/'.$siret);
        
        $inseeJson = $response->json();
        
        if ($inseeJson['header']['statut'] != 200) {
            return [
                'statut' => $inseeJson['header']['statut'],
                'error' => $inseeJson['header']['message'],
            ];
        }

        $datas = [
            'siret' => $siret,
            'siren' => $inseeJson['etablissement']['siren'],
            'tva' => $this->getTVACode($inseeJson['etablissement']['siren']),
            'nom' => $inseeJson['etablissement']['uniteLegale']['denominationUniteLegale'],
            'numeroVoie' => $inseeJson['etablissement']['adresseEtablissement']['numeroVoieEtablissement'],
            'typeVoie' => $inseeJson['etablissement']['adresseEtablissement']['typeVoieEtablissement'],
            'libelleVoie' => $inseeJson['etablissement']['adresseEtablissement']['libelleVoieEtablissement'],
            'codePostal' => $inseeJson['etablissement']['adresseEtablissement']['codePostalEtablissement'],
            'libelleCommune' => $inseeJson['etablissement']['adresseEtablissement']['libelleCommuneEtablissement'],
            'dateCreation' => $inseeJson['etablissement']['dateCreationEtablissement'],
        ];
        return [
            'statut' => 200,
            'datas' => $datas,
        ];
    }
}
