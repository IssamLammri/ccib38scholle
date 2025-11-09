<template>
  <div class="container-fluid px-4 py-5" lang="fr">
    <!-- En-tête avec design moderne -->
    <div class="header-section mb-5">
      <a
          :href="$routing.generate('app_session_index')"
          class="back-button"
      >
        <i class="fas fa-arrow-left"></i>
        <span>Retour</span>
      </a>
      <h1 class="main-title">
        <i class="fas fa-plus-circle"></i>
        Créer une Nouvelle Session
      </h1>
      <p class="subtitle">Suivez les étapes pour créer votre session en quelques clics</p>
    </div>

    <!-- Indicateur d'étapes moderne -->
    <div class="progress-wrapper mb-5">
      <div class="progress-bar-container">
        <div class="progress-bar-fill" :style="{ width: progressPercentage + '%' }"></div>
      </div>
      <div class="steps-indicator">
        <div
            v-for="(step, index) in stepsDisplayed"
            :key="index"
            class="step-item"
            :class="{
            'active': currentStep === index + 1,
            'completed': currentStep > index + 1
          }"
            @click="goToStep(index + 1)"
        >
          <div class="step-circle">
            <i v-if="currentStep > index + 1" class="fas fa-check"></i>
            <span v-else>{{ index + 1 }}</span>
          </div>
          <div class="step-label">{{ step }}</div>
        </div>
      </div>
    </div>

    <!-- Contenu des étapes -->
    <div class="content-wrapper">

      <!-- Étape 1: Enseignant (si manager) -->
      <transition name="slide-fade" mode="out-in">
        <div v-if="currentStep === 1" key="step1" class="step-content">
          <div class="step-header">
            <div class="step-icon">
              <i class="fas fa-user-tie"></i>
            </div>
            <h2>{{ isManager ? 'Qui va enseigner ?' : 'Vous êtes l\'enseignant' }}</h2>
            <p v-if="isManager">Sélectionnez l'enseignant pour cette session</p>
          </div>

          <!-- Barre de recherche + filtres spécialités -->
          <div v-if="isManager">
            <div class="search-box mb-3">
              <i class="fas fa-search search-icon"></i>
              <input
                  type="text"
                  v-model="teacherSearchQuery"
                  class="search-input"
                  placeholder="Rechercher un enseignant (nom ou spécialité)…"
              >
              <span v-if="teacherSearchQuery" class="clear-search" @click="teacherSearchQuery = ''">
                <i class="fas fa-times"></i>
              </span>
            </div>

            <div class="chips-row mb-4">
              <button
                  v-for="chip in teacherFilterChips"
                  :key="chip.key"
                  type="button"
                  class="chip"
                  :class="{ active: selectedSpecialityKey === chip.key }"
                  @click="selectSpeciality(chip.key)"
              >
                <i class="fas fa-tag"></i>
                <span>{{ chip.label }}</span>
                <span class="chip-count">{{ chip.count }}</span>
              </button>
            </div>

            <div class="selection-grid">
              <div
                  v-for="teacher in filteredTeachers"
                  :key="teacher.id"
                  class="selection-card teacher-card"
                  :class="{ 'selected': teacherId === teacher.id }"
                  @click="selectTeacher(teacher.id)"
              >
                <div class="card-icon">
                  <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="card-content">
                  <h3>{{ teacher.fullName }}</h3>
                  <p class="card-meta">
                    <i class="fas fa-users"></i>
                    {{ getTeacherClassCount(teacher.id) }} classe(s)
                  </p>
                </div>
                <div class="card-check" v-if="teacherId === teacher.id">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>

            <div v-if="filteredTeachers.length === 0" class="empty-state">
              <i class="fas fa-user-slash"></i>
              <h3>Aucun enseignant trouvé</h3>
              <p>Modifiez votre recherche ou votre filtre de spécialité</p>
            </div>
          </div>

          <div v-else class="info-card">
            <div class="info-icon">
              <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
              <h3>Session personnelle</h3>
              <p>Vous créez une session pour vos propres cours</p>
            </div>
            <button type="button" class="btn btn-primary btn-lg" @click="nextStep">
              Continuer <i class="fas fa-arrow-right ms-2"></i>
            </button>
          </div>
        </div>
      </transition>

      <!-- Étape 2: Classe -->
      <transition name="slide-fade" mode="out-in">
        <div v-if="currentStep === 2" key="step2" class="step-content">
          <div class="step-header">
            <div class="step-icon">
              <i class="fas fa-users"></i>
            </div>
            <h2>Quelle classe ?</h2>
            <p>Choisissez la classe pour cette session</p>
          </div>

          <!-- Recherche avec style moderne -->
          <div class="search-box mb-4">
            <i class="fas fa-search search-icon"></i>
            <input
                type="text"
                v-model="classSearchQuery"
                class="search-input"
                placeholder="Rechercher par nom, niveau ou spécialité..."
            >
            <span v-if="classSearchQuery" class="clear-search" @click="classSearchQuery = ''">
              <i class="fas fa-times"></i>
            </span>
          </div>

          <!-- Classes principales -->
          <div v-if="principalClasses.length > 0" class="classes-section mb-5">
            <div class="section-header principal">
              <i class="fas fa-star"></i>
              <h3>Vos Classes Principales</h3>
              <span class="badge">{{ principalClasses.length }}</span>
            </div>
            <div class="selection-grid">
              <div
                  v-for="classe in principalClasses"
                  :key="classe.id"
                  class="selection-card class-card principal-card"
                  :class="{ 'selected': studyClassId === classe.id }"
                  @click="selectClass(classe.id)"
              >
                <div class="card-badge principal-badge">
                  <i class="fas fa-crown"></i> Principale
                </div>
                <div class="card-content">
                  <h3>{{ classe.name }}</h3>
                  <div class="class-info">
                    <span class="info-chip level">
                      <i class="fas fa-layer-group"></i>
                      {{ classe.level }}
                    </span>
                    <span class="info-chip specialty">
                      <i class="fas fa-book"></i>
                      {{ classe.speciality }}
                    </span>
                  </div>
                  <div class="class-year">
                    <i class="fas fa-calendar"></i>
                    {{ classe.school_year }}
                  </div>
                </div>
                <div class="card-check" v-if="studyClassId === classe.id">
                  <i class="fas fa-check-circle"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Autres classes groupées -->
          <div v-if="otherClasses.length > 0" class="classes-section">
            <div class="section-header">
              <i class="fas fa-list"></i>
              <h3>Toutes les Classes</h3>
              <span class="badge">{{ otherClasses.length }}</span>
            </div>

            <div v-for="(classesGroup, level) in groupedOtherClasses" :key="level" class="level-group">
              <div class="level-header" @click="toggleLevel(level)">
                <div class="level-title">
                  <i class="fas fa-graduation-cap"></i>
                  <strong>{{ level }}</strong>
                  <span class="level-count">{{ classesGroup.length }} classe(s)</span>
                </div>
                <i class="fas" :class="expandedLevels.includes(level) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
              </div>

              <transition name="expand">
                <div v-show="expandedLevels.includes(level)" class="level-content">
                  <div class="selection-grid">
                    <div
                        v-for="classe in classesGroup"
                        :key="classe.id"
                        class="selection-card class-card"
                        :class="{ 'selected': studyClassId === classe.id }"
                        @click="selectClass(classe.id)"
                    >
                      <div class="card-content">
                        <h3>{{ classe.name }}</h3>
                        <div class="class-info">
                          <span class="info-chip specialty">
                            <i class="fas fa-book"></i>
                            {{ classe.speciality }}
                          </span>
                        </div>
                      </div>
                      <div class="card-check" v-if="studyClassId === classe.id">
                        <i class="fas fa-check-circle"></i>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
          </div>

          <div v-if="filteredClassesCount === 0" class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Aucune classe trouvée</h3>
            <p>Essayez de modifier votre recherche</p>
          </div>
        </div>
      </transition>

      <!-- Étape 3: Horaires et Salle -->
      <transition name="slide-fade" mode="out-in">
        <div v-if="currentStep === 3" key="step3" class="step-content">
          <div class="step-header">
            <div class="step-icon">
              <i class="fas fa-clock"></i>
            </div>
            <h2>Quand et où ?</h2>
            <p>Définissez les horaires et le lieu de la session</p>
          </div>

          <div class="form-section">
            <div class="row g-4">
              <!-- Heure de Début -->
              <div class="col-lg-6">
                <div class="form-group-modern">
                  <label class="modern-label">
                    <i class="fas fa-play-circle"></i>
                    Heure de Début
                  </label>
                  <div class="input-wrapper">
                    <flatpickr
                        v-model="startTime"
                        :config="flatpickrConfig"
                        class="modern-input"
                        id="startTime"
                        required
                    />
                    <i class="fas fa-calendar-alt input-icon"></i>
                  </div>
                </div>
              </div>

              <!-- Heure de Fin -->
              <div class="col-lg-6">
                <div class="form-group-modern">
                  <label class="modern-label">
                    <i class="fas fa-stop-circle"></i>
                    Heure de Fin
                  </label>
                  <div class="input-wrapper">
                    <flatpickr
                        v-model="endTime"
                        :config="flatpickrConfig"
                        class="modern-input"
                        id="endTime"
                        required
                    />
                    <i class="fas fa-calendar-alt input-icon"></i>
                  </div>
                  <div class="duration-badge" v-if="sessionDuration">
                    <i class="fas fa-hourglass-half"></i>
                    Durée: {{ sessionDuration }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Salle avec cartes -->
            <div class="mt-5">
              <label class="modern-label mb-3">
                <i class="fas fa-door-open"></i>
                Salle
              </label>
              <div class="selection-grid rooms-grid">
                <div
                    v-for="room in rooms"
                    :key="room.id"
                    class="selection-card room-card"
                    :class="{ 'selected': roomId === room.id }"
                    @click="selectRoom(room.id)"
                >
                  <div class="room-icon">
                    <i class="fas fa-door-open"></i>
                  </div>
                  <div class="card-content">
                    <h3>{{ room.name }}</h3>
                  </div>
                  <div class="card-check" v-if="roomId === room.id">
                    <i class="fas fa-check-circle"></i>
                  </div>
                </div>
              </div>
            </div>

            <div class="navigation-buttons">
              <button type="button" class="btn btn-outline-secondary btn-lg" @click="previousStep">
                <i class="fas fa-arrow-left"></i> Précédent
              </button>
              <button
                  type="button"
                  class="btn btn-primary btn-lg"
                  @click="nextStep"
                  :disabled="!canGoToNextStep"
              >
                Voir le récapitulatif <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Étape 4: Récapitulatif -->
      <transition name="slide-fade" mode="out-in">
        <div v-if="currentStep === 4" key="step4" class="step-content">
          <div class="step-header">
            <div class="step-icon success">
              <i class="fas fa-check-circle"></i>
            </div>
            <h2>Récapitulatif</h2>
            <p>Vérifiez les informations avant de créer la session</p>
          </div>

          <div class="summary-grid">
            <div class="summary-card">
              <div class="summary-icon teacher">
                <i class="fas fa-user-tie"></i>
              </div>
              <div class="summary-content">
                <span class="summary-label">Enseignant</span>
                <h3>{{ selectedTeacherName || 'Vous-même' }}</h3>
              </div>
            </div>

            <div class="summary-card">
              <div class="summary-icon class">
                <i class="fas fa-users"></i>
              </div>
              <div class="summary-content">
                <span class="summary-label">Classe</span>
                <h3>{{ selectedClassInfo.name || 'Non sélectionnée' }}</h3>
                <p v-if="selectedClassInfo.level">{{ selectedClassInfo.level }} - {{ selectedClassInfo.speciality }}</p>
              </div>
            </div>

            <div class="summary-card">
              <div class="summary-icon time">
                <i class="fas fa-clock"></i>
              </div>
              <div class="summary-content">
                <span class="summary-label">Horaires</span>
                <h3>{{ formatDateTime(startTime) }}</h3>
                <p>jusqu'à {{ formatTime(endTime) }}</p>
                <span class="duration-chip">
                  <i class="fas fa-hourglass-half"></i>
                  {{ sessionDuration }}
                </span>
              </div>
            </div>

            <div class="summary-card">
              <div class="summary-icon room">
                <i class="fas fa-door-open"></i>
              </div>
              <div class="summary-content">
                <span class="summary-label">Salle</span>
                <h3>{{ selectedRoomName || 'Non sélectionnée' }}</h3>
              </div>
            </div>
          </div>

          <div v-if="!isFormValid" class="alert-card warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
              <strong>Attention !</strong>
              <p>Veuillez remplir tous les champs obligatoires</p>
            </div>
          </div>

          <div class="navigation-buttons">
            <button type="button" class="btn btn-outline-secondary btn-lg" @click="previousStep">
              <i class="fas fa-arrow-left"></i> Modifier
            </button>
            <button
                type="button"
                class="btn btn-success btn-lg"
                @click="submitForm"
                :disabled="!isFormValid || isSubmitting"
            >
              <span v-if="!isSubmitting">
                <i class="fas fa-check"></i> Créer la session
              </span>
              <span v-else>
                <i class="fas fa-spinner fa-spin"></i> Création en cours...
              </span>
            </button>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

// Normalise un libellé (accents, casse, espaces)
const normalize = (s) =>
    (s || "")
        .toString()
        .normalize("NFD")
        .replace(/\p{Diacritic}/gu, "")
        .toLowerCase()
        .trim();

// Canonise les variantes ("mathématique" -> "math", "physique/chimie", etc.)
const canonicalSpeciality = (raw) => {
  const n = normalize(raw)
      .replace(/\s*\/\s*/g, "/")
      .replace(/\s+/g, " ");

  // Mappage de variantes fréquentes -> clé canonique
  if (/(^|[^a-z])math(|ematique|s)?$/.test(n)) return "math";
  if (/physique.?chimie|pysique.?chimie/.test(n)) return "physique/chimie";
  if (/francais|français/.test(n)) return "français";
  if (/anglais/.test(n)) return "anglais";
  if (/arabe|arabe et islamique/.test(n)) return "arabe";
  if (/sociologie/.test(n)) return "sociologie";
  if (/technique/.test(n)) return "technique";
  // default: renvoyer la version sans accents/casse
  return n;
};

// Libellé "joli" à afficher depuis la clé canonique
const displayLabel = (key) => {
  const map = {
    "math": "Math",
    "physique/chimie": "Physique/Chimie",
    "français": "Français",
    "anglais": "Anglais",
    "arabe": "Arabe",
    "sociologie": "Sociologie",
    "technique": "Technique",
    "toutes": "Toutes",
  };
  return map[key] || key.charAt(0).toUpperCase() + key.slice(1);
};

export default {
  name: "NewSession",
  components: {
    Flatpickr,
  },
  data() {
    return {
      currentStep: 1,
      teacherSearchQuery: "",
      selectedSpecialityKey: "toutes",
      steps: ['Enseignant', 'Classe', 'Horaires & Lieu', 'Récapitulatif'],

      startTime: "",
      endTime: "",
      roomId: "",
      studyClassId: "",
      teacherId: "",
      classSearchQuery: "",
      expandedLevels: [],
      isSubmitting: false,

      flatpickrConfig: {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        locale: {
          firstDayOfWeek: 1,
          weekdays: {
            shorthand: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
            longhand: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
          },
          months: {
            shorthand: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            longhand: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
          }
        }
      },

      rooms: [],
      classes: [],
      teachers: [],
      isManager: false,
      currentUserId: null,
    };
  },

  computed: {
    // Index { keyCanonique: { label, count } } + chips ordonnés
    specialityIndex() {
      const index = {};
      for (const t of (this.teachers || [])) {
        const arr = Array.isArray(t.speciality) ? t.speciality : (t.speciality ? [t.speciality] : []);
        for (const raw of arr) {
          const key = canonicalSpeciality(raw);
          if (!key) continue;
          if (!index[key]) index[key] = { label: displayLabel(key), count: 0 };
          index[key].count++;
        }
      }
      return index;
    },

    teacherFilterChips() {
      // "Toutes" en premier, puis A→Z
      const chips = Object.entries(this.specialityIndex)
          .map(([key, v]) => ({ key, label: v.label, count: v.count }))
          .sort((a, b) => a.label.localeCompare(b.label, 'fr'));

      // Compteur "Toutes" = total profs
      const total = (this.teachers || []).length;
      return [{ key: "toutes", label: "Toutes", count: total }, ...chips];
    },

    filteredTeachers() {
      let list = this.teachers || [];

      // Filtre par spécialité (clé canonique), sauf "toutes"
      if (this.selectedSpecialityKey && this.selectedSpecialityKey !== "toutes") {
        const key = this.selectedSpecialityKey;
        list = list.filter(t => {
          const arr = Array.isArray(t.speciality) ? t.speciality : (t.speciality ? [t.speciality] : []);
          return arr.some(raw => canonicalSpeciality(raw) === key);
        });
      }

      // Recherche texte (nom + spécialités concaténées)
      const q = normalize(this.teacherSearchQuery);
      if (q) {
        list = list.filter(t => {
          const inName = normalize(t.fullName).includes(q);
          const specStr = (Array.isArray(t.speciality) ? t.speciality : [t.speciality])
              .filter(Boolean)
              .map(s => normalize(s))
              .join(" ");
          const inSpec = specStr.includes(q);
          return inName || inSpec;
        });
      }

      // Tri : nom ASC
      return list.slice().sort((a, b) => (a.fullName || '').localeCompare(b.fullName || '', 'fr'));
    },

    progressPercentage() {
      return ((this.currentStep - 1) / (this.steps.length - 1)) * 100;
    },

    principalClasses() {
      if (!this.teacherId && this.isManager) return [];
      const targetTeacherId = this.teacherId || this.currentUserId;
      let filtered = this.classes.filter(c => c.principal_teacher === parseInt(targetTeacherId));

      if (this.classSearchQuery.trim()) {
        const query = this.classSearchQuery.toLowerCase();
        filtered = filtered.filter(c =>
            c.name.toLowerCase().includes(query) ||
            c.level.toLowerCase().includes(query) ||
            c.speciality.toLowerCase().includes(query)
        );
      }

      return filtered;
    },

    otherClasses() {
      const principal = this.principalClasses.map(c => c.id);
      let filtered = this.classes.filter(c => !principal.includes(c.id));

      if (this.classSearchQuery.trim()) {
        const query = this.classSearchQuery.toLowerCase();
        filtered = filtered.filter(c =>
            c.name.toLowerCase().includes(query) ||
            c.level.toLowerCase().includes(query) ||
            c.speciality.toLowerCase().includes(query)
        );
      }

      return filtered;
    },

    groupedOtherClasses() {
      const grouped = {};
      this.otherClasses.forEach(classe => {
        if (!grouped[classe.level]) {
          grouped[classe.level] = [];
        }
        grouped[classe.level].push(classe);
      });
      return grouped;
    },

    filteredClassesCount() {
      return this.principalClasses.length + this.otherClasses.length;
    },

    selectedTeacherName() {
      if (!this.teacherId) return '';
      const teacher = this.teachers.find(t => t.id === parseInt(this.teacherId));
      return teacher ? teacher.fullName : '';
    },

    selectedClassInfo() {
      if (!this.studyClassId) return {};
      const classe = this.classes.find(c => c.id === parseInt(this.studyClassId));
      return classe || {};
    },

    selectedRoomName() {
      if (!this.roomId) return '';
      const room = this.rooms.find(r => r.id === parseInt(this.roomId));
      return room ? room.name : '';
    },

    sessionDuration() {
      if (!this.startTime || !this.endTime) return '';
      const start = new Date(this.startTime);
      const end = new Date(this.endTime);
      const diff = Math.abs(end - start);
      const hours = Math.floor(diff / 3600000);
      const minutes = Math.floor((diff % 3600000) / 60000);
      return `${hours}h${minutes > 0 ? minutes.toString().padStart(2, '0') : '00'}`;
    },

    isFormValid() {
      return this.startTime &&
          this.endTime &&
          this.roomId &&
          this.studyClassId &&
          (this.teacherId || !this.isManager); // ✅ OK si non-manager
    },
    canGoToNextStep() {
      switch (this.currentStep) {
        case 1: return !this.isManager || this.teacherId;
        case 2: return this.studyClassId;
        case 3: return this.startTime && this.endTime && this.roomId;
        default: return true;
      }
    },
    stepsDisplayed() {
      return this.isManager ? this.steps : this.steps.slice(1);
    }
  },

  mounted() {
    const now = new Date();
    this.startTime = now;
    this.endTime = new Date(now.getTime() + 90 * 60 * 1000);

    this.axios
        .get(this.$routing.generate("app_session_create_data"))
        .then((response) => {
          this.rooms = response.data.rooms;
          this.classes = response.data.classes;
          this.teachers = response.data.teachers;
          this.isManager = response.data.isManager;
          this.currentUserId = response.data.currentUserId;
          console.log(this.currentUserId)

          // Si pas manager, sélectionner automatiquement l'enseignant courant
          if (!this.isManager) {
            this.teacherId = this.currentUserId;
            this.currentStep = 2;
            //this.steps = ['Classe', 'Horaires & Lieu', 'Récapitulatif'];
          }
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des données:", error);
        });
  },

  watch: {
    startTime(newVal) {
      const newStart = new Date(newVal);
      if (!isNaN(newStart.getTime())) {
        const newEnd = new Date(newStart.getTime() + 90 * 60 * 1000);
        this.endTime = this.formatDateToLocal(newEnd);
      }
    },
  },

  methods: {
    selectSpeciality(key) {
      this.selectedSpecialityKey = key;
    },

    getTeacherClassCount(teacherId) {
      return this.classes.filter(c => c.principal_teacher === teacherId).length;
    },

    selectTeacher(teacherId) {
      this.teacherId = teacherId;
      setTimeout(() => this.nextStep(), 300);
    },

    selectClass(classId) {
      this.studyClassId = classId;
      setTimeout(() => this.nextStep(), 300);
    },

    selectRoom(roomId) {
      this.roomId = roomId;
    },

    toggleLevel(level) {
      const index = this.expandedLevels.indexOf(level);
      if (index > -1) {
        this.expandedLevels.splice(index, 1);
      } else {
        this.expandedLevels.push(level);
      }
    },

    nextStep() {
      if (this.canGoToNextStep && this.currentStep < this.steps.length) {
        this.currentStep++;
        if (this.currentStep === 4) {
          console.log('DEBUG recap:', {
            startTime: this.startTime,
            endTime: this.endTime,
            roomId: this.roomId,
            studyClassId: this.studyClassId,
            teacherId: this.teacherId,
            isManager: this.isManager
          });
        }
      }
    },

    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },

    goToStep(step) {
      if (step < this.currentStep) {
        this.currentStep = step;
      }
    },

    formatDateToLocal(dateObj) {
      const year = dateObj.getFullYear();
      let month = dateObj.getMonth() + 1;
      if (month < 10) month = "0" + month;
      let day = dateObj.getDate();
      if (day < 10) day = "0" + day;
      let hours = dateObj.getHours();
      if (hours < 10) hours = "0" + hours;
      let minutes = dateObj.getMinutes();
      if (minutes < 10) minutes = "0" + minutes;
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    },

    formatDateTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    formatTime(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    submitForm() {
      if (!this.isFormValid || this.isSubmitting) return;

      this.isSubmitting = true;

      const payload = {
        startTime: this.startTime,
        endTime: this.endTime,
        roomId: this.roomId,
        studyClassId: this.studyClassId,
        teacherId: this.teacherId,
      };

      this.axios
          .post(this.$routing.generate("app_session_create_api"), payload)
          .then((response) => {
            if (response.data && response.data.success) {
              const newSessionId = response.data.id;
              window.location.href = this.$routing.generate("app_session_show", {
                id: newSessionId,
              });
            }
          })
          .catch((error) => {
            console.error("Erreur lors de la création de la session:", error);
            alert('Une erreur est survenue lors de la création de la session');
            this.isSubmitting = false;
          });
    },
  },
};
</script>

<style scoped>
/* Reset et base */
* {
  box-sizing: border-box;
}

/* En-tête */
.header-section {
  text-align: center;
  position: relative;
}

.back-button {
  position: absolute;
  left: 0;
  top: 0;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: white;
  border: 2px solid #e5e7eb; /* gray-200 */
  border-radius: 12px;
  color: #374151; /* gray-700 */
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s;
}

.back-button:hover {
  background: #f9fafb; /* gray-50 */
  border-color: #d1d5db; /* gray-300 */
  transform: translateX(-4px);
}

.main-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #111827; /* gray-900 */
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.main-title i {
  color: #6366f1; /* primary */
}

.subtitle {
  font-size: 1.125rem;
  color: #4b5563; /* gray-600 */
  margin: 0;
}

/* Barre de progression */
.progress-wrapper {
  max-width: 900px;
  margin: 0 auto;
}

.progress-bar-container {
  height: 6px;
  background: #e5e7eb; /* gray-200 */
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 32px;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #6366f1, #818cf8); /* primary -> primary-light */
  transition: width 0.5s ease;
  border-radius: 10px;
}

/* Indicateur d'étapes */
.steps-indicator {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.step-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  position: relative;
  cursor: pointer;
  transition: all 0.3s;
}

.step-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: white;
  border: 3px solid #d1d5db; /* gray-300 */
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.25rem;
  color: #4b5563; /* gray-600 */
  margin-bottom: 12px;
  transition: all 0.4s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.step-item.active .step-circle {
  background: #6366f1; /* primary */
  border-color: #6366f1;
  color: white;
  transform: scale(1.1);
  box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.4);
}

