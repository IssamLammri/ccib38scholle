<template>
  <div class="container-fluid px-4 py-3">
    <!-- En-tête moderne avec gradient -->
    <div class="header-section">
      <div class="header-content">
        <div class="header-left">
          <div class="icon-wrapper">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
          <div class="header-text">
            <h1 class="page-title">
              {{ isEditMode ? 'Modifier l\'enseignant' : 'Nouvel enseignant' }}
            </h1>
            <p class="page-subtitle">
              {{ isEditMode ? 'Mise à jour des informations de l\'enseignant' : 'Ajout d\'un nouvel enseignant au système' }}
            </p>
          </div>
        </div>
        <a :href="$routing.generate('app_teacher_index')" class="btn btn-modern btn-secondary">
          <i class="fas fa-arrow-left"></i>
          <span>Retour</span>
        </a>
      </div>
    </div>

    <!-- Barre de progression -->
    <div class="progress-bar-container" v-if="!isEditMode">
      <div class="progress-steps">
        <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
          <div class="step-icon">
            <i class="fas fa-user"></i>
          </div>
          <span class="step-label">Informations personnelles</span>
        </div>
        <div class="step-connector" :class="{ active: currentStep > 1 }"></div>
        <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
          <div class="step-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <span class="step-label">Informations académiques</span>
        </div>
        <div class="step-connector" :class="{ active: currentStep > 2 }"></div>
        <div class="step" :class="{ active: currentStep >= 3 }">
          <div class="step-icon">
            <i class="fas fa-chalkboard"></i>
          </div>
          <span class="step-label">Classes assignées</span>
        </div>
      </div>
    </div>

    <!-- Formulaire -->
    <form @submit.prevent="handleSubmit" class="teacher-form">
      <!-- Section 1: Informations personnelles -->
      <div class="form-card" v-show="currentStep === 1 || isEditMode">
        <div class="card-header">
          <div class="card-header-content">
            <div class="card-icon personal">
              <i class="fas fa-user"></i>
            </div>
            <div>
              <h2 class="card-title">Informations personnelles</h2>
              <p class="card-description">Coordonnées de base de l'enseignant</p>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="form-grid">
            <!-- Nom -->
            <div class="form-group">
              <label for="lastName" class="form-label">
                <i class="fas fa-user-tag"></i>
                Nom <span class="required">*</span>
              </label>
              <input
                  type="text"
                  id="lastName"
                  v-model="form.lastName"
                  class="form-control"
                  :class="{ 'error': errors.lastName }"
                  placeholder="Entrez le nom de famille"
                  @input="clearError('lastName')"
              />
              <span v-if="errors.lastName" class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ errors.lastName }}
              </span>
            </div>

            <!-- Prénom -->
            <div class="form-group">
              <label for="firstName" class="form-label">
                <i class="fas fa-user"></i>
                Prénom <span class="required">*</span>
              </label>
              <input
                  type="text"
                  id="firstName"
                  v-model="form.firstName"
                  class="form-control"
                  :class="{ 'error': errors.firstName }"
                  placeholder="Entrez le prénom"
                  @input="clearError('firstName')"
              />
              <span v-if="errors.firstName" class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ errors.firstName }}
              </span>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label for="email" class="form-label">
                <i class="fas fa-envelope"></i>
                Email <span class="required">*</span>
              </label>
              <input
                  type="email"
                  id="email"
                  v-model="form.email"
                  class="form-control"
                  :class="{ 'error': errors.email }"
                  placeholder="exemple@email.com"
                  @input="clearError('email')"
              />
              <span v-if="errors.email" class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ errors.email }}
              </span>
            </div>

            <!-- Téléphone -->
            <div class="form-group">
              <label for="phoneNumber" class="form-label">
                <i class="fas fa-phone"></i>
                Téléphone <span class="required">*</span>
              </label>
              <input
                  type="tel"
                  id="phoneNumber"
                  v-model="form.phoneNumber"
                  class="form-control"
                  :class="{ 'error': errors.phoneNumber }"
                  placeholder="06 12 34 56 78"
                  @input="clearError('phoneNumber')"
              />
              <span v-if="errors.phoneNumber" class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ errors.phoneNumber }}
              </span>
            </div>
          </div>
        </div>

        <div class="card-footer" v-if="!isEditMode">
          <button type="button" @click="nextStep" class="btn btn-modern btn-primary">
            Suivant
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Section 2: Informations académiques -->
      <div class="form-card" v-show="currentStep === 2 || isEditMode">
        <div class="card-header">
          <div class="card-header-content">
            <div class="card-icon academic">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
              <h2 class="card-title">Informations académiques</h2>
              <p class="card-description">Niveau d'études et spécialités enseignées</p>
            </div>
          </div>
        </div>

        <div class="card-body">
          <!-- Niveau d'études -->
          <div class="form-group full-width">
            <label for="educationLevel" class="form-label">
              <i class="fas fa-award"></i>
              Niveau d'études <span class="required">*</span>
            </label>
            <select
                id="educationLevel"
                v-model="form.educationLevel"
                class="form-control"
                :class="{ 'error': errors.educationLevel }"
                @change="clearError('educationLevel')"
            >
              <option value="">Sélectionnez un niveau</option>
              <option v-for="level in educationLevels" :key="level" :value="level">
                {{ level }}
              </option>
            </select>
            <span v-if="errors.educationLevel" class="error-message">
              <i class="fas fa-exclamation-circle"></i>
              {{ errors.educationLevel }}
            </span>
          </div>

          <!-- Spécialités -->
          <div class="form-group full-width">
            <label class="form-label">
              <i class="fas fa-book"></i>
              Spécialités enseignées
              <span class="badge badge-info">Optionnel</span>
            </label>

            <div class="specialities-grid">
              <div
                  v-for="speciality in availableSpecialities"
                  :key="speciality"
                  class="checkbox-card"
                  :class="{ selected: form.specialities.includes(speciality) }"
                  @click="toggleSpeciality(speciality)"
              >
                <input
                    type="checkbox"
                    :id="'spec-' + speciality"
                    :value="speciality"
                    v-model="form.specialities"
                    class="checkbox-input"
                />
                <div class="checkbox-content">
                  <i class="fas fa-book-open"></i>
                  <span>{{ speciality }}</span>
                </div>
              </div>
            </div>

            <!-- Spécialités sélectionnées -->
            <div v-if="form.specialities.length > 0" class="selected-items">
              <small class="selected-label">
                <i class="fas fa-check-circle"></i>
                {{ form.specialities.length }} spécialité{{ form.specialities.length > 1 ? 's' : '' }} sélectionnée{{ form.specialities.length > 1 ? 's' : '' }}
              </small>
              <div class="selected-badges">
                <span
                    v-for="spec in form.specialities"
                    :key="spec"
                    class="badge badge-selected"
                >
                  {{ spec }}
                  <i class="fas fa-times" @click.stop="removeSpeciality(spec)"></i>
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="card-footer" v-if="!isEditMode">
          <button type="button" @click="prevStep" class="btn btn-modern btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Précédent
          </button>
          <button type="button" @click="nextStep" class="btn btn-modern btn-primary">
            Suivant
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>

      <!-- Section 3: Classes assignées -->
      <div class="form-card" v-show="currentStep === 3 || isEditMode">
        <div class="card-header">
          <div class="card-header-content">
            <div class="card-icon classes">
              <i class="fas fa-chalkboard"></i>
            </div>
            <div>
              <h2 class="card-title">Classes assignées</h2>
              <p class="card-description">Classes où l'enseignant sera professeur principal</p>
            </div>
          </div>
        </div>

        <div class="card-body">
          <!-- Recherche de classes -->
          <div class="form-group full-width">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input
                  type="text"
                  v-model="classSearchTerm"
                  class="search-input"
                  placeholder="Rechercher une classe par nom, spécialité ou niveau..."
              />
              <button
                  v-if="classSearchTerm"
                  type="button"
                  @click="classSearchTerm = ''"
                  class="search-clear"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Liste des classes -->
          <div class="classes-container">
            <div v-if="isLoadingClasses" class="loading-state">
              <div class="spinner"></div>
              <p>Chargement des classes...</p>
            </div>

            <div v-else-if="filteredClasses.length === 0" class="empty-state">
              <i class="fas fa-folder-open"></i>
              <p>Aucune classe {{ classSearchTerm ? 'trouvée' : 'disponible' }}</p>
            </div>

            <div v-else class="classes-grid">
              <div
                  v-for="cls in filteredClasses"
                  :key="cls.id"
                  class="class-card"
                  :class="{ selected: form.classIds.includes(cls.id) }"
                  @click="toggleClass(cls.id)"
              >
                <input
                    type="checkbox"
                    :id="'class-' + cls.id"
                    :value="cls.id"
                    v-model="form.classIds"
                    class="checkbox-input"
                />
                <div class="class-card-content">
                  <div class="class-card-header">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h4>{{ cls.name }}</h4>
                  </div>
                  <div class="class-card-details">
                    <span class="class-detail">
                      <i class="fas fa-book"></i>
                      {{ cls.speciality }}
                    </span>
                    <span class="class-detail">
                      <i class="fas fa-layer-group"></i>
                      {{ cls.level }}
                    </span>
                    <span class="class-detail">
                      <i class="fas fa-calendar-day"></i>
                      {{ cls.day }}
                    </span>
                  </div>
                </div>
                <div class="class-card-check">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Classes sélectionnées -->
          <div v-if="selectedClassesDetails.length > 0" class="selected-items">
            <small class="selected-label">
              <i class="fas fa-check-circle"></i>
              {{ selectedClassesDetails.length }} classe{{ selectedClassesDetails.length > 1 ? 's' : '' }} sélectionnée{{ selectedClassesDetails.length > 1 ? 's' : '' }}
            </small>
            <div class="selected-badges">
              <span
                  v-for="cls in selectedClassesDetails"
                  :key="cls.id"
                  class="badge badge-selected"
              >
                {{ cls.name }}
                <i class="fas fa-times" @click.stop="removeClass(cls.id)"></i>
              </span>
            </div>
          </div>
        </div>

        <div class="card-footer" v-if="!isEditMode">
          <button type="button" @click="prevStep" class="btn btn-modern btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Précédent
          </button>
        </div>
      </div>

      <!-- Actions finales -->
      <div class="form-actions">
        <a :href="$routing.generate('app_teacher_index')" class="btn btn-modern btn-outline">
          <i class="fas fa-times"></i>
          <span>Annuler</span>
        </a>

        <div class="actions-right">
          <button
              v-if="!isEditMode"
              type="button"
              @click="resetForm"
              class="btn btn-modern btn-outline"
          >
            <i class="fas fa-redo"></i>
            <span>Réinitialiser</span>
          </button>

          <button
              type="submit"
              class="btn btn-modern btn-primary btn-submit"
              :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="spinner-small"></span>
            <i v-else class="fas fa-save"></i>
            <span>{{ isSubmitting ? 'Enregistrement...' : (isEditMode ? 'Mettre à jour' : 'Enregistrer') }}</span>
          </button>
        </div>
      </div>
    </form>

    <!-- Alertes -->
    <transition name="slide-fade">
      <alert
          v-if="messageAlert"
          :text="messageAlert"
          :type="typeAlert"
          class="alert-notification"
      />
    </transition>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "TeacherForm",
  components: { Alert },
  props: {
    teacherId: {
      type: Number,
      default: null
    },
    initialData: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      currentStep: 1,

      form: {
        lastName: '',
        firstName: '',
        email: '',
        phoneNumber: '',
        educationLevel: '',
        specialities: [],
        classIds: []
      },

      errors: {},

      isSubmitting: false,
      isLoadingClasses: false,

      availableClasses: [],
      classSearchTerm: '',

      messageAlert: null,
      typeAlert: null,

      educationLevels: [
        'Bac',
        'Bac +1',
        'Bac +2',
        'Bac +3 (Licence)',
        'Bac +4',
        'Bac +5 (Master)',
        'Doctorat',
        'Autre'
      ],

      availableSpecialities: [
        'Mathématiques',
        'Physique/Chimie',
        'Sciences de la Vie et de la Terre (SVT)',
        'Français',
        'Anglais',
        'Espagnol',
        'Allemand',
        'Arabe',
        'Histoire-Géographie',
        'Philosophie',
        'Économie',
        'Informatique',
        'Sport',
        'Musique',
        'Arts plastiques',
        'Autre'
      ]
    };
  },

  computed: {
    isEditMode() {
      return !!this.teacherId;
    },

    filteredClasses() {
      if (!this.classSearchTerm) {
        return this.availableClasses;
      }

      const search = this.classSearchTerm.toLowerCase();
      return this.availableClasses.filter(cls => {
        return cls.name.toLowerCase().includes(search) ||
            cls.speciality.toLowerCase().includes(search) ||
            cls.level.toLowerCase().includes(search);
      });
    },

    selectedClassesDetails() {
      return this.availableClasses.filter(cls => this.form.classIds.includes(cls.id));
    }
  },

  methods: {
    async fetchClasses() {
      this.isLoadingClasses = true;
      try {
        const res = await this.$axios.get(this.$routing.generate("study_class_list"));
        this.availableClasses = res.data.studyClass || [];
      } catch (e) {
        console.error("Erreur lors de la récupération des classes :", e);
        this.showAlert("danger", "Erreur lors du chargement des classes.");
      } finally {
        this.isLoadingClasses = false;
      }
    },

    async fetchTeacher() {
      if (!this.isEditMode) return;

      try {
        const res = await this.$axios.get(
            this.$routing.generate("api_teacher_show", { id: this.teacherId })
        );

        const teacher = res.data.teacher;

        this.form = {
          lastName: teacher.lastName || '',
          firstName: teacher.firstName || '',
          email: teacher.email || '',
          phoneNumber: teacher.phoneNumber || '',
          educationLevel: teacher.educationLevel || '',
          specialities: teacher.specialities || [],
          classIds: (teacher.classes || []).map(c => c.id)
        };

      } catch (e) {
        console.error("Erreur lors de la récupération de l'enseignant :", e);
        this.showAlert("danger", "Erreur lors du chargement des données de l'enseignant.");
      }
    },

    validateStep(step) {
      this.errors = {};

      if (step === 1) {
        if (!this.form.lastName.trim()) {
          this.errors.lastName = "Le nom est requis";
        }

        if (!this.form.firstName.trim()) {
          this.errors.firstName = "Le prénom est requis";
        }

        if (!this.form.email.trim()) {
          this.errors.email = "L'email est requis";
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
          this.errors.email = "L'email n'est pas valide";
        }

        if (!this.form.phoneNumber.trim()) {
          this.errors.phoneNumber = "Le téléphone est requis";
        } else if (!/^0[1-9]\d{8}$/.test(this.form.phoneNumber.replace(/\s/g, ''))) {
          this.errors.phoneNumber = "Le numéro de téléphone n'est pas valide";
        }
      } else if (step === 2) {
        if (!this.form.educationLevel) {
          this.errors.educationLevel = "Le niveau d'études est requis";
        }
      }

      return Object.keys(this.errors).length === 0;
    },

    nextStep() {
      if (this.validateStep(this.currentStep)) {
        this.currentStep++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        this.showAlert("warning", "Veuillez remplir tous les champs requis.");
      }
    },

    prevStep() {
      this.currentStep--;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    async handleSubmit() {
      if (!this.validateStep(1) || !this.validateStep(2)) {
        this.showAlert("danger", "Veuillez corriger les erreurs dans le formulaire.");
        this.currentStep = 1;
        return;
      }

      this.isSubmitting = true;

      try {
        const url = this.isEditMode
            ? this.$routing.generate("api_teacher_update", { id: this.teacherId })
            : this.$routing.generate("api_teacher_create");

        const res = this.isEditMode
            ? await this.$axios.put(url, this.form)
            : await this.$axios.post(url, this.form);

        if (res.data.success) {
          this.showAlert("success", this.isEditMode
              ? "Enseignant mis à jour avec succès !"
              : "Enseignant ajouté avec succès !");

          setTimeout(() => {
            const id = this.isEditMode
                ? this.teacherId
                : (res.data.id || res.data.teacher?.id);

            window.location.href = this.$routing.generate("app_teacher_show", { id });
          }, 1500);
        } else {
          this.showAlert("danger", res.data.message || "Une erreur est survenue.");
        }
      } catch (e) {
        console.error("Erreur lors de la soumission :", e);
        this.showAlert("danger", e.response?.data?.message || "Erreur lors de l'enregistrement.");

        if (e.response?.data?.errors) {
          this.errors = e.response.data.errors;
        }
      } finally {
        this.isSubmitting = false;
      }
    },

    resetForm() {
      this.form = {
        lastName: '',
        firstName: '',
        email: '',
        phoneNumber: '',
        educationLevel: '',
        specialities: [],
        classIds: []
      };
      this.errors = {};
      this.currentStep = 1;
      this.messageAlert = null;
    },

    toggleSpeciality(speciality) {
      const index = this.form.specialities.indexOf(speciality);
      if (index > -1) {
        this.form.specialities.splice(index, 1);
      } else {
        this.form.specialities.push(speciality);
      }
    },

    removeSpeciality(speciality) {
      this.form.specialities = this.form.specialities.filter(s => s !== speciality);
    },

    toggleClass(classId) {
      const index = this.form.classIds.indexOf(classId);
      if (index > -1) {
        this.form.classIds.splice(index, 1);
      } else {
        this.form.classIds.push(classId);
      }
    },

    removeClass(classId) {
      this.form.classIds = this.form.classIds.filter(id => id !== classId);
    },

    clearError(field) {
      if (this.errors[field]) {
        delete this.errors[field];
      }
    },

    showAlert(type, message) {
      this.typeAlert = type;
      this.messageAlert = message;

      setTimeout(() => {
        this.messageAlert = null;
      }, 5000);
    }
  },

  mounted() {
    this.fetchClasses();

    if (this.isEditMode) {
      this.fetchTeacher();
    } else if (this.initialData) {
      this.form = { ...this.form, ...this.initialData };
    }
  }
};
</script>

