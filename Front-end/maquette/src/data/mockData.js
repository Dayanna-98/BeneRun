export const users = [
  {
    id: "1",
    firstName: "Marie",
    lastName: "Dubois",
    email: "marie.dubois@email.com",
    password: "demo123",
    phone: "+41 22 123 45 67",
    address: "Rue de la Servette 45, 1202 Genève",
    registrationDate: "2024-01-15",
    allergies: ["Pollen", "Arachides"],
    healthIssues: ["Asthme léger"],
    accountType: "Bénévole",
    role: "volunteer",
    permissions: { location: true, messaging: true },
    profilePhoto: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400",
    hasLicense: true, isMotorized: false, hasVehicle: false,
    bibSize: "M", anonymous: false,
    warnings: 0, suspended: false,
    skills: [
      { id: "1", name: "Secourisme", level: "Avancé" },
      { id: "3", name: "Accueil du Public", level: "Intermédiaire" },
      { id: "5", name: "Communication", level: "Avancé" },
    ],
    badges: [
      { id: "1", name: "Première Mission", description: "Complété votre première mission", icon: "🌟", earnedDate: "2024-01-15" },
      { id: "2", name: "10 Missions", description: "Complété 10 missions", icon: "🏆", earnedDate: "2024-03-20" },
      { id: "3", name: "Engagement Mensuel", description: "Au moins 1 mission par mois pendant 6 mois", icon: "💪", earnedDate: "2024-06-01" },
    ],
    certificates: [
      { id: "1", name: "PSC1 - Premiers Secours", issuer: "Croix-Rouge Suisse", issueDate: "2023-09-10", expiryDate: "2026-09-10", type: "external", status: "approved" },
      { id: "2", name: "Formation Accueil du Public", issuer: "Béné'Run", issueDate: "2024-01-20", expiryDate: null, type: "platform", status: "approved" },
    ],
    missionHistory: [
      { id: "h1", eventName: "Marathon de Paris 2024", missionName: "Poste de Secours Zone A", date: "2024-04-14", duration: "8 heures", role: "Secouriste", rating: 5, certificateRequested: false, certificateIssued: false },
      { id: "h2", eventName: "Festival d'Été", missionName: "Accueil et Orientation", date: "2024-07-20", duration: "6 heures", role: "Bénévole Accueil", rating: 5, certificateRequested: true, certificateIssued: true },
      { id: "h3", eventName: "Salon du Livre", missionName: "Gestion du Stand", date: "2024-09-05", duration: "4 heures", role: "Bénévole Stand", rating: 4, certificateRequested: false, certificateIssued: false },
    ],
    favoriteMissions: ["m1", "m2"],
  },
  {
    id: "2",
    firstName: "Lucas", lastName: "Martin",
    email: "lucas.martin@email.com", password: "demo123",
    phone: "+41 22 234 56 78", address: "Avenue de France 12, 1202 Genève",
    registrationDate: "2023-09-10", allergies: ["Lactose"], healthIssues: [],
    accountType: "Responsable de mission", role: "mission_manager",
    permissions: { location: true, messaging: true, manageMissions: true },
    profilePhoto: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400",
    hasLicense: true, isMotorized: true, hasVehicle: true, bibSize: "L", anonymous: false,
    warnings: 0, suspended: false,
    skills: [
      { id: "2", name: "Organisation", level: "Expert" },
      { id: "4", name: "Gestion de Crise", level: "Avancé" },
      { id: "5", name: "Communication", level: "Expert" },
      { id: "7", name: "Logistique", level: "Avancé" },
    ],
    badges: [
      { id: "1", name: "Première Mission", description: "Complété votre première mission", icon: "🌟", earnedDate: "2023-09-10" },
      { id: "2", name: "10 Missions", description: "Complété 10 missions", icon: "🏆", earnedDate: "2023-11-15" },
      { id: "4", name: "Super Organisateur", description: "Excellentes compétences d'organisation", icon: "🎯", earnedDate: "2024-08-10" },
      { id: "5", name: "Responsable Expérimenté", description: "50 missions en tant que responsable", icon: "👔", earnedDate: "2025-01-20" },
    ],
    certificates: [
      { id: "1", name: "Formation Responsable de Mission", issuer: "Béné'Run", issueDate: "2023-09-15", expiryDate: null, type: "platform", status: "approved" },
      { id: "2", name: "Gestion de Crise", issuer: "Béné'Run", issueDate: "2024-05-15", expiryDate: null, type: "platform", status: "approved" },
      { id: "3", name: "Formation Leadership d'Équipe", issuer: "Institut de Formation Genève", issueDate: "2024-02-10", expiryDate: "2027-02-10", type: "external", status: "approved" },
      { id: "4", name: "Communication de Crise", issuer: "Association Suisse des Bénévoles", issueDate: "2025-01-05", expiryDate: null, type: "external", status: "pending" },
    ],
    missionHistory: [
      { id: "h1", eventName: "Marathon de Genève 2023", missionName: "Coordination Équipe Secours", date: "2023-10-15", duration: "10 heures", role: "Responsable de mission", rating: 5, certificateRequested: true, certificateIssued: true },
      { id: "h2", eventName: "Fêtes de Genève 2023", missionName: "Gestion Scène Principale", date: "2023-08-05", duration: "12 heures", role: "Responsable de mission", rating: 5, certificateRequested: true, certificateIssued: true },
    ],
    favoriteMissions: ["m3", "m4", "m8"],
  },
  {
    id: "3",
    firstName: "Sophie", lastName: "Bernard",
    email: "sophie.bernard@email.com", password: "demo123",
    phone: "+41 22 345 67 89", address: "Rue du Stand 28, 1204 Genève",
    registrationDate: "2023-06-20", allergies: [], healthIssues: ["Diabète type 1"],
    accountType: "Admin", role: "admin",
    permissions: { location: true, messaging: true, manageMissions: true, manageEvents: true, assignMissions: true },
    profilePhoto: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400",
    hasLicense: true, isMotorized: true, hasVehicle: true, bibSize: "M", anonymous: false,
    warnings: 1, suspended: false,
    skills: [
      { id: "2", name: "Organisation", level: "Expert" },
      { id: "4", name: "Gestion de Crise", level: "Expert" },
      { id: "5", name: "Communication", level: "Expert" },
    ],
    badges: [
      { id: "1", name: "Première Mission", description: "Complété votre première mission", icon: "🌟", earnedDate: "2023-06-20" },
      { id: "6", name: "Admin Dévoué", description: "100 missions gérées en tant qu'admin", icon: "⚡", earnedDate: "2024-10-01" },
    ],
    certificates: [
      { id: "1", name: "Formation Admin Béné'Run", issuer: "Béné'Run", issueDate: "2023-06-25", expiryDate: null, type: "platform", status: "approved" },
    ],
    missionHistory: [
      { id: "h1", eventName: "Course de l'Escalade 2023", missionName: "Coordination Générale", date: "2023-12-10", duration: "14 heures", role: "Administrateur", rating: 5, certificateRequested: true, certificateIssued: true },
    ],
    favoriteMissions: ["m1", "m5", "m6"],
  },
  {
    id: "4",
    firstName: "Alexandre", lastName: "Rousseau",
    email: "admin@benerun.ch", password: "demo123",
    phone: "+41 22 456 78 90", address: "Quai du Mont-Blanc 7, 1201 Genève",
    registrationDate: "2023-01-05", allergies: [], healthIssues: [],
    accountType: "Super-admin", role: "superadmin",
    permissions: { location: true, messaging: true, manageMissions: true, manageEvents: true, assignMissions: true, manageAccounts: true, manageReferentials: true, exportStatistics: true },
    profilePhoto: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400",
    hasLicense: true, isMotorized: true, hasVehicle: true, bibSize: "L", anonymous: false,
    warnings: 0, suspended: false,
    skills: [
      { id: "1", name: "Secourisme", level: "Expert" },
      { id: "2", name: "Organisation", level: "Expert" },
      { id: "4", name: "Gestion de Crise", level: "Expert" },
      { id: "5", name: "Communication", level: "Expert" },
    ],
    badges: [
      { id: "8", name: "Fondateur", description: "Membre fondateur de Béné'Run", icon: "🏅", earnedDate: "2023-01-05" },
      { id: "9", name: "Légende", description: "Plus de 200 missions complétées", icon: "🌟", earnedDate: "2025-02-01" },
    ],
    certificates: [
      { id: "1", name: "Formation Super Admin Béné'Run", issuer: "Béné'Run", issueDate: "2023-01-10", expiryDate: null, type: "platform", status: "approved" },
    ],
    missionHistory: [
      { id: "h1", eventName: "Course de l'Escalade 2023", missionName: "Direction Générale", date: "2023-12-10", duration: "16 heures", role: "Super Administrateur", rating: 5, certificateRequested: true, certificateIssued: true },
    ],
    favoriteMissions: ["m1", "m2", "m3", "m6", "m7"],
  },
]