.step-item.completed .step-circle {
  background: #10b981; /* success */
  border-color: #10b981;
  color: white;
  transform: scale(1.05);
}

.step-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4b5563; /* gray-600 */
  text-align: center;
  transition: all 0.3s;
}

.step-item.active .step-label {
  color: #6366f1; /* primary */
  font-size: 1rem;
}

.step-item.completed .step-label {
  color: #10b981; /* success */
}

/* Contenu principal */
.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 24px;
  padding: 48px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.step-content {
  min-height: 500px;
}

/* En-tête de chaque étape */
.step-header {
  text-align: center;
  margin-bottom: 48px;
}

.step-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #818cf8, #6366f1); /* primary-light -> primary */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 24px;
  box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
}

.step-icon i {
  font-size: 2rem;
  color: white;
}

.step-icon.success {
  background: linear-gradient(135deg, #34d399, #10b981); /* success */
  box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
}

.step-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #111827; /* gray-900 */
  margin-bottom: 12px;
}

.step-header p {
  font-size: 1.125rem;
  color: #4b5563; /* gray-600 */
  margin: 0;
}

/* Grille de sélection */
.selection-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
  margin-top: 24px;
}

.rooms-grid {
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
}

/* Cartes de sélection */
.selection-card {
  background: white;
  border: 2px solid #e5e7eb; /* gray-200 */
  border-radius: 16px;
  padding: 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.selection-card:hover {
  border-color: #818cf8; /* primary-light */
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -8px rgba(99, 102, 241, 0.3);
}

.selection-card.selected {
  border-color: #6366f1; /* primary */
  background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
  box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.4);
}

