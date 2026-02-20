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
        $admin = new Admin;
        $admin->id_utilisateur = $request->id_utilisateur;
        $admin->est_organisateur_admin = $request->est_organisateur_admin;
        $admin->permission_admin = $request->permission_admin;
        $admin->save();

        return response()->json([
            'message' => 'Admin créé'
        ], 200);
    }

        public function update(Request $request, $id)
    {
        if (Admin::where('id_admin', $id)->exists())
        {
            $admin = Admin::find($id);
            $admin->id_utilisateur = $request->id_utilisateur;
            $admin->est_organisateur_admin = $request->est_organisateur_admin;
            $admin->permission_admin = $request->permission_admin;

            $admin->save();
            return response()->json([
                'message'=>'Admin mise à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Admin inexistant'
            ], 404);
        }
    }

        public function destroy($id)
    {
        if (Admin::where('id_admin', $id)->exists())
        {
            $admin = Admin::find($id);
            $admin->delete();
            return response()->json([
                'message'=>'Admin supprimé'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Admin inexistant'
            ], 404);
        }
    } 
    
    
}