<style scoped>
/* Variables */
:global(:root) {
  --primary: #667eea;
  --primary-dark: #5568d3;
  --secondary: #718096;
  --success: #48bb78;
  --danger: #f56565;
  --warning: #ed8936;
  --info: #4299e1;

  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);

  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-tertiary: #edf2f7;

  --text-primary: #2d3748;
  --text-secondary: #718096;
  --text-tertiary: #a0aec0;

  --border-color: #e2e8f0;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;

  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Layout */
.container-fluid {
  max-width: 1400px;
  margin: 0 auto;
  background: var(--bg-secondary);
  min-height: 100vh;
}

/* Header Section */
.header-section {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.icon-wrapper {
  width: 64px;
  height: 64px;
  background: var(--gradient-primary);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  flex-shrink: 0;
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.header-text {
  flex: 1;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
  line-height: 1.2;
}

.page-subtitle {
  font-size: 1rem;
  color: var(--text-secondary);
  margin: 0;
  line-height: 1.4;
}

/* Progress Bar */
.progress-bar-container {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.progress-steps {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  position: relative;
  z-index: 2;
}

.step-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: var(--bg-tertiary);
  border: 3px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: var(--text-tertiary);
  transition: var(--transition);
}

.step.active .step-icon {
  background: var(--gradient-primary);
  border-color: var(--primary);
  color: white;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.step.completed .step-icon {
  background: var(--success);
  border-color: var(--success);
  color: white;
}

.step-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
  text-align: center;
  max-width: 120px;
}

.step.active .step-label {
  color: var(--primary);
  font-weight: 600;
}

.step-connector {
  flex: 1;
  height: 3px;
  background: var(--border-color);
  margin: 0 -1rem;
  margin-bottom: 2rem;
  transition: var(--transition);
  position: relative;
  z-index: 1;
}

.step-connector.active {
  background: var(--primary);
}

/* Form Card */
.form-card {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  overflow: hidden;
  animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--border-color);
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.card-header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  flex-shrink: 0;
}

.card-icon.personal {
  background: var(--gradient-primary);
}

.card-icon.academic {
  background: var(--gradient-success);
}

.card-icon.classes {
  background: var(--gradient-secondary);
}

.card-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.25rem 0;
}

