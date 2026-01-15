<template>
  <div class="my-5" lang="fr">
    <!-- Titre principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-primary">Liste des Sessions</h1>
      <a :href="$routing.generate('app_session_new')" class="btn btn-success">
        <i class="fas fa-plus"></i> Nouvelle Session
      </a>
    </div>

    <!-- Carte des filtres modernisée -->
    <div class="card mb-4 border-0 shadow-sm filters-card">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center">
            <div class="filter-icon-wrapper me-3">
              <i class="fas fa-filter"></i>
            </div>
            <h5 class="mb-0 fw-bold">Filtres de recherche</h5>
          </div>
        </div>

        <div class="row g-3">
          <!-- Recherche textuelle -->
          <div class="col-md-6 col-lg-4">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-search me-1"></i> Recherche globale
            </label>
            <div class="input-group input-group-modern">
              <span class="input-group-text bg-light border-end-0">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input
                  type="text"
                  v-model="searchInput"
                  @input="debouncedApplyFilters"
                  class="form-control border-start-0 ps-0"
                  placeholder="Salle, enseignant, classe..."
              />
            </div>
          </div>

          <!-- ✅ Année scolaire -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-school me-1"></i> Année scolaire
            </label>
            <select v-model="selectedSchoolYear" @change="applyFilters" class="form-select modern-select">
              <option v-for="y in schoolYears" :key="y" :value="y">
                {{ y }}
              </option>
            </select>
          </div>

          <!-- ✅ Date From -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-calendar-day me-1"></i> Du
            </label>
            <input
                type="date"
                v-model="dateFrom"
                @change="applyFilters"
                class="form-control modern-select"
            />
          </div>

          <!-- ✅ Date To -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-calendar-day me-1"></i> Au
            </label>
            <input
                type="date"
                v-model="dateTo"
                @change="applyFilters"
                class="form-control modern-select"
            />
          </div>

          <!-- Filtre par mois (optionnel: tu peux le supprimer si dateFrom/dateTo suffit) -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-calendar-alt me-1"></i> Mois
            </label>
            <select v-model="selectedMonth" @change="applyFilters" class="form-select modern-select">
              <option value="">Tous les mois</option>
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.text }}</option>
            </select>
          </div>

          <!-- Filtre par classe -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-users me-1"></i> Classe
            </label>
            <select v-model="selectedClassId" @change="applyFilters" class="form-select modern-select">
              <option value="">Toutes</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                {{ cls.name }} - [{{ cls.speciality }}]
                <span v-if="cls.schoolYear"> ({{ cls.schoolYear }})</span>
              </option>
            </select>
          </div>

          <!-- ✅ Filtre par type de classe -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-layer-group me-1"></i> Type
            </label>
            <select v-model="selectedClassType" @change="applyFilters" class="form-select modern-select">
              <option v-for="ct in classTypes" :key="ct.value" :value="ct.value">
                {{ ct.text }}
              </option>
            </select>
          </div>


          <!-- Filtre par spécialité -->
          <div class="col-md-6 col-lg-3">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-graduation-cap me-1"></i> Spécialité
            </label>
            <div class="input-group input-group-modern">
              <span class="input-group-text bg-light border-end-0">
                <i class="fas fa-graduation-cap text-muted"></i>
              </span>
              <input
                  type="text"
                  v-model="selectedSpeciality"
                  @input="debouncedApplyFilters"
                  class="form-control border-start-0 ps-0"
                  placeholder="Filtrer par spécialité"
              />
            </div>
          </div>

          <!-- Filtre par enseignant (admin uniquement) -->
          <div v-if="isAdmin" class="col-md-6 col-lg-3">
            <label class="form-label text-muted mb-2">
              <i class="fas fa-chalkboard-teacher me-1"></i> Enseignant
            </label>
            <select v-model="selectedTeacherId" @change="applyFilters" class="form-select modern-select">
              <option value="">Tous</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.firstName }} {{ teacher.lastName }}
              </option>
            </select>
          </div>
        </div>

        <!-- Boutons -->
        <div class="d-flex justify-content-end mt-3 gap-2">
          <button class="btn btn-outline-secondary btn-sm clear-filters-btn" @click="resetToCurrentMonth">
            <i class="fas fa-calendar me-2"></i> Mois courant
          </button>
          <button class="btn btn-outline-secondary btn-sm clear-filters-btn" @click="clearFilters">
            <i class="fas fa-times-circle me-2"></i> Réinitialiser
          </button>
        </div>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="card mb-4 border-0 shadow-sm">
      <div class="card-body p-4">
        <h5 class="card-title mb-3 fw-bold">
          <i class="fas fa-chart-bar me-2 text-primary"></i> Indicateurs des Sessions par Mois
        </h5>
        <div v-if="hasStats" class="row">
          <div class="col-md-4 mb-3" v-for="(stat, month) in sessionStats" :key="month">
            <div class="card stats-card border-0 shadow-sm h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <h6 class="card-title mb-0 text-capitalize fw-bold">{{ month }}</h6>
                  <div class="stats-icon">
                    <i class="fas fa-calendar-check"></i>
                  </div>
                </div>
                <div class="stats-content">
                  <div class="stat-item">
                    <span class="stat-value">{{ stat.totalSessions }}</span>
                    <span class="stat-label">sessions</span>
                  </div>
                  <div class="stat-divider"></div>
                  <div class="stat-item">
                    <span class="stat-value">{{ formatDuration(stat.totalHours) }}</span>
                    <span class="stat-label">durée totale</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center text-muted py-5">
          <i class="fas fa-info-circle fa-2x mb-3 opacity-50"></i>
          <p class="mb-0">Aucune donnée disponible pour les statistiques.</p>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="filteredSessions.length > 0" class="table-responsive">
          <table class="table table-hover mb-0 align-middle modern-table">
            <thead class="table-header">
            <tr>
              <th>État des présences</th>
              <th>Date</th>
              <th>Heure de début</th>
              <th>Heure de fin</th>
              <th>Durée</th>
              <th>Statut</th>
              <th>Salle</th>
              <th>Enseignant</th>
              <th>Classe</th>
              <th>Spécialité</th>
              <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="session in filteredSessions"
                :key="session.id"
                :class="{ 'duplicate-highlight': isDuplicateSession(session) }"
            >
              <td>
                  <span v-if="session.presenceCount > 0" class="badge bg-success-soft text-success">
                    <i class="fas fa-user-check me-1"></i>
                    {{ session.presenceCount }}
                  </span>

                <span v-else class="badge bg-warning-soft text-warning">
                    <i class="fas fa-user-times me-1"></i>
                    Non saisie
                  </span>
              </td>

              <td>{{ session.startTime ? formatDateUTC(session.startTime) : 'N/A' }}</td>
              <td>{{ session.startTime ? formatTimeUTC(session.startTime) : 'N/A' }}</td>
              <td>{{ session.endTime ? formatTimeUTC(session.endTime) : 'N/A' }}</td>
              <td><strong>{{ getSessionDuration(session) }}</strong></td>

              <td>
                  <span :class="['badge', statusBadgeClass(getSessionStatus(session))]">
                    {{ getSessionStatus(session) }}
                  </span>
              </td>

              <td>{{ session.room?.name || '-' }}</td>
              <td>{{ session.teacher?.lastName }} {{ session.teacher?.firstName }}</td>
              <td>{{ session.studyClass?.name }}</td>
              <td>{{ session.studyClass?.speciality }}</td>

              <td class="text-center">
                <div class="dropdown">
                  <button
                      class="btn btn-sm btn-light action-menu-btn"
                      type="button"
                      :id="'actionMenu' + session.id"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                      title="Actions"
                  >
                    <i class="fas fa-cog"></i>
                  </button>
                  <ul
                      class="dropdown-menu dropdown-menu-end shadow-sm action-dropdown"
                      :aria-labelledby="'actionMenu' + session.id"
                  >
                    <li>
                      <a class="dropdown-item" :href="$routing.generate('app_session_show', { id: session.id })">
                        <i class="fas fa-eye text-info me-2"></i> Voir
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" :href="$routing.generate('app_session_edit', { id: session.id })">
                        <i class="fas fa-edit text-warning me-2"></i> Modifier
                      </a>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li>
                      <a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(session)">
                        <i class="fas fa-trash me-2"></i> Supprimer
                      </a>
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- État vide -->
        <div v-else class="p-5 text-center text-muted empty-state">
          <div class="empty-state-icon mb-3">
            <i class="fas fa-inbox fa-4x opacity-25"></i>
          </div>
          <h5 class="mb-2">Aucune session trouvée</h5>
          <p class="text-muted mb-0">Essayez de modifier vos critères de recherche</p>
        </div>
      </div>
    </div>

    <!-- Modal suppression -->
    <div
        class="modal fade"
        id="deleteConfirmationModal"
        tabindex="-1"
        aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Confirmation de suppression
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body p-4">
            <p class="mb-0">Êtes-vous sûr de vouloir supprimer cette session ? Cette action est irréversible.</p>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger" @click="deleteSession" data-bs-dismiss="modal">
              <i class="fas fa-trash me-2"></i> Confirmer la suppression
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert -->
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "SessionList",
  components: { Alert },

  data() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    const toISODate = (d) => {
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      const dd = String(d.getDate()).padStart(2, "0");
      return `${yyyy}-${mm}-${dd}`;
    };

    return {
      allSessions: [],

      // Filtres
      searchInput: "",
      selectedMonth: "",
      selectedClassId: "",
      selectedSpeciality: "",
      selectedTeacherId: "",

      // ✅ nouveaux filtres
      selectedSchoolYear: "2025/2026", // sera écrasé par activeSchoolYear si fourni par l'API
      schoolYears: ["2024/2025", "2025/2026"],

      dateFrom: toISODate(firstDay),
      dateTo: toISODate(lastDay),

      // UI
      hidePastSessions: false,
      selectedClassType: "",
      classTypes: [
        { value: "", text: "Tous" },
        { value: "Arabe", text: "Arabe" },
        { value: "Soutien scolaire", text: "Soutien scolaire" },
        { value: "Autre", text: "Autre" },
      ],
      sessionToDelete: null,
      messageAlert: null,
      typeAlert: null,

      months: [
        { value: 1, text: "Janvier" },
        { value: 2, text: "Février" },
        { value: 3, text: "Mars" },
        { value: 4, text: "Avril" },
        { value: 5, text: "Mai" },
        { value: 6, text: "Juin" },
        { value: 7, text: "Juillet" },
        { value: 8, text: "Août" },
        { value: 9, text: "Septembre" },
        { value: 10, text: "Octobre" },
        { value: 11, text: "Novembre" },
        { value: 12, text: "Décembre" },
      ],

      isAdmin: false,
      classes: [],
      teachers: [],

      _debounceTimer: null,
    };
  },

  computed: {
    // ⚠️ les sessions viennent déjà filtrées du backend,
    // mais on garde le switch "hidePastSessions" en plus si tu veux
    filteredSessions() {
      const now = new Date();

      return this.allSessions.filter((session) => {
        if (!this.hidePastSessions) return true;

        const start = session.startTime ? new Date(session.startTime) : null;
        const end = session.endTime ? new Date(session.endTime) : null;

        if (start && end) return end >= now;
        if (start && !end) return start >= now;
        return true;
      });
    },

    hasStats() {
      return Object.keys(this.sessionStats).length > 0;
    },

    sessionStats() {
      const stats = {};
      this.filteredSessions.forEach((session) => {
        if (session.startTime && session.endTime) {
          const duration = (new Date(session.endTime) - new Date(session.startTime)) / (1000 * 60 * 60);
          const month = new Date(session.startTime).toLocaleString("fr-FR", { month: "long" });
          if (!stats[month]) stats[month] = { totalSessions: 0, totalHours: 0 };
          stats[month].totalSessions += 1;
          stats[month].totalHours += duration;
        }
      });
      return stats;
    },
  },

  methods: {
    // Debounce pour éviter trop d'appels API pendant la saisie
    debouncedApplyFilters() {
      clearTimeout(this._debounceTimer);
      this._debounceTimer = setTimeout(() => {
        this.applyFilters();
      }, 250);
    },

    async fetchSessions() {
      try {
        const hasDateRange = !!this.dateFrom || !!this.dateTo;

        const params = {
          search: this.searchInput?.trim() || undefined,
          speciality: this.selectedSpeciality?.trim() || undefined,

          // ✅ si dateFrom/dateTo existent, on n'envoie pas month
          month: hasDateRange ? undefined : (this.selectedMonth ? Number(this.selectedMonth) : undefined),

          classId: this.selectedClassId ? Number(this.selectedClassId) : undefined,
          teacherId: this.selectedTeacherId ? Number(this.selectedTeacherId) : undefined,

          schoolYear: this.selectedSchoolYear || undefined,
          dateFrom: this.dateFrom || undefined,
          dateTo: this.dateTo || undefined,
          classType: this.selectedClassType || undefined,
        };

        // Supprimer undefined / null / "" pour ne pas envoyer month= (vide)
        Object.keys(params).forEach((k) => {
          if (params[k] === undefined || params[k] === null || params[k] === "") {
            delete params[k];
          }
        });

        const response = await this.axios.get(this.$routing.generate("api_sessions"), { params });

        // Data
        this.allSessions = response.data.sessions || [];
        this.classes = response.data.classes || [];
        this.teachers = response.data.teachers || [];
        this.isAdmin = !!response.data.isAdmin;

        // Années scolaires (si l’API les renvoie)
        if (Array.isArray(response.data.schoolYears) && response.data.schoolYears.length > 0) {
          this.schoolYears = response.data.schoolYears;
        }

        // Année scolaire active (si l’API la renvoie)
        // On l'utilise seulement si selectedSchoolYear n'est pas défini OU si tu veux forcer.
        if (!this.selectedSchoolYear) {
          this.selectedSchoolYear = response.data.activeSchoolYear || "2025/2026";
        }

        // Optionnel : si tu veux forcer toujours la valeur backend :
        // if (response.data.activeSchoolYear) this.selectedSchoolYear = response.data.activeSchoolYear;

        // Reset alert si tout est ok
        this.messageAlert = null;
        this.typeAlert = null;
      } catch (error) {
        console.error("Erreur lors de la récupération des sessions :", error);
        this.messageAlert = "Erreur lors de la récupération des sessions.";
        this.typeAlert = "danger";
      }
    },

    applyFilters() {
      // ✅ filtrage côté serveur
      this.fetchSessions();
    },

    resetToCurrentMonth() {
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

      const toISODate = (d) => {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const dd = String(d.getDate()).padStart(2, "0");
        return `${yyyy}-${mm}-${dd}`;
      };

      this.dateFrom = toISODate(firstDay);
      this.dateTo = toISODate(lastDay);

      // ✅ important : éviter month + dateFrom/dateTo en même temps
      this.selectedMonth = "";

      this.fetchSessions();
    },

    formatDateUTC(datetime) {
      return new Date(datetime).toLocaleDateString("fr-FR", {
        timeZone: "UTC",
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric",
      });
    },

    formatTimeUTC(datetime) {
      return new Date(datetime).toLocaleTimeString("fr-FR", {
        timeZone: "UTC",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },

    getSessionDuration(session) {
      if (session.startTime && session.endTime) {
        const start = new Date(session.startTime);
        const end = new Date(session.endTime);
        const diffHours = (end - start) / (1000 * 60 * 60);
        return this.formatDuration(diffHours);
      }
      return "N/A";
    },

    formatDuration(duration) {
      const hours = Math.floor(duration);
      const minutes = Math.round((duration - hours) * 60);
      let result = "";
      if (hours > 0) result += hours + "h";
      if (minutes > 0) result += (result ? " " : "") + minutes + "min";
      return result || "0min";
    },

    getSessionStatus(session) {
      if (!session.startTime) return "N/A";
      const now = new Date();
      const start = new Date(session.startTime);
      const end = session.endTime ? new Date(session.endTime) : null;
      if (now < start) return "À venir";
      if (end && now > end) return "Terminée";
      if (!end || (now >= start && now <= end)) return "En cours";
      return "N/A";
    },

    statusBadgeClass(status) {
      switch (status) {
        case "À venir":
          return "bg-info-soft text-info";
        case "En cours":
          return "bg-success-soft text-success";
        case "Terminée":
          return "bg-secondary-soft text-secondary";
        default:
          return "bg-light text-dark";
      }
    },

    confirmDelete(session) {
      this.sessionToDelete = session;
      const deleteModal = new this.$bootstrap.Modal(document.getElementById("deleteConfirmationModal"));
      deleteModal.show();
    },

    async deleteSession() {
      try {
        await this.axios.delete(this.$routing.generate("api_session_delete", { id: this.sessionToDelete.id }));
        this.messageAlert = "Session supprimée avec succès.";
        this.typeAlert = "success";
        this.fetchSessions();
      } catch (error) {
        console.error("Erreur lors de la suppression de la session :", error);
        this.messageAlert = "Erreur lors de la suppression de la session.";
        this.typeAlert = "danger";
      }
    },

    clearFilters() {
      // reset texte
      this.searchInput = "";
      this.selectedSpeciality = "";

      // reset selects
      this.selectedMonth = "";
      this.selectedClassId = "";
      this.selectedTeacherId = "";
      this.selectedClassType = "";


      // reset année scolaire -> valeur active backend si déjà reçue, sinon 2025/2026
      this.selectedSchoolYear = this.selectedSchoolYear || "2025/2026";

      // reset dates -> mois courant
      const now = new Date();
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

      const toISODate = (d) => {
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const dd = String(d.getDate()).padStart(2, "0");
        return `${yyyy}-${mm}-${dd}`;
      };

      this.dateFrom = toISODate(firstDay);
      this.dateTo = toISODate(lastDay);

      // reset switch (cohérent avec ton défaut)
      this.hidePastSessions = false;

      // ✅ un seul appel API
      this.fetchSessions();
    },

    isDuplicateSession(session) {
      return (
          this.filteredSessions.filter(
              (s) => s.startTime === session.startTime && s.teacher?.id === session.teacher?.id
          ).length > 1
      );
    },
  },

  mounted() {
    // ✅ par défaut : mois courant + année scolaire
    this.fetchSessions();
  },
};
</script>

<style scoped>
/* ===== Base / page ===== */
.my-5 {
  background: #f6f8fb;
  padding: 1.5rem;
  border-radius: 14px;
}

h1.text-primary {
  font-weight: 800;
  letter-spacing: 0.2px;
}

.container {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

/* ===== Cards ===== */
.card {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.06) !important;
  border-radius: 12px;
}

/* Carte des filtres modernisée */
.filters-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  border-radius: 12px;
  overflow: hidden;
}

