<template>
  <div class="mobile-session-container mt-5">
    <!-- En-tête compact de la session -->
    <div class="session-header">
      <div class="header-content">
        <h1 class="session-title">{{ session.studyClass.name }}</h1>
        <p class="session-time">
          <i class="far fa-clock"></i>
          {{ formatTime(session.startTime) }} - {{ formatTime(session.endTime) }}
        </p>
      </div>
    </div>

    <!-- Alerte -->
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mx-3 mt-3" />

    <!-- Statistiques rapides -->
    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-value">{{ countPresent }}</div>
        <div class="stat-label">Présents</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ countAbsent }}</div>
        <div class="stat-label">Absents</div>
      </div>
      <div class="stat-item">
        <div class="stat-value">{{ localStudentsSession.length }}</div>
        <div class="stat-label">Total</div>
      </div>
    </div>

    <!-- Liste des étudiants -->
    <div class="students-container">
      <div class="students-header">
        <h2 class="students-title">
          <i class="fas fa-users"></i>
          Étudiants
        </h2>
        <button
            v-if="isManager"
            class="btn-add-student"
            data-bs-toggle="modal"
            data-bs-target="#newStudentToSessionModal"
        >
          <i class="fas fa-user-plus"></i>
        </button>
      </div>

      <div class="students-list">
        <div
            v-for="studentClass in localStudentsSession"
            :key="studentClass.id"
            class="student-card"
            :class="{
            'is-present': studentClass.isPresent === true,
            'is-absent': studentClass.isPresent === false,
            'is-undefined': studentClass.isPresent === null
          }"
        >
          <!-- Info étudiant -->
          <div class="student-info">
            <div class="student-name">
              {{ studentClass.student.firstName }} {{ studentClass.student.lastName }}
            </div>
            <div class="student-level">
              <i class="fas fa-graduation-cap"></i>
              {{ studentClass.student.levelClass }}
            </div>
          </div>

          <!-- Boutons de présence -->
          <div class="attendance-actions">
            <!-- État indéfini : deux gros boutons -->
            <template v-if="studentClass.isPresent === null">
              <button
                  class="btn-present"
                  @click="markAsPresent(studentClass)"
              >
                <i class="fas fa-check"></i>
                <span>Présent</span>
              </button>
              <button
                  class="btn-absent"
                  @click="markAsAbsent(studentClass)"
              >
                <i class="fas fa-times"></i>
                <span>Absent</span>
              </button>
            </template>

            <!-- Déjà marqué : un petit bouton d'annulation -->
            <template v-else>
              <button
                  v-if="!studentClass.isPresent"
                  class="btn-present-revert"
                  @click="markAsPresent(studentClass)"
              >
                <i class="fas fa-check"></i>
                <span>Annuler son absence</span>
              </button>
              <button
                  v-else
                  class="btn-absent-revert"
                  @click="markAsAbsent(studentClass)"
              >
                <i class="fas fa-times"></i>
                <span> Annuler sa présence</span>
              </button>
            </template>
          </div>


          <!-- Badge statut -->
          <div class="status-indicator">
            <span v-if="studentClass.isPresent === true" class="badge-present">
              <i class="fas fa-check-circle"></i>
            </span>
            <span v-else-if="studentClass.isPresent === false" class="badge-absent">
              <i class="fas fa-times-circle"></i>
            </span>
            <span v-else class="badge-undefined">
              <i class="fas fa-question-circle"></i>
            </span>
          </div>

          <!-- Bouton supprimer (discret) -->
          <button
              v-if="isManager"
              class="btn-delete"
              title="Supprimer"
              @click="confirmDelete(studentClass)"
          >
            <i class="fas fa-trash-alt"></i>
          </button>
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
    currentUser: {
      type: Object,
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
  computed: {
    roles() {
      return Array.isArray(this.currentUser?.roles) ? this.currentUser.roles : [];
    },
    countPresent() {
      return this.localStudentsSession.filter(s => s.isPresent === true).length;
    },
    countAbsent() {
      return this.localStudentsSession.filter(s => s.isPresent === false).length;
    },
    isAdmin()   {
      return this.roles.includes('ROLE_ADMIN');
    },
    isManager() {
      return this.roles.includes('ROLE_MANAGER') || this.roles.includes('ROLE_ADMIN');
    },
  },
  methods: {
    markAsPresent(studentClass) {
      const url = this.$routing.generate('mark_student_present_in_session', { id: studentClass.id });
      this.$axios.post(url)
          .then(() => {
            studentClass.isPresent = true;
            this.messageAlert = "Marqué comme présent";
            this.typeAlert = "success";
            setTimeout(() => { this.messageAlert = null; }, 2000);
          })
          .catch(error => {
            console.error("Erreur lors de la mise à jour de la présence", error);
            this.messageAlert = "Erreur lors de la mise à jour";
            this.typeAlert = "danger";
          });
    },
    markAsAbsent(studentClass) {
      const url = this.$routing.generate('mark_student_absent_in_session', { id: studentClass.id });
      this.$axios.post(url)
          .then(() => {
            studentClass.isPresent = false;
            this.messageAlert = "Marqué comme absent";
            this.typeAlert = "success";
            setTimeout(() => { this.messageAlert = null; }, 2000);
          })
          .catch(error => {
            console.error("Erreur lors de la mise à jour de l'absence", error);
            this.messageAlert = "Erreur lors de la mise à jour";
            this.typeAlert = "danger";
          });
    },
    formatTime(value) {
      if (!value) return '';
      // On travaille sur la chaîne brute pour éviter toute conversion de fuseau
      const iso = typeof value === 'string' ? value : String(value);
      // Matche le "T12:34" d'un ISO (avec ou sans secondes / offset / Z)
      const m = iso.match(/T(\d{2}):(\d{2})/);
      return m ? `${m[1]}:${m[2]}` : '';
    },
    confirmDelete(studentClass) {
      this.studentToDelete = studentClass;
      if (confirm(`Supprimer ${studentClass.student.firstName} ${studentClass.student.lastName} ?`)) {
        this.deleteStudent();
      }
    },
    deleteStudent() {
      const url = this.$routing.generate('delete_student_from_session', { id: this.studentToDelete.id });
      this.$axios.post(url)
          .then(() => {
            this.messageAlert = "Étudiant supprimé";
            this.typeAlert = "success";
            this.localStudentsSession = this.localStudentsSession.filter(
                studentClassRegistered => studentClassRegistered.id !== this.studentToDelete.id
            );
            this.studentToDelete = null;
            setTimeout(() => { this.messageAlert = null; }, 2000);
          })
          .catch(error => {
            console.error("Erreur lors de la suppression de l'étudiant", error);
            this.messageAlert = "Erreur lors de la suppression";
            this.typeAlert = "danger";
          });
    }
  },
};
</script>

