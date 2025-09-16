<template>
  <div class="modern-container">
    <!-- Navigation flottante -->
    <div class="floating-nav">
      <a :href="$routing.generate('app_study_class_index')" class="nav-btn back-btn">
        <i class="fas fa-arrow-left"></i>
        <span>Retour</span>
      </a>

      <div class="action-buttons">
        <button @click="printClassReport" class="nav-btn print-btn">
          <i class="fas fa-print"></i>
          <span>Imprimer</span>
        </button>
        <a :href="$routing.generate('app_study_class_edit', { id: studyClass.id })" class="nav-btn edit-btn">
          <i class="fas fa-edit"></i>
          <span>Modifier</span>
        </a>
      </div>
    </div>

    <!-- En-tête principal avec animation -->
    <div class="hero-header">
      <div class="hero-content">
        <div class="class-badge">{{ studyClass.levelClass }}</div>
        <h1 class="hero-title">{{ studyClass.name }}</h1>
        <p class="hero-subtitle">{{ studyClass.speciality }}</p>
        <div class="hero-stats">
          <div class="stat-item">
            <i class="fas fa-users"></i>
            <span>{{ filteredStudents.length }} étudiants {{ filterActive ? 'actifs' : 'inactifs' }}</span>
          </div>
          <div class="stat-item">
            <i class="fas fa-calendar"></i>
            <span>{{ studyClass.day }}</span>
          </div>
          <div class="stat-item">
            <i class="fas fa-clock"></i>
            <span>{{ formatTime(studyClass.startHour) }} - {{ formatTime(studyClass.endHour) }}</span>
          </div>
        </div>
      </div>
      <div class="hero-decoration">
        <div class="floating-element element-1"></div>
        <div class="floating-element element-2"></div>
        <div class="floating-element element-3"></div>
      </div>
    </div>

    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="typeAlert"
        class="modern-alert"
    />

    <!-- Section informations de classe modernisée -->
    <div class="glass-card class-info-card">
      <div class="card-header">
        <h2><i class="fas fa-info-circle"></i> Informations de la Classe</h2>
      </div>
      <div class="info-grid">
        <div class="info-card">
          <div class="info-icon level-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <div class="info-content">
            <label>Niveau</label>
            <value>{{ studyClass.levelClass }}</value>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon specialty-icon">
            <i class="fas fa-book"></i>
          </div>
          <div class="info-content">
            <label>Spécialité</label>
            <value>{{ studyClass.speciality }}</value>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon type-icon">
            <i class="fas fa-tag"></i>
          </div>
          <div class="info-content">
            <label>Type</label>
            <value>{{ studyClass.classType }}</value>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon schedule-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="info-content">
            <label>Planning</label>
            <value>{{ studyClass.day }}</value>
          </div>
        </div>

        <div class="info-card">
          <div class="info-icon time-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="info-content">
            <label>Horaires</label>
            <value>{{ formatTime(studyClass.startHour) }} - {{ formatTime(studyClass.endHour) }}</value>
          </div>
        </div>

        <div class="info-card" v-if="studyClass.principalTeacher">
          <div class="info-icon teacher-icon">
            <i class="fas fa-user-tie"></i>
          </div>
          <div class="info-content">
            <label>Professeur</label>
            <value>{{ studyClass.principalTeacher.firstName }} {{ studyClass.principalTeacher.lastName }}</value>
          </div>
        </div>
      </div>
    </div>

    <!-- Section étudiants modernisée -->
    <div class="glass-card students-section">
      <div class="students-header">
        <div class="section-title">
          <h3><i class="fas fa-users"></i> Étudiants de la Classe</h3>
          <div class="student-count-badge">{{ filteredStudents.length }}</div>
        </div>

        <div class="controls-container">
          <!-- Toggle de filtrage stylisé -->
          <div class="filter-toggle">
            <label class="toggle-label">
              <input type="checkbox" v-model="filterActive" class="toggle-input">
              <span class="toggle-slider">
                <span class="toggle-text">{{ filterActive ? 'Actifs' : 'Inactifs' }}</span>
              </span>
            </label>
          </div>

          <!-- Bouton ajouter modernisé -->
          <button class="add-student-btn" data-bs-toggle="modal" data-bs-target="#newStudentModal">
            <i class="fas fa-plus"></i>
            <span>Ajouter un étudiant</span>
          </button>
        </div>
      </div>

      <!-- Tableau modernisé -->
      <div class="modern-table-container">
        <div class="table-wrapper">
          <table class="modern-table">
            <thead>
            <tr>
              <th><i class="fas fa-hashtag"></i> ID</th>
              <th><i class="fas fa-user"></i> Nom</th>
              <th><i class="fas fa-user-circle"></i> Prénom</th>
              <th><i class="fas fa-birthday-cake"></i> Naissance</th>
              <th><i class="fas fa-layer-group"></i> Niveau</th>
              <th><i class="fas fa-toggle-on"></i> Statut</th>
              <th><i class="fas fa-cog"></i> Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="studentClassRegistered in filteredStudents"
                :key="studentClassRegistered.id"
                class="student-row"
                :class="{ 'inactive-student': !studentClassRegistered.active }">
              <td class="id-cell">{{ studentClassRegistered.student.id }}</td>
              <td class="name-cell">
                <div class="student-avatar">
                  {{ studentClassRegistered.student.lastName.charAt(0) }}
                </div>
                <span>{{ studentClassRegistered.student.lastName }}</span>
              </td>
              <td>{{ studentClassRegistered.student.firstName }}</td>
              <td>{{ new Date(studentClassRegistered.student.birthDate).toLocaleDateString('fr-FR') }}</td>
              <td>
                <span class="level-badge">{{ studentClassRegistered.student.levelClass }}</span>
              </td>
              <td>
                <label class="status-switch">
                  <input type="checkbox"
                         :checked="studentClassRegistered.active"
                         @change="confirmDeactivation(studentClassRegistered)">
                  <span class="switch-slider">
                      <span class="switch-indicator"></span>
                    </span>
                </label>
              </td>
              <td>
                <button class="action-btn delete-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteConfirmationModal"
                        @click="studentToDelete = studentClassRegistered">
                  <i class="fas fa-trash-alt"></i>
                  <span>Supprimer</span>
                </button>
              </td>
            </tr>
            <tr v-if="!filteredStudents.length" class="empty-state">
              <td colspan="7">
                <div class="empty-content">
                  <i class="fas fa-users-slash"></i>
                  <h4>Aucun étudiant {{ filterActive ? 'actif' : 'inactif' }}</h4>
                  <p>Cette classe ne contient aucun étudiant {{ filterActive ? 'actif' : 'inactif' }} pour le moment.</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modals modernisés -->
    <div class="modal fade" id="deactivationConfirmationModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
          <div class="modal-header">
            <div class="modal-icon warning-icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h5 class="modal-title">Désactiver l'étudiant</h5>
            <button type="button" class="btn-close modern-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="warning-message">
              <p><strong>Attention :</strong> Veuillez vérifier que cet élève a réglé tous ses frais de session avant de procéder.</p>
              <div class="action-guide">
                <div class="guide-item valid">
                  <i class="fas fa-check-circle"></i>
                  <span>Si les paiements sont à jour → Désactiver</span>
                </div>
                <div class="guide-item invalid">
                  <i class="fas fa-times-circle"></i>
                  <span>Si des paiements sont en attente → Consulter l'administration</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Annuler
            </button>
            <button type="button" class="btn-modern btn-warning" @click="deactivateStudent" data-bs-dismiss="modal">
              <i class="fas fa-user-slash"></i> Désactiver
            </button>
          </div>
        </div>
      </div>
    </div>

    <new-student-to-class-modal :students="studentsNotInStudyClass" :studyClass="studyClass" />

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal">
          <div class="modal-header">
            <div class="modal-icon danger-icon">
              <i class="fas fa-trash-alt"></i>
            </div>
            <h5 class="modal-title">Supprimer l'étudiant</h5>
            <button type="button" class="btn-close modern-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Cette action supprimera définitivement cet étudiant de la classe.</p>
            <p><strong>Cette action est irréversible.</strong></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn-modern btn-secondary" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Annuler
            </button>
            <button type="button" class="btn-modern btn-danger" @click="deleteStudent" data-bs-dismiss="modal">
              <i class="fas fa-trash-alt"></i> Confirmer
            </button>
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
    studentsInStudyClass: {
      type: Object,
      required: true,
    },
    studentsNotInStudyClass: {
      type: Object,
      required: true,
    }
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
      studentToDelete: null,
      studentToDeactivate: null,
      filterActive: true,
      localStudentsInStudyClass: [...this.studentsInStudyClass],
    };
  },
  computed: {
    filteredStudents() {
      return this.localStudentsInStudyClass.filter(student => student.active === this.filterActive);
    }
  },
  methods: {
    formatTime(timeString) {
      if (!timeString) return '';
      const date = new Date(timeString);
      return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'UTC'
      });
    },
    printClassReport() {
      const printContent = this.generatePrintContent();
      const printWindow = window.open('', '_blank');
      printWindow.document.write(printContent);
      printWindow.document.close();

      printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
      };
    },
    generatePrintContent() {
      const currentDate = new Date().toLocaleDateString('fr-FR');
      const activeStudents = this.localStudentsInStudyClass.filter(student => student.active);

      return `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Rapport de classe - ${this.studyClass.name}</title>
          <style>
            body {
              font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
              margin: 20px;
              color: #333;
              line-height: 1.6;
            }
            .header {
              text-align: center;
              margin-bottom: 30px;
              border-bottom: 3px solid #6c63ff;
              padding-bottom: 20px;
            }
            .header h1 {
              color: #6c63ff;
              margin-bottom: 10px;
            }
            .class-info {
              background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
              color: white;
              padding: 25px;
              border-radius: 15px;
              margin-bottom: 30px;
            }
            .class-info h2 {
              margin-top: 0;
              color: white;
            }
            .info-grid {
              display: grid;
              grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
              gap: 15px;
              margin-top: 20px;
            }
            .info-item {
              background: rgba(255,255,255,0.1);
              padding: 15px;
              border-radius: 10px;
              backdrop-filter: blur(10px);
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin-top: 20px;
              box-shadow: 0 4px 20px rgba(0,0,0,0.1);
              border-radius: 10px;
              overflow: hidden;
            }
            th, td {
              padding: 15px;
              text-align: left;
              border-bottom: 1px solid #eee;
            }
            th {
              background: linear-gradient(135deg, #6c63ff, #5a52d5);
              color: white;
              font-weight: 600;
            }
            tr:nth-child(even) {
              background-color: #f8f9ff;
            }
            .footer {
              margin-top: 40px;
              text-align: center;
              font-size: 12px;
              color: #888;
              border-top: 1px solid #eee;
              padding-top: 20px;
            }
            @media print {
              body { margin: 0; }
              .header { page-break-after: avoid; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>${this.studyClass.name} - ${this.studyClass.speciality}</h1>
            <p>Rapport généré le ${currentDate}</p>
          </div>

          <div class="class-info">
            <h2>📋 Informations sur la Classe</h2>
            <div class="info-grid">
              <div class="info-item"><strong>🎓 Niveau :</strong> ${this.studyClass.levelClass}</div>
              <div class="info-item"><strong>📚 Spécialité :</strong> ${this.studyClass.speciality}</div>
              <div class="info-item"><strong>🏷️ Type :</strong> ${this.studyClass.classType}</div>
              <div class="info-item"><strong>📅 Jour :</strong> ${this.studyClass.day}</div>
              <div class="info-item"><strong>⏰ Horaires :</strong> ${this.formatTime(this.studyClass.startHour)} - ${this.formatTime(this.studyClass.endHour)}</div>
              ${this.studyClass.principalTeacher ? `<div class="info-item"><strong>👨‍🏫 Professeur :</strong> ${this.studyClass.principalTeacher.firstName} ${this.studyClass.principalTeacher.lastName}</div>` : ''}
            </div>
          </div>

          <div class="students-section">
            <h2>👥 Liste des Étudiants Actifs (${activeStudents.length} étudiants)</h2>
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nom de famille</th>
                  <th>Prénom</th>
                  <th>Date de naissance</th>
                  <th>Niveau</th>
                </tr>
              </thead>
              <tbody>
                ${activeStudents.map((studentReg, index) => `
                  <tr>
                    <td>${studentReg.student.id}</td>
                    <td>${studentReg.student.lastName}</td>
                    <td>${studentReg.student.firstName}</td>
                    <td>${new Date(studentReg.student.birthDate).toLocaleDateString('fr-FR')}</td>
                    <td>${studentReg.student.levelClass}</td>
                  </tr>
                `).join('')}
                ${activeStudents.length === 0 ? '<tr><td colspan="5" style="text-align: center; color: #666; padding: 40px;">Aucun étudiant actif dans cette classe</td></tr>' : ''}
              </tbody>
            </table>
          </div>

          <div class="footer">
            <p>📄 Document généré automatiquement le ${currentDate}</p>
          </div>
        </body>
        </html>
      `;
    },
    confirmDeactivation(student) {
      if (!student.active) return;

      this.studentToDeactivate = student;
      const deactivationModal = new this.$bootstrap.Modal(document.getElementById('deactivationConfirmationModal'));
      deactivationModal.show();
    },
    deactivateStudent() {
      const url = this.$routing.generate('deactivate_student_from_class', { id: this.studentToDeactivate.id });

      this.$axios.post(url)
          .then(() => {
            this.localStudentsInStudyClass = this.localStudentsInStudyClass.map(student =>
                student.id === this.studentToDeactivate.id ? { ...student, active: false } : student
            );
          })
          .catch(error => console.error("Erreur lors de la désactivation", error));
    },
    deleteStudent() {
      const url = this.$routing.generate('delete_student_from_class', { id: this.studentToDelete.id });

      this.$axios.post(url)
          .then(() => {
            this.messageAlert = "L'étudiant a été supprimé avec succès!";
            this.typeAlert = "success";
            this.localStudentsInStudyClass = this.localStudentsInStudyClass.filter(student =>
                student.id !== this.studentToDelete.id
            );
          })
          .catch(error => {
            console.error("Erreur lors de la suppression de l'étudiant", error);
            this.messageAlert = "Erreur lors de la suppression de l'étudiant.";
            this.typeAlert = "danger";
          });
    }
  }
};
</script>

