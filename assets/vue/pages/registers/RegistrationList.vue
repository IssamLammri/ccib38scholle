<!-- src/components/RegistrationList.vue -->
<template>
  <div class="my-5" lang="fr">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-primary">
        <i class="fa-solid fa-user-graduate me-2"></i>
        Liste des inscrits au cours d’arabe 2025 / 2026
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
      <!-- Total affiché -->
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
        <StatusCard
            title="Pas encore inscrit"
            color="secondary"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'registration').length"
        />
        <StatusCard
            title="Liste d’attente"
            color="warning"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'waiting').length"
        />
        <StatusCard
            title="En attente de paiement"
            color="primary"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'payment').length"
        />
        <StatusCard
            title="Validation"
            color="info"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'validation').length"
        />
        <StatusCard
            title="Prêt à distribuer"
            color="success"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'distribution').length"
        />
        <StatusCard
            title="Création de compte"
            color="dark"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'compte_creation').length"
        />
        <StatusCard
            title="Terminé"
            color="light"
            :count="filteredRegistrations.filter(r => r.stepRegistration === 'completed').length"
        />
      </div>
    </div>

    <!-- Tableau des inscriptions -->
    <div v-if="filteredRegistrations.length" class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
              <th>Nom complet</th>
              <th>Date de naissance</th>
              <th>Niveau</th>
              <th>Parent</th>
              <th>Email</th>
              <th>Status</th>
              <th>Voir la demande</th>
              <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="reg in filteredRegistrations" :key="reg.id">
              <td>
                {{ reg.childFirstName }} {{ reg.childLastName }}
              </td>
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
                    :title="`#${reg.id} — Voir la demande de ${reg.childFirstName} ${reg.childLastName}`"
                >
                  <i class="fas fa-eye me-1"></i>
                </a>
              </td>
              <td>
                <button
                    class="btn btn-sm btn-success"
                    :disabled="!getNextStep(reg)"
                    @click="advanceStep(reg)"
                    :title="`#${reg.id} — Passer à l’étape « ${statusRegister({ stepRegistration: getNextStep(reg) })} »`"
                >
                  Passer
                </button>
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
import StatusCard from './StatusCard.vue';

export default {
  name: 'RegistrationList',
  components: { StatusCard },
  data() {
    return {
      registrations: [],
      searchInput: '',
      levelFilter: '',
      statusFilter: '',
      // Séquence complète des steps (incluant completed)
      stepsSequence: [
        'registration',
        'waiting',
        'payment',
        'validation',
        'distribution',
        'compte_creation',
        'completed'
      ]
    };
  },
  computed: {
    levelsList() {
      return this.registrations
          .map(r => r.previousLevel || '—')
          .filter((v, i, a) => a.indexOf(v) === i && v !== '—')
          .sort();
    },
    allStatuses() {
      return [
        'Pas encore inscrit',
        'Liste d’attente',
        'En attente de paiement',
        'Validation',
        'Prêt à distribuer',
        'Création de compte',
        'Terminé',
        'Inconnu'
      ];
    },
    filteredRegistrations() {
      const term = this.searchInput.trim().toLowerCase();
      return this.registrations.filter(r => {
        const lvl  = r.previousLevel || '—';
        const stat = this.statusRegister(r);
        const matchSearch =
            r.childFirstName.toLowerCase().includes(term) ||
            r.childLastName.toLowerCase().includes(term) ||
            r.fatherFirstName.toLowerCase().includes(term) ||
            r.fatherLastName.toLowerCase().includes(term);
        const matchLevel  = this.levelFilter ? lvl.toLowerCase() === this.levelFilter.toLowerCase() : true;
        const matchStatus = this.statusFilter ? stat === this.statusFilter : true;
        return matchSearch && matchLevel && matchStatus;
      });
    }
  },
  methods: {
    getNextStep(register) {
      const idx = this.stepsSequence.indexOf(register.stepRegistration);
      if (idx === -1 || idx === this.stepsSequence.length - 1) {
        return null;
      }
      return this.stepsSequence[idx + 1];
    },
    advanceStep(register) {
      const next = this.getNextStep(register);
      if (!next) return;

      // Libellé de la prochaine étape
      const nextLabel = this.statusRegister({ stepRegistration: next });

      // Popup de confirmation
      const msg = `Êtes‑vous sûr de vouloir passer l’inscription de ` +
          `${register.childFirstName} ${register.childLastName} ` +
          `à l’étape « ${nextLabel} » ?`;
      if (!window.confirm(msg)) {
        return;
      }

      // Si confirmé, on appelle l’API
      this.axios
          .post(
              this.$routing.generate('app_registration_advance', { id: register.id }),
              { step: next }
          )
          .then(() => {
            register.stepRegistration = next;
          })
          .catch(err => {
            console.error("Erreur à l'avancement :", err);
            // ici, vous pouvez afficher un toast d'erreur
          });
    },
    fetchRegisters() {
      this.axios
          .get(this.$routing.generate('app_registers_list'))
          .then(({ data }) => {
            this.registrations = Array.isArray(data) ? data : data.registers;
          })
          .catch(err => console.error('Erreur récupération inscrits :', err));
    },
    formatDate(dateStr) {
      if (!dateStr) return '—';
      const d = new Date(dateStr);
      return isNaN(d)
          ? '—'
          : d.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
    },
    statusRegister(r) {
      switch (r.stepRegistration) {
        case 'registration':    return 'Pas encore inscrit';
        case 'waiting':         return 'Liste d’attente';
        case 'payment':         return 'En attente de paiement';
        case 'validation':      return 'Validation';
        case 'distribution':    return 'Prêt à distribuer';
        case 'compte_creation': return 'Création de compte';
        case 'completed':       return 'Terminé';
        default:                return 'Inconnu';
      }
    },
    badgeClass(r) {
      switch (r.stepRegistration) {
        case 'registration':    return 'bg-secondary text-white';
        case 'waiting':         return 'bg-warning text-dark';
        case 'payment':         return 'bg-primary text-white';
        case 'validation':      return 'bg-info text-white';
        case 'distribution':    return 'bg-success text-white';
        case 'compte_creation': return 'bg-dark text-white';
        case 'completed':       return 'bg-light text-dark';
        default:                return 'bg-light text-dark';
      }
    },
    applyFilter() {
      // computed se met à jour automatiquement
    }
  },
  mounted() {
    this.fetchRegisters();
  }
};
</script>

<style scoped>
.table th,
.table td {
  vertical-align: middle;
}
.card-title { font-weight: 600; }
.display-1 { font-size: 4rem; font-weight: 700; }
.display-4 { font-size: 2rem; font-weight: 600; }
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
