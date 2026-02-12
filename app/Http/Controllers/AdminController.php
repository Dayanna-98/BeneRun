<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;

class AdminController extends Controller
{

public function index(Request $request)
    {
        $admins = Admin::with('user')->get();
        return response()->json($admins);
    }

    public function show($id)
    {
        $admin = Admin::with('user')->find($id);

        if ($admin) {
            return response()->json($admin);
        }

        return response()->json([
            'message' => 'Admin inexistant'
        ], 404);
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'est_organisateur_admin' => 'required|boolean',
            'permission_admin' => 'required|in:visualiser_benevoles,suppression_benevoles,creer_course,attribuer_badge,creer_certificat',
            'id_utilisateur' => 'required|exists:users,id_utilisateur'
        ]);

        $admin = new Admin;
        $admin->id_utilisateur = $request->id_utilisateur;
        $admin->est_organisateur_admin = $request->est_organisateur_admin;
        $admin->permission_admin = $request->permission_admin;
        $admin->save();

        return response()->json([
            'message' => 'Admin créé',
            'admin' => $admin
        ], 201);
    }

 
    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Admin inexistant'
            ], 404);
        }

        $request->validate([
            'est_organisateur_admin' => 'boolean',
            'permission_admin'       => 'string',
            'id_utilisateur'         => 'exists:users,id_utilisateur'
        ]);

        $admin->update($request->only([
            'est_organisateur_admin',
            'permission_admin',
            'id_utilisateur'
        ]));

        return response()->json([
            'message' => 'Admin mis à jour',
            'admin'   => $admin
        ], 200);
    }
 
    public function destroy($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'message' => 'Admin inexistant'
            ], 404);
        }

        $admin->delete();

        return response()->json([
            'message' => 'Admin supprimé'
        ], 200);
        }
    
    
}
