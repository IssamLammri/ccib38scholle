<!-- PaymentsDashboard.vue -->
<template>
  <div class="payments-page">
    <!-- Loader moderne -->
    <div class="loader-overlay" v-if="isLoading">
      <div class="loader-card">
        <span class="loader-spinner"></span>
        <span class="loader-text">Chargement des paiements...</span>
      </div>
    </div>

    <!-- En-tête avec titre et bouton -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Paiements</h1>
        <p class="page-subtitle">
          Retrouvez ici tous les paiements effectués. Recherchez, filtrez, visualisez.
        </p>
      </div>

      <button
          class="add-payment-btn"
          data-bs-toggle="modal"
          data-bs-target="#newPaiementsModal"
      >
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path d="M10 4v12m-6-6h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Ajouter un paiement</span>
      </button>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-4" />

    <!-- KPIs modernes -->
    <div v-if="isAdmin" class="kpi-section">
      <div class="kpi-card gradient-purple">
        <div class="kpi-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Chiffre d'affaires</div>
          <div class="kpi-value">{{ money(sumAmount(filteredPayments)) }}</div>
          <div class="kpi-footer">Tous types confondus</div>
        </div>
      </div>

      <div class="kpi-card gradient-blue">
        <div class="kpi-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Nombre de paiements</div>
          <div class="kpi-value">{{ filteredPayments.length }}</div>
          <div class="kpi-footer">{{ payments.length }} au total</div>
        </div>
      </div>

      <div class="kpi-card gradient-green">
        <div class="kpi-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Panier moyen</div>
          <div class="kpi-value">{{ money(avgAmount(filteredPayments)) }}</div>
          <div class="kpi-footer">montant payé / paiement</div>
        </div>
      </div>

      <div class="kpi-card gradient-orange">
        <div class="kpi-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">Top type</div>
          <div class="kpi-value">{{ topTypeLabel }}</div>
          <div class="kpi-footer">dans la sélection</div>
        </div>
      </div>
    </div>

    <!-- Recherche moderne -->
    <div class="search-section">
      <div class="search-container">
        <div class="search-input-wrapper">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <input
              type="text"
              class="search-input"
              placeholder="Rechercher: parent, élève, méthode, classe, agent…"
              v-model.trim="q"
          />
        </div>
        <div class="search-results">
          <span class="result-count">{{ filteredPayments.length }}</span>
          <span class="result-label">paiement{{ filteredPayments.length > 1 ? 's' : '' }} trouvé{{ filteredPayments.length > 1 ? 's' : '' }}</span>
        </div>
      </div>
    </div>

    <!-- Filtres modernes -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="filter-group">
          <label class="filter-label">Type</label>
          <select v-model="typeFilter" class="filter-select">
            <option value="all">Tous</option>
            <option value="soutien">Soutien</option>
            <option value="arabe">Arabe</option>
            <option value="livres">Livres</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Agent (caissier)</label>
          <input
              type="text"
              class="filter-input"
              v-model.trim="agentFilter"
              placeholder="Nom de l'agent"
          />
        </div>

        <div class="filter-group">
          <label class="filter-label">Méthode</label>
          <select v-model="methodFilter" class="filter-select">
            <option value="all">Toutes</option>
            <option v-for="m in paymentMethods" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Mois (soutien)</label>
          <select v-model="monthFilter" class="filter-select">
            <option value="all">Tous</option>
            <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Année</label>
          <select v-model.number="yearFilter" class="filter-select">
            <option value="all">Toutes</option>
            <option v-for="y in yearsAvailable" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Montant min</label>
          <input
              type="number"
              class="filter-input"
              v-model.number="minAmount"
              placeholder="0"
          />
        </div>

        <div class="filter-group">
          <label class="filter-label">Montant max</label>
          <input
              type="number"
              class="filter-input"
              v-model.number="maxAmount"
              placeholder="∞"
          />
        </div>
      </div>

      <div class="filters-actions">
        <button class="clear-filters-btn" @click="resetFilters">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M12 4L4 12m0-8l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          Réinitialiser
        </button>
        <button class="export-btn" @click="exportCsv">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M14 10v2.667A1.333 1.333 0 0 1 12.667 14H3.333A1.333 1.333 0 0 1 2 12.667V10m2.667-4L8 9.333 11.333 6M8 2v7.333" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          Exporter CSV
        </button>
      </div>
    </div>

    <!-- Onglets modernes -->
    <div class="tabs-section">
      <div class="tabs-wrapper">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            class="tab-button"
            :class="{ active: activeTab === tab.value }"
            @click="activeTab = tab.value"
        >
          <span class="tab-label">{{ tab.label }}</span>
          <span class="tab-count">{{ getTabCount(tab.value) }}</span>
        </button>
      </div>
    </div>

    <!-- Tableau moderne -->
    <div class="table-container">
      <div class="table-wrapper">
        <!-- Tableau "Tous" -->
        <table v-if="activeTab === 'all'" class="payments-table">
          <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Type</th>
            <th>Montant</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Classe</th>
            <th>Méthode</th>
            <th>Agent</th>
            <th>Mois</th>
            <th v-if="isAdmin"></th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="(p, i) in filteredPayments"
              :key="p.id || i"
              class="payment-row"
              :class="getRowClass(p)"
          >
            <td class="index-cell">{{ i + 1 }}</td>
            <td class="date-cell">
              <span class="date-badge">{{ d(p.paymentDate) }}</span>
            </td>
            <td class="type-cell">
                <span class="type-badge" :class="getBadgeClass(p.serviceType)">
                  {{ labelType(p.serviceType) }}
                </span>
            </td>
            <td class="amount-cell">
              <strong>{{ money(p.amountPaid) }}</strong>
            </td>
            <td class="parent-cell">{{ p.parent?.fullNameParent || '—' }}</td>
            <td class="student-cell">
              {{ p.student ? (p.student.firstName + ' ' + p.student.lastName) : '—' }}
            </td>
            <td class="class-cell">
                <span v-if="p.studyClass">
                  {{ p.studyClass.name }}
                  <span class="class-speciality">({{ p.studyClass.speciality }})</span>
                </span>
              <span v-else class="no-data">—</span>
            </td>
            <td class="method-cell">
              <span class="method-badge">{{ p.paymentType || '—' }}</span>
            </td>
            <td class="agent-cell">
              {{ p.processedBy?.fullName || (p.processedBy ? (p.processedBy.firstName + ' ' + p.processedBy.lastName) : '—') }}
            </td>
            <td class="month-cell">{{ p.month || '—' }}</td>
            <td v-if="isAdmin" class="actions-cell">
              <button class="delete-btn" @click="deletePayment(p)" title="Supprimer">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="!filteredPayments.length">
            <td :colspan="isAdmin ? 11 : 10" class="empty-state">
              <div class="empty-content">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4m0 0-4-4m4 4-4 4m0 6H6a2 2 0 0 0-2 2c0 1.1.9 2 2 2h12v-4m0 0-4-4m4 4-4 4"/>
                </svg>
                <p class="empty-title">Aucun paiement trouvé</p>
                <p class="empty-subtitle">Essayez de modifier vos filtres de recherche</p>
              </div>
            </td>
          </tr>
          </tbody>
        </table>

        <!-- Tableau Soutien -->
        <table v-if="activeTab === 'soutien'" class="payments-table">
          <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Méthode</th>
            <th>Agent</th>
            <th v-if="isAdmin"></th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="(p, i) in getFilteredByType('soutien')"
              :key="p.id || i"
              class="payment-row row-soutien"
          >
            <td class="index-cell">{{ i + 1 }}</td>
            <td class="date-cell">
              <span class="date-badge">{{ d(p.paymentDate) }}</span>
            </td>
            <td class="amount-cell">
              <strong>{{ money(p.amountPaid) }}</strong>
            </td>
            <td class="parent-cell">{{ p.parent?.fullNameParent || '—' }}</td>
            <td class="student-cell">{{ p.student?.firstName }} {{ p.student?.lastName }}</td>
            <td class="method-cell">
              <span class="method-badge">{{ p.paymentType || '—' }}</span>
            </td>
            <td class="agent-cell">
              {{ p.processedBy?.fullName || (p.processedBy ? (p.processedBy.firstName + ' ' + p.processedBy.lastName) : '—') }}
            </td>
            <td v-if="isAdmin" class="actions-cell">
              <button class="delete-btn" @click="deletePayment(p)">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </td>
          </tr>
          </tbody>
        </table>

        <!-- Tableau Arabe -->
        <table v-if="activeTab === 'arabe'" class="payments-table">
          <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Méthode</th>
            <th>Agent</th>
            <th v-if="isAdmin"></th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="(p, i) in getFilteredByType('arabe')"
              :key="p.id || i"
              class="payment-row row-arabe"
          >
            <td class="index-cell">{{ i + 1 }}</td>
            <td class="date-cell">
              <span class="date-badge">{{ d(p.paymentDate) }}</span>
            </td>
            <td class="amount-cell">
              <strong>{{ money(p.amountPaid) }}</strong>
            </td>
            <td class="parent-cell">{{ p.parent?.fullNameParent || '—' }}</td>
            <td class="student-cell">{{ p.student?.firstName }} {{ p.student?.lastName }}</td>
            <td class="method-cell">
              <span class="method-badge">{{ p.paymentType || '—' }}</span>
            </td>
            <td class="agent-cell">
              {{ p.processedBy?.fullName || (p.processedBy ? (p.processedBy.firstName + ' ' + p.processedBy.lastName) : '—') }}
            </td>
            <td v-if="isAdmin" class="actions-cell">
              <button class="delete-btn" @click="deletePayment(p)">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </td>
          </tr>
          </tbody>
        </table>

        <!-- Tableau Livres -->
        <table v-if="activeTab === 'livres'" class="payments-table">
          <thead>
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Méthode</th>
            <th>Agent</th>
            <th>Montant</th>
            <th>Articles</th>
            <th v-if="isAdmin"></th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="(p, i) in getFilteredByType('livres')"
              :key="p.id || i"
              class="payment-row row-livres"
          >
            <td class="index-cell">{{ i + 1 }}</td>
            <td class="date-cell">
              <span class="date-badge">{{ d(p.paymentDate) }}</span>
            </td>
            <td class="parent-cell">{{ p.parent?.fullNameParent || '—' }}</td>
            <td class="student-cell">{{ p.student?.firstName }} {{ p.student?.lastName }}</td>
            <td class="method-cell">
              <span class="method-badge">{{ p.paymentType || '—' }}</span>
            </td>
            <td class="agent-cell">
              {{ p.processedBy?.fullName || (p.processedBy ? (p.processedBy.firstName + ' ' + p.processedBy.lastName) : '—') }}
            </td>
            <td class="amount-cell">
              <strong>{{ money(p.amountPaid) }}</strong>
            </td>
            <td class="books-cell">
              <div v-if="(p.bookItems || []).length" class="books-list">
                <div v-for="(bi, j) in p.bookItems" :key="`bi-${i}-${j}`" class="book-item">
                  <span class="book-name">{{ bi.book?.name || 'Livre' }}</span>
                  <span class="book-quantity">× {{ bi.quantity }}</span>
                  <span class="book-total">{{ money(bi.lineTotal) }}</span>
                </div>
              </div>
              <span v-else class="no-data">—</span>
            </td>
            <td v-if="isAdmin" class="actions-cell">
              <button class="delete-btn" @click="deletePayment(p)">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal création -->
    <new-payment-modal
        :parents="parents"
        :books="books"
        @payment-added="handlePaymentAdded"
    />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";