<style scoped>
/* Container principal */
.mobile-session-container {
  min-height: 100vh;
  background: #f5f7fa;
  padding-bottom: 2rem;
}

/* En-tête de session */
.session-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 1.5rem 1rem 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
  max-width: 680px;
  margin: 0 auto;
}

.session-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.02em;
}

.session-time {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.05rem;
  color: rgba(255, 255, 255, 0.95);
  margin: 0;
  font-weight: 500;
}

.session-time i {
  font-size: 1.1rem;
}

/* Barre de statistiques */
.stats-bar {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  max-width: 680px;
  margin: -1.5rem auto 0;
  padding: 0 1rem;
  position: relative;
  z-index: 10;
}

.stat-item {
  background: white;
  border-radius: 16px;
  padding: 1rem;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

/* Container des étudiants */
.students-container {
  max-width: 680px;
  margin: 2rem auto 0;
  padding: 0 1rem;
}

.students-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.students-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.students-title i {
  color: #667eea;
}

.btn-add-student {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  color: white;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  transition: all 0.2s;
}

.btn-add-student:active {
  transform: scale(0.95);
}

/* Liste des étudiants */
.students-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Carte étudiant */
.student-card {
  background: white;
  border-radius: 18px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  display: grid;
  grid-template-columns: 1fr auto;
  grid-template-rows: auto auto;
  gap: 1rem;
  position: relative;
  border: 3px solid transparent;
  transition: all 0.2s;
}

.student-card.is-present {
  border-color: #10b981;
  background: linear-gradient(to right, #f0fdf4 0%, white 10%);
}

.student-card.is-absent {
  border-color: #ef4444;
  background: linear-gradient(to right, #fef2f2 0%, white 10%);
}

/* Info étudiant */
.student-info {
  grid-column: 1;
  grid-row: 1;
}

.student-name {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.35rem;
}

.student-level {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 500;
}

.student-level i {
  color: #94a3b8;
}

/* Indicateur de statut */
.status-indicator {
  grid-column: 2;
  grid-row: 1;
  display: flex;
  align-items: flex-start;
}

.status-indicator span {
  font-size: 2rem;
}

.badge-present {
  color: #10b981;
}

.badge-absent {
  color: #ef4444;
}

.badge-undefined {
  color: #94a3b8;
}

/* Actions de présence */
.attendance-actions {
  grid-column: 1 / -1;
  grid-row: 2;
  display: flex;
  gap: 0.75rem;
}

.btn-present-revert,
.btn-absent-revert,
.btn-present,
.btn-absent {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  height: 56px;
  border-radius: 14px;
  border: none;
  font-size: 1rem;
  font-weight: 700;
  transition: all 0.2s;
  letter-spacing: 0.01em;
}

.btn-present {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-present-revert {
  background: linear-gradient(135deg, #8feacc 0%, #66c3a4 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-present:active {
  transform: translateY(2px);
  box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}

.btn-absent {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-absent-revert {
  background: linear-gradient(135deg, #ef9898 0%, #ea8a8a 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-absent:active {
  transform: translateY(2px);
  box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}

/* Bouton supprimer */
.btn-delete {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: #f1f5f9;
  border: none;
  color: #64748b;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  transition: all 0.2s;
  opacity: 0.7;
}

.btn-delete:active {
  background: #fee2e2;
  color: #dc2626;
  opacity: 1;
}

/* Responsive */
@media (max-width: 380px) {
  .session-title {
    font-size: 1.5rem;
  }

  .stat-value {
    font-size: 1.75rem;
  }

  .student-name {
    font-size: 1.05rem;
  }

  .btn-present,
  .btn-absent {
    font-size: 0.95rem;
    height: 52px;
  }

  .btn-present span,
  .btn-absent span {
    display: none;
  }

  .btn-present i,
  .btn-absent i {
    font-size: 1.5rem;
  }
}

@media (min-width: 640px) {
  .student-card {
    padding: 1.5rem;
  }
}
</style>