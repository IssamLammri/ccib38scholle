<template>
  <div class="container mt-5" lang="fr">
    <!-- Bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a
          :href="$routing.generate('app_session_index')"
          class="btn btn-outline-secondary"
      >
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
      <h1 class="text-primary mb-0">Créer une Nouvelle Session</h1>
    </div>

    <!-- Formulaire -->
    <div class="card shadow-sm p-4">
      <form @submit.prevent="submitForm">
        <div class="row">
          <!-- Heure de Début -->
          <div class="col-md-6 mb-3">
            <label for="startTime" class="form-label">Heure de Début</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </span>
              <flatpickr
                  v-model="startTime"
                  :config="flatpickrConfig"
                  class="form-control bg-white"
              :id="'startTime'"
              required
              />
            </div>
          </div>

          <!-- Heure de Fin -->
          <div class="col-md-6 mb-3">
            <label for="endTime" class="form-label">Heure de Fin</label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </span>
              <flatpickr
                  v-model="endTime"
                  :config="flatpickrConfig"
                  class="form-control bg-white"
                  :id="'endTime'"
                  required
              />
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Salle -->
          <div class="col-md-4 mb-3">
            <label for="roomId" class="form-label">Salle</label>
            <select
                id="roomId"
                v-model="roomId"
                class="form-control"
                required
            >
              <option value="">-- Choisir une salle --</option>
              <option
                  v-for="room in rooms"
                  :value="room.id"
                  :key="room.id"
              >
                {{ room.name }}
              </option>
            </select>
          </div>

          <!-- Classe (visible si pas en 'edit') -->
          <div class="col-md-4 mb-3">
            <label for="studyClassId" class="form-label">Classe</label>
            <select
                id="studyClassId"
                v-model="studyClassId"
                class="form-control"
            >
              <option value="">-- Choisir une classe --</option>
              <option
                  v-for="classe in classes"
                  :value="classe.id"
                  :key="classe.id"
              >
                {{ classe.name }} ({{ classe.speciality }})
              </option>
            </select>
          </div>

          <!-- Enseignant (si manager) -->
          <div class="col-md-4 mb-3" v-if="isManager">
            <label for="teacherId" class="form-label">Enseignant</label>
            <select
                id="teacherId"
                v-model="teacherId"
                class="form-control"
            >
              <option value="">-- Choisir un enseignant --</option>
              <option
                  v-for="teacher in teachers"
                  :value="teacher.id"
                  :key="teacher.id"
              >
                {{ teacher.fullName }}
              </option>
            </select>
          </div>
        </div>

        <!-- Boutons -->
        <div class="d-flex justify-content-between mt-4">
          <a
              :href="$routing.generate('app_session_index')"
              class="btn btn-outline-secondary"
          >
            Annuler
          </a>
          <button type="submit" class="btn btn-success">
            <i class="fa fa-plus" aria-hidden="true"></i> Créer
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>

import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

export default {
  name: "NewSession",
  components: {
    Flatpickr,
  },
  data() {
    return {
      // Les champs du formulaire
      startTime: "",
      endTime: "",
      roomId: "",
      studyClassId: "",
      teacherId: "",
      flatpickrConfig: {
        enableTime: true,
        dateFormat: "Y-m-d H:i", // Format d'affichage
        time_24hr: true,         // Force l'affichage en 24h
      },

      // Données chargées depuis l'API
      rooms: [],
      classes: [],
      teachers: [],
      isManager: false,
    };
  },
  mounted() {
    // Initialiser startTime & endTime
    const now = new Date();
    this.startTime = now; // Flatpickr accepte un objet Date
    this.endTime = new Date(now.getTime() + 90 * 60 * 1000); // +1h30


    // 2. Récupérer les données nécessaires : salles, classes, teachers...
    this.axios
        .get(this.$routing.generate("app_session_create_data"))
        .then((response) => {
          this.rooms = response.data.rooms;
          this.classes = response.data.classes;
          this.teachers = response.data.teachers;
          this.isManager = response.data.isManager;
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des données:", error);
        });
  },
  watch: {
    // Dès que startTime est modifié, si l'utilisateur n'a pas changé manuellement endTime,
    // on recalcule endTime = startTime + 1h30.
    startTime(newVal, oldVal) {
      // Si l'utilisateur n'a pas modifié manuellement endTime ou si on veut toujours forcer
      // on peut recalculer la date de fin.
      const newStart = new Date(newVal); // parse la string en date
      if (!isNaN(newStart.getTime())) {
        // Ajoute 90 minutes
        const newEnd = new Date(newStart.getTime() + 90 * 60 * 1000);
        this.endTime = this.formatDateToLocal(newEnd);
      }
    },
  },
  methods: {
    formatDateISO(dateObj) {
      if (!(dateObj instanceof Date) || isNaN(dateObj)) {
        return null;
      }
      return dateObj.toISOString().slice(0, 16);
      // .slice(0,16) => '2025-01-22T14:30'
      // Ajustez selon vos besoins : .toISOString() donne l'heure UTC
      // vous pouvez gérer le fuseau si nécessaire.
    },
    /**
     * Formate un objet Date en chaîne "YYYY-MM-DDTHH:mm" pour l'input type="datetime-local"
     */
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

      // Format standard pour datetime-local => YYYY-MM-DDTHH:mm
      return `${year}-${month}-${day}T${hours}:${minutes}`;
    },

    submitForm() {
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
              // Redirection vers la page "show"
              window.location.href = this.$routing.generate("app_session_show", {
                id: newSessionId,
              });
            }
          })
          .catch((error) => {
            console.error("Erreur lors de la création de la session:", error);
          });
    },
  },
};
</script>

<style scoped>
/* Styles personnalisés si besoin. */
/* Tout est déjà en français via les labels et placeholders. */
</style>