.card-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: linear-gradient(135deg, #818cf8, #6366f1); /* primary-light -> primary */
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.card-icon i {
  font-size: 1.75rem;
  color: white;
}

.card-content h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827; /* gray-900 */
  margin-bottom: 8px;
}

.card-meta {
  font-size: 0.875rem;
  color: #4b5563; /* gray-600 */
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-check {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #6366f1; /* primary */
  display: flex;
  align-items: center;
  justify-content: center;
  animation: scaleIn 0.3s ease;
}

.card-check i {
  color: white;
  font-size: 1rem;
}

@keyframes scaleIn {
  from {
    transform: scale(0);
  }
  to {
    transform: scale(1);
  }
}

/* Carte d'information */
.info-card {
  background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
  border: 2px solid #818cf8; /* primary-light */
  border-radius: 16px;
  padding: 48px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
}

.info-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.2);
}

.info-icon i {
  font-size: 2rem;
  color: #6366f1; /* primary */
}

.info-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827; /* gray-900 */
  margin-bottom: 8px;
}

.info-content p {
  font-size: 1rem;
  color: #4b5563; /* gray-600 */
  margin: 0;
}

/* Recherche */
.search-box {
  position: relative;
  max-width: 600px;
  margin: 0 auto;
}

.search-input {
  width: 100%;
  padding: 16px 48px 16px 48px;
  border: 2px solid #e5e7eb; /* gray-200 */
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s;
  background: white;
}

