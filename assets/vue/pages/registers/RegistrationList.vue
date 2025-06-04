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
            <!-- On affiche ici la liste complète des statuts même s'ils n'existent pas encore dans registrations -->
            <option
                v-for="st in allStatuses"
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
      <div class="mb-5">
        <!-- Total affiché en grand -->
        <div class="row mb-4 justify-content-center">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-body py-4">
                <h5 class="card-title text-primary mb-2">Total affiché</h5>
                <p class="display-1 mb-0">{{ filteredRegistrations.length }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Autres statuts -->
        <div class="row text-center g-3">
          <!-- Pas encore inscrit -->
          <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body py-3">
                <h6 class="card-title text-secondary mb-1">Pas encore inscrit</h6>
                <p class="display-4 mb-0">
                  {{ filteredRegistrations.filter(r => r.stepRegistration === 'registration').length }}
                </p>
              </div>
            </div>
          </div>
          <!-- Liste d’attente -->
          <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body py-3">
                <h6 class="card-title text-warning mb-1">Liste d’attente</h6>
                <p class="display-4 mb-0">
                  {{ filteredRegistrations.filter(r => r.stepRegistration === 'waiting').length }}
                </p>
              </div>
            </div>
          </div>
          <!-- En attente de paiement -->
          <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body py-3">
                <h6 class="card-title text-primary mb-1">En attente de paiement</h6>
                <p class="display-4 mb-0">
                  {{ filteredRegistrations.filter(r => r.stepRegistration === 'payment').length }}
                </p>
              </div>
            </div>
          </div>
          <!-- Validation -->
          <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body py-3">
                <h6 class="card-title text-info mb-1">Validation</h6>
                <p class="display-4 mb-0">
                  {{ filteredRegistrations.filter(r => r.stepRegistration === 'validation').length }}
                </p>
              </div>
            </div>
          </div>
          <!-- Prêt à distribuer -->
          <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body py-3">
                <h6 class="card-title text-success mb-1">Prêt à distribuer</h6>
                <p class="display-4 mb-0">
                  {{ filteredRegistrations.filter(r => r.stepRegistration === 'distribution').length }}
                </p>
              </div>
            </div>
          </div>
          <!-- Création de compte -->
          <div class="col-md-2 col-sm-4 col-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body py-3">
                <h6 class="card-title text-dark mb-1">Création de compte</h6>
                <p class="display-4 mb-0">
                  {{ filteredRegistrations.filter(r => r.stepRegistration === 'compte_creation').length }}
                </p>
              </div>
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
                  <span :class="['badge', badgeClass(reg)]">
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
    // Liste des niveaux présents dans 'registrations'
    levelsList() {
      return this.registrations
          .map(r => r.previousLevel || '—')
          .filter((v, i, a) => a.indexOf(v) === i && v !== '—')
          .sort();
    },
    // Liste fixe de tous les libellés de statut possibles
    allStatuses() {
      return [
        'Pas encore inscrit',
        'Liste d’attente',
        'En attente de paiement',
        'Validation',
        'Prêt à distribuer',
        'Création de compte',
        'Inconnu'
      ];
    },
    // Tableau filtré en fonction de searchInput, levelFilter et statusFilter
    filteredRegistrations() {
      const term = this.searchInput.trim().toLowerCase();
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
          : d.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });
    },
    statusRegister(register) {
      switch (register.stepRegistration) {
        case 'registration':
          return 'Pas encore inscrit';
        case 'waiting':
          return 'Liste d’attente';
        case 'payment':
          return 'En attente de paiement';
        case 'validation':
          return 'Validation';
        case 'distribution':
          return 'Prêt à distribuer';
        case 'compte_creation':
          return 'Création de compte';
        default:
          return 'Inconnu';
      }
    },
    badgeClass(register) {
      switch (register.stepRegistration) {
        case 'registration':
          return 'bg-secondary text-white';    // gris
        case 'waiting':
          return 'bg-warning text-dark';        // jaune
        case 'payment':
          return 'bg-primary text-white';       // bleu
        case 'validation':
          return 'bg-info text-white';          // cyan
        case 'distribution':
          return 'bg-success text-white';       // vert
        case 'compte_creation':
          return 'bg-dark text-white';          // noir
        default:
          return 'bg-light text-dark';          // clair par défaut
      }
    },
    applyFilter() {
      // Le calcul de filteredRegistrations est réactif, donc on n’a rien à faire ici.
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
.card-title {
  font-weight: 600;
}
.display-1 {
  font-size: 4rem;
  font-weight: 700;
}
.display-4 {
  font-size: 2rem;
  font-weight: 600;
}
/* Si vous utilisez Bootstrap 5, ces classes existent déjà ;
   sinon, adaptez-les à votre CSS ou framework : */
.bg-secondary { background-color: #6c757d; }
.bg-warning   { background-color: #ffc107; }
.bg-primary   { background-color: #0d6efd; }
.bg-info      { background-color: #0dcaf0; }
.bg-success   { background-color: #198754; }
.bg-dark      { background-color: #212529; }
.bg-light     { background-color: #f8f9fa; }
.text-white   { color: #fff !important; }
.text-dark    { color: #000 !important; }
</style>
