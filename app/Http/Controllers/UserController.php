<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

private function resolveActorFromBearerToken(Request $request): ?User
{
    $actor = $request->user('sanctum');
    return $actor instanceof User ? $actor : null;
}

private function isSuperAdminRequest(Request $request): bool
{
    $actor = $this->resolveActorFromBearerToken($request);
    if ($actor !== null) {
        return $this->normalizeRole($actor->role_utilisateur) === 'superadmin';
    }

    $headerRole = $request->header('X-User-Role');
    if (!empty($headerRole) && $this->normalizeRole($headerRole) === 'superadmin') {
        return true;
    }

    return false;
}

private function normalizeRole(?string $role): string
{
    return str_replace(['-', '_', ' '], '', strtolower((string) $role));
}

public function competences($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'message' => 'User inexistant'
        ], 404);
    }

    $competences = $user->competences()->orderBy('nom_competence')->get();
    return response()->json($competences);
}

public function addCompetence(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'message' => 'User inexistant'
        ], 404);
    }

    $validated = $request->validate([
        'id_competence' => 'required|integer|exists:competences,id_competence',
    ]);

    $alreadyLinked = $user->competences()
        ->where('competences.id_competence', $validated['id_competence'])
        ->exists();

    if ($alreadyLinked) {
        return response()->json([
            'message' => 'Compétence déjà associée à cet utilisateur'
        ], 409);
    }

    $user->competences()->attach($validated['id_competence']);

    return response()->json([
        'message' => 'Compétence ajoutée au profil'
    ], 201);
}

public function removeCompetence($id, $competenceId)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'message' => 'User inexistant'
        ], 404);
    }

    $exists = $user->competences()
        ->where('competences.id_competence', $competenceId)
        ->exists();

    if (!$exists) {
        return response()->json([
            'message' => 'Compétence non associée à cet utilisateur'
        ], 404);
    }

    $user->competences()->detach($competenceId);

    return response()->json([
        'message' => 'Compétence supprimée du profil'
    ], 200);
}

public function badges($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'message' => 'User inexistant'
        ], 404);
    }

    $badges = $user->badges()->orderBy('titre_badge')->get();
    return response()->json($badges);
}

public function addBadge(Request $request, $id)
{
    if (!$this->isSuperAdminRequest($request)) {
        return response()->json([
            'message' => 'Action réservée aux super-admins'
        ], 403);
    }

    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'message' => 'User inexistant'
        ], 404);
    }

    $validated = $request->validate([
        'id_badge' => 'required|integer|exists:badges,id_badge',
    ]);

    $alreadyLinked = $user->badges()
        ->where('badges.id_badge', $validated['id_badge'])
        ->exists();

    if ($alreadyLinked) {
        return response()->json([
            'message' => 'Badge déjà attribué à cet utilisateur'
        ], 409);
    }

    $user->badges()->attach($validated['id_badge'], [
        'attribue_le' => now(),
    ]);

    return response()->json([
        'message' => 'Badge attribué au profil'
    ], 201);
}

public function removeBadge(Request $request, $id, $badgeId)
{
    if (!$this->isSuperAdminRequest($request)) {
        return response()->json([
            'message' => 'Action réservée aux super-admins'
        ], 403);
    }

    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'message' => 'User inexistant'
        ], 404);
    }

    $exists = $user->badges()
        ->where('badges.id_badge', $badgeId)
        ->exists();

    if (!$exists) {
        return response()->json([
            'message' => 'Badge non associé à cet utilisateur'
        ], 404);
    }

    $user->badges()->detach($badgeId);

    return response()->json([
        'message' => 'Badge supprimé du profil'
    ], 200);
}

public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }

    if ($user->est_suspendu_utilisateur) {
        return response()->json([
            'message' => 'Ce compte est suspendu.'
        ], 403);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'message' => 'Connexion réussie',
        'token' => $token,
        'user' => $user,
    ], 200);
}

public function me(Request $request)
{
    $actor = $this->resolveActorFromBearerToken($request);

    if (!$actor) {
        return response()->json([
            'message' => 'Non authentifié'
        ], 401);
    }

    return response()->json($actor, 200);
}

public function logout(Request $request)
{
    $actor = $this->resolveActorFromBearerToken($request);

    if (!$actor) {
        return response()->json([
            'message' => 'Non authentifié'
        ], 401);
    }

    $currentToken = $actor->currentAccessToken();
    if ($currentToken) {
        $currentToken->delete();
    }

    return response()->json([
        'message' => 'Déconnexion réussie'
    ], 200);
}