import NewPaymentModal from "./NewPaymentModal.vue";

export default {
  name: "PaymentsDashboard",
  components: { Alert, NewPaymentModal },
  props: {
    books: { type: Array, required: true },
    currentUser: { type: Object, required: true }
  },
  data() {
    return {
      payments: [],
      parents: [],
      messageAlert: null,
      typeAlert: null,

      // Filtres
      q: "",
      typeFilter: "all",
      methodFilter: "all",
      monthFilter: "all",
      yearFilter: "all",
      agentFilter: "",
      minAmount: null,
      maxAmount: null,

      activeTab: "all",

      tabs: [
        { label: "Tous", value: "all" },
        { label: "Soutien", value: "soutien" },
        { label: "Arabe", value: "arabe" },
        { label: "Livres", value: "livres" }
      ],

      months: [
        "Janvier","Février","Mars","Avril","Mai","Juin",
        "Juillet","Août","Septembre","Octobre","Novembre","Décembre"
      ],

      paymentsPage: 1,
      paymentsHasMore: true,
      isLoading: false,
    };
  },
  computed: {
    paymentMethods() {
      const set = new Set(this.payments.map(p => p.paymentType).filter(Boolean));
      return Array.from(set);
    },
    yearsAvailable() {
      const set = new Set(
          this.payments
              .map(p => this.safeYear(p.paymentDate))
              .filter(Boolean)
      );
      return Array.from(set).sort((a,b)=>a-b);
    },
    filteredPayments() {
      const s = this.q.toLowerCase();
      const agentS = this.agentFilter.toLowerCase();

      return this.payments.filter(p => {
        const hay = [
          p.parent?.fullNameParent,
          p.paymentType,
          p.student?.firstName, p.student?.lastName,
          p.studyClass?.name, p.studyClass?.speciality,
          p.processedBy?.fullName,
          p.processedBy?.firstName,
          p.processedBy?.lastName,
        ]
            .map(x => (x || "").toString().toLowerCase())
            .join(" ");

        if (s && !hay.includes(s)) return false;

        if (agentS) {
          const agentHay = [
            p.processedBy?.fullName,
            p.processedBy?.firstName,
            p.processedBy?.lastName,
          ]
              .map(x => (x || "").toString().toLowerCase())
              .join(" ");
          if (!agentHay.includes(agentS)) return false;
        }

        if (this.typeFilter !== "all" && p.serviceType !== this.typeFilter) return false;
        if (this.methodFilter !== "all" && p.paymentType !== this.methodFilter) return false;

        const y = this.safeYear(p.paymentDate);
        if (this.yearFilter !== "all" && y !== this.yearFilter) return false;

        if (this.monthFilter !== "all") {
          const m = p.month || this.months[new Date(p.paymentDate).getMonth()] || null;
          if (m !== this.monthFilter) return false;
        }

        const amt = Number(p.amountPaid || 0);
        if (this.minAmount != null && amt < this.minAmount) return false;
        return !(this.maxAmount != null && amt > this.maxAmount);
      });
    },
    topTypeLabel() {
      if (!this.filteredPayments.length) return "—";
      const counts = this.groupBy(this.filteredPayments, p => p.serviceType || "inconnu");
      const [type] = Object.entries(counts).sort((a,b)=>b[1]-a[1])[0];
      return this.labelType(type);
    },
    isAdmin() {
      return this.currentUser?.roles?.includes("ROLE_ADMIN");
    }
  },
  methods: {
    async loadAllPayments() {
      if (this.isLoading) return;

      this.isLoading = true;
      this.payments = [];
      this.parents = [];
      this.paymentsPage = 1;
      this.paymentsHasMore = true;

      try {
        while (this.paymentsHasMore) {
          const url = this.$routing.generate("all_payments") + "?page=" + this.paymentsPage;
          const { data } = await this.axios.get(url);

          const batch = data.payments || [];
          const hasMore = !!data.hasMore;

          this.payments = [...this.payments, ...batch];

          if (this.paymentsPage === 1 && Array.isArray(data.parents)) {
            this.parents = data.parents;
          }

          this.paymentsHasMore = hasMore;
          this.paymentsPage += 1;
        }
      } catch (e) {
        console.error(e);
        this.messageAlert = "Erreur lors du chargement des paiements.";
        this.typeAlert = "danger";
      } finally {
        this.isLoading = false;
      }
    },

    d(date) {
      if (!date) return "—";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    money(v) {
      const n = Number(v || 0);
      return n.toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },
    labelType(t) {
      switch (t) {
        case "soutien": return "Soutien";
        case "arabe":   return "Arabe";
        case "livres":  return "Livres";
        default:        return "—";
      }
    },
    getBadgeClass(t) {
      return {
        soutien: "badge-soutien",
        arabe: "badge-arabe",
        livres: "badge-livres"
      }[t] || "badge-default";
    },
    getRowClass(p) {
      return {
        "row-soutien": p.serviceType === "soutien",
        "row-arabe":   p.serviceType === "arabe",
        "row-livres":  p.serviceType === "livres",
      };
    },
    sumAmount(list) {
      return list.reduce((a,p)=>a + Number(p.amountPaid || 0), 0);
    },
    avgAmount(list) {
      const n = list.length || 1;
      return this.sumAmount(list) / n;
    },
    safeYear(date) {
      if (!date) return null;
      const d = new Date(date);
      return Number.isFinite(d.getTime()) ? d.getFullYear() : null;
    },
    groupBy(arr, keyFn) {
      return arr.reduce((acc, x) => {
        const k = keyFn(x);
        acc[k] = (acc[k] || 0) + 1;
        return acc;
      }, {});
    },

    resetFilters() {
      this.q = "";
      this.typeFilter = "all";
      this.methodFilter = "all";
      this.monthFilter = "all";
      this.yearFilter = "all";
      this.agentFilter = "";
      this.minAmount = null;
      this.maxAmount = null;
    },
    exportCsv() {
      const rows = [
        ["Date","Type","Montant","Parent","Élève","Classe","Spécialité","Méthode","Agent","Mois","Année"]
      ];
      this.filteredPayments.forEach(p => {
        const agentName = p.processedBy?.fullName
            || [p.processedBy?.firstName, p.processedBy?.lastName].filter(Boolean).join(" ")
            || "";

        rows.push([
          this.d(p.paymentDate),
          this.labelType(p.serviceType),
          Number(p.amountPaid || 0).toFixed(2).replace('.',','),
          p.parent?.fullNameParent || "",
          p.student ? `${p.student.firstName||""} ${p.student.lastName||""}`.trim() : "",
          p.studyClass?.name || "",
          p.studyClass?.speciality || "",
          p.paymentType || "",
          agentName,
          p.month || "",
          this.safeYear(p.paymentDate) || ""
        ]);
      });
      const csv = rows.map(r => r.map(x => `"${String(x).replaceAll('"','""')}"`).join(";")).join("\n");
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const url  = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url; a.download = "paiements.csv"; a.click();
      URL.revokeObjectURL(url);
    },
    async deletePayment(payment) {
      if (!confirm("Supprimer ce paiement ?")) return;
      try {
        await this.axios.delete(this.$routing.generate("payment_delete", { id: payment.id }));
        await this.loadAllPayments();
        this.messageAlert = "Paiement supprimé avec succès.";
        this.typeAlert = "success";
      } catch (e) {
        console.error(e);
        this.messageAlert = "Erreur lors de la suppression.";
        this.typeAlert = "danger";
      }
    },
    handlePaymentAdded() {
      this.loadAllPayments();
      this.messageAlert = "Paiement enregistré avec succès !";
      this.typeAlert = "success";
    },
    getTabCount(tabValue) {
      if (tabValue === 'all') return this.filteredPayments.length;
      return this.filteredPayments.filter(p => p.serviceType === tabValue).length;
    },
    getFilteredByType(type) {
      return this.filteredPayments.filter(p => p.serviceType === type);
    }
  },
  mounted() {
    this.loadAllPayments();
  }
};
</script>

<style scoped>
/* Layout général */
.payments-page {
  padding: 0.5rem;
  min-height: 100vh;
  margin: 0 auto;
}

/* Loader moderne */
.loader-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}

