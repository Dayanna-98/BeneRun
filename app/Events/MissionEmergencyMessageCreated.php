<?php

namespace App\Events;

use App\Models\MissionEmergencyMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissionEmergencyMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public MissionEmergencyMessage $urgence)
    {
        $this->urgence->loadMissing([
            'emetteur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'mission:id_mission,titre_mission,statut_mission,date_mission,heure_fin_mission',
            'evenement:id_evenement,nom_evenement',
            'prisEnChargePar:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
            'consultations.utilisateur:id_utilisateur,nom_utilisateur,prenom_utilisateur,email',
        ]);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('emergency.superadmins')];
    }

    public function broadcastAs(): string
    {
        return 'emergency.message.created';
    }

    public function broadcastWith(): array
    {
        return [
            'urgence' => $this->urgence->toArray(),
        ];
    }
}