.search-input:focus {
  outline: none;
  border-color: #6366f1; /* primary */
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #4b5563; /* gray-600 */
  font-size: 1.125rem;
}

.clear-search {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #e5e7eb; /* gray-200 */
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s;
}

.clear-search:hover {
  background: #d1d5db; /* gray-300 */
}

/* Sections de classes */
.classes-section {
  margin-bottom: 32px;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 20px;
  background: #f9fafb; /* gray-50 */
  border-radius: 12px;
  margin-bottom: 24px;
}

.section-header.principal {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border: 2px solid #fbbf24;
}

.section-header i {
  font-size: 1.25rem;
  color: #6366f1; /* primary */
}

.section-header.principal i {
  color: #f59e0b;
}

.section-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827; /* gray-900 */
  margin: 0;
  flex: 1;
}

.section-header .badge {
  background: white;
  color: #6366f1; /* primary */
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Cartes de classe */
.class-card {
  position: relative;
}

.principal-card {
  border-color: #fbbf24;
}

.principal-card:hover {
  border-color: #f59e0b;
  box-shadow: 0 12px 24px -8px rgba(245, 158, 11, 0.3);
}

.principal-card.selected {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-color: #f59e0b;
}

.card-badge {
  position: absolute;
  top: -12px;
  right: 16px;
  background: #6366f1; /* primary */
  color: white;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 4px 8px -2px rgba(99, 102, 241, 0.4);
}

.principal-badge {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.class-info {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-top: 12px;
}

.info-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
}

.info-chip.level {
  background: #dbeafe;
  color: #1e40af;
}

.info-chip.specialty {
  background: #f3e8ff;
  color: #6b21a8;
}

.class-year {
  margin-top: 12px;
  font-size: 0.875rem;
  color: #4b5563; /* gray-600 */
  display: flex;
  align-items: center;
  gap: 6px;
}

/* Groupes de niveau */
.level-group {
  margin-bottom: 20px;
  border: 2px solid #e5e7eb; /* gray-200 */
  border-radius: 12px;
  overflow: hidden;
}

.level-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  background: #f9fafb; /* gray-50 */
  cursor: pointer;
  transition: all 0.3s;
}

