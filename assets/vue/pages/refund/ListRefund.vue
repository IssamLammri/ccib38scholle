<template>
  <div class="refund-page">
    <!-- En-tête avec design moderne -->
    <div class="page-header">
      <div class="header-content">
        <div class="breadcrumb">
          <span class="breadcrumb-item">Finance</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6"/>
          </svg>
          <span class="breadcrumb-item active">Remboursements</span>
        </div>
        <h1 class="page-title">Remboursements</h1>
        <p class="page-subtitle">
          Gérez et suivez tous vos remboursements en temps réel
        </p>
      </div>

      <div class="header-actions">
        <button
            class="primary-btn"
            @click="goToNewRefund"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
          </svg>
          Nouveau remboursement
        </button>

        <div v-if="isAdmin" class="stats-cards">
          <div class="stat-card primary">
            <div class="stat-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Total page</div>
              <div class="stat-value">{{ formatCurrency(totalPageAmount) }}</div>
            </div>
          </div>

          <div class="stat-card secondary">
            <div class="stat-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12h6M9 16h6M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                <path d="M13 2v7h7"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Remboursements</div>
              <div class="stat-value">{{ total }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-4" />

    <!-- Section recherche et filtres combinés -->
    <div class="search-filters-section">
      <div class="search-bar">
        <div class="search-input-wrapper">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
          <input
              type="text"
              class="search-input"
              placeholder="Rechercher par parent, référence, commentaire..."
              v-model.trim="q"
              @input="page = 1"
          />
          <button v-if="q" @click="q = ''; page = 1" class="clear-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <button @click="showFilters = !showFilters" class="filter-toggle" :class="{ active: showFilters || hasActiveFilters }">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M7 12h10M11 18h2"/>
          </svg>
          Filtres
          <span v-if="hasActiveFilters" class="filter-badge">{{ activeFiltersCount }}</span>
        </button>
      </div>

      <!-- Filtres dépliables -->
      <transition name="slide-down">
        <div v-if="showFilters" class="filters-panel">
          <div class="filters-grid">
            <div class="filter-group">
              <label class="filter-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 6v6l4 2"/>
                </svg>
                Statut
              </label>
              <select class="filter-select" v-model="status" @change="page = 1">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="processed">Traité</option>
                <option value="canceled">Annulé</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="5" width="20" height="14" rx="2"/>
                  <path d="M2 10h20"/>
                </svg>
                Méthode
              </label>
              <select class="filter-select" v-model="method" @change="page = 1">
                <option value="">Toutes les méthodes</option>
                <option v-for="m in methodOptions" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                ID Parent
              </label>
              <input
                  type="number"
                  min="1"
                  class="filter-select"
                  v-model.number="parentId"
                  @input="page = 1"
                  placeholder="Ex: 123"
              />
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Date de début
              </label>
              <input type="date" class="filter-select" v-model="dateFrom" @change="page = 1" />
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Date de fin
              </label>
              <input type="date" class="filter-select" v-model="dateTo" @change="page = 1" />
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Résultats par page
              </label>
              <select class="filter-select" v-model.number="limit" @change="page = 1">
                <option v-for="n in [10,20,50,100]" :key="n" :value="n">{{ n }}</option>
              </select>
            </div>
          </div>

          <div class="filters-actions">
            <button v-if="hasActiveFilters" @click="clearFilters" class="clear-filters-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18M7 12h10M11 18h2"/>
              </svg>
              Réinitialiser les filtres
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Tableau moderne avec cartes responsives -->
    <div class="table-container">
      <!-- Vue tableau desktop -->
      <div class="table-wrapper desktop-view">
        <table class="refunds-table">
          <thead>
          <tr>
            <th>Date</th>
            <th>Parent</th>
            <th>Montant</th>
            <th>Méthode</th>
            <th>Statut</th>
            <th>Factures</th>
            <th>Référence</th>
            <th>Commentaire</th>
            <th v-if="isAdmin" class="actions-column">Actions</th>
          </tr>
          </thead>

          <tbody>
          <tr
              v-for="r in paginatedRefunds"
              :key="r.id"
              class="refund-row"
              @click="goToRefund(r)"
          >
            <td class="date-cell">
              <span class="date-badge">{{ formatDate(r.refundDate) }}</span>
            </td>

            <td class="parent-cell">
              <div class="parent-info">
                <span class="parent-name">{{ parentLabel(r.parent) }}</span>
                <small v-if="r.parent && r.parent.id" class="parent-id">#{{ r.parent.id }}</small>
              </div>
            </td>

            <td class="amount-cell">
              <span class="amount-value">{{ formatCurrency(r.amount) }}</span>
            </td>

            <td class="method-cell">
              <span class="method-badge">{{ r.method || '—' }}</span>
            </td>

            <td class="status-cell">
                <span :class="['status-badge', 'status-' + (r.status || 'pending')]">
                  <span class="status-dot"></span>
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
                    >
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
                      </svg>
                      #{{ inv.id }}
                    </span>
                </div>
              </template>
              <span v-else class="no-data">—</span>
            </td>

            <td class="ref-cell">
              <span v-if="r.reference" class="ref-text">{{ r.reference }}</span>
              <span v-else class="no-data">—</span>
            </td>

            <td class="comment-cell">
              <span v-if="r.comment" class="comment-text" :title="r.comment">{{ r.comment }}</span>
              <span v-else class="no-data">—</span>
            </td>

            <!-- Dans la section tableau desktop -->
            <td v-if="isAdmin" class="actions-cell" @click.stop>
              <div class="action-buttons">
                <!-- Menu déroulant pour le statut -->
                <select
                    class="status-select"
                    :value="r.status || 'pending'"
                    @change="updateStatus(r, $event.target.value)"
                    :disabled="statusLoading[r.id]"
                >
                  <option value="pending">En attente</option>
                  <option value="processed">Traité</option>
                  <option value="canceled">Annulé</option>
                </select>

                <button class="action-btn delete-btn" @click="deleteRefund(r)" title="Supprimer">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="paginatedRefunds.length === 0">
            <td :colspan="isAdmin ? 9 : 8" class="empty-state">
              <div class="empty-content">
                <div class="empty-icon">
                  <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 12h6M9 16h6M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                    <path d="M13 2v7h7"/>
                  </svg>
                </div>
                <p class="empty-title">Aucun remboursement trouvé</p>
                <p class="empty-subtitle">Essayez d'ajuster vos critères de recherche ou de filtrage</p>
                <button v-if="hasActiveFilters" @click="clearFilters" class="empty-action">
                  Réinitialiser les filtres
                </button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Vue cartes mobile -->
      <div class="mobile-view">
        <div v-for="r in paginatedRefunds" :key="r.id" class="refund-card" @click="goToRefund(r)">
          <div class="card-header">
            <span class="date-badge">{{ formatDate(r.refundDate) }}</span>
            <span :class="['status-badge', 'status-' + (r.status || 'pending')]">
              <span class="status-dot"></span>
              {{ prettyStatus(r.status) }}
            </span>
          </div>

          <div class="card-body">
            <div class="card-row">
              <span class="card-label">Parent</span>
              <span class="card-value">
                {{ parentLabel(r.parent) }}
                <small v-if="r.parent && r.parent.id" class="parent-id">#{{ r.parent.id }}</small>
              </span>
            </div>

            <div class="card-row highlight">
              <span class="card-label">Montant</span>
              <span class="card-value amount">{{ formatCurrency(r.amount) }}</span>
            </div>

            <div class="card-row">
              <span class="card-label">Méthode</span>
              <span class="method-badge">{{ r.method || '—' }}</span>
            </div>

            <div v-if="r.invoices && r.invoices.length" class="card-row">
              <span class="card-label">Factures</span>
              <div class="invoices-list">
                <span
                    v-for="inv in r.invoices"
                    :key="inv.id"
                    class="invoice-chip"
                    @click.stop="goToInvoice(inv)"
                >
                  #{{ inv.id }}
                </span>
              </div>
            </div>

            <div v-if="r.reference" class="card-row">
              <span class="card-label">Référence</span>
              <span class="card-value">{{ r.reference }}</span>
            </div>

            <div v-if="r.comment" class="card-row">
              <span class="card-label">Commentaire</span>
              <span class="card-value comment">{{ r.comment }}</span>
            </div>
          </div>
        </div>

        <div v-if="paginatedRefunds.length === 0" class="empty-content mobile">
          <div class="empty-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M9 12h6M9 16h6M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
              <path d="M13 2v7h7"/>
            </svg>
          </div>
          <p class="empty-title">Aucun remboursement trouvé</p>
          <p class="empty-subtitle">Essayez d'ajuster vos critères</p>
          <button v-if="hasActiveFilters" @click="clearFilters" class="empty-action">
            Réinitialiser les filtres
          </button>
        </div>
      </div>

      <!-- Pagination améliorée -->
      <div class="pagination" v-if="total > 0">
        <button class="pagination-btn" :disabled="page <= 1" @click="page = page - 1">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6"/>
          </svg>
          Précédent
        </button>

        <div class="pagination-info">
          <span class="page-number">{{ page }}</span>
          <span class="page-separator">/</span>
          <span class="page-total">{{ totalPages }}</span>
        </div>

        <button class="pagination-btn" :disabled="page >= totalPages" @click="page = page + 1">
          Suivant
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 18l6-6-6-6"/>
          </svg>
        </button>
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
    currentUser: { type: Object, required: true },
  },
  data() {
    return {
      allRefunds: [],
      page: 1,
      limit: 20,
      q: "",
      status: "",
      method: "",
      parentId: null,
      dateFrom: "",
      dateTo: "",
      messageAlert: null,
      typeAlert: null,
      showFilters: false,
      statusLoading: {}, // Pour gérer le loading par remboursement
    };
  },
  computed: {
    roles() {
      return Array.isArray(this.currentUser?.roles) ? this.currentUser.roles : [];
    },
    isAdmin() {
      return this.roles.includes("ROLE_ADMIN");
    },
    methodOptions() {
      const set = new Set(this.allRefunds.map(r => r.method).filter(Boolean));
      return Array.from(set).sort((a, b) => String(a).localeCompare(String(b), 'fr'));
    },
    filteredRefunds() {
      const q = (this.q || "").toLowerCase().trim();
      const df = this.dateFrom ? new Date(this.dateFrom + "T00:00:00") : null;
      const dt = this.dateTo ? new Date(this.dateTo + "T23:59:59") : null;

      return this.allRefunds.filter(r => {
        if (this.status && (r.status || "").toLowerCase() !== this.status) return false;
        if (this.method && (r.method || "") !== this.method) return false;
        if (this.parentId && (!r.parent || Number(r.parent.id) !== Number(this.parentId))) return false;

        if (df || dt) {
          const d = r.refundDate ? new Date(r.refundDate) : null;
          if (!d || Number.isNaN(d.getTime())) return false;
          if (df && d < df) return false;
          if (dt && d > dt) return false;
        }

        if (q) {
          const hay = [
            this.parentLabel(r.parent),
            r.reference,
            r.comment,
            r.method,
          ].filter(Boolean).join(" ").toLowerCase();
          if (!hay.includes(q)) return false;
        }
        return true;
      });
    },
    total() {
      return this.filteredRefunds.length;
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.total / this.limit));
    },
    paginatedRefunds() {
      const start = (this.page - 1) * this.limit;
      return this.filteredRefunds.slice(start, start + this.limit);
    },
    totalPageAmount() {
      return this.paginatedRefunds.reduce((acc, r) => acc + Number(r.amount || 0), 0);
    },
    hasActiveFilters() {
      return !!(
          this.q ||
          this.status ||
          this.method ||
          this.parentId ||
          this.dateFrom ||
          this.dateTo
      );
    },
    activeFiltersCount() {
      let count = 0;
      if (this.q) count++;
      if (this.status) count++;
      if (this.method) count++;
      if (this.parentId) count++;
      if (this.dateFrom) count++;
      if (this.dateTo) count++;
      return count;
    },
  },
  methods: {
    async updateStatus(refund, newStatus) {
      if (!newStatus || newStatus === refund.status) return;

      // start loading (clé dynamique, Vue 3 OK)
      this.statusLoading = { ...this.statusLoading, [refund.id]: true };

      try {
        const url = this.$routing
            ? this.$routing.generate("api_refund_update", { id: refund.id })
            : `/refunds/${refund.id}`;

        await this.axios.patch(url, { status: newStatus });

        // MAJ de l'item dans le tableau
        // Option 1 (directe, réactive en Vue 3) :
        const idx = this.allRefunds.findIndex(r => r.id === refund.id);
        if (idx !== -1) {
          this.allRefunds[idx] = { ...this.allRefunds[idx], status: newStatus };
        }

        // Option 2 (immutabilité, si tu préfères) :
        // this.allRefunds = this.allRefunds.map(r =>
        //   r.id === refund.id ? { ...r, status: newStatus } : r
        // );

        this.messageAlert = "Statut mis à jour avec succès.";
        this.typeAlert = "success";
        setTimeout(() => (this.messageAlert = null), 3000);
      } catch (e) {
        console.error("Erreur lors de la mise à jour du statut:", e);
        this.messageAlert = "Erreur lors de la mise à jour du statut.";
        this.typeAlert = "danger";
      } finally {
        // stop loading
        const copy = { ...this.statusLoading };
        copy[refund.id] = false;
        this.statusLoading = copy;
      }
    },
    parentLabel(p) {
      if (!p) return "Non renseigné";
      const direct = p.fullNameParent || p.fullName;
      if (direct) return direct;
      const father = [p.fatherLastName, p.fatherFirstName].filter(Boolean).join(" ");
      const mother = [p.motherLastName, p.motherFirstName].filter(Boolean).join(" ");
      return father || mother || "Non renseigné";
    },
    prettyStatus(s) {
      const map = { pending: "En attente", processed: "Traité", canceled: "Annulé" };
      return map[s] || s || "—";
    },
    formatDate(date) {
      if (!date) return "";
      const d = new Date(date);
      if (Number.isNaN(d.getTime())) return "";
      return d.toLocaleDateString("fr-FR");
    },
    formatCurrency(amount) {
      const n = Number(amount || 0);
      return n.toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },
    goToNewRefund() {
      const url = this.$routing ? this.$routing.generate("app_refund_new") : "/refunds/new";
      window.location.href = url;
    },
    goToRefund(refund) {
      const url = this.$routing ? this.$routing.generate("api_refund_show", { id: refund.id }) : `/refunds/${refund.id}`;
      window.open(url, "_blank");
    },
    goToInvoice(inv) {
      const url = this.$routing ? this.$routing.generate("app_invoice_show", { id: inv.id }) : `/app/invoice/${inv.id}`;
      window.open(url, "_blank");
    },
    async fetchRefunds() {
      try {
        const url = this.$routing ? this.$routing.generate("api_refund_list") : "/refunds";
        const { data } = await this.axios.get(url);

        let list = [];
        if (Array.isArray(data)) list = data;
        else if (Array.isArray(data.items)) list = data.items;
        else if (data.message && Array.isArray(data.message.refunds)) list = data.message.refunds;
        else if (Array.isArray(data.refunds)) list = data.refunds;
        else if (data.message && data.message.refund) list = [data.message.refund];

        this.allRefunds = list;
        this.page = 1;
      } catch (e) {
        console.error("Erreur lors de la récupération des remboursements :", e);
        this.messageAlert = "Erreur lors du chargement des remboursements.";
        this.typeAlert = "danger";
      }
    },
    async deleteRefund(refund) {
      if (!confirm("Êtes-vous sûr de vouloir supprimer ce remboursement ?")) return;
      try {
        const url = this.$routing ? this.$routing.generate("api_refund_delete", { id: refund.id }) : `/refunds/${refund.id}`;
        await this.axios.delete(url);
        this.allRefunds = this.allRefunds.filter(r => r.id !== refund.id);
        this.messageAlert = "Remboursement supprimé avec succès.";
        this.typeAlert = "success";
        if (this.page > this.totalPages) this.page = this.totalPages;
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
      this.page = 1;
    },
  },
  mounted() {
    this.fetchRefunds();
  },
};
</script>