.filter-icon-wrapper {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.1rem;
}

/* ===== Switch ===== */
.modern-switch .form-check-input {
  width: 3rem;
  height: 1.5rem;
  cursor: pointer;
}

.modern-switch .form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
}

.modern-switch label {
  cursor: pointer;
  font-weight: 600;
  color: #495057;
}

/* ===== Input groups ===== */
.input-group-modern .input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-right: none;
}

.input-group-modern .form-control {
  border-left: none;
  background-color: white;
}

.input-group-modern .form-control:focus {
  box-shadow: none;
  border-color: #dee2e6;
}

.input-group-modern .input-group-text + .form-control:focus {
  border-left: none;
}

/* ===== Select / date inputs ===== */
.modern-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.55rem 0.75rem;
  transition: all 0.2s ease;
}

.modern-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

/* ===== Clear filters button ===== */
.clear-filters-btn {
  border-radius: 999px;
  padding: 0.45rem 1rem;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.clear-filters-btn:hover {
  background-color: #6c757d;
  color: white;
  border-color: #6c757d;
}

/* ===== Stats cards ===== */
.stats-card {
  border-radius: 12px;
  border-left: 4px solid #0d6efd;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  background: white;
}

.stats-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 22px rgba(0, 0, 0, 0.08) !important;
}

