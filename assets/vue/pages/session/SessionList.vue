<template>
  <div class="my-5" lang="fr">
    <!-- Titre principal -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-primary">Liste des Sessions</h1>
      <a :href="$routing.generate('app_session_new')" class="btn btn-success">
        <i class="fas fa-plus"></i> Nouvelle Session
      </a>
    </div>

    <!-- Carte des filtres -->
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="card-title mb-0">Filtres</h5>
          <!-- Switch: masquer les sessions passées (actif par défaut) -->
          <div class="form-check form-switch" title="Masquer les sessions passées">
            <input
                class="form-check-input"
                type="checkbox"
                id="switchHidePast"
                v-model="hidePastSessions"
                @change="applyFilters"
                aria-label="Masquer les sessions passées"
            />
            <label class="form-check-label ms-2" for="switchHidePast">
              Masquer les sessions passées
            </label>
          </div>
        </div>

        <div class="row g-3">
          <!-- Recherche textuelle -->
          <div class="col-md-6 col-lg-4">
            <label class="form-label fw-bold">Recherche</label>
            <input
                type="text"
                v-model="searchInput"
                @input="applyFilters"
                class="form-control"
                placeholder="Salle, enseignant, classe, spécialité ou date"
            />
          </div>
          <!-- Filtre par mois -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label fw-bold">Mois</label>
            <select v-model="selectedMonth" @change="applyFilters" class="form-select">
              <option value="">Tous</option>
              <option v-for="m in months" :key="m.value" :value="m.value">{{ m.text }}</option>
            </select>
          </div>
          <!-- Filtre par classe -->
          <div class="col-md-4 col-lg-2">
            <label class="form-label fw-bold">Classe</label>
            <select v-model="selectedClassId" @change="applyFilters" class="form-select">
              <option value="">Toutes</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }} - [{{ cls.speciality }}]</option>
            </select>
          </div>
          <!-- Filtre par spécialité -->
          <div class="col-md-6 col-lg-3">
            <label class="form-label fw-bold">Spécialité</label>
            <input
                type="text"
                v-model="selectedSpeciality"
                @input="applyFilters"
                class="form-control"
                placeholder="Spécialité"
            />
          </div>
          <!-- Filtre par enseignant (pour admin uniquement) -->
          <div v-if="isAdmin" class="col-md-6 col-lg-3">
            <label class="form-label fw-bold">Enseignant</label>
            <select v-model="selectedTeacherId" @change="applyFilters" class="form-select">
              <option value="">Tous</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.firstName }} {{ teacher.lastName }}
              </option>
            </select>
          </div>
          <!-- Bouton Effacer les filtres -->
          <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-outline-secondary" @click="clearFilters">
              <i class="fas fa-eraser"></i> Effacer les filtres
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistiques des sessions sous forme d'indicateurs modernes -->
    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">
          <i class="fas fa-info-circle me-2"></i> Indicateurs des Sessions par Mois
        </h5>
        <div v-if="hasStats" class="row">
          <div class="col-md-4 mb-3" v-for="(stat, month) in sessionStats" :key="month">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0 text-capitalize">{{ month }}</h5>
                  <i class="fas fa-calendar-alt fa-2x text-muted"></i>
                </div>
                <p class="card-text mt-2">
                  <strong>{{ stat.totalSessions }}</strong> sessions<br />
                  <strong>{{ formatDuration(stat.totalHours) }}</strong> cumulées
                </p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center text-muted py-5">
          <i class="fas fa-info-circle fa-2x mb-2"></i>
          <p>Aucune donnée disponible pour les statistiques.</p>
        </div>
      </div>
    </div>

    <!-- Tableau des sessions -->
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div v-if="filteredSessions.length > 0" class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
              <th>ID</th>
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
              <td>{{ session.id }}</td>
              <td>{{ session.startTime ? formatDateUTC(session.startTime) : 'N/A' }}</td>
              <td>{{ session.startTime ? formatTimeUTC(session.startTime) : 'N/A' }}</td>
              <td>{{ session.endTime ? formatTimeUTC(session.endTime) : 'N/A' }}</td>
              <td>{{ getSessionDuration(session) }}</td>
              <td>
                  <span :class="['badge', statusBadgeClass(getSessionStatus(session))]">
                    {{ getSessionStatus(session) }}
                  </span>
              </td>
              <td>{{ session.room.name }}</td>
              <td>{{ session.teacher.lastName }} {{ session.teacher.firstName }}</td>
              <td>{{ session.studyClass.name }}</td>
              <td>{{ session.studyClass.speciality }}</td>
              <td class="text-center">
                <a
                    :href="$routing.generate('app_session_show', { id: session.id })"
                    class="btn btn-outline-info btn-sm me-2"
                    title="Voir"
                >
                  <i class="fas fa-eye"></i>
                </a>
                <a
                    :href="$routing.generate('app_session_edit', { id: session.id })"
                    class="btn btn-outline-warning btn-sm me-2"
                    title="Modifier"
                >
                  <i class="fas fa-edit"></i>
                </a>
                <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(session)" title="Supprimer">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
        <!-- État vide -->
        <div v-else class="p-5 text-center text-muted">
          <img
              src="https://via.placeholder.com/150?text=No+Data"
              alt="Aucune session"
              class="img-fluid mb-3"
              style="max-width:150px"
          />
          <p>Aucune session trouvée.</p>
        </div>
      </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div
        class="modal fade"
        id="deleteConfirmationModal"
        tabindex="-1"
        aria-labelledby="deleteConfirmationModalLabel"
        aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmation de suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">Êtes-vous sûr de vouloir supprimer cette session ?</div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger btn-sm" @click="deleteSession" data-bs-dismiss="modal">
              Confirmer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Composant Alert personnalisé -->
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "SessionList",
  components: { Alert },
  data() {
    return {
      allSessions: [],
      searchInput: "",
      selectedMonth: "",
      selectedClassId: "",
      selectedSpeciality: "",
      selectedTeacherId: "",
      hidePastSessions: true, // \u2190 masque les sessions termin\u00e9es par d\u00e9faut
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
        { value: 12, text: "Décembre" }
      ],
      isAdmin: false,
      classes: [],
      teachers: []
    };
  },
  computed: {
    filteredSessions() {
      const now = new Date();
      return this.allSessions.filter((session) => {
        // Texte
        const query = this.searchInput.trim().toLowerCase();
        const searchMatch =
            query === "" ||
            (session.room?.name || "").toLowerCase().includes(query) ||
            (session.teacher?.lastName || "").toLowerCase().includes(query) ||
            (session.teacher?.firstName || "").toLowerCase().includes(query) ||
            (session.studyClass?.name || "").toLowerCase().includes(query) ||
            (session.studyClass?.speciality || "").toLowerCase().includes(query);

        // Mois
        const monthMatch =
            this.selectedMonth === "" ||
            (session.startTime && new Date(session.startTime).getMonth() + 1 === parseInt(this.selectedMonth));

        // Classe
        const classMatch = this.selectedClassId === "" || session.studyClass?.id === parseInt(this.selectedClassId);

        // Sp\u00e9cialit\u00e9
        const specialityMatch =
            this.selectedSpeciality === "" ||
            (session.studyClass?.speciality || "").toLowerCase().includes(this.selectedSpeciality.toLowerCase());

        // Enseignant
        const teacherMatch = this.selectedTeacherId === "" || session.teacher?.id === parseInt(this.selectedTeacherId);

        // Masquer les pass\u00e9es si l'option est active
        const timeMatch = (() => {
          if (!this.hidePastSessions) return true; // afficher tout
          // garder les sessions \u00e0 venir ou en cours
          const start = session.startTime ? new Date(session.startTime) : null;
          const end = session.endTime ? new Date(session.endTime) : null;
          if (start && end) return end >= now; // inclut en cours et futures
          if (start && !end) return start >= now;
          return true; // si pas d'infos temps, ne pas filtrer
        })();

        return searchMatch && monthMatch && classMatch && specialityMatch && teacherMatch && timeMatch;
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
          if (!stats[month]) {
            stats[month] = { totalSessions: 0, totalHours: 0 };
          }
          stats[month].totalSessions += 1;
          stats[month].totalHours += duration;
        }
      });
      return stats;
    }
  },
  methods: {
    async fetchSessions() {
      try {
        const response = await this.axios.get(this.$routing.generate("api_sessions"));
        this.allSessions = response.data.sessions;
        if (response.data.classes) {
          this.classes = response.data.classes;
        }
        if (response.data.teachers) {
          this.teachers = response.data.teachers;
        }
        if (response.data.isAdmin) {
          this.isAdmin = response.data.isAdmin;
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des sessions :", error);
        this.messageAlert = "Erreur lors de la récupération des sessions.";
        this.typeAlert = "danger";
      }
    },
    applyFilters() {
      // Le filtrage s'actualise automatiquement via la computed property filteredSessions.
    },
    // Méthode pour formater la date en UTC
    formatDateUTC(datetime) {
      return new Date(datetime).toLocaleDateString("fr-FR", {
        timeZone: "UTC",
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric"
      });
    },
    // Méthode pour formater l'heure en UTC
    formatTimeUTC(datetime) {
      return new Date(datetime).toLocaleTimeString("fr-FR", {
        timeZone: "UTC",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false
      });
    },
    getSessionDuration(session) {
      if (session.startTime && session.endTime) {
        const start = new Date(session.startTime);
        const end = new Date(session.endTime);
        const diff = end - start;
        const diffHours = diff / (1000 * 60 * 60);
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
    // \u2192 Nouvelles m\u00e9thodes de statut
    getSessionStatus(session) {
      if (!session.startTime) return "N/A";
      const now = new Date();
      const start = new Date(session.startTime);
      const end = session.endTime ? new Date(session.endTime) : null;
      if (now < start) return "\u00c0 venir";
      if (end && now > end) return "Termin\u00e9e";
      // Si pas d'endTime ou maintenant entre start et end
      if (!end || (now >= start && now <= end)) return "En cours";
      return "N/A";
    },
    statusBadgeClass(status) {
      switch (status) {
        case "\u00c0 venir":
          return "bg-info";
        case "En cours":
          return "bg-success";
        case "Termin\u00e9e":
          return "bg-secondary";
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
      this.searchInput = "";
      this.selectedMonth = "";
      this.selectedClassId = "";
      this.selectedSpeciality = "";
      this.selectedTeacherId = "";
      this.hidePastSessions = true; // on r\u00e9active le masquage par d\u00e9faut
    },
    isDuplicateSession(session) {
      // Retourne true si plus d'une session a la m\u00eame date de d\u00e9but et le m\u00eame enseignant
      return (
          this.filteredSessions.filter(
              (s) => s.startTime === session.startTime && s.teacher?.id === session.teacher?.id
          ).length > 1
      );
    }
  },
  mounted() {
    this.fetchSessions();
  }
};
</script>

<style scoped>
/* Global */
.container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Filtres */
.card-title {
  font-size: 1.25rem;
}

/* Tableau */
.table th,
.table td {
  vertical-align: middle;
}

/* Am\u00e9lioration du hover */
.table-hover tbody tr:hover {
  background-color: #f8f9fa;
}

/* Indicateurs modernes pour les statistiques */
.card.border-0 {
  border-left: 4px solid #007bff;
}

.card.border-0 .card-body {
  padding: 1rem;
}

.card-text strong {
  font-size: 1.2rem;
}

/* Illustration pour \u00e9tat vide */
.img-fluid {
  opacity: 0.8;
}

/* Style pour les sessions en doublon */
.duplicate-highlight {
  background-color: #ffe5e5; /* Rouge clair */
}
</style>
