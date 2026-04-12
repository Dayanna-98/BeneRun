<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

public function login(Request $request)
{
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }

    if ($user->est_suspendu_utilisateur) {
        return response()->json([
            'message' => 'Ce compte est suspendu.'
        ], 403);
    }

    return response()->json([
        'message' => 'Connexion réussie',
        'user' => $user
    ], 200);
}

public function index(Request $request)
{
    // Récupère tous les utilisateurs
    $users = User::all();
    return response()->json($users);
}

    public function show($id)
    {
        $user = User::find($id);
        if (!empty($user))
        {
            return response()->json($user);
        } else {
            return response()->json([
                "message"=>"User inexistant"
            ], 404);
        }
    }
 
    public function store(Request $request)
    {
        $user = new User;
        $user->nom_utilisateur = $request->nom_utilisateur;
        $user->prenom_utilisateur = $request->prenom_utilisateur;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role_utilisateur = $request->role_utilisateur ?? 'bénévole';
        $user->telephone_utilisateur = $request->telephone_utilisateur;
        $user->adresse_utilisateur = $request->adresse_utilisateur;
        $user->date_naissance_utilisateur = $request->date_naissance_utilisateur;
        $user->allergies_utilisateur = $request->allergies_utilisateur;
        $user->problemes_sante_utilisateur = $request->problemes_sante_utilisateur;
        $user->possede_permis_utilisateur = (bool) $request->possede_permis_utilisateur;
        $user->est_motorise_utilisateur = (bool) $request->est_motorise_utilisateur;
        $user->possede_vehicule_utilisateur = (bool) $request->possede_vehicule_utilisateur;
        $user->taille_tshirt_utilisateur = $request->taille_tshirt_utilisateur;
        $user->est_anonyme_utilisateur = (bool) $request->est_anonyme_utilisateur;
        $user->est_suspendu_utilisateur = (bool) $request->est_suspendu_utilisateur;
        $user->raison_suspension_utilisateur = $request->raison_suspension_utilisateur;
        $user->permissions_utilisateur = $request->permissions_utilisateur;
        $user->nombre_missions_utilisateur = $request->nombre_missions_utilisateur ?? 0;

        $user->save();
        return response()->json([
            'message'=>'User ajouté',
            'user' => $user 
        ], 200);
    }
 
    public function update(Request $request, $id)
    {
        if (User::where('id_utilisateur', $id)->exists())
        {
            $user = User::find($id);
            $user->nom_utilisateur = $request->nom_utilisateur;
            $user->prenom_utilisateur = $request->prenom_utilisateur;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->role_utilisateur = $request->role_utilisateur ?? $user->role_utilisateur;
            $user->telephone_utilisateur = $request->telephone_utilisateur;
            $user->adresse_utilisateur = $request->adresse_utilisateur;
            $user->date_naissance_utilisateur = $request->date_naissance_utilisateur;
            $user->allergies_utilisateur = $request->allergies_utilisateur;
            $user->problemes_sante_utilisateur = $request->problemes_sante_utilisateur;
            $user->possede_permis_utilisateur = (bool) $request->possede_permis_utilisateur;
            $user->est_motorise_utilisateur = (bool) $request->est_motorise_utilisateur;
            $user->possede_vehicule_utilisateur = (bool) $request->possede_vehicule_utilisateur;
            $user->taille_tshirt_utilisateur = $request->taille_tshirt_utilisateur;
            $user->est_anonyme_utilisateur = (bool) $request->est_anonyme_utilisateur;
            $user->est_suspendu_utilisateur = (bool) $request->est_suspendu_utilisateur;
            $user->raison_suspension_utilisateur = $request->raison_suspension_utilisateur;
            $user->permissions_utilisateur = $request->permissions_utilisateur;
            $user->nombre_missions_utilisateur = $request->nombre_missions_utilisateur ?? 0;


            $user->save();
            return response()->json([
                'message'=>'User mis à jour',
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'message'=>'User inexistant'
            ], 404);
        }
    }
 
    public function destroy($id)
    {
        if (User::where('id_utilisateur', $id)->exists())
        {
            $user = User::find($id);
            $user->delete();
            return response()->json([
                'message'=>'User supprimé'
            ], 200);
        } else {
            return response()->json([
                'message'=>'User inexistant'
            ], 404);
        }
    } 
    
}
