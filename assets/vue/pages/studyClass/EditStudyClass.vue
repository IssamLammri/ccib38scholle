<template>
  <div class="container mt-5" lang="fr">
    <!-- Bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a
          :href="$routing.generate('app_study_class_index')"
          class="btn btn-outline-secondary"
      >
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
      <h1 class="text-primary mb-0">Modifier une Classe</h1>
    </div>
    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="messageType"
    />

    <!-- Formulaire -->
    <div class="card shadow-sm p-4">
      <form @submit.prevent="save">
        <div class="row">
          <!-- Nom -->
          <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Nom</label>
            <input
                type="text"
                id="name"
                class="form-control"
                v-model="form.name"
                required
                :disabled="!canEditClass"
            />
          </div>
          <!-- Niveau -->
          <!-- Niveau -->
          <div class="col-md-6 mb-3">
            <label for="level" class="form-label">Niveau</label>

            <!-- Si Arabe : select avec niveaux Arabe -->
            <select
                v-if="isArabic"
                id="level"
                class="form-control"
                v-model="form.level"
                :disabled="!canEditClass"
                required
            >
              <option value="">-- Choisir un niveau --</option>
              <option
                  v-for="opt in levelOptions"
                  :key="opt"
                  :value="opt"
              >
                {{ opt }}
              </option>
            </select>

            <!-- Si Soutien scolaire : select avec niveaux Support -->
            <select
                v-else-if="isSupport"
                id="level"
                class="form-control"
                v-model="form.level"
                :disabled="!canEditClass"
                required
            >
              <option value="">-- Choisir un niveau --</option>
              <option
                  v-for="opt in levelOptions"
                  :key="opt"
                  :value="opt"
              >
                {{ opt }}
              </option>
            </select>

            <!-- Sinon : input number -->
            <input
                v-else
                type="number"
                id="level"
                class="form-control"
                v-model.number="form.level"
                :disabled="!canEditClass"
                required
                placeholder="Saisir un niveau (numérique)"
            />
          </div>
        </div>

        <div class="row">
          <!-- Spécialité -->
          <div class="col-md-6 mb-3">
            <label for="speciality" class="form-label">Spécialité</label>
            <input
                type="text"
                id="speciality"
                class="form-control"
                v-model="form.speciality"
                required
                :disabled="!canEditClass"
            />
          </div>
          <!-- Type de classe -->
          <div class="col-md-6 mb-3">
            <label for="classType" class="form-label">Type de classe</label>
            <select
                id="classType"
                class="form-control"
                v-model="form.classType"
                required
                :disabled="!canEditClass"
            >
              <option value="">-- Choisir un type --</option>
              <option value="Arabe">Arabe</option>
              <option value="Soutien scolaire">Soutien scolaire</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
        </div>

        <div class="row">
          <!-- Jour -->
          <div class="col-md-6 mb-3">
            <label for="day" class="form-label">Jour</label>
            <select
                id="day"
                class="form-control"
                v-model="form.day"
                required
                :disabled="!canEditClass"
            >
              <option value="">-- Choisir un jour --</option>
              <option v-for="day in days" :key="day" :value="day">
                {{ day }}
              </option>
            </select>
          </div>
          <!-- Professeur principal (si manager) -->
          <div class="col-md-6 mb-3">
            <label for="teacherId" class="form-label">Professeur principal</label>
            <select
                id="teacherId"
                class="form-control"
                v-model="form.principalTeacherId"
                :disabled="!canEditClass"
            >
              <option value="">-- Choisir un professeur --</option>
              <option
                  v-for="teacher in teachers"
                  :key="teacher.id"
                  :value="teacher.id"
              >
                {{ teacher.firstName }} {{ teacher.lastName }}
              </option>
            </select>
          </div>
        </div>

        <div class="row">
          <!-- Heure de Début -->
          <div class="col-md-6 mb-3">
            <label for="startHour" class="form-label">
              Heure de début
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-clock"></i>
              </span>
              <flat-pickr
                  v-if="isFormReady"
                  v-model="form.startHour"
                  :config="timePickerConfig"
                  class="form-control bg-white"
                  id="startHour"
                  required
                  :disabled="!canEditClass"
                  placeholder="Sélectionner l'heure de début"
              />
            </div>
          </div>

          <!-- Heure de Fin -->
          <div class="col-md-6 mb-3">
            <label for="endHour" class="form-label">
              Heure de fin
            </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-clock"></i>
              </span>
              <flat-pickr
                  v-if="isFormReady"
                  v-model="form.endHour"
                  :config="timePickerConfig"
                  class="form-control bg-white"
                  id="endHour"
                  :disabled="!canEditClass"
                  required
                  placeholder="Sélectionner l'heure de fin"
              />
            </div>
          </div>
        </div>

        <!-- Affichage de la durée calculée -->
        <div v-if="calculatedDuration" class="row">
          <div class="col-12 mb-3">
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>&nbsp;&nbsp;
              <strong>Durée du cours :</strong> {{ calculatedDuration }}
            </div>
          </div>
        </div>

        <!-- Boutons -->
        <div class="d-flex justify-content-between mt-4">
          <a
              :href="$routing.generate('app_study_class_index')"
              class="btn btn-outline-secondary"
          >
            Annuler
          </a>
          <button
              v-if="canEditClass"
              type="submit"
              class="btn btn-success"
              :disabled="!isFormValid || isSaving"
          >
            <i v-if="isSaving" class="fas fa-spinner fa-spin" aria-hidden="true"></i>
            <i v-else class="fa fa-save" aria-hidden="true"></i>
            {{ isSaving ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import FlatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { French } from 'flatpickr/dist/l10n/fr.js';
import Alert from "../../ui/Alert.vue";

export default {
  name: 'EditStudyClass',
  components: {Alert, FlatPickr },
  props: {
    studyClass: { type: Object, required: true },
    userCurrent: { type: Object, required: true },
    allTeachers: { type: Array, default: () => [] },
  },
  data() {
    return {
      levelOptionsArabic: [
        'CP','N0','N1_1','N1_2','N2_1','N2_2','N3_1','N3_2',
        'N4_1','N4_2','N5_1','N5_2','N6_1','N6_2','Adult'
      ],
      levelOptionsSupport: [
        'CP','CE1','CE2','CM1','CM2','6ème','5ème','4ème','3ème',
        '2nde','1ère','Terminale'
      ],
      isFormReady: false,
      messageAlert: null,
      messageType: null,
      isSaving: false,
      form: {
        name: '',
        level: '',
        speciality: '',
        classType: '',
        day: '',
        startHour: null,
        endHour: null,
        principalTeacherId: null
      },
      days: [
        'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'
      ],
      teachers: this.allTeachers,
      isManager: false,
      timePickerConfig: {
        enableTime: true,
        noCalendar: true,
        dateFormat: 'H:i',
        time_24hr: true,
        locale: French,
        defaultHour: 12,
        allowInput: true,
        clickOpens: true,
        wrap: false,
        minuteIncrement: 15 // Incrément de 15 minutes
      }
    };
  },
  computed: {
    isArabic() {
      return this.form.classType === 'Arabe';
    },
    isSupport() {
      return this.form.classType === 'Soutien scolaire';
    },
    levelOptions() {
      if (this.isArabic)  return this.levelOptionsArabic;
      if (this.isSupport) return this.levelOptionsSupport;
      return [];
    },
    canEditClass() {
      if (!this.userCurrent || !Array.isArray(this.userCurrent.roles)) {
        return false;
      }
      return this.userCurrent.roles.includes('ROLE_ADMIN') ||
          this.userCurrent.roles.includes('ROLE_MANAGER');
    },
    // Validation du formulaire
    isFormValid() {
      const hasLevel = this.isArabic || this.isSupport
          ? !!this.form.level
          : this.form.level !== '' && this.form.level !== null; // pour le number

      return this.form.name &&
          hasLevel &&
          this.form.speciality &&
          this.form.classType &&
          this.form.day &&
          this.form.startHour &&
          this.form.endHour;
    },

    // Calcul de la durée du cours
    calculatedDuration() {
      const startStr = this.form.startHour; // ex. "15:00"
      const endStr   = this.form.endHour;   // ex. "17:00"

      // Si l'une des deux est manquante, on sort
      if (!startStr || !endStr) {
        return null;
      }

      // Récupère heures et minutes en nombre
      const [startH, startM] = startStr.split(':').map(Number);
      const [endH, endM]     = endStr.split(':').map(Number);

      // Calcule la différence
      let diffH = endH - startH;
      let diffM = endM - startM;

      // Ajuste si les minutes sont négatives
      if (diffM < 0) {
        diffM += 60;
        diffH -= 1;
      }

      // (Optionnel) Gère le passage au jour suivant
      if (diffH < 0) {
        diffH += 24;
      }

      // Pad des minutes sur deux chiffres
      const pad = num => num < 10 ? '0' + num : String(num);

      // Retourne au format français, ex. "2h05"
      return `${diffH}h${pad(diffM)}`;
    }
  },
  watch: {
    studyClass: {
      handler() {
        this.initForm();
      },
      immediate: true,
      deep: true
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.initForm();
      // Délai pour s'assurer que tous les composants sont prêts
      setTimeout(() => {
        this.isFormReady = true;
      }, 100);
    });
    // charger teachers/isManager si nécessaire
  },
  methods: {
    extractTimeFromISOString(isoString) {
      if (!isoString || typeof isoString !== 'string') {
        return '';
      }
      const date = new Date(isoString);
      if (isNaN(date.getTime())) {
        return '';
      }
      // On utilise getUTCHours() pour récupérer l'heure UTC « 15 » dans votre exemple
      const hours   = date.getUTCHours().toString().padStart(2, '0');
      const minutes = date.getUTCMinutes().toString().padStart(2, '0');
      return `${hours}:${minutes}`;
    },
    initForm() {
      if (!this.studyClass) {
        return;
      }

      const s = this.studyClass;

      this.form = {
        name: s.name || '',
        level: s.level || null,
        speciality: s.speciality || '',
        classType: s.classType || '',
        day: s.day || '',
        startHour: this.extractTimeFromISOString(s.startHour),
        endHour: this.extractTimeFromISOString(s.endHour),
        principalTeacherId: s.principalTeacher?.id || null
      };
    },

    save() {
      if (!this.isFormValid) {
        alert('Veuillez remplir tous les champs obligatoires correctement.');
        return;
      }

      this.isSaving = true;

      try {
        // Formatage sécurisé des heures pour le serveur
        const startHourFormatted = this.form.startHour;
        const endHourFormatted = this.form.endHour;

        const payload = {
          name: this.form.name,
          level: (this.isArabic || this.isSupport) ? this.form.level : Number(this.form.level),
          speciality: this.form.speciality,
          classType: this.form.classType,
          day: this.form.day,
          startHour: startHourFormatted,
          endHour: endHourFormatted,
          principalTeacherId: this.form.principalTeacherId
        };


        this.axios
            .post(
                this.$routing.generate('save_data_study_class', { id: this.studyClass.id }),
                payload
            )
            .then(response => {
              this.$emit('updated', response.data);

              this.messageAlert = 'Classe mise à jour avec succès.';
              this.messageType = 'success';
            })
            .catch(err => {

              let errorMessage = 'Erreur lors de la sauvegarde';
              if (err.response && err.response.data && err.response.data.message) {
                errorMessage = err.response.data.message;
              }
              this.messageAlert = errorMessage;
              this.messageType = 'danger';
              alert(`Erreur: ${errorMessage}`);
            })
            .finally(() => {
              this.isSaving = false;
            });
      } catch (error) {
        alert('Erreur lors de la préparation des données');
        this.isSaving = false;
      }
    }
  }
};
</script>

<style scoped>
/* Styles pour améliorer l'affichage de FlatPickr */
.flatpickr-input {
  background-color: white !important;
}

/* Éviter les conflits de styles */
.input-group .form-control {
  z-index: 1;
}

/* Style pour les informations de debug */
.alert pre {
  margin-bottom: 0;
  font-size: 0.8em;
}

/* Style pour les boutons désactivés */
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Animation pour le spinner */
.fa-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>