<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;


class CourseController extends Controller
{
      public function index(Request $request)
{
    // Récupère tous les utilisateurs
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
 
    public function store(Request $request)
    {
       	
    //    protected $fillable = ['nom_course', 'lieu_course', 'informations_course', 'date_debut_course', 'date_fin_course', 'heure_debut_course', 'heure_fin_course', 'annule_course','date_annulation_course', 'raison_annulation_course', 'publie_course' ];

        $course= new Course;
        $course->nom_course = $request->nom_course;
        $course->lieu_course = $request->lieu_course;
        $course->informations_course = $request->informations_course;
        $course->date_debut_course = $request->date_debut_course;
        $course->date_fin_course = $request->date_fin_course;
        $course->heure_debut_course = $request->heure_debut_course;
        $course->heure_fin_course = $request->heure_fin_course;
        $course->annule_course = $request->annule_course;
        $course->date_annulation_course = $request->date_annulation_course;
        $course->raison_annulation_course = $request->raison_annulation_course;
        $course->publie_course = $request->publie_course;


        $request->validate([
            'nom_course' => 'required|string|max:255',
            'lieu_course' => 'required|string|max:255',
            'date_debut_course' => 'required|date',
            'date_fin_course' => 'required|date',
            'heure_debut_course' => 'required|date_format:H:i:s',
            'heure_fin_course' => 'required|date_format:H:i:s',
            'annule_course' => 'required|boolean',
            'publie_course' => 'required|boolean',
        ]);

        $course->save();
        return response()->json([
            'message'=>'Course ajoutée'
        ], 200);
    }
 
    public function update(Request $request, $id)
    {
        if (Course::where('id_course', $id)->exists())
        {
            $course = Course::find($id);
            $course->nom_course = $request->nom_course;
            $course->lieu_course = $request->lieu_course;
            $course->informations_course = $request->informations_course;
            $course->date_debut_course = $request->date_debut_course;
            $course->date_fin_course = $request->date_fin_course;
            $course->heure_debut_course = $request->heure_debut_course;
            $course->heure_fin_course = $request->heure_fin_course;
            $course->annule_course = $request->annule_course;
            $course->date_annulation_course = $request->date_annulation_course;
            $course->raison_annulation_course = $request->raison_annulation_course;
            $course->publie_course = $request->publie_course;
     



            $course->save();
            return response()->json([
                'message'=>'Course mise à jour'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Course inexistante'
            ], 404);
        }
    }
 
    public function destroy($id)
    {
        if (Course::where('id_course', $id)->exists())
        {
            $course = Course::find($id);
            $course->delete();
            return response()->json([
                'message'=>'Course supprimée'
            ], 200);
        } else {
            return response()->json([
                'message'=>'Course inexistante'
            ], 404);
        }
    } 

}
