<template>
  <div class="refund-page">
    <!-- En-tête -->
    <!-- ...dans <template> / .page-header -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Remboursements</h1>
        <p class="page-subtitle">
          Retrouvez ici tous les remboursements : parent, montant, factures associées, méthode et statut.
        </p>
      </div>

      <div class="header-actions">
        <button
            v-if="isAdmin"
            class="primary-btn"
            @click="goToNewRefund"
            title="Créer un nouveau remboursement"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Nouveau remboursement
        </button>

        <div v-if="isAdmin" class="total-card">
          <div class="total-label">Total remboursé (page)</div>
          <div class="total-amount">{{ formatCurrency(totalPageAmount) }}</div>
        </div>
      </div>
    </div>


    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-4" />

    <!-- Recherche & Filtres -->
    <div class="search-section">
      <div class="search-container">
        <div class="search-input-wrapper">
          <img src="/static/icons/search.svg" alt="Rechercher" class="search-icon" />
          <input
              type="text"
              class="search-input"
              placeholder="Rechercher par parent, référence, commentaire…"
              v-model.trim="q"
              @keyup.enter="fetchRefunds(1)"
          />
        </div>

        <div class="search-results">
          <span class="result-count">{{ total }}</span>
          <span class="result-label">remboursement{{ total > 1 ? 's' : '' }}</span>
        </div>
      </div>
    </div>

    <div class="filters-section">
      <div class="filters-grid">
        <div class="filter-group">
          <label class="filter-label">Statut</label>
          <select class="filter-select" v-model="status" @change="fetchRefunds(1)">
            <option value="">Tous</option>
            <option value="pending">En attente</option>
            <option value="processed">Trait&eacute;</option>
            <option value="canceled">Annul&eacute;</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Méthode</label>
          <select class="filter-select" v-model="method" @change="fetchRefunds(1)">
            <option value="">Toutes</option>
            <option v-for="m in methods" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Parent (ID)</label>
          <input
              type="number"
              min="1"
              class="filter-select"
              v-model.number="parentId"
              @change="fetchRefunds(1)"
              placeholder="Ex: 123"
          />
        </div>

        <div class="filter-group">
          <label class="filter-label">Du</label>
          <input type="date" class="filter-select" v-model="dateFrom" @change="fetchRefunds(1)" />
        </div>

        <div class="filter-group">
          <label class="filter-label">Au</label>
          <input type="date" class="filter-select" v-model="dateTo" @change="fetchRefunds(1)" />
        </div>

        <div class="filter-group">
          <label class="filter-label">Par page</label>
          <select class="filter-select" v-model.number="limit" @change="fetchRefunds(1)">
            <option v-for="n in [10,20,50,100]" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
      </div>

      <button v-if="hasActiveFilters" @click="clearFilters" class="clear-filters-btn">
        Réinitialiser les filtres
      </button>
    </div>

    <!-- Tableau -->
    <div class="table-container">
      <div class="table-wrapper">
        <table class="refunds-table">
          <thead>
          <tr>
            <th>Date</th>
            <th>Parent</th>
            <th>Montant</th>
            <th>Méthode</th>
            <th>Statut</th>
            <th>Factures liées</th>
            <th>Référence</th>
            <th>Commentaire</th>
            <th v-if="isAdmin" class="actions-column">Actions</th>
          </tr>
          </thead>

          <tbody>
          <tr
              v-for="r in items"
              :key="r.id"
              class="refund-row"
              @click="goToRefund(r)"
          >
            <td class="date-cell">
              <span class="date-badge">{{ formatDate(r.refundDate) }}</span>
            </td>
            <td class="parent-cell">
              {{ r.parent?.fullNameParent || r.parent?.fullName || 'Non renseigné' }}
              <small v-if="r.parent?.id" class="parent-id">(#{{ r.parent.id }})</small>
            </td>
            <td class="amount-cell">{{ formatCurrency(r.amount) }}</td>
            <td class="method-cell">
              <span class="type-badge method-badge">{{ r.method || '—' }}</span>
            </td>
            <td class="status-cell">
                <span :class="['status-badge', 'status-' + (r.status || 'pending')]">
                  {{ prettyStatus(r.status) }}
                </span>
            </td>
            <td class="invoices-cell" @click.stop>
              <template v-if="r.invoices && r.invoices.length">
                <div class="invoices-list">
                    <span
                        v-for="inv in r.invoices"
                        :key="inv.id"
                        class="invoice-chip"
                        @click="goToInvoice(inv)"
                        title="Ouvrir la facture"
                    >
                      #{{ inv.id }}
                    </span>
                </div>
              </template>
              <span v-else class="no-data">—</span>
            </td>
            <td class="ref-cell">{{ r.reference || '—' }}</td>
            <td class="comment-cell">
              <span v-if="r.comment" class="comment-text">{{ r.comment }}</span>
              <span v-else class="no-data">—</span>
            </td>
            <td v-if="isAdmin" class="actions-cell" @click.stop>
              <button class="delete-btn" @click="deleteRefund(r)" title="Supprimer le remboursement">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </td>
          </tr>

          <tr v-if="items.length === 0">
            <td :colspan="isAdmin ? 9 : 8" class="empty-state">
              <div class="empty-content">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M9 12h6M9 16h6M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                  <path d="M13 2v7h7"/>
                </svg>
                <p class="empty-title">Aucun remboursement trouvé</p>
                <p class="empty-subtitle">Essayez d’ajuster la recherche ou les filtres</p>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="total > 0">
        <button :disabled="page <= 1" @click="fetchRefunds(page - 1)">Précédent</button>
        <span>Page {{ page }} / {{ totalPages }}</span>
        <button :disabled="page >= totalPages" @click="fetchRefunds(page + 1)">Suivant</button>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "ListRefund",
  components: { Alert },
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      // Données serveur
      items: [],
      total: 0,
      page: 1,
      limit: 20,

      // Filtres / recherche (envoyés au serveur)
      q: "",
      status: "",
      method: "",
      parentId: null,
      dateFrom: "",
      dateTo: "",

      // UI
      messageAlert: null,
      typeAlert: null,

      // Valeurs suggérées
      methods: ["CB", "Virement", "Espèces", "Chèque", "Carte bancaire"],
    };
  },
  computed: {
    roles() {
      return Array.isArray(this.currentUser?.roles) ? this.currentUser.roles : [];
    },
    isAdmin()   {
      return this.roles.includes('ROLE_ADMIN');
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.total / this.limit));
    },
    hasActiveFilters() {
      return !!(this.q || this.status || this.method || this.parentId || this.dateFrom || this.dateTo || (this.limit !== 20));
    },
    totalPageAmount() {
      return this.items.reduce((acc, r) => acc + Number(r.amount || 0), 0);
    },
  },
  methods: {
    goToNewRefund() {
      const url = this.$routing
          ? this.$routing.generate('app_refund_new')
          : '/refunds/new'; // fallback si pas de $routing
      window.location.href = url;
    },
    async fetchRefunds(gotoPage = null) {
      if (gotoPage) this.page = gotoPage;

      const params = {
        page: this.page,
        limit: this.limit,
      };

      if (this.q) params.q = this.q;
      if (this.status) params.status = this.status;
      if (this.method) params.method = this.method;
      if (this.parentId) params.parentId = this.parentId;
      if (this.dateFrom) params.dateFrom = this.dateFrom;
      if (this.dateTo) params.dateTo = this.dateTo;

      try {
        // Si tu utilises friendsofsymfony/ux-router: this.$routing.generate('api_refund_index')
        // Sinon, mets l’URL absolue '/api/refunds'
        const url = this.$routing ? this.$routing.generate("api_refund_index") : "/refunds";

        const { data } = await this.axios.get(url, { params });

        this.items = data.items || [];
        this.total = data.total || 0;
        this.page = data.page || 1;
        this.limit = data.limit || this.limit;

      } catch (e) {
        console.error("Erreur lors de la récupération des remboursements :", e);
        this.messageAlert = "Erreur lors du chargement des remboursements.";
        this.typeAlert = "danger";
      }
    },

    prettyStatus(s) {
      const map = {
        pending: "En attente",
        processed: "Traité",
        canceled: "Annulé",
      };
      return map[s] || s || "—";
    },

    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleDateString("fr-FR");
    },

    formatCurrency(amount) {
      if (amount === undefined || amount === null || amount === "") return "0,00 €";
      return Number(amount).toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },

    goToRefund(refund) {
      // À adapter si tu as une page détail refund
      // Exemple : route Twig /refunds/{id}
      const url = this.$routing ? this.$routing.generate("api_refund_show", { id: refund.id }) : `/api/refunds/${refund.id}`;
      window.open(url, "_blank");
    },

    goToInvoice(inv) {
      const url = this.$routing ? this.$routing.generate("app_invoice_show", { id: inv.id }) : `/app/invoice/${inv.id}`;
      window.open(url, "_blank");
    },

    async deleteRefund(refund) {
      if (!confirm("Êtes-vous sûr de vouloir supprimer ce remboursement ?")) return;

      try {
        const url = this.$routing ? this.$routing.generate("api_refund_delete", { id: refund.id }) : `/api/refunds/${refund.id}`;
        await this.axios.delete(url);
        this.messageAlert = "Remboursement supprimé avec succès.";
        this.typeAlert = "success";
        this.fetchRefunds(); // recharge la page courante
      } catch (e) {
        console.error("Erreur lors de la suppression :", e);
        this.messageAlert = "Erreur lors de la suppression.";
        this.typeAlert = "danger";
      }
    },

    clearFilters() {
      this.q = "";
      this.status = "";
      this.method = "";
      this.parentId = null;
      this.dateFrom = "";
      this.dateTo = "";
      this.limit = 20;
      this.fetchRefunds(1);
    },
  },
  mounted() {
    this.fetchRefunds(1);
  },
};
</script>

