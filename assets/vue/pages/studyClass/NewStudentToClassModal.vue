<!-- ListPayments.vue -->
<template>
  <div class="modal fade" id="newStudentModal" tabindex="-1" aria-labelledby="newStudentModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> <!-- Ajout de la classe pour centrer le modal -->
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="newStudentModal">Ajouter un élève</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Input pour rechercher les étudiants -->
          <input
              type="text"
              v-model="searchTerm"
              placeholder="Rechercher un élève"
              class="form-control mb-3"
          />

          <ul class="list-group">
            <li
                v-for="student in filteredStudents"
                :key="student.id"
                :class="['list-group-item d-flex justify-content-between align-items-center', { 'active': isSelected(student) }]"
                @click="toggleSelectStudent(student)"
            >
              <div>
                {{ student.firstName }} {{ student.lastName }}
              </div>
              <div class="d-flex justify-content-between align-items-center"
              style="width: 60%">
                <div class="text-center w-50">
                  <small class="text-muted">{{ formatBirthDate(student.birthDate) }}</small>
                </div>
                <div class="text-end w-50">
                  <small class="text-muted">{{ student.levelClass }}</small>
                </div>
              </div>
            </li>
          </ul>

          <!-- Affichage des informations des étudiants sélectionnés -->
          <div v-if="selectedStudents.length" class="mt-3">
            <h6>Élèves sélectionnés :</h6>
            <ul>
              <li v-for="student in selectedStudents" :key="student.id">
                {{ student.firstName }} {{ student.lastName }} - {{ student.levelClass }}
              </li>
            </ul>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          <button type="button" class="btn btn-primary" @click="addSelectedStudents">Ajouter</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: 'NewPaymentModal',
  props: {
    students: {
      type: Object,
      required: true
    },
    studyClass: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      selectedStudents: [], // Liste des étudiants sélectionnés
      searchTerm: "", // Terme de recherche
    };
  },
  computed: {
    // Filtrer les étudiants en fonction du terme de recherche
    filteredStudents() {
      return this.students.filter(student => {
        const fullName = `${student.firstName} ${student.lastName}`.toLowerCase();
        return fullName.includes(this.searchTerm.toLowerCase());
      });
    }
  },
  methods: {
    // Méthode pour vérifier si un étudiant est sélectionné
    isSelected(student) {
      return this.selectedStudents.includes(student);
    },
    // Méthode pour ajouter ou retirer un étudiant sélectionné
    toggleSelectStudent(student) {
      const index = this.selectedStudents.indexOf(student);
      if (index > -1) {
        // Si l'étudiant est déjà sélectionné, le retirer
        this.selectedStudents.splice(index, 1);
      } else {
        // Sinon, l'ajouter à la sélection
        this.selectedStudents.push(student);
      }
    },
    // Méthode pour formater la date de naissance
    formatBirthDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString('fr-FR', options);
    },
    // Méthode pour envoyer les étudiants sélectionnés via Axios
    addSelectedStudents() {
      if (this.selectedStudents.length > 0) {
        const studentIds = this.selectedStudents.map(student => student.id);
        const url = this.$routing.generate('new_student_to_class', { id: this.studyClass.id });
        this.$axios.post(url, {
          studentIds: studentIds
        })
            .then(response => {
              window.location.href = this.$routing.generate('app_study_class_show', { id: this.studyClass.id })
              // Émettre un événement pour notifier que les étudiants ont été ajoutés
              /*this.$emit('students-added', this.selectedStudents);

              // Fermer le modal après l'ajout
              const modalElement = document.getElementById('newStudentModal');
              const modalInstance = bootstrap.Modal.getInstance(modalElement);
              modalInstance.hide();*/
            })
            .catch(error => {
              // Gérer les erreurs
              console.error("Erreur lors de l'ajout des étudiants", error);
            });
      }
    }
  }
}
</script>

<style scoped>
/* Style pour indiquer une ligne sélectionnée (bootstrap gère 'active') */
.list-group-item.active {
  background-color: #007bff;
  color: white;
}

/* Centrer le modal */
.modal-dialog-centered {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh; /* Hauteur minimale égale à la hauteur de la fenêtre */
}

/* Style pour le contenu supplémentaire */
.d-flex .text-center {
  flex: 1;
}

.d-flex .text-end {
  flex: 1;
  text-align: right;
}
</style>