.level-header:hover {
  background: #f3f4f6; /* gray-100 */
}

.level-title {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.125rem;
}

.level-title i {
  color: #6366f1; /* primary */
  font-size: 1.25rem;
}

.level-count {
  font-size: 0.875rem;
  color: #4b5563; /* gray-600 */
  font-weight: 400;
  margin-left: 8px;
}

.level-content {
  padding: 20px;
  background: white;
}

/* Formulaire moderne */
.form-section {
  max-width: 900px;
  margin: 0 auto;
}

.form-group-modern {
  position: relative;
}

.modern-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1rem;
  font-weight: 600;
  color: #111827; /* gray-900 */
  margin-bottom: 12px;
}

.modern-label i {
  color: #6366f1; /* primary */
}

.input-wrapper {
  position: relative;
}

.modern-input {
  width: 100%;
  padding: 16px 48px 16px 16px;
  border: 2px solid #e5e7eb; /* gray-200 */
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s;
  background: white;
}

.modern-input:focus {
  outline: none;
  border-color: #6366f1; /* primary */
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.input-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #4b5563; /* gray-600 */
  pointer-events: none;
}

.duration-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 12px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  color: #1e40af;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Cartes de salle */
.room-card {
  text-align: center;
  padding: 32px 20px;
}

.room-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  background: linear-gradient(135deg, #818cf8, #6366f1); /* primary-light -> primary */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.room-icon i {
  font-size: 1.75rem;
  color: white;
}

