<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;


class CourseController extends Controller
{
      public function index(Request $request)
{
    // Récupère toutes les courses
    $courses = Course::all();
    return response()->json($courses);
}

    public function show($id)
    {
        $course = Course::find($id);
        if (!empty($course))
        {
            return response()->json($course);
        } else {
            return response()->json([
                "message"=>"Course inexistante"
            ], 404);
        }
    }
 
    public function store(Request $request) // Ajouter une course
    {
        $validated = $request->validate([ 
            'nom_course' => 'required|string|max:255',
            'lieu_course' => 'required|string|max:255',
            'informations_course' => 'required|string',
            'date_debut_course' => 'required|date_format:Y-m-d H:i:s',
            'date_fin_course' => 'required|date_format:Y-m-d H:i:s|after:date_debut_course',
            'annule_course' => 'required|boolean',
            'publie_course' => 'required|boolean',
        ]);

        $course = Course::create($validated);

        return response()->json(['message' => 'Course crée', 'course' => $course],201);
    }

    public function update(Request $request, $id) // Modifier un certificat en le recherchant selon son id
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['message' => 'Course inexistante'], 404);
        }

        $validated = $request->validate([
            'nom_course' => 'required|string|max:255',
            'lieu_course' => 'required|string|max:255',
            'date_debut_course' => 'required|date',
            'date_fin_course' => 'required|date|after:date_debut_course',
            'annule_course' => 'required|boolean',
            'publie_course' => 'required|boolean',
        ]);

        $course->update($validated);

        return response()->json(['message' => 'Course mise à jour', 'course'   => $course], 200);
    }
 
    public function destroy($id) // Supprimer une course
    {
        if(Course::where('id_course', $id)->exists()){
            $course = Course::find($id);
            $course->delete();
            return response()->json(['message'=>'Course supprimée'], 200);
        } else {
            return response()->json(['message'=>'Course inexistante'], 404);
        }
    }

}