<style scoped>

/* Variables CSS - À placer EN PREMIER dans le style scoped */
:root {
  --primary: #3b82f6;
  --primary-dark: #2563eb;
  --primary-light: #60a5fa;
  --success: #22c55e;
  --success-dark: #16a34a;
  --warning: #f59e0b;
  --danger: #ef4444;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  --radius: 12px;
  --radius-lg: 16px;
  --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Alternative : définir les variables sur le composant racine si :root ne fonctionne pas avec scoped */
.refund-page {
  --primary: #3b82f6;
  --primary-dark: #2563eb;
  --primary-light: #60a5fa;
  --success: #22c55e;
  --success-dark: #16a34a;
  --warning: #f59e0b;
  --danger: #ef4444;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  --radius: 12px;
  --radius-lg: 16px;
  --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);

  padding: 2rem;
  background: linear-gradient(to bottom, #f9fafb 0%, #ffffff 100%);
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}
* {
  box-sizing: border-box;
}

.refund-page {
  padding: 2rem;
  background: linear-gradient(to bottom, #f9fafb 0%, #ffffff 100%);
  min-height: 100vh;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2.5rem;
  gap: 2rem;
  flex-wrap: wrap;
}

.header-content {
  flex: 1;
  min-width: 300px;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: var(--gray-500);
}

.breadcrumb-item {
  transition: var(--transition);
}

.breadcrumb-item:not(.active):hover {
  color: var(--primary);
  cursor: pointer;
}

.breadcrumb-item.active {
  color: var(--gray-900);
  font-weight: 500;
}

.breadcrumb svg {
  color: var(--gray-400);
}

.page-title {
  font-size: 2.25rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--gray-900) 0%, var(--gray-700) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0 0 0.75rem 0;
  line-height: 1.2;
}

.page-subtitle {
  color: var(--gray-600);
  margin: 0;
  line-height: 1.6;
  font-size: 1rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: stretch;
  flex-wrap: wrap;
}

.primary-btn {
  display: inline-flex;
  gap: 0.625rem;
  align-items: center;
  border: none;
  border-radius: var(--radius);
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  font-weight: 600;
  font-size: 0.938rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  transition: var(--transition);
  white-space: nowrap;
}

.primary-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.primary-btn:active {
  transform: translateY(0);
}

.stats-cards {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  min-width: 200px;
  transition: var(--transition);
  background: white;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.stat-card.primary {
  background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
  color: white;
}

.stat-card.secondary {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
}

.stat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.813rem;
  opacity: 0.9;
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1;
}

/* Recherche et filtres */
.search-filters-section {
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.search-bar {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-input-wrapper {
  flex: 1;
  min-width: 300px;
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 1rem;
  color: var(--gray-400);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.875rem 3rem 0.875rem 3rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  font-size: 1rem;
  transition: var(--transition);
  background: var(--gray-50);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  background: white;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.clear-search {
  position: absolute;
  right: 0.75rem;
  background: var(--gray-200);
  border: none;
  border-radius: 6px;
  padding: 0.375rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gray-600);
  transition: var(--transition);
}

.clear-search:hover {
  background: var(--gray-300);
  color: var(--gray-900);
}

.filter-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.25rem;
  background: var(--gray-100);
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  font-size: 0.938rem;
  font-weight: 500;
  color: var(--gray-700);
  cursor: pointer;
  transition: var(--transition);
  white-space: nowrap;
  position: relative;
}

.filter-toggle:hover {
  background: var(--gray-200);
  border-color: var(--gray-300);
}

.filter-toggle.active {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

.filter-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  background: var(--primary-dark);
  border-radius: 10px;
  font-size: 0.75rem;
  font-weight: 700;
}

.filter-toggle.active .filter-badge {
  background: rgba(255, 255, 255, 0.3);
}

.filters-panel {
  border-top: 1px solid var(--gray-200);
  padding: 1.5rem;
  background: var(--gray-50);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 0.625rem;
}

.filter-label svg {
  color: var(--gray-400);
}

.filter-select {
  padding: 0.75rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  font-size: 0.938rem;
  background: white;
  transition: var(--transition);
  color: var(--gray-900);
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.filters-actions {
  display: flex;
  justify-content: flex-end;
}

.clear-filters-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: white;
  border: 2px solid var(--gray-300);
  border-radius: var(--radius);
  color: var(--gray-700);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: var(--transition);
}

.clear-filters-btn:hover {
  background: var(--gray-100);
  border-color: var(--gray-400);
}

/* Transitions */
.slide-down-enter-active, .slide-down-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  max-height: 500px;
  overflow: hidden;
}

.slide-down-enter, .slide-down-leave-to {
  max-height: 0;
  opacity: 0;
}

/* Table */
.table-container {
  background: white;
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow);
}

