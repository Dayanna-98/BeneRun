<?php

namespace App\Http\Controllers;

use App\Models\Telephone;
use Illuminate\Http\Request;

class TelephoneController extends Controller
{
    public function index()// Récupérer tous les téléphones
    {
        $telephones = Telephone::all();
        return response()->json($telephones);
    }

    public function show($id) // Rechercher un téléphone selon son id
    {
        $telephone = Telephone::find($id);
        if (!empty($telephone)){
            return response()->json($telephone);
        } 
        else {
            return response()->json(["message"=>"Téléphone inexistant"], 404);
        }
    }

    public function store(Request $request) // Ajouter un téléphone
    {
        $validated = $request->validate([
            'id_mission' => 'required|exists:missions,id_mission',
            'id_course' => 'required|exists:courses,id_course',
            'description_telephone' => 'required|string',
            'numero_telephone' => 'required|long',
            'detail_telephone' => 'required|string'
        ]);

        $telephone = Telephone::create($validated);

        return response()->json(['message' => 'Téléphone crée', 'telephone' => $telephone],201);
    }

    public function update(Request $request, $id) // Modifier un téléphone en le recherchant selon son id
    {
        $telephone = Telephone::find($id);
        if (!$telephone) {
            return response()->json(['message' => 'Téléphone inexistant'], 404);
        }

        $validated = $request->validate([
            'id_mission' => 'required|exists:missions,id_mission',
            'id_course' => 'required|exists:courses,id_course',
            'description_telephone' => 'required|string',
            'numero_telephone' => 'required|long',
            'detail_telephone' => 'required|string'
        ]);

        $telephone->update($validated);

        return response()->json(['message' => 'Téléphone mise à jour', 'telephone' => $telephone], 200);
    }

    public function destroy($id) // Supprimer un téléphone
    {
        if(Telephone::where('id_telephone', $id)->exists()){
            $telephone = Telephone::find($id);
            $telephone->delete();
            return response()->json(['message'=>'Téléphone supprimée'], 200);
        } else {
            return response()->json(['message'=>'Téléphone inexistant'], 404);
        }
    }

}