export let userProfile = users[0]

export const availableSkills = [
  { id: "1", name: "Secourisme" },
  { id: "2", name: "Organisation" },
  { id: "3", name: "Accueil du Public" },
  { id: "4", name: "Gestion de Crise" },
  { id: "5", name: "Communication" },
  { id: "6", name: "Animation" },
  { id: "7", name: "Logistique" },
  { id: "8", name: "Technique" },
  { id: "9", name: "Cuisine" },
  { id: "10", name: "Traduction" },
]

export const badges = [
  { id: "1", name: "Première Mission", description: "Complété votre première mission", icon: "🌟", earnedDate: "2024-01-15" },
  { id: "2", name: "10 Missions", description: "Complété 10 missions", icon: "🏆", earnedDate: "2024-03-20" },
  { id: "3", name: "Engagement Mensuel", description: "Au moins 1 mission par mois pendant 6 mois", icon: "💪", earnedDate: "2024-06-01" },
  { id: "4", name: "Super Organisateur", description: "Excellentes compétences d'organisation", icon: "🎯", earnedDate: "2024-08-10" },
]

export const certificates = [
  { id: "1", name: "PSC1 - Premiers Secours", issuer: "Croix-Rouge Française", issueDate: "2023-09-10", expiryDate: "2026-09-10", type: "external", status: "approved" },
  { id: "2", name: "Formation Accueil du Public", issuer: "Association BenevolesPro", issueDate: "2024-01-20", expiryDate: null, type: "platform", status: "approved" },
  { id: "3", name: "Gestion de Crise", issuer: "Association BenevolesPro", issueDate: "2024-05-15", expiryDate: null, type: "platform", status: "pending" },
]