.table-wrapper {
  overflow-x: auto;
}

.refunds-table {
  width: 100%;
  border-collapse: collapse;
}

.refunds-table thead {
  background: linear-gradient(to bottom, var(--gray-50) 0%, var(--gray-100) 100%);
  border-bottom: 2px solid var(--gray-200);
}

.refunds-table th {
  padding: 1rem 1.25rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.refund-row {
  border-bottom: 1px solid var(--gray-100);
  cursor: pointer;
  transition: var(--transition);
}

.refund-row:hover {
  background: var(--gray-50);
}

.refunds-table td {
  padding: 1.25rem;
  font-size: 0.938rem;
  color: var(--gray-700);
  vertical-align: middle;
}

.date-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 0.875rem;
  background: var(--gray-100);
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  white-space: nowrap;
}

.parent-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.parent-name {
  font-weight: 500;
  color: var(--gray-900);
}

.parent-id {
  color: var(--gray-500);
  font-size: 0.813rem;
}

.amount-cell {
  font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
  white-space: nowrap;
}

.amount-value {
  font-weight: 700;
  color: var(--success-dark);
  font-size: 1rem;
}

.method-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  border: 1px solid #bfdbfe;
  color: #1e40af;
  border-radius: 6px;
  font-size: 0.813rem;
  font-weight: 600;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.875rem;
  border-radius: 999px;
  font-size: 0.813rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.status-pending {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #92400e;
  border: 1px solid #fcd34d;
}

