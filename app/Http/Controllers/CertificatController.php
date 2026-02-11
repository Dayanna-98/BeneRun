<?php

namespace App\Http\Controllers;

use App\Models\Certificat;
use Illuminate\Http\Request;

class CertificatController extends Controller
{
    public function index()// Récupérer tous les certificats
    {
        $certificats = Certificat::all();
        return response()->json($certificats);
    }

    public function show($id) // Rechercher un certificat selon son id
    {
        $certificat = Certificat::find($id);
        if (!empty($certificat)){
            return response()->json($certificat);
        } 
        else {
            return response()->json(["message"=>"Certificat inexistant"], 404);
        }
    }

    public function store(Request $request) // Ajouter un certificat
    {
        $validated = $request->validate([
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'titre_certificat' => 'required|string',
        ]);

        $certificat = Certificat::create($validated);

        return response()->json(['message' => 'Certificat crée', 'certificat' => $certificat],201);
    }

    public function update(Request $request, $id) // Modifier un certificat en le recherchant selon son id
    {
        $certificat = Certificat::find($id);
        if (!$certificat) {
            return response()->json(['message' => 'Certificat inexistant'], 404);
        }

        $validated = $request->validate([
            'id_benevole' => 'required|exists:benevoles,id_benevole',
            'titre_certificat' => 'required|string',
        ]);

        $certificat->update($validated);

        return response()->json(['message' => 'Certificat mise à jour', 'certificat'   => $certificat], 200);
    }

    public function destroy($id) // Supprimer un certificat
    {
        if(Certificat::where('id_certificat', $id)->exists()){
            $certificat = Certificat::find($id);
            $certificat->delete();
            return response()->json(['message'=>'Certificat supprimé'], 200);
        } else {
            return response()->json(['message'=>'Certificat inexistant'], 404);
        }
    }

}