export const skills = [
  { id: "1", name: "Secourisme", level: "Avancé" },
  { id: "2", name: "Organisation", level: "Expert" },
  { id: "3", name: "Accueil du Public", level: "Intermédiaire" },
  { id: "4", name: "Gestion de Crise", level: "Avancé" },
  { id: "5", name: "Communication", level: "Avancé" },
]

export const missionHistory = [
  { id: "h1", eventName: "Marathon de Paris 2024", missionName: "Poste de Secours Zone A", date: "2024-04-14", duration: "8 heures", role: "Secouriste", rating: 5, certificateRequested: false, certificateIssued: false },
  { id: "h2", eventName: "Festival d'Été", missionName: "Accueil et Orientation", date: "2024-07-20", duration: "6 heures", role: "Bénévole Accueil", rating: 5, certificateRequested: true, certificateIssued: true },
  { id: "h3", eventName: "Salon du Livre", missionName: "Gestion du Stand", date: "2024-09-05", duration: "4 heures", role: "Responsable Stand", rating: 4, certificateRequested: false, certificateIssued: false },
]

export const events = [
  { id: "e1", name: "Course de l'Escalade 2025", description: "La célèbre course commémorant la victoire de Genève en 1602. Plus de 35'000 participants attendus.", date: "2025-12-07", location: "Vieille-Ville de Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2044,6.1432", imageUrl: "https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800", organizer: "Course de l'Escalade", category: "Sport", totalVolunteersNeeded: 150, currentVolunteers: 87, waitingList: [{ id: "w1", userId: "5", name: "Jean Dupont", registeredAt: "2025-02-15T10:30:00" }], missionsCount: 8, progress: 58 },
  { id: "e2", name: "Fêtes de Genève 2025", description: "Le plus grand événement festif de Genève avec concerts gratuits et feux d'artifice.", date: "2025-08-01", location: "Quai du Mont-Blanc, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2082,6.1549", imageUrl: "https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800", organizer: "Ville de Genève", category: "Festival", totalVolunteersNeeded: 200, currentVolunteers: 134, waitingList: [], missionsCount: 12, progress: 67 },
  { id: "e3", name: "Salon International de l'Auto 2025", description: "Le salon automobile international de Genève.", date: "2025-03-05", location: "Palexpo, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2308,6.1098", imageUrl: "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=800", organizer: "Palexpo SA", category: "Salon", totalVolunteersNeeded: 180, currentVolunteers: 156, waitingList: [], missionsCount: 10, progress: 87 },
  { id: "e4", name: "Marathon de Genève 2025", description: "Marathon international à travers les rues de Genève.", date: "2025-05-10", location: "Centre-ville de Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2044,6.1432", imageUrl: "https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=800", organizer: "Genève Marathon", category: "Sport", totalVolunteersNeeded: 120, currentVolunteers: 98, waitingList: [], missionsCount: 9, progress: 82 },
  { id: "e5", name: "Festival de la Terre 2025", description: "Festival écologique promouvant le développement durable.", date: "2025-06-15", location: "Parc des Bastions, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.1991,6.1421", imageUrl: "https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=800", organizer: "Association Terre Genève", category: "Écologie", totalVolunteersNeeded: 80, currentVolunteers: 45, waitingList: [], missionsCount: 6, progress: 56 },
]

