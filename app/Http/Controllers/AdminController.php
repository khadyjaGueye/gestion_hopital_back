<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequeste;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserServiceResource;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            "data" => [
                "users" => $users
            ]
        ]);
    }

    public function listeService()
    {
        $services = Service::all();
        return response()->json([
            "data" => [
                "services" => UserServiceResource::collection($services),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createService(ServiceRequeste $request)
    {
        $user = Service::create([
            "nom" => $request
        ]);
        return response()->json([
            "data" => [
                "status" => 200,
                "message" => "Isertion resuisie",
                "user" => $user
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        return DB::transaction(function()use($request){
            $service = Service::find($request->service_id);
               // Vérifier si l'utilisateur avec le même email existe déjà
        $existingEmail = User::where('email', $request->email)->first();
        $existingTelephone = User::where('telephone', $request->telephone)->first();
        if ($existingEmail || $existingTelephone) {
            // Utilisateur avec le même email existe déjà, retournez une réponse d'erreur
            return response()->json([
                "data" => [
                    "status" => 400,
                    "message" => "L'utilisateur avec cet email existe déjà."
                ]
            ], 400);
        }
        // L'utilisateur n'existe pas, donc nous le créons
        $user = User::create([
            "nom" => $request->nom,
            "prenom" => $request->prenom,
            "telephone" => $request->telephone,
            "email" => $request->email,
            "password" => Hash::make(12345),
            "role" => $request->role,
            "specialite" => $request->specialite,
        ]);
        $user->services()->attach($service);
        return response()->json([
            "data" => [
                "status" => 200,
                "message" => "Insertion réussie",
                "user" => $user
            ]
        ]);
        });

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = User::select('users.*')->where('id', $id)->first();
        return response()->json([
            "data" => [
                "status" => 200,
                "user" => $user,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if ($user) {
            // Vérifier si 'nom' est défini avant de l'assigner
            $nom = $request->has('nom') ? $request->nom : $user->nom;
            $prenom = $request->has('prenom') ? $request->prenom : $user->prenom;
            $telephone = $request->has('telephone') ? $request->telephone : $user->telephone;
            $email = $request->has('email') ? $request->email : $user->email;
            $specialite = $request->has('specialite') ? $request->specialite : $user->specialite;
            $password = $request->has('password') ? $request->password : $user->password;
            $role = $request->has('role') ? $request->role : $user->role;

            $user->update([
                "nom" => $nom,
                "prenom" => $prenom,
                "telephone" => $telephone,
                "email" => $email,
                "specialite" => $specialite,
                "password" => bcrypt($password),
                "role" => $role,
            ]);
            //$user = $user->fresh();
            return response()->json([
                "data" => [
                    "status" => 200,
                    "message" => "modification avec succès",
                    "user" => $user
                ]
            ]);
        } else {
            return response()->json([
                "data" => [
                    "status" => 404,
                    "message" => "Utilisateur non trouvé."
                ]
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if ($user && $user->exists()) {
            $user->delete();

            return response()->json([
                "data" => [
                    'message' => 'Utilisateur supprimé avec succès',
                    'status' => 200
                ]
            ]);
        } else {
            return response()->json([
                "data" => [
                    'message' => 'Utilisateur non trouvé',
                    'status' => 404
                ]
            ], 404);
        }
    }
}
