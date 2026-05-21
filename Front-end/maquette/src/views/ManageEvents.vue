<template>
  <div class="min-vh-100 pb-5" style="background:#f0f4f8">

    <header class="text-white sticky-top overflow-hidden" style="background:linear-gradient(135deg,#1a2230 0%,#2d3a4a 100%)">
      <div class="p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link p-0 text-white" @click="router.go(-1)">
            <ArrowLeft style="width:24px;height:24px" />
          </button>
          <div>
            <div class="x-small" style="color:rgba(255,255,255,.55)">Administration</div>
            <h1 class="fs-5 fw-semibold mb-0">Gestion des Événements</h1>
          </div>
        </div>
      </div>
      <div class="px-3 pb-3 d-flex justify-content-end">
        <button
          class="btn btn-sm fw-semibold d-flex align-items-center gap-2 px-3 rounded-pill flex-shrink-0"
          style="background:linear-gradient(135deg,#d4e645,#a3c200);color:#1a2230;border:none"
          @click="router.push('/manage-events/create')"
        >
          <Plus style="width:16px;height:16px" /> Créer
        </button>
      </div>
    </header>

    <div class="p-3 mx-auto d-flex flex-column gap-3" style="max-width:1100px">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex flex-column gap-3">
          <div class="row g-2 align-items-center">
            <div class="col-12 col-lg-6 position-relative">
              <Search class="position-absolute text-muted" style="width:18px;height:18px;left:12px;top:50%;transform:translateY(-50%)" />
              <input
                v-model="searchQuery"
                class="form-control ps-5"
                type="text"
                placeholder="Rechercher un événement, lieu ou organisateur..."
              />
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <select v-model="sortBy" class="form-select">
                <option value="nearest">Plus proche</option>
                <option value="newest">Plus récent</option>
                <option value="volunteers_desc">+ bénévoles requis</option>
                <option value="spots_asc">Moins de places restantes</option>
              </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <button
                class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2"
                :disabled="activeQuickFiltersCount === 0"
                @click="resetQuickFilters"
              >
                <X style="width:16px;height:16px" />
                Réinitialiser ({{ activeQuickFiltersCount }})
              </button>
            </div>
          </div>

          <div class="filter-pills">
            <button class="btn btn-sm rounded-pill px-3" :class="statusTab === 'upcoming' ? 'btn-primary' : 'btn-outline-primary'" @click="statusTab = 'upcoming'">
              À venir ({{ statusCounts.upcoming }})
            </button>
            <button class="btn btn-sm rounded-pill px-3" :class="statusTab === 'active' ? 'btn-primary' : 'btn-outline-primary'" @click="statusTab = 'active'">
              En cours ({{ statusCounts.active }})
            </button>
            <button class="btn btn-sm rounded-pill px-3" :class="statusTab === 'finished' ? 'btn-primary' : 'btn-outline-primary'" @click="statusTab = 'finished'">
              Terminés ({{ statusCounts.finished }})
            </button>
            <button class="btn btn-sm rounded-pill px-3" :class="statusTab === 'cancelled' ? 'btn-primary' : 'btn-outline-primary'" @click="statusTab = 'cancelled'">
              Annulés ({{ statusCounts.cancelled }})
            </button>
            <button class="btn btn-sm rounded-pill px-3" :class="statusTab === 'all' ? 'btn-primary' : 'btn-outline-primary'" @click="statusTab = 'all'">
              Tous ({{ eventsList.length }})
            </button>
          </div>

          <div class="d-flex flex-wrap gap-3 align-items-center">
            <div class="form-check mb-0">
              <input id="fPublic" v-model="quickFilters.publicOnly" class="form-check-input" type="checkbox" />
              <label for="fPublic" class="form-check-label small">Publiques</label>
            </div>
            <div class="form-check mb-0">
              <input id="fPrivate" v-model="quickFilters.privateOnly" class="form-check-input" type="checkbox" />
              <label for="fPrivate" class="form-check-label small">Privées</label>
            </div>
            <div class="form-check mb-0">
              <input id="fFull" v-model="quickFilters.fullOnly" class="form-check-input" type="checkbox" />
              <label for="fFull" class="form-check-label small">Complets</label>
            </div>
            <div class="form-check mb-0">
              <input id="fOpen" v-model="quickFilters.openRegistrationsOnly" class="form-check-input" type="checkbox" />
              <label for="fOpen" class="form-check-label small">Inscriptions ouvertes</label>
            </div>
          </div>
        </div>
      </div>

      <div v-if="selectedEventIds.length > 0" class="card border-0 shadow-sm bulk-actions-card">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
          <div class="small fw-semibold">{{ selectedEventIds.length }} sélectionné(s)</div>
          <button class="btn btn-sm btn-outline-success" :disabled="isBulkSaving" @click="bulkSetPublished(true)">Rendre publics</button>
          <button class="btn btn-sm btn-outline-secondary" :disabled="isBulkSaving" @click="bulkSetPublished(false)">Passer privés</button>
          <button class="btn btn-sm btn-outline-primary" :disabled="isBulkSaving" @click="bulkSetRegistrations(true)">Ouvrir inscriptions</button>
          <button class="btn btn-sm btn-outline-warning" :disabled="isBulkSaving" @click="bulkSetRegistrations(false)">Fermer inscriptions</button>
          <button class="btn btn-sm btn-outline-danger" :disabled="isBulkSaving" @click="bulkCancel">Annuler</button>
          <button class="btn btn-sm btn-dark" :disabled="isBulkSaving" @click="bulkArchive">Archiver</button>
          <button class="btn btn-sm btn-link ms-auto" :disabled="isBulkSaving" @click="clearSelection">Vider</button>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary">{{ filteredEvents.length }}</div>
              <div class="x-small text-muted">Événements visibles</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-success">{{ totalMissions }}</div>
              <div class="x-small text-muted">Total missions</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-primary" style="color:#2563eb!important">{{ totalVolunteers }}</div>
              <div class="x-small text-muted">Bénévoles inscrits</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card text-center stat-card">
            <div class="card-body p-3">
              <div class="fs-3 fw-bold text-warning">{{ totalPlaces }}</div>
              <div class="x-small text-muted">Places totales</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Tous les événements</h5>
          <div class="small text-muted">{{ filteredEvents.length }} résultat(s)</div>
        </div>
        <div class="card-body d-flex flex-column gap-3">
          <CardListSkeleton v-if="isLoading" :count="3" :image-height="128" />
          <div v-else-if="errorMessage" class="alert alert-danger small mb-0">{{ errorMessage }}</div>

          <div
            v-for="event in visibleEvents"
            :key="event.id"
            class="border rounded overflow-hidden event-row"
            @click="openDetailsDrawer(event)"
            style="cursor:pointer"
          >
            <div class="p-3">
              <div class="d-flex align-items-start justify-content-between mb-3 gap-2">
                <div class="d-flex align-items-start gap-2 flex-fill">
                  <input
                    class="form-check-input mt-1"
                    type="checkbox"
                    :checked="isSelected(event.id)"
                    @click.stop
                    @change="toggleSelection(event.id)"
                  />
                  <div>
                    <h6 class="fw-semibold text-primary mb-1">{{ event.name }}</h6>
                    <p class="small text-muted mb-0 text-clamp-2">{{ event.description }}</p>
                  </div>
                </div>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm rounded-pill px-2" style="background:#f3f4f6;color:#4b5563;border:none" @click.stop="router.push('/manage-events/edit/' + event.id)">
                    <Edit style="width:16px;height:16px" />
                  </button>
                  <button class="btn btn-sm rounded-pill px-2" style="background:#f3f4f6;color:#4b5563;border:none" @click.stop="cloneEvent(event)">
                    <Copy style="width:16px;height:16px" />
                  </button>
                  <button class="btn btn-sm rounded-pill px-2" style="background:#fff1f2;color:#ef4444;border:none" :disabled="!canDeleteEvent(event)" @click.stop="handleDeleteEvent(event.id)">
                    <Trash2 style="width:16px;height:16px" />
                  </button>
                </div>
              </div>

              <div class="row g-2 small text-muted mb-3">
                <div class="col-6 d-flex align-items-center gap-2">
                  <Calendar style="width:16px;height:16px" />
                  <span class="x-small">{{ formatDateRange(event.startDate, event.endDate) }}</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <MapPin style="width:16px;height:16px" />
                  <span class="x-small text-truncate">{{ event.location }}</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <Briefcase style="width:16px;height:16px" />
                  <span class="x-small">{{ event.missionsCount }} missions</span>
                </div>
                <div class="col-6 d-flex align-items-center gap-2">
                  <Users style="width:16px;height:16px" />
                  <span class="x-small">{{ event.currentVolunteers }}/{{ event.totalVolunteersNeeded }}</span>
                </div>
              </div>

              <div class="mini-timeline mb-3">
                <div class="mini-timeline__bar">
                  <div class="mini-timeline__progress" :style="{ width: timelineProgress(event) + '%' }"></div>
                </div>
                <div class="d-flex justify-content-between x-small text-muted mt-1">
                  <span>{{ formatShortDate(event.startDate) }}</span>
                  <span>{{ formatStatusLabel(getEventStatus(event)) }}</span>
                  <span>{{ formatShortDate(event.endDate) }}</span>
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge border" :class="event.isPublished ? 'bg-info-subtle text-info border-info' : 'bg-secondary-subtle text-secondary border-secondary'">
                  {{ event.isPublished ? 'Publique' : 'Privée' }}
                </span>
                <span class="badge border" :class="isOpenForRegistrations(event) ? 'bg-success-subtle text-success border-success' : 'bg-warning-subtle text-warning border-warning'">
                  {{ isOpenForRegistrations(event) ? 'Inscriptions ouvertes' : 'Inscriptions fermées' }}
                </span>
                <span class="badge border" :class="categoryBadgeClass(event.category)">{{ event.category }}</span>
                <span v-if="event.isCancelled" class="badge bg-danger-subtle text-danger border border-danger">Annulé</span>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <span v-for="issue in healthIssues(event)" :key="issue" class="badge bg-light text-dark border">{{ issue }}</span>
              </div>

              <div class="d-flex flex-wrap gap-2 mt-3">
                <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2" @click.stop="openWaitingListForEvent(event)">
                  <Users style="width:14px;height:14px" /> Liste d'attente
                </button>
                <button class="btn btn-outline-secondary btn-sm" @click.stop="toggleEventVisibility(event)">
                  {{ event.isPublished ? 'Passer privée' : 'Rendre publique' }}
                </button>
                <button class="btn btn-outline-warning btn-sm" @click.stop="toggleEventRegistrations(event)">
                  {{ isOpenForRegistrations(event) ? 'Fermer inscriptions' : 'Ouvrir inscriptions' }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="hasMoreEvents" ref="sentinelRef" class="text-center py-2 text-muted small">Faites défiler ou chargez plus d'événements.</div>
          <button v-if="hasMoreEvents" class="btn btn-outline-primary btn-sm w-100" @click="loadMoreEvents">Voir plus d'événements</button>

          <EmptyState
            v-if="!isLoading && !errorMessage && filteredEvents.length === 0"
            icon="🗂"
            title="Aucun événement à afficher"
            description="Ajustez vos filtres ou créez un nouvel événement."
          />
        </div>
      </div>
    </div>

    <Teleport to="body">
      <div v-if="showDetailsDrawer" class="drawer-overlay" @click="closeDetailsDrawer"></div>
      <aside v-if="showDetailsDrawer" class="event-drawer shadow-lg">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="x-small text-muted">Détail événement</div>
            <h5 class="mb-0">{{ drawerEvent?.name }}</h5>
          </div>
          <button class="btn btn-sm btn-light" @click="closeDetailsDrawer"><X style="width:16px;height:16px" /></button>
        </div>
        <div class="p-3 d-flex flex-column gap-3" v-if="drawerEvent">
          <div class="small text-muted">{{ drawerEvent.description }}</div>
          <div class="small"><strong>Lieu:</strong> {{ drawerEvent.location }}</div>
          <div class="small"><strong>Organisateur:</strong> {{ drawerEvent.organizer }}</div>
          <div class="small"><strong>Période:</strong> {{ formatDateRange(drawerEvent.startDate, drawerEvent.endDate) }}</div>
          <div class="small"><strong>Statut:</strong> {{ formatStatusLabel(getEventStatus(drawerEvent)) }}</div>

          <div class="drawer-timeline">
            <div class="mini-timeline__bar">
              <div class="mini-timeline__progress" :style="{ width: timelineProgress(drawerEvent) + '%' }"></div>
            </div>
            <div class="d-flex justify-content-between x-small text-muted mt-1">
              <span>{{ formatShortDate(drawerEvent.startDate) }}</span>
              <span>Aujourd'hui</span>
              <span>{{ formatShortDate(drawerEvent.endDate) }}</span>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <span v-for="issue in healthIssues(drawerEvent)" :key="issue" class="badge bg-light text-dark border">{{ issue }}</span>
          </div>

          <div class="d-grid gap-2">
            <button class="btn btn-primary" @click="router.push('/manage-events/edit/' + drawerEvent.id)">Modifier</button>
            <button class="btn btn-outline-secondary" @click="cloneEvent(drawerEvent)">Cloner l'événement</button>
            <button class="btn btn-outline-secondary" @click="toggleEventVisibility(drawerEvent)">
              {{ drawerEvent.isPublished ? 'Passer privée' : 'Rendre publique' }}
            </button>
            <button class="btn btn-outline-warning" @click="toggleEventRegistrations(drawerEvent)">
              {{ isOpenForRegistrations(drawerEvent) ? 'Fermer inscriptions' : 'Ouvrir inscriptions' }}
            </button>
            <button class="btn btn-outline-primary" @click="openWaitingListForEvent(drawerEvent)">Ouvrir la liste d'attente</button>
          </div>
        </div>
      </aside>
    </Teleport>

    <Teleport to="body">
      <div v-if="showWaitingListModal" class="modal d-block" tabindex="-1" style="background:rgba(0,0,0,.45)">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow">
            <div class="modal-header">
              <div>
                <h5 class="modal-title mb-1">Liste d'attente de l'événement</h5>
                <div class="small text-muted">{{ selectedEventForQueue?.name }}</div>
              </div>
              <button class="btn-close" @click="closeWaitingListModal"></button>
            </div>

            <div class="modal-body d-flex flex-column gap-3">
              <div class="alert alert-info small mb-0">Choisissez une mission de destination, puis assignez les bénévoles unitairement ou en lot.</div>

              <div class="row g-2">
                <div class="col-6 col-md-3">
                  <div class="queue-stat border rounded p-2 h-100">
                    <div class="x-small text-muted">En attente</div>
                    <div class="fw-semibold">{{ waitingList.length }}</div>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="queue-stat border rounded p-2 h-100">
                    <div class="x-small text-muted">Affichés</div>
                    <div class="fw-semibold">{{ filteredWaitingList.length }}</div>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="queue-stat border rounded p-2 h-100">
                    <div class="x-small text-muted">Sélectionnés</div>
                    <div class="fw-semibold">{{ selectedWaitingEntries.length }}</div>
                  </div>
                </div>
                <div class="col-6 col-md-3">
                  <div class="queue-stat border rounded p-2 h-100">
                    <div class="x-small text-muted">Places restantes</div>
                    <div class="fw-semibold" :class="remainingMissionSlots <= 0 ? 'text-danger' : 'text-success'">
                      {{ selectedMissionForQueue ? Math.max(remainingMissionSlots, 0) : '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="row g-2">
                <div class="col-12 col-md-5">
                  <label class="form-label small fw-medium">Mission de destination</label>
                  <select v-model="selectedMissionIdForQueue" class="form-select">
                    <option value="">Sélectionner une mission...</option>
                    <option v-for="mission in eventMissions" :key="mission.id" :value="mission.id">
                      {{ mission.name }} ({{ mission.currentVolunteers }}/{{ mission.maxVolunteers }})
                    </option>
                  </select>
                  <div v-if="selectedMissionForQueue" class="x-small mt-1" :class="remainingMissionSlots <= 0 ? 'text-danger' : 'text-muted'">
                    {{ selectedMissionForQueue.currentVolunteers }}/{{ selectedMissionForQueue.maxVolunteers }} inscrits
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <label class="form-label small fw-medium">Rechercher un bénévole</label>
                  <input v-model="queueSearchQuery" class="form-control" type="text" placeholder="Nom ou email..." />
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label small fw-medium">Trier par</label>
                  <select v-model="queueSortBy" class="form-select">
                    <option value="oldest">Plus ancien</option>
                    <option value="newest">Plus récent</option>
                    <option value="name_asc">Nom A-Z</option>
                  </select>
                </div>
              </div>

              <div class="d-flex flex-wrap align-items-center gap-2 queue-toolbar">
                <div class="form-check m-0">
                  <input id="queueSelectAll" class="form-check-input" type="checkbox" :checked="allFilteredSelected" :disabled="filteredWaitingList.length === 0" @change="toggleSelectAllFiltered" />
                  <label for="queueSelectAll" class="form-check-label small">Tout sélectionner (filtre actif)</label>
                </div>
                <button
                  class="btn btn-sm btn-primary"
                  :disabled="!canAssignToSelectedMission || selectedWaitingEntries.length === 0 || assigningBulk"
                  @click="assignSelectedVolunteers"
                >
                  {{ assigningBulk ? 'Assignation en cours...' : `Assigner la sélection (${selectedWaitingEntries.length})` }}
                </button>
              </div>

              <div v-if="waitingListSuccess" class="alert alert-success small mb-0">{{ waitingListSuccess }}</div>
              <div v-if="waitingListError" class="alert alert-danger small mb-0">{{ waitingListError }}</div>

              <div v-if="waitingListLoading" class="text-muted small">Chargement de la liste d'attente...</div>
              <div v-else-if="filteredWaitingList.length === 0" class="text-muted small">Aucun bénévole en attente pour cet événement.</div>

              <div v-else class="d-flex flex-column gap-2">
                <div v-for="entry in filteredWaitingList" :key="entry.id" class="border rounded p-3 d-flex flex-column gap-2">
                  <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="d-flex align-items-start gap-2">
                      <input
                        class="form-check-input mt-1"
                        type="checkbox"
                        :checked="isWaitingSelected(entry.id)"
                        @change="toggleWaitingSelection(entry.id)"
                      />
                      <div>
                        <div class="fw-semibold">{{ entry.fullName }}</div>
                        <div class="small text-muted">{{ entry.email || 'Email non disponible' }}</div>
                      </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                      <span class="badge bg-light text-dark border">En attente</span>
                      <span class="badge border" :class="priorityBadgeClass(waitingPriority(entry))">
                        {{ waitingPriority(entry) }}
                      </span>
                    </div>
                  </div>
                  <div class="small text-muted d-flex flex-wrap gap-3">
                    <span>Inscription: {{ formatDateTime(entry.appliedAt) }}</span>
                    <span>Attente: {{ waitingDays(entry) }} jour(s)</span>
                    <span v-if="entry.remark">Remarque: {{ entry.remark }}</span>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button class="btn btn-primary btn-sm" :disabled="!canAssignToSelectedMission || assigningPostulationId === entry.id" @click="assignVolunteerToSelectedMission(entry)">
                      {{ assigningPostulationId === entry.id ? 'Assignation...' : 'Assigner à la mission sélectionnée' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <div v-if="selectedMissionIdForQueue && selectedMissionIsFull" class="small text-danger me-auto">La mission sélectionnée est complète.</div>
              <button class="btn btn-outline-secondary" @click="closeWaitingListModal">Fermer</button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

      <script setup>
      import { ref, computed, onMounted, watch } from 'vue'
      import { useRouter } from 'vue-router'
      import { ArrowLeft, Plus, Edit, Trash2, Calendar, MapPin, Users, Briefcase, Search, X, Copy } from 'lucide-vue-next'
      import eventService from '@/services/eventService'
      import missionService from '@/services/missionService'
      import api from '@/services/api'
      import chatApiService from '@/services/chatApiService'
      import { getCurrentUser, hasMinRole } from '@/utils/auth'
      import CardListSkeleton from '@/components/ui/CardListSkeleton.vue'
      import EmptyState from '@/components/ui/EmptyState.vue'
      import { useInfiniteScroll } from '@/composables/useInfiniteScroll'
      import { parseLocalDateTime, normalizeDateInput } from '@/utils/dateTime'
      import { useToast } from '@/composables/useToast'

      const router = useRouter()
      const user = getCurrentUser()
      const toast = useToast()
      if (!user || !hasMinRole('admin')) router.push('/')

      const eventsList = ref([])
      const isLoading = ref(true)
      const errorMessage = ref('')
      const searchQuery = ref('')
      const sortBy = ref('nearest')
      const statusTab = ref('upcoming')
      const quickFilters = ref({
        publicOnly: false,
        privateOnly: false,
        fullOnly: false,
        openRegistrationsOnly: false,
      })

      const showWaitingListModal = ref(false)
      const selectedEventForQueue = ref(null)
      const selectedMissionIdForQueue = ref('')
      const eventMissions = ref([])
      const waitingList = ref([])
      const waitingListLoading = ref(false)
      const waitingListError = ref('')
      const waitingListSuccess = ref('')
      const queueSearchQuery = ref('')
      const queueSortBy = ref('oldest')
      const assigningPostulationId = ref('')
      const selectedWaitingIds = ref([])
      const assigningBulk = ref(false)

      const selectedEventIds = ref([])
      const isBulkSaving = ref(false)

      const showDetailsDrawer = ref(false)
      const drawerEventId = ref('')

      const PAGE_SIZE = 6
      const visibleCount = ref(PAGE_SIZE)

      const nowTimestamp = () => Date.now()
      const normalize = (value) => String(value || '').trim().toLowerCase()

      const getEventStartDate = (event) => {
        const startDate = event.startDate || event.date
        const startTime = event.startTime || '00:00'
        return parseLocalDateTime(startDate, startTime)
      }

      const getEventEndDate = (event) => {
        const endDate = event.endDate || event.startDate || event.date
        const endTime = event.endTime || '23:59'
        return parseLocalDateTime(endDate, endTime)
      }

      const getRemainingSpots = (event) => Number(event.totalVolunteersNeeded || 0) - Number(event.currentVolunteers || 0)
      const isEventFull = (event) => getRemainingSpots(event) <= 0

      const getEventStatus = (event) => {
        if (event.isCancelled) return 'cancelled'

        const start = getEventStartDate(event)?.getTime()
        const end = getEventEndDate(event)?.getTime()
        const now = nowTimestamp()

        if (start == null || end == null || Number.isNaN(start) || Number.isNaN(end)) {
          return 'upcoming'
        }

        if (now < start) return 'upcoming'
        if (now > end) return 'finished'
        return 'active'
      }

      const formatStatusLabel = (status) => ({
        upcoming: 'À venir',
        active: 'En cours',
        finished: 'Terminé',
        cancelled: 'Annulé',
      }[status] || 'Inconnu')

      const isOpenForRegistrations = (event) => {
        if (event.isCancelled) return false
        if (!event.isPublished) return false
        if (getEventStatus(event) !== 'upcoming') return false
        return !isEventFull(event)
      }

      const healthIssues = (event) => {
        const issues = []
        if ((event.locationMode || 'manual') === 'manual' && !event.googleMapsUrl) {
          issues.push('Périmètre map manquant')
        }
        if (Number(event.missionsCount || 0) === 0) issues.push('Aucune mission liée')
        if (!event.isPublished) issues.push('Non publié')
        if (isEventFull(event)) issues.push('Quota atteint')
        if (Number(event.currentVolunteers || 0) === 0 && getEventStatus(event) === 'upcoming') issues.push('Aucun inscrit')
        return issues
      }

      const timelineProgress = (event) => {
        const start = getEventStartDate(event)?.getTime()
        const end = getEventEndDate(event)?.getTime()
        const now = nowTimestamp()

        if (start == null || end == null || Number.isNaN(start) || Number.isNaN(end) || end <= start) return 0
        if (now <= start) return 0
        if (now >= end) return 100

        return Math.max(0, Math.min(100, Math.round(((now - start) / (end - start)) * 100)))
      }

      const statusCounts = computed(() => {
        const counts = { upcoming: 0, active: 0, finished: 0, cancelled: 0 }
        for (const event of eventsList.value) {
          counts[getEventStatus(event)] += 1
        }
        return counts
      })

      const activeQuickFiltersCount = computed(() =>
        [
          quickFilters.value.publicOnly,
          quickFilters.value.privateOnly,
          quickFilters.value.fullOnly,
          quickFilters.value.openRegistrationsOnly,
        ].filter(Boolean).length
      )

      const filteredEvents = computed(() => {
        const query = normalize(searchQuery.value)

        let rows = eventsList.value.filter((event) => {
          const status = getEventStatus(event)
          const matchStatus = statusTab.value === 'all' ? true : status === statusTab.value

          const searchable = [event.name, event.location, event.organizer]
            .map(normalize)
            .join(' ')
          const matchSearch = !query || searchable.includes(query)

          const matchPublic = !quickFilters.value.publicOnly || event.isPublished
          const matchPrivate = !quickFilters.value.privateOnly || !event.isPublished
          const matchFull = !quickFilters.value.fullOnly || isEventFull(event)
          const matchOpen = !quickFilters.value.openRegistrationsOnly || isOpenForRegistrations(event)

          if (quickFilters.value.publicOnly && quickFilters.value.privateOnly) {
            return false
          }

          return matchStatus && matchSearch && matchPublic && matchPrivate && matchFull && matchOpen
        })

        rows = rows.sort((a, b) => {
          if (sortBy.value === 'newest') {
            return (getEventStartDate(b)?.getTime() || 0) - (getEventStartDate(a)?.getTime() || 0)
          }

          if (sortBy.value === 'volunteers_desc') {
            return Number(b.totalVolunteersNeeded || 0) - Number(a.totalVolunteersNeeded || 0)
          }

          if (sortBy.value === 'spots_asc') {
            return getRemainingSpots(a) - getRemainingSpots(b)
          }

          return (getEventStartDate(a)?.getTime() || 0) - (getEventStartDate(b)?.getTime() || 0)
        })

        return rows
      })

      const visibleEvents = computed(() => filteredEvents.value.slice(0, visibleCount.value))
      const hasMoreEvents = computed(() => visibleCount.value < filteredEvents.value.length)
      const loadMoreEvents = () => {
        if (hasMoreEvents.value) visibleCount.value += PAGE_SIZE
      }
      const { sentinelRef } = useInfiniteScroll({ canLoadMore: () => hasMoreEvents.value, onLoadMore: loadMoreEvents })

      const drawerEvent = computed(() =>
        eventsList.value.find((event) => event.id === drawerEventId.value) || null
      )

      const totalMissions = computed(() => filteredEvents.value.reduce((sum, event) => sum + Number(event.missionsCount || 0), 0))
      const totalVolunteers = computed(() => filteredEvents.value.reduce((sum, event) => sum + Number(event.currentVolunteers || 0), 0))
      const totalPlaces = computed(() => filteredEvents.value.reduce((sum, event) => sum + Number(event.totalVolunteersNeeded || 0), 0))

      const selectedMissionForQueue = computed(() =>
        eventMissions.value.find((mission) => mission.id === selectedMissionIdForQueue.value) || null
      )

      const remainingMissionSlots = computed(() => {
        const mission = selectedMissionForQueue.value
        if (!mission) return 0
        return Number(mission.maxVolunteers || 0) - Number(mission.currentVolunteers || 0)
      })

      const selectedMissionIsFull = computed(() => {
        const mission = selectedMissionForQueue.value
        if (!mission) return false
        return remainingMissionSlots.value <= 0
      })

      const canAssignToSelectedMission = computed(() => !!selectedMissionForQueue.value && !selectedMissionIsFull.value)

      const waitingDays = (entry) => {
        if (!entry?.appliedAt) return 0
        const appliedAt = new Date(entry.appliedAt).getTime()
        if (Number.isNaN(appliedAt)) return 0
        const diff = nowTimestamp() - appliedAt
        return Math.max(0, Math.floor(diff / (1000 * 60 * 60 * 24)))
      }

      const waitingPriority = (entry) => {
        const days = waitingDays(entry)
        if (days >= 14) return 'Urgent'
        if (days >= 7) return 'Élevée'
        return 'Normale'
      }

      const priorityBadgeClass = (priority) => ({
        Urgent: 'bg-danger-subtle text-danger border-danger',
        Élevée: 'bg-warning-subtle text-warning border-warning',
        Normale: 'bg-success-subtle text-success border-success',
      }[priority] || 'bg-light text-dark border-secondary')

      const filteredWaitingList = computed(() => {
        const query = queueSearchQuery.value.trim().toLowerCase()

        let rows = waitingList.value
        if (query) {
          rows = rows.filter((entry) =>
            [entry.fullName, entry.email].some((value) => String(value || '').toLowerCase().includes(query))
          )
        }

        rows = [...rows].sort((a, b) => {
          if (queueSortBy.value === 'newest') {
            return new Date(b.appliedAt || 0).getTime() - new Date(a.appliedAt || 0).getTime()
          }

          if (queueSortBy.value === 'name_asc') {
            return String(a.fullName || '').localeCompare(String(b.fullName || ''), 'fr', { sensitivity: 'base' })
          }

          return new Date(a.appliedAt || 0).getTime() - new Date(b.appliedAt || 0).getTime()
        })

        return rows
      })

      const selectedWaitingEntries = computed(() => {
        const selected = new Set(selectedWaitingIds.value)
        return filteredWaitingList.value.filter((entry) => selected.has(entry.id))
      })

      const allFilteredSelected = computed(() => {
        if (filteredWaitingList.value.length === 0) return false
        const selected = new Set(selectedWaitingIds.value)
        return filteredWaitingList.value.every((entry) => selected.has(entry.id))
      })

      const isWaitingSelected = (entryId) => selectedWaitingIds.value.includes(entryId)

      const toggleWaitingSelection = (entryId) => {
        if (isWaitingSelected(entryId)) {
          selectedWaitingIds.value = selectedWaitingIds.value.filter((id) => id !== entryId)
          return
        }

        selectedWaitingIds.value = [...selectedWaitingIds.value, entryId]
      }

      const toggleSelectAllFiltered = () => {
        if (allFilteredSelected.value) {
          const filteredIds = new Set(filteredWaitingList.value.map((entry) => entry.id))
          selectedWaitingIds.value = selectedWaitingIds.value.filter((id) => !filteredIds.has(id))
          return
        }

        const merged = new Set(selectedWaitingIds.value)
        for (const entry of filteredWaitingList.value) {
          merged.add(entry.id)
        }
        selectedWaitingIds.value = Array.from(merged)
      }

      const formatDate = (d) => {
        if (!d) return '-'
        const parsed = parseLocalDateTime(d, '00:00')
        if (!parsed) return '-'
        return parsed.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
      }

      const formatShortDate = (d) => {
        if (!d) return '-'
        const parsed = parseLocalDateTime(d, '00:00')
        if (!parsed) return '-'
        return parsed.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
      }

      const formatDateRange = (start, end) => {
        const startLabel = formatDate(start)
        const endLabel = formatDate(end)
        if (startLabel === endLabel) return startLabel
        return `${startLabel} - ${endLabel}`
      }

      const formatDateTime = (value) => {
        if (!value) return 'Date inconnue'
        const parsed = new Date(value)
        if (Number.isNaN(parsed.getTime())) return 'Date inconnue'

        return parsed.toLocaleString('fr-FR', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit',
        })
      }

      const categoryBadgeClass = (cat) => ({
        Sport: 'bg-primary-subtle text-primary border-primary',
        Festival: 'bg-info-subtle text-info border-info',
        Salon: 'bg-warning-subtle text-warning border-warning',
        Écologie: 'bg-success-subtle text-success border-success',
      }[cat] || 'bg-secondary-subtle text-secondary border-secondary')

      const toIsoDate = (dateValue) => {
        return normalizeDateInput(dateValue)
      }

      const addDays = (dateValue, daysToAdd) => {
        const date = parseLocalDateTime(dateValue, '00:00')
        if (!date) return ''
        date.setDate(date.getDate() + daysToAdd)
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        return `${year}-${month}-${day}`
      }

      const buildEventPayload = (event, overrides = {}) => ({
        ...event,
        ...overrides,
        startDate: overrides.startDate ?? event.startDate,
        endDate: overrides.endDate ?? event.endDate,
        date: overrides.startDate ?? event.startDate,
        totalVolunteersNeeded: overrides.totalVolunteersNeeded ?? event.totalVolunteersNeeded,
      })

      const loadEvents = async () => {
        isLoading.value = true
        errorMessage.value = ''

        try {
          const [eventsPayload, missionsPayload] = await Promise.all([
            eventService.getAll(),
            missionService.getAll().catch(() => []),
          ])

          const missionStatsByEvent = (Array.isArray(missionsPayload) ? missionsPayload : []).reduce((acc, mission) => {
            const eventId = String(mission.eventId || '')
            if (!eventId) return acc

            if (!acc[eventId]) {
              acc[eventId] = { currentVolunteers: 0, totalCapacity: 0, missionsCount: 0 }
            }

            acc[eventId].currentVolunteers += Number(mission.currentVolunteers || 0)
            acc[eventId].totalCapacity += Number(mission.maxVolunteers || 0)
            acc[eventId].missionsCount += 1
            return acc
          }, {})

          eventsList.value = (Array.isArray(eventsPayload) ? eventsPayload : []).map((event) => {
            const stats = missionStatsByEvent[event.id] || { currentVolunteers: 0, totalCapacity: 0, missionsCount: 0 }
            return {
              ...event,
              missionsCount: Number(event.missionsCount || stats.missionsCount || 0),
              currentVolunteers: Number(stats.currentVolunteers || 0),
              totalVolunteersNeeded: Number(event.totalVolunteersNeeded || stats.totalCapacity || 0),
            }
          })
        } catch (error) {
          errorMessage.value = error.message || 'Impossible de charger les événements'
        } finally {
          isLoading.value = false
        }
      }

      const canDeleteEvent = (event) => getEventStatus(event) === 'upcoming'

      const handleDeleteEvent = async (id) => {
        if (!confirm('Supprimer cet événement ? Les missions associées seront également supprimées.')) return

        try {
          await eventService.delete(id)
          eventsList.value = eventsList.value.filter((event) => event.id !== id)
          selectedEventIds.value = selectedEventIds.value.filter((eventId) => eventId !== id)
          if (drawerEventId.value === id) closeDetailsDrawer()
          toast.success('Événement supprimé avec succès.')
        } catch (error) {
          toast.error(error.message || 'Erreur lors de la suppression de l\'événement.')
        }
      }

      const updateSingleEvent = async (eventId, updater, successMessage) => {
        const target = eventsList.value.find((event) => event.id === eventId)
        if (!target) return

        try {
          const nextPayload = updater(target)
          await eventService.update(eventId, nextPayload)
          Object.assign(target, nextPayload)
          if (successMessage) toast.success(successMessage)
        } catch (error) {
          toast.error(error.message || 'Mise à jour impossible pour le moment.')
        }
      }

      const toggleEventVisibility = async (event) => {
        const nextPublished = !event.isPublished
        await updateSingleEvent(
          event.id,
          (current) => buildEventPayload(current, { isPublished: nextPublished }),
          nextPublished ? 'Événement rendu public.' : 'Événement passé en privé.'
        )
      }

      const toggleEventRegistrations = async (event) => {
        const willOpen = !isOpenForRegistrations(event)
        await updateSingleEvent(
          event.id,
          (current) => buildEventPayload(current, {
            isPublished: willOpen,
            isCancelled: false,
            cancellationDate: '',
            cancellationReason: '',
          }),
          willOpen ? 'Inscriptions ouvertes.' : 'Inscriptions fermées.'
        )
      }

      const cloneEvent = async (event) => {
        const startDate = event.startDate || event.date
        const endDate = event.endDate || startDate

        try {
          await eventService.create({
            ...event,
            name: `${event.name} (copie)`,
            startDate: addDays(startDate, 7),
            endDate: addDays(endDate, 7),
            date: addDays(startDate, 7),
            isPublished: false,
            isCancelled: false,
            cancellationDate: '',
            cancellationReason: '',
            imageFile: null,
          })

          await loadEvents()
          toast.success('Événement cloné avec succès.')
        } catch (error) {
          toast.error(error.message || 'Clonage impossible pour le moment.')
        }
      }

      const isSelected = (eventId) => selectedEventIds.value.includes(eventId)

      const toggleSelection = (eventId) => {
        if (isSelected(eventId)) {
          selectedEventIds.value = selectedEventIds.value.filter((id) => id !== eventId)
          return
        }

        selectedEventIds.value = [...selectedEventIds.value, eventId]
      }

      const clearSelection = () => {
        selectedEventIds.value = []
      }

      const applyBulkUpdate = async (updater, successMessage) => {
        if (selectedEventIds.value.length === 0) return
        isBulkSaving.value = true

        const errors = []

        for (const eventId of selectedEventIds.value) {
          const target = eventsList.value.find((event) => event.id === eventId)
          if (!target) continue

          try {
            const payload = updater(target)
            await eventService.update(eventId, payload)
            Object.assign(target, payload)
          } catch {
            errors.push(target.name)
          }
        }

        isBulkSaving.value = false

        if (errors.length > 0) {
          toast.warning(`${successMessage} (partiel). Échecs: ${errors.join(', ')}`)
        } else {
          toast.success(successMessage)
        }
      }

      const bulkSetPublished = async (value) => {
        await applyBulkUpdate(
          (event) => buildEventPayload(event, { isPublished: value }),
          value ? 'Événements rendus publics.' : 'Événements passés en privé.'
        )
      }

      const bulkSetRegistrations = async (value) => {
        await applyBulkUpdate(
          (event) => buildEventPayload(event, {
            isPublished: value,
            isCancelled: false,
            cancellationDate: '',
            cancellationReason: '',
          }),
          value ? 'Inscriptions ouvertes sur la sélection.' : 'Inscriptions fermées sur la sélection.'
        )
      }

      const bulkCancel = async () => {
        if (!confirm('Annuler les événements sélectionnés ?')) return
        const today = toIsoDate(new Date())

        await applyBulkUpdate(
          (event) => buildEventPayload(event, {
            isCancelled: true,
            cancellationDate: today,
            cancellationReason: event.cancellationReason || 'Annulé depuis la gestion en masse',
          }),
          'Événements annulés.'
        )
      }

      const bulkArchive = async () => {
        await applyBulkUpdate(
          (event) => buildEventPayload(event, { isPublished: false }),
          'Événements archivés (mode privé).'
        )
      }

      const openDetailsDrawer = (event) => {
        drawerEventId.value = event.id
        showDetailsDrawer.value = true
      }

      const closeDetailsDrawer = () => {
        showDetailsDrawer.value = false
        drawerEventId.value = ''
      }

      const mapWaitingPostulation = (postulation) => {
        const userPayload = postulation.utilisateur || {}
        const firstName = userPayload.prenom_utilisateur || ''
        const lastName = userPayload.nom_utilisateur || ''
        const fullName = `${firstName} ${lastName}`.trim() || `Utilisateur #${postulation.id_utilisateur}`

        return {
          id: String(postulation.id_postulation),
          postulationId: Number(postulation.id_postulation),
          userId: String(postulation.id_utilisateur || ''),
          fullName,
          email: userPayload.email || '',
          appliedAt: postulation.date_postulation,
          remark: postulation.remarque || '',
        }
      }

      const loadEventQueueContext = async (event) => {
        waitingListLoading.value = true
        waitingListError.value = ''
        waitingListSuccess.value = ''

        try {
          const [missionsPayload, postulationsResponse] = await Promise.all([
            missionService.getAll({ id_evenement: event.id }),
            api.get('/postulations'),
          ])

          eventMissions.value = missionsPayload
          selectedMissionIdForQueue.value = missionsPayload[0]?.id || ''

          const payload = postulationsResponse.data
          const rows = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []

          waitingList.value = rows
            .filter((postulation) => String(postulation.id_evenement) === event.id)
            .filter((postulation) => !postulation.id_mission)
            .filter((postulation) => postulation.statut_postulation === 'en_attente')
            .map(mapWaitingPostulation)
            .sort((a, b) => new Date(a.appliedAt || 0).getTime() - new Date(b.appliedAt || 0).getTime())
        } catch (error) {
          waitingListError.value = error.response?.data?.message || error.message || 'Impossible de charger la liste d\'attente.'
        } finally {
          waitingListLoading.value = false
        }
      }

      const openWaitingListForEvent = async (event) => {
        selectedEventForQueue.value = event
        queueSearchQuery.value = ''
        queueSortBy.value = 'oldest'
        selectedWaitingIds.value = []
        waitingList.value = []
        eventMissions.value = []
        showWaitingListModal.value = true
        await loadEventQueueContext(event)
      }

      const closeWaitingListModal = () => {
        showWaitingListModal.value = false
        selectedEventForQueue.value = null
        selectedMissionIdForQueue.value = ''
        eventMissions.value = []
        waitingList.value = []
        waitingListError.value = ''
        waitingListSuccess.value = ''
        queueSearchQuery.value = ''
        queueSortBy.value = 'oldest'
        selectedWaitingIds.value = []
        assigningPostulationId.value = ''
        assigningBulk.value = false
      }

      const assignVolunteerToSelectedMission = async (entry) => {
        if (!canAssignToSelectedMission.value) {
          waitingListError.value = 'Veuillez sélectionner une mission avec des places disponibles.'
          return
        }

        waitingListError.value = ''
        waitingListSuccess.value = ''
        assigningPostulationId.value = entry.id

        try {
          await api.put(`/postulations/${entry.postulationId}`, {
            id_mission: Number(selectedMissionForQueue.value.id),
            statut_postulation: 'accepte',
            remarque: `Assigné à la mission ${selectedMissionForQueue.value.name} depuis la gestion des événements.`,
          })

          const conversationResponse = await chatApiService.ensureMissionConversation({
            missionId: selectedMissionForQueue.value.id,
            name: `${selectedMissionForQueue.value.name} • ${selectedEventForQueue.value?.name || 'Événement'}`,
          })

          if (conversationResponse?.conversation?.id) {
            await chatApiService.addParticipant({
              conversationId: conversationResponse.conversation.id,
              userId: entry.userId,
            })
          }

          waitingList.value = waitingList.value.filter((postulation) => postulation.id !== entry.id)
          selectedWaitingIds.value = selectedWaitingIds.value.filter((id) => id !== entry.id)

          const missionIndex = eventMissions.value.findIndex((mission) => mission.id === selectedMissionForQueue.value.id)
          if (missionIndex !== -1) {
            const mission = eventMissions.value[missionIndex]
            eventMissions.value[missionIndex] = {
              ...mission,
              currentVolunteers: Number(mission.currentVolunteers || 0) + 1,
            }
          }

          waitingListSuccess.value = `${entry.fullName} a été assigné à ${selectedMissionForQueue.value.name}.`
          await loadEvents()
        } catch (error) {
          waitingListError.value = error.response?.data?.message || error.message || 'Assignation impossible pour le moment.'
        } finally {
          assigningPostulationId.value = ''
        }
      }

      const assignSelectedVolunteers = async () => {
        if (!canAssignToSelectedMission.value) {
          waitingListError.value = 'Veuillez sélectionner une mission avec des places disponibles.'
          return
        }

        if (selectedWaitingEntries.value.length === 0) {
          waitingListError.value = 'Sélectionnez au moins un bénévole.'
          return
        }

        waitingListError.value = ''
        waitingListSuccess.value = ''
        assigningBulk.value = true

        const maxAssignable = Math.max(0, remainingMissionSlots.value)
        const toAssign = selectedWaitingEntries.value.slice(0, maxAssignable)
        const failed = []

        for (const entry of toAssign) {
          try {
            await api.put(`/postulations/${entry.postulationId}`, {
              id_mission: Number(selectedMissionForQueue.value.id),
              statut_postulation: 'accepte',
              remarque: `Assigné à la mission ${selectedMissionForQueue.value.name} depuis la gestion des événements.`,
            })

            const conversationResponse = await chatApiService.ensureMissionConversation({
              missionId: selectedMissionForQueue.value.id,
              name: `${selectedMissionForQueue.value.name} • ${selectedEventForQueue.value?.name || 'Événement'}`,
            })

            if (conversationResponse?.conversation?.id) {
              await chatApiService.addParticipant({
                conversationId: conversationResponse.conversation.id,
                userId: entry.userId,
              })
            }

            waitingList.value = waitingList.value.filter((postulation) => postulation.id !== entry.id)
            selectedWaitingIds.value = selectedWaitingIds.value.filter((id) => id !== entry.id)

            const missionIndex = eventMissions.value.findIndex((mission) => mission.id === selectedMissionForQueue.value.id)
            if (missionIndex !== -1) {
              const mission = eventMissions.value[missionIndex]
              eventMissions.value[missionIndex] = {
                ...mission,
                currentVolunteers: Number(mission.currentVolunteers || 0) + 1,
              }
            }
          } catch {
            failed.push(entry.fullName)
          }
        }

        assigningBulk.value = false

        if (toAssign.length === 0) {
          waitingListError.value = 'La mission est complète.'
          return
        }

        if (failed.length > 0) {
          waitingListError.value = `Assignation partielle. Échecs: ${failed.join(', ')}`
        }

        const skipped = selectedWaitingEntries.value.length - toAssign.length
        waitingListSuccess.value = skipped > 0
          ? `${toAssign.length} bénévole(s) assigné(s). ${skipped} non assigné(s) faute de places.`
          : `${toAssign.length} bénévole(s) assigné(s) avec succès.`

        await loadEvents()
      }

      const resetQuickFilters = () => {
        quickFilters.value.publicOnly = false
        quickFilters.value.privateOnly = false
        quickFilters.value.fullOnly = false
        quickFilters.value.openRegistrationsOnly = false
      }

      onMounted(loadEvents)
      watch([
        searchQuery,
        statusTab,
        sortBy,
        () => quickFilters.value.publicOnly,
        () => quickFilters.value.privateOnly,
        () => quickFilters.value.fullOnly,
        () => quickFilters.value.openRegistrationsOnly,
      ], () => {
        visibleCount.value = PAGE_SIZE
      })

      watch(filteredWaitingList, (rows) => {
        const validIds = new Set(rows.map((entry) => entry.id))
        selectedWaitingIds.value = selectedWaitingIds.value.filter((id) => validIds.has(id))
      })
      </script>

      <style scoped>
      .filter-pills {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
      }

      .stat-card {
        border: 0;
        box-shadow: 0 6px 18px rgba(26, 34, 48, 0.08);
      }

      .bulk-actions-card {
        border-left: 4px solid #3b82f6;
      }

      .event-row {
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border-color: #e5e7eb !important;
      }

      .text-clamp-2 {
        -webkit-line-clamp: 2;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .mini-timeline__bar {
        width: 100%;
        height: 8px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
      }

      .mini-timeline__progress {
        height: 100%;
        background: linear-gradient(90deg, #2563eb 0%, #22c55e 100%);
      }

      .drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
        z-index: 1040;
      }

      .event-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: min(100%, 420px);
        height: 100dvh;
        background: #fff;
        z-index: 1045;
        overflow-y: auto;
      }

      .queue-stat {
        background: #f8fafc;
      }

      .queue-toolbar {
        padding: 0.5rem 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #f8fafc;
      }

      @media (max-width: 767px) {
        .event-drawer {
          width: 100%;
        }
      }
      </style>