/* Récapitulatif */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.summary-card {
  background: white;
  border: 2px solid #e5e7eb; /* gray-200 */
  border-radius: 16px;
  padding: 24px;
  display: flex;
  gap: 16px;
  transition: all 0.3s;
}

.summary-card:hover {
  border-color: #818cf8; /* primary-light */
  box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.2);
  transform: translateY(-2px);
}

.summary-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.summary-icon.teacher {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
}

.summary-icon.class {
  background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
}

.summary-icon.time {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.summary-icon.room {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
}

.summary-icon i {
  font-size: 1.75rem;
}

.summary-icon.teacher i {
  color: #1e40af;
}

.summary-icon.class i {
  color: #6b21a8;
}

.summary-icon.time i {
  color: #d97706;
}

.summary-icon.room i {
  color: #047857;
}

.summary-content {
  flex: 1;
}

.summary-label {
  display: block;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #4b5563; /* gray-600 */
  font-weight: 600;
  margin-bottom: 6px;
}

.summary-content h3 {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827; /* gray-900 */
  margin: 0 0 4px 0;
}

.summary-content p {
  font-size: 0.875rem;
  color: #4b5563; /* gray-600 */
  margin: 0;
}

.duration-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding: 4px 12px;
  background: #fef3c7;
  color: #d97706;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Alerte */
.alert-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 24px;
}