.card-description {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

.card-body {
  padding: 2rem;
}

.card-footer {
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--border-color);
  background: var(--bg-secondary);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

/* Form Elements */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-primary);
}

.form-label i {
  color: var(--primary);
  font-size: 0.875rem;
}

.required {
  color: var(--danger);
  font-weight: 700;
}

.form-control {
  padding: 0.875rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  color: var(--text-primary);
  background: var(--bg-primary);
  transition: var(--transition);
  font-family: inherit;
}

.form-control:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control.error {
  border-color: var(--danger);
}

.form-control.error:focus {
  box-shadow: 0 0 0 3px rgba(245, 101, 101, 0.1);
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8125rem;
  color: var(--danger);
  margin-top: 0.25rem;
}

.error-message i {
  font-size: 0.75rem;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.badge-info {
  background: rgba(66, 153, 225, 0.1);
  color: var(--info);
}

/* Checkbox Cards */
.specialities-grid,
.classes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.checkbox-card,
.class-card {
  position: relative;
  padding: 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  background: var(--bg-primary);
}

.checkbox-card:hover,
.class-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.checkbox-card.selected,
.class-card.selected {
  border-color: var(--primary);
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
}

.checkbox-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.checkbox-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-primary);
}

.checkbox-content i {
  color: var(--primary);
  font-size: 1rem;
}

