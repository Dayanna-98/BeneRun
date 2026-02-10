<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{

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
        $user->password = $request->password;


        $user->save();
        return response()->json([
            'message'=>'User ajouté'
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
            $user->password = $request->password;



            $user->save();
            return response()->json([
                'message'=>'User mis à jour'
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