.alert-card.warning {
  background: #fef3c7;
  border: 2px solid #fbbf24;
}

.alert-card i {
  font-size: 1.75rem;
  color: #f59e0b;
  flex-shrink: 0;
}

.alert-card strong {
  display: block;
  font-size: 1rem;
  color: #92400e;
  margin-bottom: 4px;
}

.alert-card p {
  font-size: 0.875rem;
  color: #92400e;
  margin: 0;
}

/* État vide */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #4b5563; /* gray-600 */
}

.empty-state i {
  font-size: 4rem;
  color: #d1d5db; /* gray-300 */
  margin-bottom: 16px;
}

.empty-state h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #374151; /* gray-700 */
  margin-bottom: 8px;
}

.empty-state p {
  font-size: 1rem;
  margin: 0;
}

/* Boutons de navigation */
.navigation-buttons {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-top: 40px;
  padding-top: 32px;
  border-top: 2px solid #e5e7eb; /* gray-200 */
}

.btn {
  padding: 14px 32px;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-primary {
  background: linear-gradient(135deg, #6366f1, #4f46e5); /* primary -> primary-dark */
  color: white;
  box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.4);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.5);
}

.btn-success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.4);
}

.btn-success:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px -4px rgba(16, 185, 129, 0.5);
}

.btn-outline-secondary {
  background: white;
  border: 2px solid #d1d5db; /* gray-300 */
  color: #374151; /* gray-700 */
}

