<!-- NewStudentToSessionModal.vue -->
<template>
  <div
      class="modal fade"
      ref="modal"
      id="newStudentToSessionModal"
      tabindex="-1"
      aria-labelledby="newStudentToSessionModalLabel"
      aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- En-tête du modal -->
        <div class="modal-header">
          <h5 class="modal-title" id="newStudentToSessionModalLabel">
            Ajouter un élève
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <!-- Corps du modal -->
        <div class="modal-body">
          <!-- Champ de recherche -->
          <div class="mb-3">
            <input
                type="text"
                v-model="searchTerm"
                placeholder="Rechercher un élève"
                class="form-control"
            />
          </div>

          <!-- Zone scrollable pour la liste des étudiants -->
          <div class="students-list-container mb-3">
            <ul class="list-group">
              <!-- Indicateur de chargement -->
              <li v-if="isLoading" class="list-group-item text-center">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Chargement...
              </li>
              <!-- Message si aucun élève n'est trouvé -->
              <li v-else-if="!filteredStudents.length" class="list-group-item text-center">
                Aucun élève trouvé.
              </li>
              <!-- Affichage de la liste filtrée -->
              <li
                  v-for="student in filteredStudents"
                  :key="student.id"
                  :class="['list-group-item', { active: isSelected(student) }]"
                  @click="toggleSelectStudent(student)"
                  style="cursor: pointer;"
              >
                <div class="d-flex justify-content-between align-items-center">
                  <span>{{ student.firstName }} {{ student.lastName }}</span>
                  <small class="text-muted">{{ formatBirthDate(student.birthDate) }}</small>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                  <small class="text-muted">Niveau : {{ student.level }}</small>
                </div>
              </li>
            </ul>
          </div>

          <!-- Affichage des informations des étudiants sélectionnés -->
          <div v-if="selectedStudents.length" class="mt-3">
            <h6>Élèves sélectionnés :</h6>
            <ul>
              <li v-for="student in selectedStudents" :key="student.id">
                {{ student.firstName }} {{ student.lastName }} - Niveau : {{ student.level }}
              </li>
            </ul>
          </div>
        </div>
        <!-- Pied de page du modal -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="resetModal">
            Fermer
          </button>
          <button
              type="button"
              class="btn btn-primary"
              @click="addSelectedStudents"
              :disabled="selectedStudents.length === 0"
          >
            Ajouter
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "NewStudentToSessionModal",
  props: {
    session: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      selectedStudents: [], // Liste des étudiants sélectionnés
      students: [],         // Liste complète des étudiants
      searchTerm: "",       // Terme de recherche
      isLoading: false,     // Indicateur de chargement
    };
  },
  computed: {
    // Filtre les étudiants en fonction du terme de recherche
    filteredStudents() {
      if (!this.searchTerm) return this.students;
      const term = this.searchTerm.toLowerCase();
      return this.students.filter((student) => {
        const fullName = `${student.firstName} ${student.lastName}`.toLowerCase();
        return fullName.includes(term);
      });
    },
  },
  mounted() {
    this.fetchStudents();
    // Ajoute un listener pour réinitialiser le modal à sa fermeture
    const modalEl = this.$refs.modal;
    modalEl.addEventListener("hidden.bs.modal", this.resetModal);
  },
  beforeUnmount() {
    // Nettoyage du listener lors de la destruction du composant
    const modalEl = this.$refs.modal;
    if (modalEl) {
      modalEl.removeEventListener("hidden.bs.modal", this.resetModal);
    }
  },
  methods: {
    // Récupère la liste des étudiants via Axios
    async fetchStudents() {
      this.isLoading = true;
      try {
        const response = await this.$axios.get(this.$routing.generate("all_students"));
        this.students = response.data.allStudents;
      } catch (error) {
        console.error("Erreur lors de la récupération des étudiants :", error);
      } finally {
        this.isLoading = false;
      }
    },
    // Vérifie si un étudiant est déjà sélectionné
    isSelected(student) {
      return this.selectedStudents.some((s) => s.id === student.id);
    },
    // Ajoute ou retire un étudiant de la sélection
    toggleSelectStudent(student) {
      const index = this.selectedStudents.findIndex((s) => s.id === student.id);
      if (index !== -1) {
        this.selectedStudents.splice(index, 1);
      } else {
        this.selectedStudents.push(student);
      }
    },
    // Formate la date de naissance au format "jour mois année"
    formatBirthDate(date) {
      const options = { year: "numeric", month: "long", day: "numeric" };
      return new Date(date).toLocaleDateString("fr-FR", options);
    },
    // Envoie la liste des étudiants sélectionnés au serveur
    async addSelectedStudents() {
      if (!this.selectedStudents.length) return;
      const studentIds = this.selectedStudents.map((student) => student.id);
      const url = this.$routing.generate("new_student_to_session", { id: this.session.id });
      try {
        await this.$axios.post(url, { studentIds });
        window.location.href = this.$routing.generate("app_session_show", { id: this.session.id });
      } catch (error) {
        console.error("Erreur lors de l'ajout des étudiants", error);
      }
    },
    // Réinitialise le champ de recherche et la liste des étudiants sélectionnés
    resetModal() {
      this.searchTerm = "";
      this.selectedStudents = [];
    },
  },
};
</script>

<style scoped>
/* Centrer le modal verticalement */
.modal-dialog-centered {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
}

/* Style pour l'élément sélectionné */
.list-group-item.active {
  background-color: #007bff;
  color: white;
}

/* Assurer que tous les éléments de la liste soient cliquables */
.list-group-item {
  cursor: pointer;
}

/* Zone de défilement pour la liste des étudiants */
.students-list-container {
  max-height: 300px; /* Ajustez cette valeur selon vos besoins */
  overflow-y: auto;
}
</style>
