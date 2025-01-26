<template>
  <div class="container mt-5" lang="fr">
    <!-- Bouton retour -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a :href="$routing.generate('app_session_index')" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
      <h1 class="text-primary mb-0">Créer une Nouvelle Session</h1>
    </div>

    <!-- Filtre de date -->
    <div class="card shadow-sm p-4 mb-4">
      <h5 class="mb-3">Filtrer les statistiques par date</h5>
      <div class="row">
        <div class="col-md-5 mb-3">
          <label for="filterStartDate" class="form-label">Date de début</label>
          <flatpickr v-model="filterStartDate" :config="flatpickrConfig" class="form-control bg-white" />
        </div>
        <div class="col-md-5 mb-3">
          <label for="filterEndDate" class="form-label">Date de fin</label>
          <flatpickr v-model="filterEndDate" :config="flatpickrConfig" class="form-control bg-white" />
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button @click="fetchStatistics" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Filtrer
          </button>
        </div>
      </div>
    </div>

    <!-- Indicateurs de statistiques -->
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="card text-white bg-primary shadow-sm p-3">
          <h5 class="card-title">Total Sessions</h5>
          <p class="card-text display-6">{{ totalSessions }}</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-white bg-success shadow-sm p-3">
          <h5 class="card-title">Total Heures</h5>
          <p class="card-text display-6">{{ totalHours }}</p>
        </div>
      </div>
    </div>

    <!-- Tableau des statistiques des enseignants -->
    <div class="card shadow-sm p-4 mb-4">
      <h5 class="mb-3">Statistiques des Enseignants</h5>
      <table class="table table-bordered">
        <thead>
        <tr>
          <th>Nom</th>
          <th>Sessions</th>
          <th>Heures</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="teacher in teachersStats" :key="teacher.id">
          <td>{{ teacher.fullName }}</td>
          <td>{{ teacher.sessions }}</td>
          <td>{{ teacher.hours }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

export default {
  name: "SessionCreate",
  components: {
    Flatpickr,
  },
  data() {
    return {
      filterStartDate: "",
      filterEndDate: "",
      totalSessions: 0,
      totalHours: 0,
      teachersStats: [],
      flatpickrConfig: {
        enableTime: false,
        dateFormat: "Y-m-d",
      },
    };
  },
  mounted() {
    this.fetchStatistics();
  },
  methods: {
    fetchStatistics() {
      const params = {};
      if (this.filterStartDate) params.startDate = this.filterStartDate;
      if (this.filterEndDate) params.endDate = this.filterEndDate;

      this.axios
          .get(this.$routing.generate("app_dashboard_stats"), { params })
          .then(response => {
            this.totalSessions = response.data.totalSessions;
            this.totalHours = response.data.totalHours;
            this.teachersStats = response.data.teachersStats;
          })
          .catch(error => {
            console.error("Erreur lors de la récupération des statistiques:", error);
          });
    },
  },
};
</script>

<style scoped>
.card {
  border-radius: 10px;
}
</style>
