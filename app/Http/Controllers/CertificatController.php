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

       public function store(Request $request)
    {
        $certificat = new Certificat;
        $certificat->id_benevole = $request->id_benevole;
        $certificat->titre_certificat = $request->titre_certificat;
        $certificat->save();

        return response()->json([
            'message'=>'Certificat  ajouté',
        ], 200);
    }


        public function update(Request $request, $id)
    {
        if (Certificat::where('id_certificat', $id)->exists())
        {
            $certificat = Certificat::find($id);
            $certificat->id_benevole = $request->id_benevole;
            $certificat->titre_certificat = $request->titre_certificat;
           
            $certificat->save();
            return response()->json([
                'message'=>'Certificat mis à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Certificat inexistant'
            ], 404);
        }
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
