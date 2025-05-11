<!-- src/components/RegistrationList.vue -->
<template>
  <div class="my-5" lang="fr">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-primary">
        <i class="fa-solid fa-user-graduate me-2"></i> Liste des inscrits au cours d’arabe 2025 / 2026
      </h1>
    </div>

    <!-- Barre de recherche et filtres -->
    <div class="card mb-4 shadow-sm p-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Rechercher par élève ou parent :</label>
          <input
              type="text"
              v-model="searchInput"
              @input="applyFilter"
              class="form-control"
              placeholder="Prénom, nom élève ou nom parent"
          />
        </div>
        <div class="col-md-3">
          <label class="form-label">Filtrer par niveau :</label>
          <select
              v-model="levelFilter"
              @change="applyFilter"
              class="form-select"
          >
            <option value="">Tous</option>
            <option value="—">Sans niveau</option>
            <option
                v-for="lvl in levelsList"
                :key="lvl"
                :value="lvl"
            >
              {{ lvl }}
            </option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Filtrer par statut :</label>
          <select
              v-model="statusFilter"
              @change="applyFilter"
              class="form-select"
          >
            <option value="">Tous</option>
            <option
                v-for="st in statusesList"
                :key="st"
                :value="st"
            >
              {{ st }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4 text-center">
      <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Total affiché</h5>
            <p class="display-4 mb-0">{{ filteredRegistrations.length }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">En attente de paiement</h5>
            <p class="display-4 mb-0">{{ countToPay }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Liste d’attente</h5>
            <p class="display-4 mb-0">{{ countWaiting }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tableau des inscriptions -->
    <div v-if="filteredRegistrations.length" class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Prénom</th>
              <th>Nom</th>
              <th>Date de naissance</th>
              <th>Niveau</th>
              <th>Parent</th>
              <th>Email</th>
              <th>Status</th>
              <th>Voir la demande</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="reg in filteredRegistrations" :key="reg.id">
              <td>{{ reg.id }}</td>
              <td>{{ reg.childFirstName }}</td>
              <td>{{ reg.childLastName }}</td>
              <td>{{ formatDate(reg.childDob) }}</td>
              <td>{{ reg.previousLevel || '—' }}</td>
              <td>{{ reg.fatherLastName }} {{ reg.fatherFirstName }}</td>
              <td>{{ reg.contactEmail }}</td>
              <td>
                  <span
                      :class="[
                      'badge',
                      reg.wasEnrolled2024 === 'oui'
                        ? 'bg-info text-white'
                        : 'bg-success text-white'
                    ]"
                  >
                    {{ statusRegister(reg) }}
                  </span>
              </td>
              <td>
                <a
                    :href="$routing.generate('app_registration_arabic_course_view', { token: reg.token })"
                    class="btn btn-outline-primary btn-sm"
                    :title="`Voir la demande de ${reg.childFirstName} ${reg.childLastName}`"
                >
                  <i class="fas fa-eye me-1"></i>
                </a>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Message si vide -->
    <div v-else class="p-5 text-center text-muted">
      <p>Aucune inscription trouvée.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RegistrationList',
  data() {
    return {
      registrations: [],
      searchInput: '',
      levelFilter: '',
      statusFilter: ''
    }
  },
  computed: {
    levelsList() {
      return this.registrations
          .map(r => r.previousLevel || '—')
          .filter((v, i, a) => a.indexOf(v) === i && v !== '—')
          .sort();
    },
    statusesList() {
      return [...new Set(this.registrations.map(r => this.statusRegister(r)))];
    },
    filteredRegistrations() {
      const term = this.searchInput.toLowerCase();
      return this.registrations.filter(r => {
        const lvl = r.previousLevel || '—';
        const stat = this.statusRegister(r);
        const matchesSearch =
            r.childFirstName.toLowerCase().includes(term) ||
            r.childLastName.toLowerCase().includes(term) ||
            r.fatherFirstName.toLowerCase().includes(term) ||
            r.fatherLastName.toLowerCase().includes(term);
        const matchesLevel = this.levelFilter
            ? lvl.toLowerCase() === this.levelFilter.toLowerCase()
            : true;
        const matchesStatus = this.statusFilter
            ? stat === this.statusFilter
            : true;
        return matchesSearch && matchesLevel && matchesStatus;
      });
    },
    countToPay() {
      return this.filteredRegistrations.filter(r => r.wasEnrolled2024 === 'oui').length;
    },
    countWaiting() {
      return this.filteredRegistrations.length - this.countToPay;
    }
  },
  methods: {
    fetchRegisters() {
      this.axios
          .get(this.$routing.generate('app_registers_list'))
          .then(({ data }) => {
            this.registrations = Array.isArray(data) ? data : data.registers;
          })
          .catch(error => {
            console.error('Erreur lors de la récupération des inscriptions :', error);
          });
    },
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const d = new Date(dateStr);
      return isNaN(d)
          ? '—'
          : d.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
    },
    statusRegister(r) {
      return r.wasEnrolled2024 === 'oui'
          ? 'En attente de paiement'
          : 'Liste d’attente';
    },
    applyFilter() {
      // Les filtres sont réactifs via les computed
    }
  },
  mounted() {
    this.fetchRegisters();
  }
}
</script>

<style scoped>
.table th,
.table td {
  vertical-align: middle;
}
</style>