.stats-icon {
  width: 45px;
  height: 45px;
  background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0d6efd;
  font-size: 1.3rem;
}

.stats-content {
  display: flex;
  gap: 15px;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #e9ecef;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #212529;
  line-height: 1;
}

.stat-label {
  font-size: 0.85rem;
  color: #6c757d;
  margin-top: 6px;
}

.stat-divider {
  width: 1px;
  background: #dee2e6;
}

/* ===== Table ===== */
.modern-table {
  border-collapse: separate;
  border-spacing: 0;
}

/* Header plus contrasté */
.table-header {
  background: #eef4ff;
  border-bottom: 1px solid #dbe7ff;
}

.table-header th {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.78rem;
  letter-spacing: 0.6px;
  color: #274c77;
  padding: 1rem;
  border: none;
}

/* Rows */
.modern-table tbody tr {
  transition: background-color 0.15s ease;
  border-bottom: 1px solid #f0f0f0;
}

.modern-table tbody tr:hover {
  background-color: #f1f5ff;
}

.modern-table td {
  padding: 1rem;
  vertical-align: middle;
}

/* ===== Badges ===== */
.badge {
  padding: 0.45rem 0.75rem;
  font-weight: 600;
  border-radius: 8px;
  font-size: 0.85rem;
}

.bg-success-soft {
  background-color: #e8f7ee;
  color: #0f5132;
  border: 1px solid #b7ebcd;
}

