<?php
namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\MissionContact;
use App\Models\User;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    private function resolveResponsableId($requestedId): ?int
    {
        $requestedId = is_numeric($requestedId) ? (int) $requestedId : null;

        if ($requestedId && User::where('id_utilisateur', $requestedId)->exists()) {
            return $requestedId;
        }

        return User::query()->orderBy('id_utilisateur')->value('id_utilisateur');
    }

    private function mapTypeMission($type): string
    {
        $allowed = ['secours', 'logistique', 'accueil', 'technique', 'animation', 'autre'];
        $normalized = strtolower((string) $type);

        return in_array($normalized, $allowed, true) ? $normalized : 'autre';
    }

    public function index()// Récupérer toutes les missions
    {
        $missions = Mission::all();
        return response()->json($missions);
    }

    public function show($id) // Rechercher une mission selon son id
    {
        $mission = Mission::find($id);
        if (!empty($mission)){
            return response()->json($mission);
        } 
        else {
            return response()->json(["message"=>"Mission inexistante"], 404);
        }
    }

    public function store(Request $request)
    {
        $responsableId = $this->resolveResponsableId($request->responsable_utilisateur_id);

        if (!$responsableId) {
            return response()->json([
                'message' => 'Aucun utilisateur disponible pour creer une mission.'
            ], 422);
        }

        $mission = new Mission();
        $mission->id_evenement = $request->id_evenement;
        $mission->responsable_utilisateur_id = $responsableId;
        $mission->titre_mission = $request->titre_mission;
        $mission->type_mission = $this->mapTypeMission($request->type_mission);
        $mission->description_mission = $request->description_mission;
        $mission->date_mission = $request->date_mission;
        $mission->heure_debut_mission = $request->heure_debut_mission;
        $mission->heure_fin_mission = $request->heure_fin_mission;
        $mission->lieu_mission = $request->lieu_mission;
        $mission->latitude_mission = $request->latitude_mission;
        $mission->longitude_mission = $request->longitude_mission;
        $mission->nombre_benevoles_max = $request->nombre_benevoles_max ?? 0;
        $mission->nombre_benevoles_backup = $request->nombre_benevoles_backup ?? 0;
        $mission->statut_mission = $request->statut_mission ?? 'draft';
        $mission->inscription_requise = $request->inscription_requise ?? true;
        $mission->visibilite_mission = $request->visibilite_mission ?? 'public';
        $mission->consignes_securite = $request->consignes_securite;
        $mission->image_mission = $request->image_mission;
        $mission->publie_le_mission = $request->est_publie_mission ? now() : null;
        $mission->save();

        if ($request->filled('contact_nom') || $request->filled('contact_telephone') || $request->filled('contact_email')) {
            MissionContact::create([
                'id_mission' => $mission->id_mission,
                'nom_contact' => $request->contact_nom ?? 'Contact mission',
                'telephone_contact' => $request->contact_telephone ?? 'N/A',
                'email_contact' => $request->contact_email,
                'est_contact_principal' => true,
                'est_contact_jour_j' => false,
            ]);
        }

        return response()->json([
            'message' => 'Mission ajoutee',
            'mission' => $mission
        ], 201);
    }

    public function update(Request $request, $id) // Modifier une mission
    {
        $mission = Mission::find($id);

        if (!$mission) {
            return response()->json([
                'message' => 'Mission inexistante'
            ], 404);
        }

        $responsableId = $this->resolveResponsableId($request->responsable_utilisateur_id ?? $mission->responsable_utilisateur_id);

        if (!$responsableId) {
            return response()->json([
                'message' => 'Aucun utilisateur disponible pour mettre a jour la mission.'
            ], 422);
        }

        $mission->id_evenement = $request->id_evenement ?? $mission->id_evenement;
        $mission->responsable_utilisateur_id = $responsableId;
        $mission->titre_mission = $request->titre_mission ?? $mission->titre_mission;
        $mission->type_mission = $this->mapTypeMission($request->type_mission ?? $mission->type_mission);
        $mission->description_mission = $request->description_mission ?? $mission->description_mission;
        $mission->date_mission = $request->date_mission ?? $mission->date_mission;
        $mission->heure_debut_mission = $request->heure_debut_mission ?? $mission->heure_debut_mission;
        $mission->heure_fin_mission = $request->heure_fin_mission ?? $mission->heure_fin_mission;
        $mission->lieu_mission = $request->lieu_mission ?? $mission->lieu_mission;
        $mission->latitude_mission = $request->latitude_mission ?? $mission->latitude_mission;
        $mission->longitude_mission = $request->longitude_mission ?? $mission->longitude_mission;
        $mission->nombre_benevoles_max = $request->nombre_benevoles_max ?? $mission->nombre_benevoles_max;
        $mission->nombre_benevoles_backup = $request->nombre_benevoles_backup ?? $mission->nombre_benevoles_backup;
        $mission->statut_mission = $request->statut_mission ?? $mission->statut_mission;
        $mission->inscription_requise = $request->inscription_requise ?? $mission->inscription_requise;
        $mission->visibilite_mission = $request->visibilite_mission ?? $mission->visibilite_mission;
        $mission->consignes_securite = $request->consignes_securite ?? $mission->consignes_securite;
        $mission->image_mission = $request->image_mission ?? $mission->image_mission;
        $mission->publie_le_mission = $request->est_publie_mission
            ? ($mission->publie_le_mission ?? now())
            : null;
        $mission->save();

        if ($request->filled('contact_nom') || $request->filled('contact_telephone') || $request->filled('contact_email')) {
            MissionContact::updateOrCreate(
                ['id_mission' => $mission->id_mission, 'est_contact_principal' => true],
                [
                    'nom_contact' => $request->contact_nom ?? 'Contact mission',
                    'telephone_contact' => $request->contact_telephone ?? 'N/A',
                    'email_contact' => $request->contact_email,
                    'est_contact_jour_j' => false,
                ]
            );
        }

        return response()->json([
            'message' => 'Mission mise a jour',
            'mission' => $mission
        ], 200);
    }


    public function destroy($id) // Supprimer une mission
    {
        if(Mission::where('id_mission', $id)->exists()){
            $mission = Mission::find($id);
            $mission->delete();
            return response()->json(['message'=>'Mission supprimée'], 200);
        } else {
            return response()->json(['message'=>'Mission inexistante'], 404);
        }
    }

}