.btn-outline-secondary:hover {
  background: #f9fafb; /* gray-50 */
  border-color: #9ca3af; /* gray-400 approximatif */
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-lg {
  padding: 16px 40px;
  font-size: 1.125rem;
}

/* Animations */
.slide-fade-enter-active {
  transition: all 0.4s ease;
}

.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from {
  transform: translateX(30px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(-30px);
  opacity: 0;
}

.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}

.expand-enter-to,
.expand-leave-from {
  max-height: 2000px;
  opacity: 1;
}

/* Responsive */
@media (max-width: 768px) {
  .content-wrapper {
    padding: 24px;
  }

  .main-title {
    font-size: 1.75rem;
  }

  .back-button {
    position: static;
    margin-bottom: 20px;
  }

  .steps-indicator {
    flex-wrap: wrap;
    gap: 16px;
  }

  .step-circle {
    width: 50px;
    height: 50px;
    font-size: 1rem;
  }

  .step-label {
    font-size: 0.75rem;
  }

  .selection-grid {
    grid-template-columns: 1fr;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .navigation-buttons {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 576px) {
  .step-item {
    flex-direction: row;
    justify-content: flex-start;
  }

  .step-circle {
    margin-bottom: 0;
    margin-right: 12px;
  }

  .step-label {
    text-align: left;
  }
}

.chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 999px;
  border: 2px solid #e5e7eb; /* gray-200 */
  background: white;
  color: #374151; /* gray-700 */
  font-weight: 600;
  cursor: pointer;
  transition: all .2s ease;
}

.chip i {
  color: #4b5563; /* gray-600 */
}

.chip:hover {
  border-color: #818cf8; /* primary-light */
  box-shadow: 0 4px 10px -4px rgba(99, 102, 241, .25);
  transform: translateY(-1px);
}

.chip.active {
  background: linear-gradient(135deg, #818cf8, #6366f1); /* primary-light -> primary */
  color: white;
  border-color: #6366f1; /* primary */
}

.chip.active i {
  color: white;
}

.chip-count {
  background: rgba(255, 255, 255, .25);
  border: 1px solid rgba(255, 255, 255, .35);
  padding: 2px 8px;
  border-radius: 999px;
  font-size: .75rem;
}
</style>