.bg-warning-soft {
  background-color: #fff4df;
  color: #7a4b00;
  border: 1px solid #ffd59a;
}

.bg-info-soft {
  background-color: #e6f6ff;
  color: #055160;
  border: 1px solid #a8e1ff;
}

.bg-secondary-soft {
  background-color: #f1f3f5;
  color: #343a40;
  border: 1px solid #dee2e6;
}

/* ===== Action menu button ===== */
.action-menu-btn {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 0.45rem 0.6rem;
  background: white;
  transition: all 0.2s ease;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-menu-btn:hover {
  background: #0d6efd;
  color: white;
  border-color: #0d6efd;
  transform: rotate(15deg);
}

.action-menu-btn i {
  font-size: 1rem;
}

.action-dropdown {
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 10px;
  padding: 0.5rem 0;
  min-width: 190px;
}

.action-dropdown .dropdown-item {
  padding: 0.6rem 1.2rem;
  font-size: 0.92rem;
  transition: all 0.15s ease;
}

.action-dropdown .dropdown-item:hover {
  background-color: #f8f9fa;
  padding-left: 1.5rem;
}

.action-dropdown .dropdown-divider {
  margin: 0.5rem 0;
}

/* ===== Empty state ===== */
.empty-state {
  padding: 4rem 2rem !important;
}

.empty-state-icon {
  display: inline-block;
}

/* ===== Duplicates ===== */
.duplicate-highlight {
  background-color: #fff4df !important;
  border-left: 4px solid #ffc107;
}

/* ===== Buttons ===== */
.btn-success {
  box-shadow: 0 8px 18px rgba(25, 135, 84, 0.18);
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .filter-icon-wrapper {
    width: 35px;
    height: 35px;
  }

  .stats-card {
    margin-bottom: 1rem;
  }

  .modern-switch {
    width: 100%;
    margin-top: 1rem;
  }

  .my-5 {
    padding: 1rem;
  }
}
</style>