public function index(Request $request)
{
    $users = User::all();
    return response()->json($users);
}

    public function show($id)
    {
        $user = User::find($id);
        if (!empty($user)) {
            return response()->json($user);
        } else {
            return response()->json([
                "message" => "User inexistant"
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_utilisateur' => 'required|string|max:255',
            'prenom_utilisateur' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_utilisateur' => 'nullable|in:bénévole,responsable,admin,superadmin',
            'telephone_utilisateur' => 'nullable|string|max:255',
            'adresse_utilisateur' => 'nullable|string|max:255',
            'date_naissance_utilisateur' => 'nullable|date',
            'allergies_utilisateur' => 'nullable|string',
            'problemes_sante_utilisateur' => 'nullable|string',
            'possede_permis_utilisateur' => 'nullable|boolean',
            'est_motorise_utilisateur' => 'nullable|boolean',
            'possede_vehicule_utilisateur' => 'nullable|boolean',
            'taille_tshirt_utilisateur' => 'nullable|in:XS,S,M,L,XL',
            'est_anonyme_utilisateur' => 'nullable|boolean',
            'est_suspendu_utilisateur' => 'nullable|boolean',
            'raison_suspension_utilisateur' => 'nullable|string|max:255',
            'permissions_utilisateur' => 'nullable|string',
            'nombre_missions_utilisateur' => 'nullable|integer|min:0',
        ], [
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = new User;
        $user->nom_utilisateur = $validated['nom_utilisateur'];
        $user->prenom_utilisateur = $validated['prenom_utilisateur'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role_utilisateur = $validated['role_utilisateur'] ?? 'bénévole';
        $user->telephone_utilisateur = $validated['telephone_utilisateur'] ?? null;
        $user->adresse_utilisateur = $validated['adresse_utilisateur'] ?? null;
        $user->date_naissance_utilisateur = $validated['date_naissance_utilisateur'] ?? null;
        $user->allergies_utilisateur = $validated['allergies_utilisateur'] ?? null;
        $user->problemes_sante_utilisateur = $validated['problemes_sante_utilisateur'] ?? null;
        $user->possede_permis_utilisateur = (bool) ($validated['possede_permis_utilisateur'] ?? false);
        $user->est_motorise_utilisateur = (bool) ($validated['est_motorise_utilisateur'] ?? false);
        $user->possede_vehicule_utilisateur = (bool) ($validated['possede_vehicule_utilisateur'] ?? false);
        $user->taille_tshirt_utilisateur = $validated['taille_tshirt_utilisateur'] ?? null;
        $user->est_anonyme_utilisateur = (bool) ($validated['est_anonyme_utilisateur'] ?? false);
        $user->est_suspendu_utilisateur = (bool) ($validated['est_suspendu_utilisateur'] ?? false);
        $user->raison_suspension_utilisateur = $validated['raison_suspension_utilisateur'] ?? null;
        $user->permissions_utilisateur = $validated['permissions_utilisateur'] ?? null;
        $user->nombre_missions_utilisateur = $validated['nombre_missions_utilisateur'] ?? 0;

        $user->save();

        return response()->json([
            'message' => 'User ajouté',
            'user' => $user 
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nom_utilisateur' => 'required|string|max:255',
            'prenom_utilisateur' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id, 'id_utilisateur'),
            ],
            'password' => 'nullable|string|min:8',
            'role_utilisateur' => 'nullable|in:bénévole,responsable,admin,superadmin',
            'telephone_utilisateur' => 'nullable|string|max:255',
            'adresse_utilisateur' => 'nullable|string|max:255',
            'date_naissance_utilisateur' => 'nullable|date',
            'allergies_utilisateur' => 'nullable|string',
            'problemes_sante_utilisateur' => 'nullable|string',
            'possede_permis_utilisateur' => 'nullable|boolean',
            'est_motorise_utilisateur' => 'nullable|boolean',
            'possede_vehicule_utilisateur' => 'nullable|boolean',
            'taille_tshirt_utilisateur' => 'nullable|in:XS,S,M,L,XL',
            'est_anonyme_utilisateur' => 'nullable|boolean',
            'est_suspendu_utilisateur' => 'nullable|boolean',
            'raison_suspension_utilisateur' => 'nullable|string|max:255',
            'permissions_utilisateur' => 'nullable|string',
            'nombre_missions_utilisateur' => 'nullable|integer|min:0',
        ], [
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        if (!User::where('id_utilisateur', $id)->exists()) {
            return response()->json([
                'message' => 'User inexistant'
            ], 404);
        }

        $user = User::find($id);

        if (
            array_key_exists('role_utilisateur', $validated)
            && $validated['role_utilisateur'] !== $user->role_utilisateur
            && !$this->isSuperAdminRequest($request)
        ) {
            return response()->json([
                'message' => 'Action réservée aux super-admins'
            ], 403);
        }

        $user->nom_utilisateur = $validated['nom_utilisateur'];
        $user->prenom_utilisateur = $validated['prenom_utilisateur'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->role_utilisateur = $validated['role_utilisateur'] ?? $user->role_utilisateur;
        $user->telephone_utilisateur = $validated['telephone_utilisateur'] ?? null;
        $user->adresse_utilisateur = $validated['adresse_utilisateur'] ?? null;
        $user->date_naissance_utilisateur = $validated['date_naissance_utilisateur'] ?? null;
        $user->allergies_utilisateur = $validated['allergies_utilisateur'] ?? null;
        $user->problemes_sante_utilisateur = $validated['problemes_sante_utilisateur'] ?? null;
        $user->possede_permis_utilisateur = (bool) ($validated['possede_permis_utilisateur'] ?? false);
        $user->est_motorise_utilisateur = (bool) ($validated['est_motorise_utilisateur'] ?? false);
        $user->possede_vehicule_utilisateur = (bool) ($validated['possede_vehicule_utilisateur'] ?? false);
        $user->taille_tshirt_utilisateur = $validated['taille_tshirt_utilisateur'] ?? null;
        $user->est_anonyme_utilisateur = (bool) ($validated['est_anonyme_utilisateur'] ?? false);
        $user->est_suspendu_utilisateur = (bool) ($validated['est_suspendu_utilisateur'] ?? false);
        $user->raison_suspension_utilisateur = $validated['raison_suspension_utilisateur'] ?? null;
        $user->permissions_utilisateur = $validated['permissions_utilisateur'] ?? null;
        $user->nombre_missions_utilisateur = $validated['nombre_missions_utilisateur'] ?? 0;

        $user->save();

        return response()->json([
            'message'=>'User mis à jour',
            'user' => $user
        ], 200);
    }

    public function destroy($id)
    {
        if (User::where('id_utilisateur', $id)->exists()) {
            $user = User::find($id);
            $user->delete();

            return response()->json([
                'message' => 'User supprimé'
            ], 200);
        } else {
            return response()->json([
                'message' => 'User inexistant'
            ], 404);
        }
    }
}
