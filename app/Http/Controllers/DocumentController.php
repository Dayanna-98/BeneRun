<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()// Récupérer tous les documents
    {
        $documents = Document::all();
        return response()->json($documents);
    }

    public function show($id) // Rechercher un document selon son id
    {
        $document = Document::find($id);
        if (!empty($document)){
            return response()->json($document);
        } 
        else {
            return response()->json(["message"=>"Document inexistant"], 404);
        }
    }

    public function store(Request $request) // Ajouter un document
    {
        $validated = $request->validate([
            'id_mission' => 'required|exists:missions,id_mission',
            'id_course' => 'required|exists:courses,id_course',
            'nom_fichier_document' => 'required|string',
            'type_mime_document' => 'required|string',
            'date_document' => 'required|date'
        ]);

        $document = Document::create($validated);

        return response()->json(['message' => 'Document crée', 'document' => $document],201);
    }

    public function update(Request $request, $id) // Modifier un document en le recherchant selon son id
    {
        $document = Document::find($id);
        if (!$document) {
            return response()->json(['message' => 'Document inexistant'], 404);
        }

        $validated = $request->validate([
            'id_mission' => 'required|exists:missions,id_mission',
            'id_course' => 'required|exists:courses,id_course',
            'nom_fichier_document' => 'required|string',
            'type_mime_document' => 'required|int',
            'date_document' => 'required|date'
        ]);

        $document->update($validated);

        return response()->json(['message' => 'Document mise à jour', 'document' => $document], 200);
    }

    public function destroy($id) // Supprimer un document
    {
        if(Document::where('id_document', $id)->exists()){
            $document = Document::find($id);
            $document->delete();
            return response()->json(['message'=>'Document supprimé'], 200);
        } else {
            return response()->json(['message'=>'Document inexistant'], 404);
        }
    }
}
