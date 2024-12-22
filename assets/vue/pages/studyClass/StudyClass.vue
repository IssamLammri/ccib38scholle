<template>
  <div class="container mt-5">
    <!-- En-tête avec navigation et titre -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a :href="$routing.generate('app_study_class_index')" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
      <h1 class="mb-0">{{ studyClass.name }} - {{ studyClass.speciality }}</h1>
      <a :href="$routing.generate('app_study_class_edit', { id: studyClass.id })" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-edit"></i> Modifier
      </a>
    </div>

    <!-- Informations sur la classe -->
    <div class="card shadow-sm mb-4 p-3">
      <h2 class="mb-3">Informations sur la Classe</h2>
      <p><strong>Niveau :</strong> {{ studyClass.levelClass }}</p>
    </div>

    <!-- Bouton Ajouter un étudiant -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Liste des Étudiants</h3>
      <button class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#newStudentModal">
        <i class="fas fa-user-plus me-2"></i> Ajouter un étudiant
      </button>
    </div>

    <!-- Tableau des étudiants -->
    <div class="table-responsive">
      <table class="table table-striped">
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
          <td>{{ studentClassRegistered.student.levelClass }}</td>
          <td>
            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"
                    @click="studentToDelete = studentClassRegistered">
              Supprimer
            </button>
          </td>
        </tr>
        <tr v-if="!localStudentsInStudyClass.length">
          <td colspan="6" class="text-center text-muted">Aucun étudiant dans cette classe</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Ajouter un étudiant -->
    <new-student-to-class-modal :students="studentsNotInStudyClass" :study-class="studyClass"></new-student-to-class-modal>

    <!-- Modal Confirmation Suppression -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmation de suppression</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Êtes-vous sûr de vouloir supprimer cet étudiant de la classe ?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
            <button type="button" class="btn btn-danger btn-sm" @click="deleteStudent" data-bs-dismiss="modal">Confirmer</button>
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

.card {
  border-radius: 5px;
  background-color: #f9f9f9;
}

h1, h2, h3 {
  font-family: 'Arial', sans-serif;
  font-weight: 600;
  margin: 0;
  color: #333;
}

.table {
  font-size: 0.9rem;
}

.table-striped tbody tr:nth-of-type(odd) {
  background-color: #f8f8f8;
}

.btn {
  border-radius: 5px;
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}

.btn-primary:hover {
  background-color: #0056b3;
  border-color: #0056b3;
}

.btn-outline-secondary {
  color: #333;
  border-color: #333;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}
</style>