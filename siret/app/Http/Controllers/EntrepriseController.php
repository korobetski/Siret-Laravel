<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class EntrepriseController extends Controller
{
    const CACHE_MAX_AGE = 60;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 6;

        // ->withPath('/entreprises') permet de relativiser les url
        if ($request->has('query')) {
            $entreprises = Cache::remember('entreprises&'.json_encode($request->all()), self::CACHE_MAX_AGE, function () use ($limit, $request) {
                return Entreprise::search($request->query->get("query"))->paginate($limit)->withPath('/entreprises')->withQueryString();
            });
        } else {
            $entreprises = Cache::remember('entreprises&'.json_encode($request->all()), self::CACHE_MAX_AGE, function () use ($limit) {
                return Entreprise::paginate($limit)->withPath('/entreprises')->withQueryString();
            });
        }
        
        return response()->json([
            'statut' => '200',
            'pagination' => $entreprises,
        ], 200)->header('Cache-Control', "max_age=".self::CACHE_MAX_AGE);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // on check avant tout si le siret n'est pas déjà présent dans la base
        $entreprise = Entreprise::firstWhere('siret', $request->input()['siret']);
        if ($entreprise != null) {
            return $this->errorResponse(400, 'STR_SIRET_DUPLICATION');
        }

        // ensuite on tente d'hydrater la base
        $entreprise = Entreprise::create($request->input());
        if ($entreprise == null) {
            return $this->errorResponse(400, 'STR_DATABASE_INSERT_ERROR');
        }

        Cache::flush();
        return $this->datasResponse(201, $entreprise);
    }

    /**
     * Ajoute une entreprise dans la base uniquement avec un numéro Siret en paramètre POST
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $siret
     * @return \Illuminate\Http\Response
     */
    public function autoCreate(Request $request, $siret)
    {
        if (!is_numeric($siret) || strlen((string)$siret) != 14) {
            return $this->errorResponse(400, 'STR_SIRET_INVALID');
        }

        $inseeResponse = $this->insee($siret)->original;
        if ($inseeResponse['statut'] != 200) {
            return $inseeResponse;
        }

        return $this->store($request->replace($inseeResponse['datas']));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!is_numeric($id)) {
            return $this->errorResponse(400, 'STR_ID_MUST_BE_NUMERIC');
        }
        
        $entreprise = Cache::remember('entreprise&'.$id, self::CACHE_MAX_AGE, function () use ($id) {
            return Entreprise::find($id);
        });
        if ($entreprise == null) {
            return $this->errorResponse(404, 'STR_ID_NOT_FOUND_IN_DATABASE');
        }

        return $this->datasResponse(200, $entreprise)->header('Cache-Control', "max_age=".self::CACHE_MAX_AGE);
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
        if (!is_numeric($id)) {
            return $this->errorResponse(400, 'STR_ID_MUST_BE_NUMERIC');
        }

        $entreprise = Entreprise::find($id);
        if ($entreprise == null) {
            return $this->errorResponse(404, 'STR_ID_NOT_FOUND_IN_DATABASE');
        }
        //Cache::forget('entreprise&'.$id);
        Cache::flush();
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

        return $this->datasResponse(200, $entreprise);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!is_numeric($id)) {
            return $this->errorResponse(400, 'STR_ID_MUST_BE_NUMERIC');
        }

        $response = Entreprise::destroy($id);
        if ($response == 0) {
            return $this->errorResponse(404, 'STR_DELETE_ENTRY_ERROR');
        }

        Cache::flush();
        return $this->datasResponse(200, 'STR_ENTRY_DELETED');
    }

    /**
     * Retourne le code TVA d'après le siren (9 chiffres).
     *
     * @param  int  $siren
     * @return string $tvaCode
     */
    private function getTVACode($siren):string
    {
        // https://fr.wikipedia.org/wiki/Code_Insee#Num%C3%A9ro_de_TVA_intracommunautaire
        $tvaKey = (12 + 3 * ($siren % 97)) % 97;
        return sprintf('FR%02d%d', $tvaKey, $siren);
    }


    public function insee($siret)
    {
        if (!is_numeric($siret) || strlen((string)$siret) != 14) {
            return $this->errorResponse(400, 'STR_SIRET_INVALID');
        }

        // clé perso de l'api Insee
        $token = "96b6a150-dc78-3da0-8cf2-9a8a93453bbf";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ])->withOptions(['verify' => false])->get('https://api.insee.fr/entreprises/sirene/V3/siret/'.$siret);
        
        $inseeJson = $response->json();
        
        if ($inseeJson['header']['statut'] != 200) {
            return $this->errorResponse($inseeJson['header']['statut'], $inseeJson['header']['message']);
        }
        
        $etablissement = $inseeJson['etablissement'];
        $datas = [
            'siret' => $siret,
            'siren' => $etablissement['siren'],
            'tva' => $this->getTVACode($etablissement['siren']),
            'nom' => $etablissement['uniteLegale']['denominationUniteLegale'],
            'numeroVoie' => $etablissement['adresseEtablissement']['numeroVoieEtablissement'],
            'typeVoie' => $etablissement['adresseEtablissement']['typeVoieEtablissement'],
            'libelleVoie' => $etablissement['adresseEtablissement']['libelleVoieEtablissement'],
            'codePostal' => $etablissement['adresseEtablissement']['codePostalEtablissement'],
            'libelleCommune' => $etablissement['adresseEtablissement']['libelleCommuneEtablissement'],
            'dateCreation' => $etablissement['dateCreationEtablissement'],
        ];
        /*
        ** https://sirene.fr/static-resources/htm/v_sommaire.htm#11
        ** "denominationUniteLegale" est à null pour les personnes physiques. 
        ** par exemple : M Moutarde avec le siret 83153941600014
        */
        if (is_null($datas['nom'])) {
            $datas['nom'] = sprintf('%s %s',
                $etablissement['uniteLegale']['sexeUniteLegale'],
                $etablissement['uniteLegale']['nomUniteLegale']
            );
        }
        return $this->datasResponse(200, $datas);
    }

    /**
     * Formate les réponses json
     *
     * @param  int  $code codes de statut de réponse HTTP
     * @param  string  $datas données à transmettre
     * @return object réponse en json
     */
    private function datasResponse($code, $datas)
    {
        return response()->json([
            'statut' => $code,
            'datas' => $datas
        ], $code);
    }

    /**
     * Formate les erreurs en réponse json
     *
     * @param  int  $code codes de statut de réponse HTTP
     * @param  string  $message description de l'erreur
     * @return object réponse en json
     */
    private function errorResponse($code, $message)
    {
        return response()->json([
            'statut' => $code,
            'error' => $message
        ], $code);
    }
}
