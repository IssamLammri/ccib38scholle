<template>
  <div class="container mt-5" lang="fr">
    <!-- Bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a :href="$routing.generate('app_study_class_index')" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
      <h1 class="text-primary mb-0">Créer une Classe</h1>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="messageType" />

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
          <div class="col-md-6 mb-3">
            <label for="level" class="form-label">Niveau</label>

            <!-- Si Arabe : select -->
            <select
                v-if="isArabic"
                id="level"
                class="form-control"
                v-model="form.level"
                :disabled="!canEditClass"
                required
            >
              <option value="">-- Choisir un niveau --</option>
              <option v-for="opt in levelOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>

            <!-- Si Soutien scolaire : select -->
            <select
                v-else-if="isSupport"
                id="level"
                class="form-control"
                v-model="form.level"
                :disabled="!canEditClass"
                required
            >
              <option value="">-- Choisir un niveau --</option>
              <option v-for="opt in levelOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>

            <!-- Sinon : input number -->
            <input
                v-else
                type="text"
                id="level"
                class="form-control"
                v-model.number="form.level"
                :disabled="!canEditClass"
                required
                placeholder="Saisir un niveau"
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
              <option value="Atelier">Atelier</option>
              <option value="Competition">Compétition</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
        </div>

        <!-- Année scolaire + Salle principale -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="schoolYear" class="form-label">Année scolaire</label>
            <select
                id="schoolYear"
                class="form-control"
                v-model="form.schoolYear"
                required
                :disabled="!canEditClass"
            >
              <option value="">-- Choisir une année --</option>
              <option v-for="y in SCHOOL_YEARS" :key="y" :value="y">{{ y }}</option>
            </select>
            <small class="text-muted">Par défaut : 2025/2026</small>
          </div>

          <div class="col-md-6 mb-3">
            <label for="principalRoomId" class="form-label">Salle principale</label>
            <select
                id="principalRoomId"
                class="form-control"
                v-model="form.principalRoomId"
                :disabled="!canEditClass || rooms.length === 0"
            >
            <option :value="null">-- Aucune salle --</option>
              <option v-for="r in rooms" :key="r.id" :value="r.id">
                {{ r.name }}
              </option>
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
              <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
            </select>
          </div>

          <!-- Professeur principal -->
          <div class="col-md-6 mb-3">
            <label for="teacherId" class="form-label">Professeur principal</label>
            <select
                id="teacherId"
                class="form-control"
                v-model="form.principalTeacherId"
                :disabled="!canEditClass"
            >
              <option :value="null">-- Choisir un professeur --</option>
              <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                {{ teacher.firstName }} {{ teacher.lastName }}
              </option>
            </select>
          </div>
        </div>

        <!-- >>> NOUVEAU : URL du groupe WhatsApp -->
        <div class="row">
          <div class="col-md-12 mb-3">
            <label for="whatsappUrl" class="form-label">
              Lien du groupe WhatsApp (optionnel)
            </label>
            <div class="input-group">
      <span class="input-group-text">
        <i class="fab fa-whatsapp"></i>
      </span>
              <input
                  type="url"
                  id="whatsappUrl"
                  class="form-control"
                  v-model="form.whatsappUrl"
                  :disabled="!canEditClass"
                  placeholder="https://chat.whatsapp.com/… ou https://wa.me/…"
              />
            </div>
            <small class="text-muted">
              Collez ici le lien d’invitation vers le groupe WhatsApp de cette classe.
            </small>
          </div>
        </div>

        <div class="row">
          <!-- Heure de Début -->
          <div class="col-md-6 mb-3">
            <label for="startHour" class="form-label">Heure de début</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-clock"></i></span>
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
            <label for="endHour" class="form-label">Heure de fin</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-clock"></i></span>
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

        <!-- Durée calculée -->
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
          <a :href="$routing.generate('app_study_class_index')" class="btn btn-outline-secondary">
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
            {{ isSaving ? 'Création...' : 'Créer' }}
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

/**
 * Convertit "HH:mm" -> ISO time "1970-01-01THH:mm:00.000Z"
 * (souvent bien accepté par un champ Doctrine time avec normalizer)
 */
function toIsoTime(hhmm) {
  if (!hhmm || typeof hhmm !== 'string' || !/^\d{2}:\d{2}$/.test(hhmm)) return null;
  const [h, m] = hhmm.split(':').map(Number);
  const d = new Date(Date.UTC(1970, 0, 1, h, m, 0));
  return d.toISOString(); // "1970-01-01T14:30:00.000Z"
}

/**
 * Extrait le meilleur message d’erreur possible depuis une réponse backend.
 */
function extractBackendMessage(err, fallback = 'Erreur lors de la création') {
  const data = err?.response?.data;

  // API Platform / RFC7807
  if (typeof data?.['hydra:description'] === 'string' && data['hydra:description'].trim()) return data['hydra:description'];
  if (typeof data?.detail === 'string' && data.detail.trim()) return data.detail;
  if (typeof data?.message === 'string' && data.message.trim()) return data.message;
  if (typeof data?.title === 'string' && data.title.trim()) return data.title;

  // Violations (API Platform)
  if (Array.isArray(data?.violations) && data.violations.length) {
    const first = data.violations[0];
    if (first?.message) return `${first.propertyPath ?? 'champ'}: ${first.message}`;
  }

  // errors[]
  if (Array.isArray(data?.errors) && data.errors.length) {
    const first = data.errors[0];
    if (typeof first === 'string') return first;
    if (first?.message) return first.message;
  }

  // texte brut ?
  if (typeof data === 'string' && data.trim()) return data;

  return fallback;
}

