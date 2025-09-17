<template>
  <div class="my-5" lang="fr">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="text-primary">
        <i class="fa-solid fa-user-graduate me-2"></i>
        Inscriptions – Soutien scolaire 2025 / 2026
      </h1>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm p-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Rechercher :</label>
          <input
              type="text"
              v-model.trim="searchInput"
              @input="applyFilter"
              class="form-control"
              placeholder="Prénom/nom élève ou parent, email…"
          />
        </div>

        <div class="col-md-3">
          <label class="form-label">Niveau :</label>
          <select v-model="levelFilter" @change="applyFilter" class="form-select">
            <option value="">Tous</option>
            <option v-for="lvl in levelsList" :key="lvl" :value="lvl">{{ lvl }}</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Statut :</label>
          <select v-model="statusFilter" @change="applyFilter" class="form-select">
            <option value="">Tous</option>
            <option v-for="opt in statusLabelOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-3">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-muted mb-2">Total affiché</h6>
            <div class="display-6 m-0">{{ filteredRegistrations.length }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3" v-for="s in statusLabelOptions" :key="s.value">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-muted mb-2">{{ s.label }}</h6>
            <div class="display-6 m-0">
              {{
                filteredRegistrations.filter(r => normalizeStatus(r.status) === s.value).length
              }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div v-if="filteredRegistrations.length" class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
              <th>Élève</th>
              <th>Niveau</th>
              <th>Matières</th>
              <th>Parent</th>
              <th>Email</th>
              <th>Téléphones</th>
              <th>Statut</th>
              <th>Créée le</th>
              <th style="width: 220px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="reg in filteredRegistrations" :key="reg.id">
              <td>{{ reg.studentFirstName }} {{ reg.studentLastName }}</td>
              <td>{{ reg.level || '—' }}</td>
              <td>
                <span v-if="Array.isArray(reg.subjects) && reg.subjects.length">
                  {{ reg.subjects.join(', ') }}
                </span>
                <span v-else>—</span>
              </td>
              <td>{{ reg.parentFirstName }} {{ reg.parentLastName }}</td>
              <td>{{ reg.email }}</td>
              <td>
                <span>{{ reg.phone || '—' }}</span>
                <span v-if="reg.motherPhone"> / {{ reg.motherPhone }}</span>
              </td>
              <td>
                <span :class="['badge', badgeClass(normalizeStatus(reg.status))]">
                  {{ statusLabel(normalizeStatus(reg.status)) }}
                </span>
              </td>
              <td>{{ formatDate(reg.createdAt) }}</td>
              <td>
                <div class="d-flex gap-2">
                  <!-- Voir la demande (si token fourni par l'API) -->
                  <a
                      v-if="reg.token"
                      :href="$routing.generate?.('app_registration_academic_support_view', { token: reg.token })"
                      class="btn btn-outline-primary btn-sm"
                      :title="`#${reg.id} — Voir la demande de ${reg.studentFirstName} ${reg.studentLastName}`"
                  >
                    <i class="fas fa-eye"></i>
                  </a>

                  <!-- Changer le statut -->
                  <div class="input-group input-group-sm" style="width: 160px;">
                    <label class="input-group-text">Statut</label>
                    <select
                        class="form-select"
                        :disabled="isUpdatingId === reg.id"
                        :value="normalizeStatus(reg.status)"
                        @change="onChangeStatus(reg, $event.target.value)"
                    >
                      <option v-for="opt in statusLabelOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                      </option>
                    </select>
                  </div>

                  <button
                      class="btn btn-outline-secondary btn-sm"
                      :disabled="isUpdatingId === reg.id"
                      @click="refreshRow(reg)"
                      title="Rafraîchir la ligne"
                  >
                    <i :class="['fas', isUpdatingId === reg.id ? 'fa-spinner fa-spin' : 'fa-rotate-right']"></i>
                  </button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Vide -->
    <div v-else class="p-5 text-center text-muted">
      <p>Aucune inscription trouvée.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RegistrationListAcademicSupport',
  props: {
    /** Optionnel : passer un tableau préchargé, sinon le composant appelle l’API */
    initialRegistrations: { type: Array, default: () => [] }
  },
  data() {
    return {
      registrations: [],
      searchInput: '',
      levelFilter: '',
      statusFilter: '',
      isUpdatingId: null,
      // valeurs canoniques
      statusLabelOptions: [
        { value: 'en_cours', label: 'En cours de traitement' },
        { value: 'inscrit',  label: 'Inscrit' },
        { value: 'annule',   label: 'Annulé' }
      ]
    };
  },
  computed: {
    levelsList() {
      return [...new Set(this.registrations.map(r => r.level).filter(Boolean))].sort();
    },
    filteredRegistrations() {
      const term = this.searchInput.toLowerCase();
      return this.registrations.filter(r => {
        const matchesSearch =
            (!term) ||
            (r.studentFirstName && r.studentFirstName.toLowerCase().includes(term)) ||
            (r.studentLastName  && r.studentLastName.toLowerCase().includes(term))  ||
            (r.parentFirstName  && r.parentFirstName.toLowerCase().includes(term))  ||
            (r.parentLastName   && r.parentLastName.toLowerCase().includes(term))   ||
            (r.email            && r.email.toLowerCase().includes(term));

        const matchesLevel  = this.levelFilter ? r.level === this.levelFilter : true;

        const statusCode = this.normalizeStatus(r.status);
        const matchesStatus = this.statusFilter ? statusCode === this.statusFilter : true;

        return matchesSearch && matchesLevel && matchesStatus;
      });
    }
  },
  methods: {
    /* ----------- FETCH / INIT ----------- */
    fetchRegistrations() {
      // Si les données ne sont pas passées en props, on fetch
      if (this.initialRegistrations.length) {
        // normaliser à l’arrivée
        this.registrations = this.initialRegistrations.map(this.normalizeIncoming);
        return;
      }
      const url =
          this.$routing.generate('app_registers_academic_support_list')
      this.axios
          .get(url)
          .then(({ data }) => {
            console.log(data)
            const rows = Array.isArray(data) ? data : (data.registrations || []);
            this.registrations = rows.map(this.normalizeIncoming);
          })
          .catch(err => console.error('Erreur récupération inscrits :', err));
    },
    normalizeIncoming(r) {
      // mappe les statuts exotiques -> canonique
      return {
        ...r,
        status: this.normalizeStatus(r.status)
      };
    },

    /* ----------- HELPERS ----------- */
    normalizeStatus(value) {
      const v = String(value || '').toLowerCase();
      if (['en_cours', 'en-cours', 'en cours', 'processing', 'pending'].includes(v)) return 'en_cours';
      if (['inscrit', 'accepted', 'enrolled'].includes(v)) return 'inscrit';
      if (['annule', 'annulé', 'cancelled', 'canceled'].includes(v)) return 'annule';
      return 'en_cours';
    },
    statusLabel(code) {
      const item = this.statusLabelOptions.find(s => s.value === code);
      return item ? item.label : 'En cours de traitement';
    },
    badgeClass(code) {
      switch (code) {
        case 'inscrit':  return 'bg-success';
        case 'annule':   return 'bg-danger';
        case 'en_cours':
        default:         return 'bg-secondary';
      }
    },
    formatDate(iso) {
      if (!iso) return '—';
      const d = new Date(iso);
      return isNaN(d) ? '—' : d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    },

    /* ----------- UI ----------- */
    applyFilter() {/* computed s’actualise tout seul */},
    refreshRow(reg) {
      // si besoin : re-fetch 1 ligne depuis l’API; ici on “pulse” juste l’icône
      this.isUpdatingId = reg.id;
      setTimeout(() => (this.isUpdatingId = null), 800);
    },

    /* ----------- UPDATE STATUT ----------- */
    onChangeStatus(reg, newCode) {
      if (this.normalizeStatus(reg.status) === newCode) return;
      const label = this.statusLabel(newCode);
      if (!confirm(`Confirmer le changement de statut en « ${label} » pour #${reg.id} ?`)) {
        // reset le select
        this.$forceUpdate();
        return;
      }

      this.isUpdatingId = reg.id;
      const url =
          this.$routing?.generate?.('app_registration_academic_support_update_status', { id: reg.id }) ??
          `/api/inscription-requests/${reg.id}/status`;

      this.axios
          .patch(url, { status: newCode })
          .then(() => (reg.status = newCode))
          .catch(err => {
            console.error('Erreur de mise à jour du statut :', err);
            alert('La mise à jour a échoué.');
          })
          .finally(() => (this.isUpdatingId = null));
    }
  },
  mounted() {
    this.fetchRegistrations();
  }
};
</script>

<style scoped>
.table th, .table td { vertical-align: middle; }
.display-6 { font-weight: 700; }
.badge { font-weight: 600; }
</style>
