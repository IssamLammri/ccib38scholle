<template>
  <div class="container my-5">
    <!-- Informations de la session -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div>
              <h2 class="card-title mb-1">
                {{ session.studyClass.name }}
                <small class="text-muted">({{ session.studyClass.speciality }})</small>
              </h2>
              <p class="card-subtitle">
                Le {{ formatDate(session.startTime) }} de {{ formatTime(session.startTime) }} à {{ formatTime(session.endTime) }}
              </p>
            </div>
            <div class="mt-3 mt-md-0">
              <span class="badge bg-primary me-2" style="font-size: 18px">
                {{ session.room.name }} - {{ session.room.comment }}
              </span>
              <span class="badge bg-info" style="font-size: 18px">
                {{ session.studyClass.levelClass }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Alerte -->
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert"></alert>
    <!-- Section principale avec la carte du professeur et la liste des étudiants -->
    <div class="row">
      <!-- Carte du Professeur -->
      <div class="col-md-4 mb-4">
        <div class="card text-center shadow-sm">
          <img
              src="/static/icons/prof_icon.jpg"
              alt="Photo du Professeur"
              class="card-img-top rounded-circle mx-auto mt-3"
              style="width: 8rem; height: 8rem; object-fit: cover;"
          />
          <div class="card-body">
            <h5 class="card-title">{{ session.teacher.firstName }} {{ session.teacher.lastName }}</h5>
            <p class="card-text text-muted">Professeur</p>
          </div>
        </div>
      </div>

      <!-- Liste des étudiants -->
      <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Liste des Étudiants</h4>
            <button
                class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#newStudentToSessionModal"
            >
              <i class="fas fa-user-plus me-2"></i> Ajouter un étudiant
            </button>
          </div>
          <div class="card-body p-0">
            <table class="table table-hover mb-0">
              <thead class="table-light">
              <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Niveau</th>
                <th>Présence</th>
                <th>Actions</th>
                <th></th>
              </tr>
              </thead>
              <tbody>
              <tr
                  v-for="studentClass in localStudentsSession"
                  :key="studentClass.id"
              >
                <td>{{ studentClass.student.lastName }}</td>
                <td>{{ studentClass.student.firstName }}</td>
                <td>{{ studentClass.student.levelClass }}</td>
                <td>
                    <span
                        v-if="studentClass.isPresent === true"
                        class="badge bg-success"
                    >
                      Présent
                    </span>
                  <span
                      v-else-if="studentClass.isPresent === false"
                      class="badge bg-danger"
                  >
                      Absent
                    </span>
                  <span
                      v-else
                      class="badge bg-secondary"
                  >
                      Non défini
                    </span>
                </td>
                <td>
                  <button
                      v-if="studentClass.isPresent !== true"
                      class="btn btn-outline-success btn-sm me-1"
                      @click="markAsPresent(studentClass)"
                      title="Marquer comme présent"
                  >
                    <i class="fas fa-check"></i>
                  </button>
                  <button
                      v-if="studentClass.isPresent !== false"
                      class="btn btn-outline-danger btn-sm"
                      @click="markAsAbsent(studentClass)"
                      title="Marquer comme absent"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </td>
                <td>
                  <button
                      class="btn btn-danger btn-sm"
                      @click="confirmDelete(studentClass)"
                  >
                    Supprimer
                  </button>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modale d'ajout d'étudiant -->
    <new-student-to-session-modal :session="session" />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";
import NewStudentToSessionModal from "./NewStudentToSessionModal.vue";

export default {
  name: "SessionShow",
  components: { NewStudentToSessionModal, Alert },
  props: {
    session: {
      type: Object,
      required: false,
    },
    studentsSession: {
      type: Array,
      required: false,
    },
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
      studentToDelete: null,
      localStudentsSession: [...this.studentsSession],
    };
  },
  methods: {
    markAsPresent(studentClass) {
      const url = this.$routing.generate('mark_student_present_in_session', { id: studentClass.id });
      this.$axios.post(url)
          .then(() => {
            studentClass.isPresent = true;
            this.messageAlert = "L'étudiant a été marqué comme présent.";
            this.typeAlert = "success";
          })
          .catch(error => {
            console.error("Erreur lors de la mise à jour de la présence", error);
            this.messageAlert = "Erreur lors de la mise à jour de la présence.";
            this.typeAlert = "danger";
          });
    },
    markAsAbsent(studentClass) {
      const url = this.$routing.generate('mark_student_absent_in_session', { id: studentClass.id });
      this.$axios.post(url)
          .then(() => {
            studentClass.isPresent = false;
            this.messageAlert = "L'étudiant a été marqué comme absent.";
            this.typeAlert = "success";
          })
          .catch(error => {
            console.error("Erreur lors de la mise à jour de l'absence", error);
            this.messageAlert = "Erreur lors de la mise à jour de l'absence.";
            this.typeAlert = "danger";
          });
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('fr-FR', options);
    },
    formatTime(timeString) {
      const date = new Date(timeString);
      const hours = String(date.getUTCHours()).padStart(2, '0');
      const minutes = String(date.getUTCMinutes()).padStart(2, '0');
      return `${hours}:${minutes}`;
    },
    confirmDelete(studentClass) {
      this.studentToDelete = studentClass;
      if (confirm(`Voulez-vous vraiment supprimer l'étudiant ${studentClass.student.firstName} ${studentClass.student.lastName} ?`)) {
        this.deleteStudent();
      }
    },
    deleteStudent() {
      const url = this.$routing.generate('delete_student_from_session', { id: this.studentToDelete.id });
      this.$axios.post(url)
          .then(() => {
            this.messageAlert = "L'étudiant a été supprimé avec succès!";
            this.typeAlert = "success";
            this.localStudentsSession = this.localStudentsSession.filter(
                studentClassRegistered => studentClassRegistered.id !== this.studentToDelete.id
            );
            this.studentToDelete = null;
          })
          .catch(error => {
            console.error("Erreur lors de la suppression de l'étudiant", error);
            this.messageAlert = "Erreur lors de la suppression de l'étudiant.";
            this.typeAlert = "danger";
          });
    }
  },
};
</script>

<style scoped>
/* Vous pouvez ajouter ici des styles personnalisés pour peaufiner l'UI */
.card {
  border: none;
}
</style>