export default {
  name: 'CreateStudyClass',
  components: { Alert, FlatPickr },
  props: {
    userCurrent: { type: Object, required: true },
    allTeachers: { type: Array, default: () => [] },
    // rooms = [{id, name}]
    rooms: { type: Array, default: () => [] },
    createRouteName: { type: String, default: 'create_study_class' }
  },
  data() {
    return {
      SCHOOL_YEARS: ['2024/2025', '2025/2026'],

      levelOptionsArabic: [
        'CP','N0','N1_1','N1_2','N2_1','N2_2','N3_1','N3_2',
        'N4_1','N4_2','N5_1','N5_2','N6_1','N6_2','Adolescent','Adult'
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
        level: '',                 // si Arabe/Soutien → string (liste); sinon → number
        speciality: '',
        classType: '',
        day: '',
        startHour: null,           // "HH:mm"
        endHour: null,             // "HH:mm"
        principalTeacherId: null,  // facultatif
        schoolYear: '2025/2026',   // défaut requis
        principalRoomId: null,     // facultatif
        whatsappUrl: '',
      },

      days: ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'],
      teachers: this.allTeachers,
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
        minuteIncrement: 15,
      }
    };
  },
  computed: {
    isArabic() { return this.form.classType === 'Arabe'; },
    isSupport() { return this.form.classType === 'Soutien scolaire'; },
    levelOptions() {
      if (this.isArabic)  return this.levelOptionsArabic;
      if (this.isSupport) return this.levelOptionsSupport;
      return [];
    },
    canEditClass() {
      if (!this.userCurrent || !Array.isArray(this.userCurrent.roles)) return false;
      return this.userCurrent.roles.includes('ROLE_ADMIN') ||
          this.userCurrent.roles.includes('ROLE_MANAGER');
    },
    isFormValid() {
      const hasLevel = this.isArabic || this.isSupport
          ? !!this.form.level
          : this.form.level !== '' && this.form.level !== null;

      return this.form.name &&
          hasLevel &&
          this.form.speciality &&
          this.form.classType &&
          this.form.day &&
          this.form.schoolYear &&
          this.form.startHour &&
          this.form.endHour;
    },
    calculatedDuration() {
      const startStr = this.form.startHour;
      const endStr   = this.form.endHour;
      if (!startStr || !endStr) return null;

      const [startH, startM] = startStr.split(':').map(Number);
      const [endH, endM]     = endStr.split(':').map(Number);

      let diffH = endH - startH;
      let diffM = endM - startM;
      if (diffM < 0) { diffM += 60; diffH -= 1; }
      if (diffH < 0) { diffH += 24; }

      const pad = n => n < 10 ? '0' + n : String(n);
      return `${diffH}h${pad(diffM)}`;
    }
  },
  mounted() {
    // prêt pour afficher Flatpickr
    setTimeout(() => { this.isFormReady = true; }, 50);
  },
  methods: {
    async save() {
      if (!this.isFormValid) {
        alert('Veuillez remplir tous les champs obligatoires correctement.');
        return;
      }
      this.isSaving = true;

      try {
        // Casts propres (évite NaN avec <select>)
        const principalRoomId = (this.form.principalRoomId == null || this.form.principalRoomId === '')
            ? null
            : Number(this.form.principalRoomId);

        const principalTeacherId = (this.form.principalTeacherId == null || this.form.principalTeacherId === '')
            ? null
            : Number(this.form.principalTeacherId);

        // Envoi des heures en ISO (choix "Option 2")
        const payload = {
          name: this.form.name,
          level: (this.isArabic || this.isSupport) ? this.form.level : (this.form.level),
          speciality: this.form.speciality,
          classType: this.form.classType,
          day: this.form.day,
          startHour: toIsoTime(this.form.startHour),
          endHour:   toIsoTime(this.form.endHour),
          principalTeacherId,
          schoolYear: this.form.schoolYear,
          principalRoomId,
          whatsappUrl: this.form.whatsappUrl?.trim() || null,
        };

        const url = this.$routing.generate(this.createRouteName);
        const res = await this.$axios.post(url, payload, { headers: { Accept: 'application/json' } });

        this.messageAlert = 'Classe créée avec succès.';
        this.messageType = 'success';

        // Redirection si l'API renvoie l'id
        const createdId = res?.data?.id;
        console.log('Created StudyClass ID:', createdId);
        if (createdId) {
          window.location.href = this.$routing.generate('app_study_class_show', { id: createdId });
        } else {
          this.resetForm();
        }
      } catch (err) {
        console.error('Create error payload:', err?.response?.data ?? err);
        const errorMessage = extractBackendMessage(err, 'Erreur lors de la création');
        this.messageAlert = errorMessage;
        this.messageType = 'danger';
        alert(`Erreur: ${errorMessage}`);
      } finally {
        this.isSaving = false;
      }
    },

    resetForm() {
      this.form = {
        name: '',
        level: '',
        speciality: '',
        classType: '',
        day: '',
        startHour: null,
        endHour: null,
        principalTeacherId: null,
        schoolYear: '2025/2026',
        principalRoomId: null,
        whatsappUrl: '',
      };
    }
  }
};
</script>


<style scoped>
.flatpickr-input { background-color: white !important; }
.input-group .form-control { z-index: 1; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.fa-spin { animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
