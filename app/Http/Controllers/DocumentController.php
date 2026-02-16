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

    public function store(Request $request)
    {
        $document= new Document();
        $document->id_mission = $request->id_mission;
        $document->id_course = $request->id_course;
        $document->nom_fichier_document = $request->nom_fichier_document;
        $document->type_mime_document = $request->type_mime_document;
        $document->date_document = $request->date_document;
        
        $document->save();
        return response()->json([
            'message'=>'Document ajouté'
        ], 200);
    }

        public function update(Request $request, $id)
    {
        if (Document::where('id_course', $id)->exists())
        {
            $document = Document::find($id);
            $document->id_mission = $request->id_mission;
            $document->id_course = $request->id_course;
            $document->nom_fichier_document = $request->nom_fichier_document;
            $document->type_mime_document = $request->type_mime_document;
            $document->date_document = $request->date_document;
     
            $document->save();
            return response()->json([
                'message'=>'Document mise à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Document inexistante'
            ], 404);
        }
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