<style scoped>
/* Variables CSS pour la cohérence */
:root {
  --primary-gradient: linear-gradient(135deg, #6c63ff 0%, #5a52d5 100%);
  --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
  --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --danger-gradient: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
  --glass-bg: rgba(255, 255, 255, 0.1);
  --glass-border: rgba(255, 255, 255, 0.2);
  --shadow-soft: 0 8px 32px rgba(108, 99, 255, 0.1);
  --shadow-medium: 0 12px 40px rgba(108, 99, 255, 0.15);
  --shadow-strong: 0 20px 60px rgba(108, 99, 255, 0.2);
  --border-radius: 16px;
  --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Container principal */
.modern-container {
  min-height: 100vh;
  background: #ffffff;
  padding: 2rem;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Navigation flottante */
.floating-nav {
  position: fixed;
  top: 1rem;
  left: 1rem;
  right: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: var(--border-radius);
  padding: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  max-width: calc(100vw - 2rem);
  box-sizing: border-box;
  margin-left: 280px;
}

.nav-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  transition: var(--transition-smooth);
  cursor: pointer;
  font-size: 0.9rem;
}

.back-btn {
  background: rgba(108, 99, 255, 0.1);
  color: #6c63ff;
  border: 1px solid rgba(108, 99, 255, 0.2);
}

.back-btn:hover {
  background: rgba(108, 99, 255, 0.15);
  color: #5a52d5;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(108, 99, 255, 0.2);
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.print-btn {
  background: var(--success-gradient);
  color: white;
  box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
}

.print-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
}

.edit-btn {
  background: var(--primary-gradient);
  color: white;
  border: none;
}

.edit-btn:hover {
  background: linear-gradient(135deg, #5a52d5 0%, #4a44c0 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(108, 99, 255, 0.3);
}

/* En-tête héro */
.hero-header {
  position: relative;
  text-align: center;
  padding: 6rem 0 4rem;
  margin-bottom: 3rem;
  overflow: hidden;
  background: var(--primary-gradient);
  border-radius: var(--border-radius);
}

.hero-content {
  position: relative;
  z-index: 2;
}

.class-badge {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 25px;
  font-weight: 600;
  margin-bottom: 1rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 800;
  color: white;
  margin-bottom: 0.5rem;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  line-height: 1.1;
}

.hero-subtitle {
  font-size: 1.5rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 2rem;
  font-weight: 300;
}

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 1rem 1.5rem;
  border-radius: 12px;
  color: white;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-item i {
  font-size: 1.2rem;
  opacity: 0.8;
}

/* Éléments flottants décoratifs */
.hero-decoration {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
}

.floating-element {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  animation: float 6s ease-in-out infinite;
}

.element-1 {
  width: 100px;
  height: 100px;
  top: 20%;
  left: 10%;
  animation-delay: 0s;
}

.element-2 {
  width: 150px;
  height: 150px;
  top: 60%;
  right: 15%;
  animation-delay: 2s;
}

.element-3 {
  width: 80px;
  height: 80px;
  top: 80%;
  left: 20%;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(10deg); }
}

/* Cartes en verre */
.glass-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: var(--border-radius);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: var(--transition-smooth);
}

.glass-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

/* Section informations de classe */
.card-header h2 {
  color: #333;
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.card-header h2 i {
  color: #6c63ff;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.info-card {
  background: rgba(248, 249, 250, 0.8);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: var(--transition-smooth);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.info-card:hover {
  background: rgba(248, 249, 250, 1);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.info-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.level-icon { background: var(--primary-gradient); }
.specialty-icon { background: var(--warning-gradient); }
.type-icon { background: var(--success-gradient); }
.schedule-icon { background: var(--warning-gradient); }
.time-icon { background: var(--primary-gradient); }
.teacher-icon { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); }

.info-content {
  flex: 1;
}

.info-content label {
  display: block;
  color: rgba(0, 0, 0, 0.6);
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-content value {
  display: block;
  color: #333;
  font-size: 1.1rem;
  font-weight: 600;
}

/* Section étudiants */
.students-section {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.students-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.section-title h3 {
  color: #333;
  font-size: 1.6rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.student-count-badge {
  background: var(--primary-gradient);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
}

.controls-container {
  display: flex;
  gap: 1rem;
  align-items: center;
}

/* Toggle de filtrage stylisé */
.filter-toggle {
  position: relative;
}

.toggle-label {
  display: block;
  cursor: pointer;
}

.toggle-input {
  display: none;
}

.toggle-slider {
  display: block;
  width: 120px;
  height: 40px;
  background: rgba(218, 0, 0, 0.1);
  border-radius: 20px;
  position: relative;
  transition: var(--transition-smooth);
  border: 1px solid rgba(236, 8, 8, 0.2);
}

.toggle-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: #000000;
  font-weight: 600;
  font-size: 0.9rem;
  z-index: 2;
}

.toggle-slider::before {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 36px;
  height: 36px;
  background: var(--success-gradient);
  border-radius: 18px;
  transition: var(--transition-smooth);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.toggle-input:checked + .toggle-slider::before {
  transform: translateX(80px);
  background: var(--warning-gradient);
}

.toggle-input:checked + .toggle-slider {
  background: rgba(255, 255, 255, 0.15);
}

/* Bouton ajouter modernisé */
.add-student-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--primary-gradient);
  color: white;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-smooth);
  box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
}

.add-student-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(108, 99, 255, 0.4);
}

/* Tableau modernisé */
.modern-table-container {
  background: rgba(248, 249, 250, 0.3);
  border-radius: var(--border-radius);
  padding: 1rem;
  margin-top: 1.5rem;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.table-wrapper {
  overflow-x: auto;
  border-radius: 12px;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.modern-table th {
  background: #f8f9fa;
  color: #333;
  padding: 1.25rem 1rem;
  font-weight: 600;
  text-align: left;
  border-bottom: 2px solid rgba(0, 0, 0, 0.1);
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.modern-table th i {
  margin-right: 0.5rem;
  opacity: 0.7;
  color: #6c63ff;
}

.modern-table td {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  color: #333;
  vertical-align: middle;
  background: white;
}

.student-row {
  transition: var(--transition-smooth);
}

.student-row:hover {
  background: rgba(108, 99, 255, 0.05);
  transform: scale(1.005);
}

.inactive-student {
  opacity: 0.6;
  background: rgba(0, 0, 0, 0.02);
}

.id-cell {
  font-weight: 600;
  color: rgba(0, 0, 0, 0.7);
}

.name-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary-gradient);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1rem;
  box-shadow: 0 4px 15px rgba(108, 99, 255, 0.3);
}

.level-badge {
  background: var(--secondary-gradient);
  color: #000000;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 600;
  box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
}

/* Switch de statut */
.status-switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 30px;
  cursor: pointer;
}

.status-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch-slider {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.3);
  transition: var(--transition-smooth);
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.switch-slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 4px;
  top: 3px;
  background: white;
  transition: var(--transition-smooth);
  border-radius: 50%;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

input:checked + .switch-slider {
  background: var(--success-gradient);
}

input:checked + .switch-slider:before {
  transform: translateX(26px);
}

/* Boutons d'action */
.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-smooth);
}