<style scoped>
.refund-page { padding: 2rem; background: #f8f9fa; min-height: 100vh; }
/* Réutilise le style de ta page factures pour continuité */
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; gap: 2rem; flex-wrap: wrap; }
.header-content { flex: 1; min-width: 300px; }
.page-title { font-size: 2rem; font-weight: 700; color: #1a202c; margin: 0 0 0.5rem 0; }
.page-subtitle { color: #64748b; margin: 0; line-height: 1.6; }
.total-card { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; padding: 1.5rem 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(34,197,94,.2); min-width: 200px; }
.total-label { font-size: .875rem; opacity: .9; margin-bottom: .5rem; }
.total-amount { font-size: 1.75rem; font-weight: 700; }

/* Recherche / Filtres */
.search-section { background: #fff; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.search-container { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
.search-input-wrapper { flex: 1; min-width: 300px; position: relative; display: flex; align-items: center; }
.search-icon { position: absolute; left: 1rem; width: 20px; height: 20px; opacity: .5; }
.search-input { width: 100%; padding: .875rem 1rem .875rem 3rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; transition: all .2s; }
.search-input:focus { outline: none; border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.1); }
.search-results { display: flex; align-items: baseline; gap: .5rem; color: #64748b; }
.result-count { font-size: 1.5rem; font-weight: 700; color: #22c55e; }
.filters-section { background: #fff; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.filters-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 1rem; margin-bottom: 1rem; }
.filter-group { display: flex; flex-direction: column; }
.filter-label { font-size: .875rem; font-weight: 600; color: #475569; margin-bottom: .5rem; }
.filter-select { padding: .75rem; border: 2px solid #e2e8f0; border-radius: 8px; font-size: .938rem; background: #fff; transition: all .2s; }
.filter-select:focus { outline: none; border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,.1); }
.clear-filters-btn { padding: .625rem 1.25rem; background: #f1f5f9; border: none; border-radius: 8px; color: #475569; font-size: .875rem; font-weight: 500; cursor: pointer; transition: .2s; }
.clear-filters-btn:hover { background: #e2e8f0; }

/* Tableau */
.table-container { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
.table-wrapper { overflow-x: auto; }
.refunds-table { width: 100%; border-collapse: collapse; }
.refunds-table thead { background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
.refunds-table th { padding: 1rem; text-align: left; font-size: .813rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .05em; }
.refund-row { border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background-color .2s; }
.refund-row:hover { background-color: #f8fafc; }
.refunds-table td { padding: 1rem; font-size: .938rem; color: #334155; vertical-align: top; }
.date-badge { display: inline-block; padding: .375rem .75rem; background: #f1f5f9; border-radius: 6px; font-size: .875rem; font-weight: 500; color: #475569; }
.parent-id { color: #94a3b8; margin-left: .25rem; }
.amount-cell { font-family: 'SF Mono','Monaco','Courier New',monospace; white-space: nowrap; }
.type-badge { padding: .25rem .625rem; border-radius: 4px; font-size: .75rem; font-weight: 500; }
.method-badge { background: #dbeafe; color: #1e40af; }
.status-badge { padding: .25rem .625rem; border-radius: 999px; font-size: .75rem; font-weight: 700; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-processed { background: #dcfce7; color: #166534; }
.status-canceled { background: #fee2e2; color: #991b1b; }
.invoices-list { display: flex; gap: .5rem; flex-wrap: wrap; }
.invoice-chip { background: #eef2ff; color: #4338ca; padding: .25rem .5rem; border-radius: 6px; font-size: .75rem; cursor: pointer; }
.invoice-chip:hover { filter: brightness(.95); }
.comment-text { display: block; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.no-data { color: #94a3b8; }
.delete-btn { padding: .5rem; background: #fee2e2; border: none; border-radius: 6px; color: #dc2626; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: .2s; }
.delete-btn:hover { background: #fecaca; transform: translateY(-1px); }
.empty-state { padding: 4rem 2rem !important; text-align: center; }
.empty-content { display: flex; flex-direction: column; align-items: center; gap: 1rem; }
.empty-content svg { color: #cbd5e1; }
.empty-title { font-size: 1.125rem; font-weight: 600; color: #475569; margin: 0; }
.empty-subtitle { color: #94a3b8; margin: 0; }
.pagination { display: flex; justify-content: center; align-items: center; gap: .75rem; padding: 1rem; }
.pagination button { padding: .5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; cursor: pointer; }
.pagination button:disabled { opacity: .5; cursor: not-allowed; }

/* Responsive */
@media (max-width: 768px) {
  .refund-page { padding: 1rem; }
  .page-header { flex-direction: column; }
  .filters-grid { grid-template-columns: 1fr; }
  .refunds-table { font-size: .875rem; }
  .refunds-table th, .refunds-table td { padding: .75rem .5rem; }
}
/* Ajoute ceci dans <style scoped> de ListRefund.vue */
.header-actions {
  display: flex;
  gap: 1rem;
  align-items: stretch;
}
.primary-btn {
  display: inline-flex;
  gap: .5rem;
  align-items: center;
  border: none;
  border-radius: 10px;
  padding: .75rem 1rem;
  background: linear-gradient(135deg,#3b82f6 0%, #2563eb 100%);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 6px 14px rgba(37,99,235,.25);
  transition: transform .08s ease, box-shadow .2s ease, filter .2s ease;
}
.primary-btn:hover { filter: brightness(1.05); box-shadow: 0 8px 18px rgba(37,99,235,.35); }
.primary-btn:active { transform: translateY(1px); }

</style>
