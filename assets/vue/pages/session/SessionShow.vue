<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-7">
        <h1> {{session.studyClass.name}} ({{session.studyClass.speciality}}) </h1>
        <h4>Le {{formatDate(session.startTime)}} de {{formatTime(session.startTime)}} à {{formatTime(session.endTime)}} </h4>
        <span class="badge rounded-pill badge-primary m-2" style="font-size: 18px"> {{session.room.name}} - {{session.room.comment}} </span>
        <span class="badge rounded-pill badge-info m-2"  style="font-size: 18px"> {{session.studyClass.levelClass}} </span>
      </div>

      <div class="col-3">
        <div class="me-3 p-2">
          <div class="d-flex mb-3 mb-sm-0 flex-column p-2 mx-auto">
             <span class="stakeholder-infos text-center d-inline-block align-top mt-1">
                <strong class="d-block author fs-6">Professeur</strong>
              </span>
            <span class="stakeholder-photo text-center d-inline-block align-top">
                <img
                    src="/static/icons/prof_icon.jpg"
                    class="rounded-circle module-logo"
                    style="width: 8rem;"
                >
              </span>
            <span class="stakeholder-infos text-center d-inline-block align-top mt-1">
                <strong class="d-block author fs-6">{{session.teacher.firstName}} {{session.teacher.lastName}}</strong>
              </span>
          </div>
        </div>
      </div>
    </div>
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert"></alert>

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