.class-card-content {
  flex: 1;
}

.class-card-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.class-card-header i {
  color: var(--primary);
  font-size: 1rem;
}

.class-card-header h4 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.class-card-details {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.class-detail {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.class-detail i {
  width: 14px;
  color: var(--text-tertiary);
  font-size: 0.75rem;
}

.class-card-check {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  font-size: 1.25rem;
  color: var(--primary);
  opacity: 0;
  transition: var(--transition);
}

.class-card.selected .class-card-check {
  opacity: 1;
}

/* Search Box */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-primary);
  transition: var(--transition);
}

.search-box:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-box i {
  color: var(--text-tertiary);
  font-size: 1rem;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.9375rem;
  color: var(--text-primary);
  background: transparent;
  font-family: inherit;
}

.search-clear {
  padding: 0.25rem;
  border: none;
  background: transparent;
  color: var(--text-tertiary);
  cursor: pointer;
  border-radius: 4px;
  transition: var(--transition);
}

.search-clear:hover {
  color: var(--danger);
  background: rgba(245, 101, 101, 0.1);
}

/* Classes Container */
.classes-container {
  max-height: 500px;
  overflow-y: auto;
  padding: 0.5rem;
  margin-top: 1rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-secondary);
}

.classes-container::-webkit-scrollbar {
  width: 8px;
}

