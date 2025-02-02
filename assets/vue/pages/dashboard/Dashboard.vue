<template>
  <div class="container mt-5">
    <h1 class="text-primary mb-4">Tableau de bord</h1>
    <!-- Filtre par date -->
    <div class="card shadow-sm p-4 mb-4">
      <h5 class="mb-3">Filtrer les statistiques par date</h5>
      <div class="row">
        <div class="col-md-5 mb-3">
          <label class="form-label">Date de début</label>
          <flatpickr v-model="filterStartDate" :config="flatpickrConfig" class="form-control bg-white" />
        </div>
        <div class="col-md-5 mb-3">
          <label class="form-label">Date de fin</label>
          <flatpickr v-model="filterEndDate" :config="flatpickrConfig" class="form-control bg-white" />
        </div>
        <div class="col-md-2 d-flex align-items-end" style="margin-bottom: 15px">
          <button @click="fetchStatistics" class="btn btn-primary w-100">
            <i class="fas fa-search"></i> Filtrer
          </button>
        </div>
      </div>
    </div>

    <!-- Indicateurs chiffrés -->
    <div class="row mb-4 text-center">
      <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm p-3">
          <h5 class="card-title">Total Sessions</h5>
          <p class="display-4">{{ totalSessions }}</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm p-3">
          <h5 class="card-title">Total Heures</h5>
          <p class="display-4">{{ totalHours }}</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-danger shadow-sm p-3">
          <h5 class="card-title">Montant Payé</h5>
          <p class="display-4">{{ totalPaymentAmount }} €</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-warning shadow-sm p-3">
          <h5 class="card-title">Nombre de Factures</h5>
          <p class="display-4">{{ totalInvoices }}</p>
        </div>
      </div>
    </div>

    <!-- Bouton pour afficher la liste des profs -->
    <div class="text-center mb-4">
      <button @click="showTeacherStats = !showTeacherStats" class="btn btn-outline-primary">
        Voir la liste des professeurs
      </button>
    </div>

    <!-- Liste des enseignants avec leurs heures -->
    <div v-if="showTeacherStats" class="card shadow-sm p-4 mb-4">
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

    <!-- Graphiques d'évolution -->
    <div class="row">
      <div class="col-md-6">
        <canvas ref="sessionsChart"></canvas>
      </div>
      <div class="col-md-6">
        <canvas ref="paymentsChart"></canvas>
      </div>
    </div>
    <div class="container mt-4">
      <!-- Filtrer les impayés par Mois et Année -->
      <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3">📌 Filtrer les impayés</h5>
        <div class="row">
          <!-- Sélection du Mois -->
          <div class="col-md-5 mb-3">
            <label class="form-label">Mois</label>
            <select v-model="selectedMonth" class="form-control">
              <option value="all">Tous les Mois</option>
              <option v-for="(month, index) in months" :key="index" :value="month.value">
                {{ month.label }}
              </option>
            </select>
          </div>

          <!-- Sélection de l'Année -->
          <div class="col-md-5 mb-3">
            <label class="form-label">Année</label>
            <select v-model="selectedYear" class="form-control">
              <option value="all">Toutes les Années</option>
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>

          <!-- Bouton de Filtrage -->
          <div class="col-md-2 d-flex align-items-end">
            <button @click="fetchStatistics" class="btn btn-primary w-100">
              <i class="fas fa-filter"></i> Filtrer
            </button>
          </div>
        </div>
      </div>

      <!-- Liste des Impayés -->
      <div v-if="groupedUnpaidParents.length > 0" class="card shadow-sm p-4">
        <h5 class="mb-3 text-danger">⚠️ Liste des Impayés</h5>
        <div v-for="(parent, index) in groupedUnpaidParents" :key="index" class="mb-3 p-3 shadow-sm border rounded bg-light">
          <h6 class="text-primary"><i class="fas fa-user"></i> {{ parent.parentName }}</h6>
          <ul class="list-group">
            <li v-for="(student, sIndex) in parent.students" :key="sIndex" class="list-group-item">
              <strong>{{ student.studentName }}</strong>
              <br />
              <small class="text-muted">{{ student.studyClasses.join(', ') }}</small>
            </li>
          </ul>
        </div>
      </div>

      <!-- Message si aucun impayé -->
      <div v-else class="alert alert-success mt-4">
        🎉 Aucune impayé trouvé !
      </div>
    </div>

  </div>
</template>

