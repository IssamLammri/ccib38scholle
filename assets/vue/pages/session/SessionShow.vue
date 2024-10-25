<template>
  <div class="container mt-5">
    <!-- Le reste du template est inchangé -->
    <div class="row mt-4">
      <h2>Liste des élèves</h2>
      <table class="table table-hover">
        <thead>
        <tr>
          <th scope="col">Nom</th>
          <th scope="col">Prénom</th>
          <th scope="col">Niveau</th>
          <th scope="col">Présence</th>
          <th scope="col">Actions Présence</th>
          <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="studentClass in localStudentsSession" :key="studentClass.id">
          <td>{{studentClass.student.lastName}}</td>
          <td>{{studentClass.student.firstName}}</td>
          <td>{{studentClass.student.levelClass}}</td>
          <td>
            <!-- Afficher l'état actuel -->
            <span v-if="studentClass.isPresent === true" class="badge bg-success" style="margin-left: 5px">Présent</span>
            <span v-if="studentClass.isPresent === false" class="badge bg-danger" style="margin-left: 5px">Absent</span>
          </td>
          <td>
            <!-- Boutons pour marquer la présence ou l'absence -->
            <button v-if="studentClass.isPresent === null || studentClass.isPresent === false" class="btn btn-sm" @click="markAsPresent(studentClass)">✔️</button>
            <button v-if="studentClass.isPresent === null || studentClass.isPresent === true" class="btn btn-sm" style="margin-left: 5px" @click="markAsAbsent(studentClass)">❌</button>
          </td>
          <td>
            <button class="btn btn-danger btn-sm" @click="confirmDelete(studentClass)">Supprimer</button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "SessionShow",
  components: { Alert },
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
          .then(response => {
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
          .then(response => {
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
      const options = { hour: '2-digit', minute: '2-digit' };
      return new Date(timeString).toLocaleTimeString('fr-FR', options);
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
          .then(response => {
            this.messageAlert = "L'étudiant a été supprimé avec succès!";
            this.typeAlert = "success";
            this.localStudentsSession = this.localStudentsSession.filter(studentClassRegistered =>
                studentClassRegistered.id !== this.studentToDelete.id
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