.delete-btn {
  background: var(--danger-gradient);
  color: #ff0000;
  box-shadow: 0 4px 15px rgba(252, 74, 26, 0.3);
}

.delete-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(252, 74, 26, 0.4);
}

/* État vide */
.empty-state {
  background: none;
}

.empty-content {
  text-align: center;
  padding: 4rem 2rem;
  color: rgba(0, 0, 0, 0.5);
}

.empty-content i {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.3;
  color: #6c63ff;
}

.empty-content h4 {
  color: #333;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.empty-content p {
  font-size: 1rem;
  opacity: 0.7;
}

/* Modals modernisés */
.modern-modal {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-strong);
}

.modal-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 2rem 2rem 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.modal-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.warning-icon {
  background: var(--warning-gradient);
}

.danger-icon {
  background: var(--warning-gradient);
}

.modal-title {
  color: #333;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  flex: 1;
}

.modern-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #999;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  transition: var(--transition-smooth);
}

.modern-close:hover {
  background: rgba(0, 0, 0, 0.1);
  color: #333;
}

.modal-body {
  padding: 1.5rem 2rem;
  color: #555;
  line-height: 1.6;
}

.warning-message {
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #ffeaa7;
}

.action-guide {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.guide-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 8px;
  font-weight: 500;
}

