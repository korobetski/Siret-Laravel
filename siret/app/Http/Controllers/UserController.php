<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 6;

        $users = Cache::remember('/users&'.json_encode($request->all()), self::CACHE_MAX_AGE, function () use ($limit) {
            return User::paginate($limit)->withPath('/users')->withQueryString();
        });
        
        return response()->json([
            'statut' => '200',
            'pagination' => $users,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed'
        ]);
        $validatedData["password"] = Hash::make($request->password);

        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'statut' => '201',
            'user' => $user->name,
            'gravatar' => "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $user->email ) ) ) . "?d=retro&s=48",
            'token' => $accessToken->token,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return response()->json([
            'statut' => '200',
            'datas' => $user,
        ], 200);
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
        $user = User::find($id);
        
        $nouvellesDonnees = $request->input();
        $user->save();
        return response()->json([
            'statut' => '200',
            'datas' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = User::destroy($id);
        return response()->json([
            'statut' => '200',
            'datas' => $response,
        ], 200);
    }

    /**
     * Créer un token pour l'utilisateur identifié
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createToken(Request $request)
    {
        $token = $request->user()->createToken($request->token_name);
        return ['token' => $token->plainTextToken];
    }

    /**
     * Retourne l'utilisateur identifié
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function user(Request $request)
    {
        return $request->user();
    }
}