.classes-container::-webkit-scrollbar-track {
  background: var(--bg-tertiary);
  border-radius: 4px;
}

.classes-container::-webkit-scrollbar-thumb {
  background: var(--text-tertiary);
  border-radius: 4px;
}

.classes-container::-webkit-scrollbar-thumb:hover {
  background: var(--text-secondary);
}

/* Selected Items */
.selected-items {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.selected-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.75rem;
}

.selected-label i {
  color: var(--success);
}

.selected-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge-selected {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.875rem;
  background: var(--gradient-primary);
  color: white;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
  transition: var(--transition);
}

.badge-selected i {
  cursor: pointer;
  opacity: 0.8;
  transition: var(--transition);
}

.badge-selected i:hover {
  opacity: 1;
  transform: scale(1.1);
}

/* States */
.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--text-secondary);
  text-align: center;
}

.loading-state i,
.empty-state i {
  font-size: 2.5rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

.spinner,
.spinner-small {
  width: 40px;
  height: 40px;
  border: 4px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border-width: 2px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Buttons */
.btn-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.875rem 1.5rem;
  border: 2px solid transparent;
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
}

.btn-modern:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  text-decoration: none;
}

.btn-modern:active:not(:disabled) {
  transform: translateY(0);
}

.btn-modern:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--gradient-primary);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #5568d3 0%, #663b8e 100%);
  color: white;
}

