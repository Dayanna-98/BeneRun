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

    $telephone = new Telephone;
    $telephone->id_mission = $request->id_mission;
    $telephone->id_course = $request->id_course;
    $telephone->description_telephone = $request->description_telephone;
    $telephone->numero_telephone = $request->numero_telephone;
    $telephone->detail_telephone = $request->detail_telephone;

    $telephone->save();

    return response()->json([
        'message' => 'Téléphone créé',
        'telephone' => $telephone
        ], 201);
    }


    public function update(Request $request, $id) // Modifier un téléphone
    {
    if (!Telephone::where('id_telephone', $id)->exists()) {
        return response()->json([
            'message' => 'Téléphone inexistant'
        ], 404);
    }

    $telephone = Telephone::find($id);
    $telephone->id_mission = $request->id_mission;
    $telephone->id_course = $request->id_course;
    $telephone->description_telephone = $request->description_telephone;
    $telephone->numero_telephone = $request->numero_telephone;
    $telephone->detail_telephone = $request->detail_telephone;

    $telephone->save();

    return response()->json([
        'message' => 'Téléphone mis à jour',
        'telephone' => $telephone
        ], 200);
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
