/**
 * Contenu du guide intégré par rôle.
 *
 * Chaque rôle a :
 *  - `label`  : nom affiché
 *  - `color`  : couleur accent du badge
 *  - `steps`  : slides du guide (icon Lucide name, title, description, route optionnelle)
 *
 * `newStepsFrom` liste les étapes qui sont NOUVELLES par rapport au rôle précédent
 * dans la hiérarchie (volunteer → mission_manager → admin → superadmin).
 * Cela permet d'afficher "nouvelles fonctionnalités" lors d'une montée de rôle.
 */

export const TUTORIAL_CATEGORIES = {
  onboarding: 'Découverte',
  missions: 'Missions',
  communication: 'Communication',
  profile: 'Profil & progression',
  management: 'Gestion opérationnelle',
  administration: 'Administration',
  reporting: 'Pilotage & rapports',
}

export const TUTORIAL_CONTENT = {
  volunteer: {
    label: 'Bénévole',
    color: '#c5d82e',
    steps: [
      {
        id: 'welcome',
        category: 'onboarding',
        icon: 'Heart',
        title: 'Bienvenue sur Béné\'Run !',
        description:
          'Vous faites maintenant partie de la communauté des bénévoles de Running Geneva. Voici un rapide tour d\'horizon de vos fonctionnalités.',
      },
      {
        id: 'missions',
        category: 'missions',
        icon: 'ListChecks',
        title: 'Parcourez les missions',
        description:
          'Depuis l\'onglet Missions, explorez toutes les missions disponibles, filtrez par compétences ou dates, et consultez les détails.',
        route: '/missions',
        cta: 'Voir les missions',
      },
      {
        id: 'enroll',
        category: 'missions',
        icon: 'ClipboardCheck',
        title: 'Inscrivez-vous',
        description:
          'Sur la page d\'une mission, cliquez sur « S\'inscrire » pour rejoindre immédiatement la mission (si vos compétences correspondent).',
        route: '/missions',
        cta: 'Explorer',
      },
      {
        id: 'mymissions',
        category: 'missions',
        icon: 'CalendarDays',
        title: 'Mon planning',
        description:
          'Retrouvez vos missions en cours ou passées dans la section « Mes missions ».',
        route: '/my-missions',
        cta: 'Mes missions',
      },
      {
        id: 'messaging',
        category: 'communication',
        icon: 'MessageCircle',
        title: 'Messagerie',
        description:
          'Échangez directement avec d\'autres bénévoles ou les responsables de vos missions via la messagerie intégrée.',
        route: '/messagerie',
        cta: 'Messagerie',
      },
      {
        id: 'badges',
        category: 'profile',
        icon: 'Award',
        title: 'Badges & Compétences',
        description:
          'Chaque mission accomplie vous rapporte des points de compétences. Accumulez-les pour débloquer des badges et faire évoluer votre profil.',
        route: '/profile',
        cta: 'Mon profil',
      },
      {
        id: 'favorites',
        category: 'missions',
        icon: 'Bookmark',
        title: 'Favoris',
        description:
          'Enregistrez les missions qui vous intéressent en les ajoutant à vos favoris pour les retrouver facilement.',
        route: '/favorites',
        cta: 'Mes favoris',
      },
    ],
  },

  mission_manager: {
    label: 'Responsable de mission',
    color: '#3b82f6',
    // Étapes nouvelles par rapport au rôle volunteer
    newStepIds: ['create-mission', 'manage-inscriptions', 'contact-members', 'emergency'],
    steps: [
      {
        id: 'welcome-manager',
        category: 'management',
        icon: 'ShieldCheck',
        title: 'Vous êtes maintenant Responsable de mission',
        description:
          'Félicitations ! Votre rôle a évolué. En plus des fonctionnalités bénévole, vous pouvez désormais créer et gérer vos propres missions.',
      },
      {
        id: 'create-mission',
        category: 'management',
        icon: 'PlusCircle',
        title: 'Créer une mission',
        description:
          'Dans la section « Gérer les missions », créez de nouvelles missions : définissez les horaires, le lieu, les compétences requises et les récompenses.',
        route: '/manage-missions/create',
        cta: 'Créer une mission',
        isNew: true,
      },
      {
        id: 'manage-inscriptions',
        category: 'management',
        icon: 'Users',
        title: 'Suivre les inscrits',
        description:
          'Suivez en temps réel les bénévoles inscrits et le remplissage des missions. La liste d\'attente événement reste disponible pour les remplacements.',
        route: '/my-managed-missions',
        cta: 'Mes missions gérées',
        isNew: true,
      },
      {
        id: 'contact-members',
        category: 'communication',
        icon: 'Send',
        title: 'Contacter vos bénévoles',
        description:
          'Envoyez des messages de groupe ou individuels aux participants de vos missions directement depuis la messagerie.',
        route: '/messagerie',
        cta: 'Messagerie',
        isNew: true,
      },
      {
        id: 'emergency',
        category: 'management',
        icon: 'AlertTriangle',
        title: 'Message d\'urgence',
        description:
          'En cas de problème sur une mission en cours, envoyez un message d\'urgence instantané à tous les participants.',
        isNew: true,
      },
    ],
  },

  admin: {
    label: 'Admin',
    color: '#f59e0b',
    newStepIds: ['manage-events', 'manage-users', 'manage-competences', 'manage-badges', 'statistics'],
    steps: [
      {
        id: 'welcome-admin',
        category: 'administration',
        icon: 'Star',
        title: 'Vous êtes Admin',
        description:
          'Votre rôle vous donne accès à la gestion complète des événements, utilisateurs et ressources de la plateforme.',
      },
      {
        id: 'manage-events',
        category: 'administration',
        icon: 'CalendarPlus',
        title: 'Gérer les événements',
        description:
          'Créez et administrez les événements (courses, manifestations) qui hébergent les missions bénévoles.',
        route: '/manage-events',
        cta: 'Gérer les événements',
        isNew: true,
      },
      {
        id: 'manage-users',
        category: 'administration',
        icon: 'UserCog',
        title: 'Gérer les utilisateurs',
        description:
          'Consultez, modifiez et gérez les comptes de tous les bénévoles et responsables inscrits sur la plateforme.',
        route: '/manage-users',
        cta: 'Gérer les utilisateurs',
        isNew: true,
      },
      {
        id: 'manage-competences',
        category: 'administration',
        icon: 'Tag',
        title: 'Compétences & Types de mission',
        description:
          'Créez et gérez les compétences disponibles ainsi que les types de missions suggérés pour orienter les bénévoles.',
        route: '/manage-competences',
        cta: 'Gérer les compétences',
        isNew: true,
      },
      {
        id: 'manage-badges',
        category: 'administration',
        icon: 'Medal',
        title: 'Badges & Certificats',
        description:
          'Définissez les règles d\'attribution des badges et gérez les certificats remis aux bénévoles méritants.',
        route: '/manage-badges',
        cta: 'Gérer les badges',
        isNew: true,
      },
      {
        id: 'statistics',
        category: 'reporting',
        icon: 'BarChart3',
        title: 'Statistiques',
        description:
          'Accédez au tableau de bord statistique : nombre de missions, bénévoles actifs, répartition par événement.',
        route: '/statistics',
        cta: 'Voir les stats',
        isNew: true,
      },
    ],
  },

  superadmin: {
    label: 'Super-admin',
    color: '#ef4444',
    newStepIds: ['create-accounts', 'manage-permissions', 'certificates', 'export'],
    steps: [
      {
        id: 'welcome-superadmin',
        category: 'administration',
        icon: 'Crown',
        title: 'Vous êtes Super-admin',
        description:
          'Accès complet à la plateforme. Vous gérez les comptes, les permissions et disposez de toutes les capacités administratives.',
      },
      {
        id: 'create-accounts',
        category: 'administration',
        icon: 'UserPlus',
        title: 'Créer des comptes',
        description:
          'Créez directement des comptes pour les responsables, admins ou autres super-admins sans passer par l\'inscription publique.',
        route: '/manage-users/create',
        cta: 'Créer un compte',
        isNew: true,
      },
      {
        id: 'manage-permissions',
        category: 'administration',
        icon: 'KeyRound',
        title: 'Gérer les permissions',
        description:
          'Attribuez des rôles et des permissions fine-grained à chaque utilisateur selon ses responsabilités.',
        route: '/manage-users',
        cta: 'Gérer les permissions',
        isNew: true,
      },
      {
        id: 'certificates',
        category: 'administration',
        icon: 'FileText',
        title: 'Émettre des certificats',
        description:
          'Générez et envoyez des certificats de bénévolat officiels aux participants qui le méritent.',
        route: '/manage-certificates',
        cta: 'Certificats',
        isNew: true,
      },
      {
        id: 'export',
        category: 'reporting',
        icon: 'Download',
        title: 'Export & Rapports',
        description:
          'Exportez les statistiques et données de la plateforme pour vos rapports ou audits.',
        route: '/statistics',
        cta: 'Statistiques',
        isNew: true,
      },
    ],
  },
}