.btn-secondary {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.btn-secondary:hover:not(:disabled) {
  background: var(--text-tertiary);
  color: var(--text-primary);
}

.btn-outline {
  background: transparent;
  border-color: var(--border-color);
  color: var(--text-primary);
}

.btn-outline:hover:not(:disabled) {
  border-color: var(--text-secondary);
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.btn-submit {
  position: relative;
  min-width: 180px;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 2rem;
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  flex-wrap: wrap;
}

.actions-right {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

/* Alert Notification */
.alert-notification {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 9999;
  max-width: 500px;
  box-shadow: var(--shadow-lg);
  animation: slideInRight 0.4s ease;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Responsive */
@media (max-width: 1024px) {
  .specialities-grid,
  .classes-grid {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-left {
    width: 100%;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .progress-steps {
    flex-direction: column;
    gap: 1.5rem;
  }

  .step {
    width: 100%;
    flex-direction: row;
    justify-content: flex-start;
  }

  .step-label {
    max-width: none;
    text-align: left;
  }

  .step-connector {
    display: none;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .specialities-grid,
  .classes-grid {
    grid-template-columns: 1fr;
  }

  .card-body {
    padding: 1.5rem;
  }

  .form-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .actions-right {
    width: 100%;
    flex-direction: column;
  }

  .btn-modern {
    width: 100%;
    justify-content: center;
  }

  .alert-notification {
    top: 1rem;
    right: 1rem;
    left: 1rem;
    max-width: none;
  }
}

@media (max-width: 480px) {
  .container-fluid {
    padding: 1rem;
  }

  .header-section {
    padding: 1.5rem;
  }

  .icon-wrapper {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .page-subtitle {
    font-size: 0.875rem;
  }

  .card-header,
  .card-body,
  .card-footer {
    padding: 1rem;
  }

  .form-actions {
    padding: 1rem;
  }
}
</style>