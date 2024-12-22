<template>
  <div class="modal fade" id="newStudentModal" tabindex="-1" aria-labelledby="newStudentModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <!-- En-tête du modal -->
        <div class="modal-header">
          <h5 class="modal-title">Ajouter un élève</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Corps du modal -->
        <div class="modal-body">
          <!-- Champ de recherche -->
          <input
              type="text"
              v-model="searchTerm"
              placeholder="Rechercher un élève"
              class="form-control mb-3"
          />

          <!-- Liste des étudiants filtrés -->
          <ul class="list-group">
            <li
                v-for="student in filteredStudents"
                :key="student.id"
                :class="['list-group-item', 'd-flex', 'justify-content-between', { 'active': isSelected(student) }]"
                @click="toggleSelectStudent(student)"
            >
              <div>{{ student.firstName }} {{ student.lastName }}</div>
              <div>
                <small class="text-muted">{{ formatBirthDate(student.birthDate) }}</small>
                <span class="ms-3 badge bg-light text-dark">{{ student.levelClass }}</span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Bloc des étudiants sélectionnés -->
        <div class="selected-students p-3 border-top">
          <h6 class="fw-bold mb-2">Élèves sélectionnés :</h6>
          <div v-if="selectedStudents.length">
            <ul class="list-unstyled mb-0">
              <li v-for="student in selectedStudents" :key="student.id" class="text-muted">
                {{ student.firstName }} {{ student.lastName }} - {{ student.levelClass }}
              </li>
            </ul>
          </div>
          <div v-else class="text-muted">Aucun élève sélectionné</div>
        </div>

        <!-- Pied du modal -->
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
          <button type="button" class="btn btn-primary btn-sm" @click="addSelectedStudents">Ajouter</button>
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
/* Agrandir la largeur du modal */
.modal-dialog {
  max-width: 800px;
  width: 90%;
}

/* Fixer la hauteur de la zone de liste des étudiants avec un défilement */
.modal-body {
  max-height: 400px;
  overflow-y: auto;
  padding: 15px;
}

/* Bloc des étudiants sélectionnés */
.selected-students {
  background-color: #f8f9fa;
  max-height: 150px;
  overflow-y: auto;
  border-radius: 0 0 10px 10px; /* Adoucir les coins en bas */
}

/* Style pour indiquer une sélection */
.list-group-item.active {
  background-color: #007bff !important;
  color: white !important;
  border-color: #007bff;
}

/* Style pour la liste */
.list-group-item {
  cursor: pointer;
  border: none;
}

.list-group-item:hover {
  background-color: #f8f9fa;
}

/* Style général du modal */
.modal-content {
  border-radius: 10px;
  border: 1px solid #e0e0e0;
}

/* Style pour les badges */
.badge {
  font-size: 0.8rem;
}

.fw-bold {
  font-weight: 600;
}
</style>