const ROLE_ALIAS = {
  organizer: 'mission_manager',
}

const ROLE_TUTORIAL_CHAIN = {
  volunteer: ['volunteer'],
  mission_manager: ['volunteer', 'mission_manager'],
  admin: ['volunteer', 'mission_manager', 'admin'],
  superadmin: ['volunteer', 'mission_manager', 'admin', 'superadmin'],
}

export function resolveTutorialRole(role) {
  const normalizedRole = String(role || 'volunteer').trim().toLowerCase()
  return ROLE_ALIAS[normalizedRole] || normalizedRole || 'volunteer'
}

export function getTutorialContentForRole(role) {
  const resolvedRole = resolveTutorialRole(role)
  const baseContent = TUTORIAL_CONTENT[resolvedRole]
  if (!baseContent) return null

  const chain = ROLE_TUTORIAL_CHAIN[resolvedRole] || [resolvedRole]
  const mergedSteps = []
  const seenStepIds = new Set()

  for (const roleKey of chain) {
    const content = TUTORIAL_CONTENT[roleKey]
    if (!content) continue

    for (const step of content.steps || []) {
      const stepId = String(step?.id || '').trim()
      if (!stepId || seenStepIds.has(stepId)) continue
      seenStepIds.add(stepId)
      mergedSteps.push(step)
    }
  }

  return {
    ...baseContent,
    steps: mergedSteps,
  }
}

/** Retourne uniquement les étapes nouvelles pour un changement de rôle donné. */
export function getNewStepsForRole(role) {
  const resolvedRole = resolveTutorialRole(role)
  const content = TUTORIAL_CONTENT[resolvedRole]
  if (!content) return []
  if (!content.newStepIds) return content.steps
  return content.steps.filter((s) => content.newStepIds.includes(s.id))
}

export function getTutorialStepForRoute(role, routePath) {
  const content = getTutorialContentForRole(role)
  if (!content || !routePath) return null

  const normalizedRoute = String(routePath).trim()
  if (!normalizedRoute) return null

  const index = content.steps.findIndex((step) => step.route === normalizedRoute)
  if (index === -1) return null

  return {
    content,
    index,
    step: content.steps[index],
  }
}