export const availableMissions = [
  { id: "m1", eventId: "e1", eventName: "Course de l'Escalade 2025", name: "Poste de Secours - Place Neuve", date: "2025-12-07", startTime: "07:00", endTime: "15:00", location: "Place Neuve, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2003,6.1420", description: "Assurer les premiers secours aux participants et spectateurs.", type: "Secours", requiredSkills: ["Secourisme"], organizer: "Course de l'Escalade", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: true }], currentVolunteers: 4, maxVolunteers: 6, backupVolunteers: 2, imageUrl: "https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=800", isFavorite: false, postable: true, inscription: true, visibility: "public", restrictedUserIds: [], palette: false, progress: 67, safetyInstructions: "1. Porter l'équipement de protection individuelle\n2. Vérifier le matériel médical avant la mission\n3. Communiquer immédiatement toute urgence", confirmedVolunteers: ["1", "2"], unconfirmedVolunteers: ["3", "5"] },
  { id: "m2", eventId: "e1", eventName: "Course de l'Escalade 2025", name: "Distribution de Ravitaillement", date: "2025-12-07", startTime: "08:00", endTime: "14:00", location: "Rue de la Corraterie, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2019,6.1431", description: "Distribuer eau, fruits et barres énergétiques aux coureurs.", type: "Logistique", requiredSkills: ["Organisation"], organizer: "Course de l'Escalade", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: false }], currentVolunteers: 7, maxVolunteers: 10, backupVolunteers: 3, imageUrl: "https://images.unsplash.com/photo-1530549387789-4c1017266635?w=800", isFavorite: true, postable: true, inscription: true, visibility: "public", restrictedUserIds: [], palette: false, progress: 70, safetyInstructions: "1. Maintenir une hygiène stricte\n2. Vérifier les dates de péremption\n3. Porter des gants lors de la distribution", confirmedVolunteers: ["1", "2", "3"], unconfirmedVolunteers: ["4", "5", "6", "7"] },
  { id: "m3", eventId: "e2", eventName: "Fêtes de Genève 2025", name: "Accueil et Information", date: "2025-08-01", startTime: "14:00", endTime: "23:00", location: "Quai du Mont-Blanc, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2082,6.1549", description: "Accueillir les visiteurs et orienter le public.", type: "Accueil", requiredSkills: ["Accueil du Public", "Communication"], organizer: "Ville de Genève", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: false }], currentVolunteers: 8, maxVolunteers: 12, backupVolunteers: 4, imageUrl: "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?w=800", isFavorite: false, postable: true, inscription: true, visibility: "public", restrictedUserIds: [], palette: false, progress: 67, safetyInstructions: "1. Rester courtois et professionnel\n2. Connaître les issues de secours\n3. Porter votre badge visible", confirmedVolunteers: ["1", "2", "3", "4"], unconfirmedVolunteers: ["5", "6", "7", "8"] },
  { id: "m4", eventId: "e2", eventName: "Fêtes de Genève 2025", name: "Gestion de la Scène Principale", date: "2025-08-01", startTime: "13:00", endTime: "00:00", location: "Jardin Anglais, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2055,6.1522", description: "Assister l'équipe technique et gérer l'accès à la scène.", type: "Technique", requiredSkills: ["Organisation", "Gestion de Crise"], organizer: "Ville de Genève", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: false }], currentVolunteers: 5, maxVolunteers: 8, backupVolunteers: 2, imageUrl: "https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=800", isFavorite: false, postable: false, inscription: true, visibility: "private", restrictedUserIds: [], palette: false, progress: 63, safetyInstructions: "1. Porter des chaussures de sécurité\n2. Ne pas toucher aux équipements électriques sans autorisation", confirmedVolunteers: ["2", "3"], unconfirmedVolunteers: ["4", "5", "6"] },
  { id: "m5", eventId: "e3", eventName: "Salon International de l'Auto 2025", name: "Guide et Orientation Visiteurs", date: "2025-03-05", startTime: "09:00", endTime: "18:00", location: "Palexpo, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2308,6.1098", description: "Guider les visiteurs dans les différents halls.", type: "Accueil", requiredSkills: ["Accueil du Public"], organizer: "Palexpo SA", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: true }], currentVolunteers: 15, maxVolunteers: 20, backupVolunteers: 5, imageUrl: "https://images.unsplash.com/photo-1485291571150-772bcfc10da5?w=800", isFavorite: false, postable: true, inscription: true, visibility: "restricted", restrictedUserIds: ["1", "2", "3", "4"], palette: false, progress: 75, safetyInstructions: "1. Se familiariser avec le plan du salon\n2. Connaître les sorties de secours", confirmedVolunteers: ["1", "2", "3", "4", "5"], unconfirmedVolunteers: [] },
  { id: "m6", eventId: "e4", eventName: "Marathon de Genève 2025", name: "Équipe Secours Kilomètre 15", date: "2025-05-10", startTime: "07:00", endTime: "14:00", location: "Quai Gustave-Ador, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.2067,6.1588", description: "Poste de secours mobile pour assistance médicale aux coureurs.", type: "Secours", requiredSkills: ["Secourisme"], organizer: "Genève Marathon", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: false }], currentVolunteers: 3, maxVolunteers: 5, backupVolunteers: 2, imageUrl: "https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=800", isFavorite: false, postable: true, inscription: true, visibility: "public", restrictedUserIds: [], palette: false, progress: 60, safetyInstructions: "1. Vérifier le matériel médical avant le départ\n2. Rester en contact radio avec le QG médical", confirmedVolunteers: ["1", "2"], unconfirmedVolunteers: ["3"] },
  { id: "m7", eventId: "e5", eventName: "Festival de la Terre 2025", name: "Animation Stand Recyclage", date: "2025-06-15", startTime: "10:00", endTime: "18:00", location: "Parc des Bastions, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.1991,6.1421", description: "Animer le stand de sensibilisation au recyclage.", type: "Animation", requiredSkills: ["Animation", "Communication"], organizer: "Association Terre Genève", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: false }], currentVolunteers: 2, maxVolunteers: 4, backupVolunteers: 1, imageUrl: "https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=800", isFavorite: false, postable: true, inscription: true, visibility: "public", restrictedUserIds: [], palette: true, progress: 50, safetyInstructions: "1. Surveiller les enfants pendant les ateliers\n2. Utiliser uniquement des matériaux non toxiques", confirmedVolunteers: ["1"], unconfirmedVolunteers: ["2"] },
  { id: "m8", eventId: "e5", eventName: "Festival de la Terre 2025", name: "Logistique et Installation", date: "2025-06-14", startTime: "08:00", endTime: "17:00", location: "Parc des Bastions, Genève", locationLink: "https://www.google.com/maps/search/?api=1&query=46.1991,6.1421", description: "Montage des stands et préparation du site.", type: "Logistique", requiredSkills: ["Organisation"], organizer: "Association Terre Genève", missionManagers: [{ id: "4", name: "Alexandre Rousseau", phone: "+41 22 456 78 90", email: "admin@benerun.ch", isDefault: true, isDayContact: true }], currentVolunteers: 4, maxVolunteers: 8, backupVolunteers: 2, imageUrl: "https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=800", isFavorite: false, postable: true, inscription: false, visibility: "private", restrictedUserIds: [], palette: false, progress: 50, safetyInstructions: "1. Porter des chaussures de sécurité et gants\n2. Soulever les charges avec les jambes", confirmedVolunteers: ["2", "3"], unconfirmedVolunteers: ["4", "5"] },
]

