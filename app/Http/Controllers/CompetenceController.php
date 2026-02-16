<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Competence;


class CompetenceController extends Controller
{


    public function index(Request $request)
{
    // Récupère toutes les compétences
    $competences = Competence::all();
    return response()->json($competences);
}

    public function show($id)
    {
        $competences = Competence::find($id);
        if (!empty($competences))
        {
            return response()->json($competences);
        } else {
            return response()->json([
                "message"=>"Compétence inexistante"
            ], 404);
        }
    }
 
    public function store(Request $request)
    {
        $competence= new Competence;
        $competence->nom_competence = $request->nom_competence;

        $competence->save();
        return response()->json([
            'message'=>'Compétence ajoutée',
            'competence' => $competence
        ], 200);
    }
 
    public function update(Request $request, $id)
    {
        if (Competence::where('id_competence', $id)->exists())
        {
            $competence = Competence::find($id);
            $competence->nom_competence = $request->nom_competence;
     
            $competence->save();
            return response()->json([
                'message'=>'Compétence mise à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Compétence inexistante'
            ], 404);
        }
    }
 
    public function destroy($id)
    {
        if (Competence::where('id_competence', $id)->exists())
        {
            $competence = Competence::find($id);
            $competence->delete();
            return response()->json([
                'message'=>'Compétence supprimée'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Compétence inexistante'
            ], 404);
        }
    } 
}
