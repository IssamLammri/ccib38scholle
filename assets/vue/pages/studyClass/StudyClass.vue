<template>
  <div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
      <a :href="$routing.generate('app_study_class_index')" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to list
      </a>
      <a :href="$routing.generate('app_study_class_edit', {id: studyClass.id})" class="btn btn-warning">
        <i class="fas fa-edit"></i> Edit
      </a>
    </div>
    <h1 class="mb-4">{{ studyClass.name }} - {{ studyClass.speciality }}</h1>
    <p>Niveau : {{ studyClass.level }}</p>

    <div class="search-bar d-flex align-items-center justify-content-end">
      <div>
        <div
            class="d-flex align-items-center"
            role="button"
            data-bs-auto-close="outside"
            data-bs-toggle="modal"
            data-bs-target="#newStudentModal"
        >
          <img
              src="/static/icons/new_icon.svg"
              alt=""
          >
          <span class="d-sm-none d-md-block ms-2">Ajouter un élève</span>
        </div>
      </div>
    </div>
    <new-student-to-class-modal :students="studentsNotInStudyClass" :study-class="studyClass"></new-student-to-class-modal>

    <h3 class="mt-4">Liste des étudiants</h3>
    <table class="table table-bordered mt-3">
      <thead>
      <tr>
        <th>Id</th>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Date de Naissance</th>
        <th>Niveau</th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="studentClassRegistered in localStudentsInStudyClass" :key="studentClassRegistered.id">
        <td>{{ studentClassRegistered.student.id }}</td>
        <td>{{ studentClassRegistered.student.lastName }}</td>
        <td>{{ studentClassRegistered.student.firstName }}</td>
        <td>{{ new Date(studentClassRegistered.student.birthDate).toLocaleDateString() }}</td>
        <td>{{ studentClassRegistered.student.level }}</td>
        <td>
          <button data-bs-auto-close="outside"
                  data-bs-toggle="modal"
                  data-bs-target="#deleteConfirmationModal" @click="studentToDelete = studentClassRegistered" class="btn btn-danger btn-sm">
            Supprimer
          </button>
        </td>
      </tr>
      </tbody>
    </table>

    <alert v-if="messageAlert" :type="typeAlert" :text="messageAlert">{{ messageAlert }}</alert>

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirmation de suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir supprimer cet étudiant de la classe ?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="deleteStudent">Confirmer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";
import NewStudentToClassModal from "./NewStudentToClassModal.vue";

export default {
  name: "StudyClassDetails",
  components: {
    Alert,
    NewStudentToClassModal
  },
  props: {
    studyClass: {
      type: Object,
      required: true,
    },
    studentsInStudyClass:{
      type: Object,
      required: true,
    },
    studentsNotInStudyClass:{
      type: Object,
      required: true,
    }
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
      studentToDelete: null,
      localStudentsInStudyClass: [...this.studentsInStudyClass],
    };
  },
  methods: {
    deleteStudent() {
      const url = this.$routing.generate('delete_student_from_class', { id: this.studentToDelete.id });

      this.$axios.post(url)
          .then(response => {
            this.messageAlert = "L'étudiant a été supprimé avec succès!";
            this.typeAlert = "success";

            this.localStudentsInStudyClass = this.localStudentsInStudyClass.filter(studentClassRegistered =>
                studentClassRegistered.student.id !== this.studentToDelete.student.id
            );

            // Fermer le modal après la suppression
           /* const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal'));
            deleteModal.hide();*/
          })
          .catch(error => {
            console.error("Erreur lors de la suppression de l'étudiant", error);
            this.messageAlert = "Erreur lors de la suppression de l'étudiant.";
            this.typeAlert = "danger";
          });
    },
  },
};
</script>

<style scoped>
.container {
  margin-top: 20px;
}

.btn-secondary {
  margin-bottom: 15px;
}

.table {
  margin-top: 20px;
}

.d-flex {
  margin-top: 20px;
}
</style>