export const myActiveMissions = [
  { id: "1", eventId: "e1", eventName: "Concert Solidaire 2025", name: "Équipe Secours Zone Principale", date: "2025-03-15", startTime: "14:00", endTime: "23:00", location: "Parc des Expositions, Paris", locationLink: "https://www.google.com/maps/search/?api=1&query=48.8566,2.3522", description: "Assurer la sécurité et les premiers secours pour le concert solidaire.", requiredSkills: ["Secourisme"], organizer: "Association Musique Pour Tous", responsible: { name: "Alexandre Rousseau", phone: "+41 22 456 78 90" }, currentVolunteers: 5, maxVolunteers: 6, imageUrl: "https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?w=800", isFavorite: false, safetyInstructionsConfirmed: true, volunteers: [{ id: "v1", name: "Marie Dubois", position: { lat: 48.8566, lng: 2.3522 } }, { id: "v2", name: "Pierre Durand", position: { lat: 48.8568, lng: 2.3524 } }], messages: [{ id: "msg1", senderId: "v2", senderName: "Pierre Durand", message: "Tout le monde est bien arrivé ?", timestamp: "2025-02-23T14:15:00", type: "chat" }, { id: "msg2", senderId: "v3", senderName: "Alice Bernard", message: "Oui, je suis au poste !", timestamp: "2025-02-23T14:16:00", type: "chat" }], emergencyMessages: [] },
]