<script>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
  components: { Flatpickr },
  data() {
    return {
      filterStartDate: "",
      filterEndDate: "",
      totalSessions: 0,
      totalHours: 0,
      totalInvoices: 0,
      allData: null,
      chartSessions: null,  // Référence au graphique des sessions
      chartPayments: null,  // Référence au graphique des paiements
      totalPaymentAmount: 0,
      unpaidParents: [],
      teachersStats: [],
      showTeacherStats: false,
      flatpickrConfig: { enableTime: false, dateFormat: "Y-m-d" },
      selectedMonth: 'all', // Mois sélectionné
      selectedYear: 'all',  // Année sélectionnée
      months: [
        { label: "Janvier", value: "Janvier" },
        { label: "Février", value: "Février" },
        { label: "Mars", value: "Mars" },
        { label: "Avril", value: "Avril" },
        { label: "Mai", value: "Mai" },
        { label: "Juin", value: "Juin" },
        { label: "Juillet", value: "Juillet" },
        { label: "Août", value: "Août" },
        { label: "Septembre", value: "Septembre" },
        { label: "Octobre", value: "Octobre" },
        { label: "Novembre", value: "Novembre" },
        { label: "Décembre", value: "Décembre" }
      ],
      availableYears: [
        2024, 2025
      ], // Liste dynamique des années disponibles
    };
  },
  computed: {
    groupedUnpaidParents() {
      const grouped = {};

      this.unpaidParents.forEach(entry => {
        if (!grouped[entry.ParentName]) {
          grouped[entry.ParentName] = { parentName: entry.ParentName, students: {} };
        }

        if (!grouped[entry.ParentName].students[entry.studentId]) {
          grouped[entry.ParentName].students[entry.studentId] = {
            studentName: entry.studentName,
            studyClasses: []
          };
        }

        grouped[entry.ParentName].students[entry.studentId].studyClasses.push(entry.studyClassName);
      });

      return Object.values(grouped).map(parent => ({
        parentName: parent.parentName,
        students: Object.values(parent.students)
      }));
    }
  },
  mounted() {
    this.fetchStatistics();
  },
  methods: {
    fetchStatistics() {
      const params = {};
      if (this.filterStartDate) params.startDate = this.filterStartDate;
      if (this.filterEndDate) params.endDate = this.filterEndDate;
      if (this.selectedYear) params.selectedYear = this.selectedYear;
      if (this.selectedMonth) params.selectedMonth = this.selectedMonth;


      this.axios.get(this.$routing.generate("app_dashboard_stats"), { params })
          .then(response => {
            this.totalSessions = Math.round(response.data.totalSessions);
            this.totalHours = Math.round(response.data.totalHours);
            this.totalInvoices = response.data.totalInvoices;
            this.totalPaymentAmount = Math.round(response.data.totalPaymentAmount);
            this.teachersStats = response.data.teachersStats;
            this.allData = response.data.allData;
            this.unpaidParents = response.data.unpaidParents;

            this.renderCharts(response.data);
          })
          .catch(error => console.error("Erreur de récupération des stats:", error));
    },
    renderCharts() {
      if (!this.allData) return;

      const invoices = JSON.parse(this.allData.invoices);
      const payments = JSON.parse(this.allData.payments);

      // ✅ Maintenant triés dans le bon ordre chronologique
      const invoiceData = this.aggregateByMonth(invoices, 'invoiceDate', 'totalAmount');
      const paymentData = this.aggregateByMonth(payments, 'paymentDate', 'amountPaid');

      if (this.chartSessions) this.chartSessions.destroy();
      if (this.chartPayments) this.chartPayments.destroy();

      // 📊 Graphique des factures
      const ctx1 = this.$refs.sessionsChart.getContext('2d');
      this.chartSessions = new Chart(ctx1, {
        type: 'line',
        data: {
          labels: Object.keys(invoiceData),  // 🔄 ✅ Maintenant trié correctement
          datasets: [
            {
              label: 'Montant Facturé (€)',
              data: Object.values(invoiceData),
              borderColor: 'red',
              fill: false
            }
          ]
        },
        options: { responsive: true }
      });

      // 📊 Graphique des paiements
      const ctx2 = this.$refs.paymentsChart.getContext('2d');
      this.chartPayments = new Chart(ctx2, {
        type: 'line',
        data: {
          labels: Object.keys(paymentData),  // 🔄 ✅ Maintenant trié correctement
          datasets: [
            {
              label: 'Montant Payé (€)',
              data: Object.values(paymentData),
              borderColor: 'purple',
              fill: false
            }
          ]
        },
        options: { responsive: true }
      });
    },

    /**
     * 🏷️ Fonction utilitaire pour regrouper les données par mois
     * @param {Array} data - Tableau des objets (factures ou paiements)
     * @param {String} dateKey - Clé contenant la date
     * @param {String} amountKey - Clé contenant le montant
     * @returns {Object} - Objet avec mois comme clé et montant total comme valeur
     */
    aggregateByMonth(data, dateKey, amountKey) {
      const result = {};

      data.forEach(item => {
        const date = new Date(item[dateKey]);
        const monthYear = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}`; // Format YYYY-MM
        result[monthYear] = (result[monthYear] || 0) + parseFloat(item[amountKey]);
      });

      // ✅ Trie les clés dans l'ordre chronologique
      const sortedKeys = Object.keys(result).sort((a, b) => new Date(a) - new Date(b));

      // ✅ Retourne un objet trié avec les données dans le bon ordre
      const sortedResult = {};
      sortedKeys.forEach(key => {
        sortedResult[key] = result[key];
      });

      return sortedResult;
    }
  }
};
</script>

<style scoped>
.card {
  border-radius: 10px;
}
</style>