.status-pending .status-dot {
  background: #f59e0b;
}

.status-processed {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
  color: #166534;
  border: 1px solid #86efac;
}

.status-processed .status-dot {
  background: #22c55e;
}

.status-canceled {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.status-canceled .status-dot {
  background: #ef4444;
}

.invoices-list {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.invoice-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  color: #4338ca;
  padding: 0.375rem 0.625rem;
  border-radius: 6px;
  font-size: 0.813rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  border: 1px solid #c7d2fe;
}

.invoice-chip:hover {
  background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.ref-text {
  font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
  background: var(--gray-100);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
}

.comment-text {
  display: block;
  max-width: 240px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.no-data {
  color: var(--gray-400);
  font-style: italic;
}

.actions-cell {
  padding: 0.75rem 1.25rem !important;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.625rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.875rem;
  font-weight: 500;
}

.status-btn {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #1e40af;
  border: 1px solid #bfdbfe;
}

.status-btn:hover {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.delete-btn {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.delete-btn:hover {
  background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

/* Empty state */
.empty-state {
  padding: 4rem 2rem !important;
  text-align: center;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
  max-width: 400px;
  margin: 0 auto;
}

.empty-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--gray-100);
  color: var(--gray-400);
}

.empty-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
}

.empty-subtitle {
  color: var(--gray-500);
  margin: 0;
  line-height: 1.6;
}

.empty-action {
  padding: 0.75rem 1.5rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.empty-action:hover {
  background: var(--primary-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid var(--gray-200);
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--radius);
  background: white;
  font-weight: 500;
  color: var(--gray-700);
  cursor: pointer;
  transition: var(--transition);
}

.pagination-btn:hover:not(:disabled) {
  background: var(--gray-50);
  border-color: var(--gray-300);
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-info {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  font-weight: 600;
}

.page-number {
  font-size: 1.5rem;
  color: var(--primary);
}

.page-separator {
  color: var(--gray-400);
}

.page-total {
  font-size: 1rem;
  color: var(--gray-600);
}

/* Mobile cards */
.mobile-view {
  display: none;
}

.refund-card {
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: var(--radius);
  padding: 1.25rem;
  margin-bottom: 1rem;
  cursor: pointer;
  transition: var(--transition);
}

.refund-card:hover {
  border-color: var(--primary);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--gray-100);
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.card-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.card-row.highlight {
  background: var(--gray-50);
  padding: 0.75rem;
  border-radius: 8px;
  margin: 0.5rem 0;
}

.card-label {
  font-size: 0.813rem;
  font-weight: 600;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.card-value {
  font-size: 0.938rem;
  font-weight: 500;
  color: var(--gray-900);
  text-align: right;
}

.card-value.amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--success-dark);
  font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
}

.card-value.comment {
  font-size: 0.875rem;
  color: var(--gray-600);
  text-align: right;
}

.card-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--gray-100);
}

.card-actions .action-btn {
  flex: 1;
  gap: 0.5rem;
}

.current-status .label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Modal transitions */
.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-active .modal, .modal-fade-leave-active .modal {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter, .modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter .modal, .modal-fade-leave-to .modal {
  transform: scale(0.95) translateY(-20px);
  opacity: 0;
}

/* Utility */
.mb-4 {
  margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-cards {
    width: 100%;
  }

  .stat-card {
    flex: 1;
    min-width: 160px;
  }
}

@media (max-width: 768px) {
  .refund-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    gap: 1.5rem;
  }

  .header-actions {
    width: 100%;
    flex-direction: column;
  }

  .primary-btn {
    width: 100%;
    justify-content: center;
  }

  .stats-cards {
    width: 100%;
    flex-direction: column;
  }

  .stat-card {
    width: 100%;
  }

  .page-title {
    font-size: 1.75rem;
  }

  .search-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-input-wrapper {
    width: 100%;
  }

  .filter-toggle {
    width: 100%;
    justify-content: center;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .desktop-view {
    display: none;
  }

  .mobile-view {
    display: block;
    padding: 1rem;
  }

  .pagination {
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .pagination-btn {
    flex: 1;
    min-width: 120px;
    justify-content: center;
  }

  .modal {
    max-width: 100%;
    margin: 1rem;
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 1.5rem;
  }

  .breadcrumb {
    font-size: 0.75rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }

  .refund-card {
    padding: 1rem;
  }

  .card-value.amount {
    font-size: 1.125rem;
  }
}

/* Print styles */
@media print {
  .page-header,
  .search-filters-section,
  .pagination,
  .actions-cell,
  .card-actions {
    display: none !important;
  }

  .refund-page {
    padding: 0;
  }

  .table-container {
    box-shadow: none;
  }

  .refund-row:hover {
    background: none;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Focus styles for keyboard navigation */
button:focus-visible,
input:focus-visible,
select:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .status-badge,
  .method-badge,
  .invoice-chip {
    border-width: 2px;
  }

  .btn-primary {
    border: 2px solid var(--primary-dark);
  }
}

.status-select {
  padding: 0.5rem 0.75rem;
  border: 2px solid var(--gray-200);
  border-radius: 8px;
  font-size: 0.813rem;
  font-weight: 600;
  background: white;
  cursor: pointer;
  transition: var(--transition);
  min-width: 120px;
}

.status-select:hover {
  border-color: var(--primary);
}

.status-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.status-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Version mobile du select */
.status-select-mobile {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 2px solid var(--gray-200);
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  background: white;
  cursor: pointer;
  transition: var(--transition);
}

.status-select-mobile:hover {
  border-color: var(--primary);
}

.status-select-mobile:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.status-select-mobile:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Ajuster les actions-cell */
.actions-cell {
  padding: 0.75rem 1.25rem !important;
  min-width: 200px;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

</style>