<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;

class EntrepriseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $entrepises = Entreprise::all();
        return [
            'status' => 1,
            'datas' => $entrepises
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
        $entrepise = Entreprise::query()->where('siret', $siret)->first();

        if ($entrepise == null) {
            return [
                'status' => 2,
                'error' => 'STR_SIRET_NOT_FOUND_IN_DATABASE'
            ];
        }

        return [
            'status' => 1,
            'datas' => $entrepise,
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
    public function getTVACode($siren):string {
        // https://fr.wikipedia.org/wiki/Code_Insee#Num%C3%A9ro_de_TVA_intracommunautaire
        $tvaKey = (12 + 3 * ($siren % 97)) % 97;
        return sprintf('FR%02d%d', $tvaKey, $siren);
    }
}