.guide-item.valid {
  background: rgba(17, 153, 142, 0.1);
  color: #11998e;
}

.guide-item.invalid {
  background: rgba(252, 74, 26, 0.1);
  color: #fc4a1a;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1rem 2rem 2rem;
  justify-content: flex-end;
}

/* Boutons modernisés */
.btn-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition-smooth);
  font-size: 0.95rem;
}

.btn-secondary {
  background: rgba(108, 117, 125, 0.1);
  color: #6c757d;
  border: 1px solid rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
  background: rgba(108, 117, 125, 0.2);
  transform: translateY(-1px);
}

.btn-warning {
  background: var(--warning-gradient);
  color: white;
  box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
}

.btn-warning:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(240, 147, 251, 0.4);
}

.btn-danger {
  background: var(--danger-gradient);
  color: #fa0000;
  box-shadow: 0 4px 15px rgba(252, 74, 26, 0.3);
}

.btn-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(252, 74, 26, 0.4);
}

/* Alert modernisé */
.modern-alert {
  margin-bottom: 2rem;
  border-radius: var(--border-radius);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: var(--shadow-soft);
}

/* Responsive Design */
@media (max-width: 768px) {
  .modern-container {
    padding: 1rem;
  }

  .floating-nav {
    position: relative;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
    left: 0;
    right: 0;
    top: 0;
  }

  .action-buttons {
    width: 100%;
    justify-content: center;
  }

  .nav-btn {
    flex: 1;
    justify-content: center;
    min-width: 0;
  }

  .hero-header {
    padding: 4rem 0 2rem;
  }

  .hero-title {
    font-size: 2.5rem;
  }

  .hero-stats {
    flex-direction: column;
    align-items: center;
  }

  .students-header {
    flex-direction: column;
    align-items: stretch;
  }

  .controls-container {
    flex-direction: column;
    gap: 1rem;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .table-wrapper {
    font-size: 0.9rem;
  }

  .modern-table th,
  .modern-table td {
    padding: 1rem 0.75rem;
  }
}

@media (max-width: 480px) {
  .floating-nav {
    padding: 0.75rem;
  }

  .nav-btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
  }

  .nav-btn span {
    display: none;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1.2rem;
  }

  .stat-item {
    padding: 0.75rem 1rem;
  }

  .glass-card {
    padding: 1.5rem;
  }
}
</style>