.loader-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 9999px;
  background: rgba(15, 23, 42, 0.9);
  color: #e2e8f0;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(6px);
}

.loader-spinner {
  width: 16px;
  height: 16px;
  border-radius: 9999px;
  border: 2px solid rgba(148, 163, 184, 0.5);
  border-top-color: #a855f7;
  border-right-color: #6366f1;
  animation: loader-spin 0.7s linear infinite;
}

.loader-text {
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.02em;
}

@keyframes loader-spin {
  to { transform: rotate(360deg); }
}

/* En-tête */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  gap: 2rem;
  flex-wrap: wrap;
}

.header-content {
  flex: 1;
  min-width: 300px;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1a202c;
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  color: #64748b;
  margin: 0;
  line-height: 1.6;
}

.add-payment-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.add-payment-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
}

/* KPIs modernes */
.kpi-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: white;
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.gradient-purple::before {
  background: linear-gradient(90deg, #a855f7 0%, #ec4899 100%);
}

.gradient-blue::before {
  background: linear-gradient(90deg, #3b82f6 0%, #06b6d4 100%);
}

.gradient-green::before {
  background: linear-gradient(90deg, #10b981 0%, #059669 100%);
}

.gradient-orange::before {
  background: linear-gradient(90deg, #f59e0b 0%, #ef4444 100%);
}

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.gradient-purple .kpi-icon {
  background: linear-gradient(135deg, #fae8ff 0%, #f5d0fe 100%);
  color: #a855f7;
}

.gradient-blue .kpi-icon {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #3b82f6;
}

.gradient-green .kpi-icon {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #10b981;
}

.gradient-orange .kpi-icon {
  background: linear-gradient(135deg, #fed7aa 0%, #fca5a5 100%);
  color: #f59e0b;
}

.kpi-content {
  flex: 1;
}

.kpi-label {
  font-size: 0.813rem;
  color: #64748b;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.kpi-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.kpi-footer {
  font-size: 0.813rem;
  color: #94a3b8;
}

/* Recherche */
.search-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.search-container {
  display: flex;
  align-items: center;
  gap: 1.5rem;
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
  width: 20px;
  height: 20px;
  color: #94a3b8;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-results {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
  color: #64748b;
}

.result-count {
  font-size: 1.5rem;
  font-weight: 700;
  color: #667eea;
}

.result-label {
  font-size: 0.875rem;
}

/* Filtres */
.filters-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
}

.filter-select,
.filter-input {
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.938rem;
  background: white;
  transition: all 0.2s;
}

.filter-select {
  cursor: pointer;
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filters-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
}

.clear-filters-btn,
.export-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-filters-btn {
  background: #f1f5f9;
  color: #475569;
}

.clear-filters-btn:hover {
  background: #e2e8f0;
}

.export-btn {
  background: #667eea;
  color: white;
}

.export-btn:hover {
  background: #5568d3;
  transform: translateY(-1px);
}

/* Onglets */
.tabs-section {
  margin-bottom: 1.5rem;
}

.tabs-wrapper {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 10px;
  background: white;
  color: #64748b;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tab-button:hover {
  background: #f8fafc;
}

.tab-button.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
}

.tab-count {
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.tab-button:not(.active) .tab-count {
  background: #f1f5f9;
  color: #475569;
}

.tab-button.active .tab-count {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

/* Tableau */
.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-wrapper {
  overflow-x: auto;
}

.payments-table {
  width: 100%;
  border-collapse: collapse;
}

.payments-table thead {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}

.payments-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.813rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

.payment-row {
  border-bottom: 1px solid #f1f5f9;
  transition: background-color 0.2s;
}

.payment-row:hover {
  background-color: #f8fafc;
}

.row-soutien {
  background: linear-gradient(90deg, #eff6ff 0%, #fff 10%);
}

.row-arabe {
  background: linear-gradient(90deg, #fffbeb 0%, #fff 10%);
}

.row-livres {
  background: linear-gradient(90deg, #f0fdf4 0%, #fff 10%);
}

.payments-table td {
  padding: 1rem;
  font-size: 0.938rem;
  color: #334155;
  vertical-align: middle;
}

.index-cell {
  color: #94a3b8;
  font-weight: 500;
}

.date-badge {
  display: inline-block;
  padding: 0.375rem 0.75rem;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
}

.type-badge {
  display: inline-block;
  padding: 0.375rem 0.875rem;
  border-radius: 6px;
  font-size: 0.813rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-soutien {
  background: #dbeafe;
  color: #1e40af;
}

.badge-arabe {
  background: #fef3c7;
  color: #92400e;
}

.badge-livres {
  background: #d1fae5;
  color: #065f46;
}

.badge-default {
  background: #f1f5f9;
  color: #475569;
}

.amount-cell {
  font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
  white-space: nowrap;
}

.class-speciality {
  color: #94a3b8;
  font-size: 0.875rem;
}

.method-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  background: #e0e7ff;
  color: #4338ca;
  border-radius: 4px;
  font-size: 0.813rem;
  font-weight: 500;
}

.no-data {
  color: #cbd5e1;
}

.books-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.book-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: #f8fafc;
  border-radius: 6px;
  font-size: 0.813rem;
}

.book-name {
  flex: 1;
  color: #475569;
}

.book-quantity {
  color: #94a3b8;
  font-weight: 500;
}

.book-total {
  color: #1e293b;
  font-weight: 600;
}

.delete-btn {
  padding: 0.5rem;
  background: #fee2e2;
  border: none;
  border-radius: 6px;
  color: #dc2626;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-btn:hover {
  background: #fecaca;
  transform: translateY(-1px);
}

.empty-state {
  padding: 4rem 2rem !important;
  text-align: center;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.empty-content svg {
  color: #cbd5e1;
}

.empty-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #475569;
  margin: 0;
}

.empty-subtitle {
  color: #94a3b8;
  margin: 0;
}

/* Responsive */
@media (max-width: 1200px) {
  .filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .payments-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
  }

  .kpi-section {
    grid-template-columns: 1fr;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .search-container {
    flex-direction: column;
    align-items: stretch;
  }

  .tabs-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .payments-table {
    font-size: 0.875rem;
  }

  .payments-table th,
  .payments-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>