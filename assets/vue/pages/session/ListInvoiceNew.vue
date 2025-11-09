<template>
  <div class="invoice-page">
    <!-- En-tête avec titre et résumé -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">Factures</h1>
        <p class="page-subtitle">
          Retrouvez ici toutes les factures, ainsi que les informations associées, comme le nom du parent et les paiements liés.
        </p>
      </div>

      <div v-if="isAdmin" class="total-card">
        <div class="total-label">Total Montant Net</div>
        <div class="total-amount">{{ formatCurrency(totalNet) }}</div>
      </div>
    </div>

    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="typeAlert"
        class="mb-4"
    />

    <!-- Barre de recherche améliorée -->
    <div class="search-section">
      <div class="search-container">
        <div class="search-input-wrapper">
          <img src="/static/icons/search.svg" alt="Rechercher" class="search-icon" />
          <input
              type="text"
              class="search-input"
              placeholder="Rechercher par nom de parent, élève, date..."
              v-model="globalPaymentSearchInput"
          />
        </div>
        <div class="search-results">
          <span class="result-count">{{ countResult }}</span>
          <span class="result-label">facture{{ countResult > 1 ? 's' : '' }} trouvée{{ countResult > 1 ? 's' : '' }}</span>
        </div>
      </div>
    </div>

    <!-- Filtres améliorés -->
    <div class="filters-section">
      <div class="filters-grid">
        <div class="filter-group">
          <label class="filter-label">Mois</label>
          <select class="filter-select" v-model="selectedMonth">
            <option value="">Tous les mois</option>
            <option v-for="(mois, index) in months" :key="index" :value="mois.value">
              {{ mois.text }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Année</label>
          <select class="filter-select" v-model="selectedYear">
            <option value="">Toutes les années</option>
            <option v-for="year in years" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Type de paiement</label>
          <select class="filter-select" v-model="selectedPaymentType">
            <option value="">Tous les types</option>
            <option v-for="pt in paymentTypes" :key="pt" :value="pt">
              {{ pt }}
            </option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label">Type de service</label>
          <select class="filter-select" v-model="selectedServiceType">
            <option value="">Tous les services</option>
            <option v-for="st in serviceTypes" :key="st" :value="st">
              {{ st }}
            </option>
          </select>
        </div>
      </div>

      <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="clear-filters-btn"
      >
        Réinitialiser les filtres
      </button>
    </div>

    <!-- Tableau responsive amélioré -->
    <div class="table-container">
      <div class="table-wrapper">
        <table class="invoices-table">
          <thead>
          <tr>
            <th>Date</th>
            <th>Montant Total</th>
            <th>Réduction</th>
            <th>Montant Net</th>
            <th>Parent</th>
            <th>Commentaire</th>
            <th>Paiements</th>
            <th v-if="isAdmin" class="actions-column">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="invoice in filteredInvoices"
              :key="invoice.id"
              class="invoice-row"
              @click="goToInvoice(invoice)"
          >
            <td class="date-cell">
              <span class="date-badge">{{ formatDate(invoice.invoiceDate) }}</span>
            </td>
            <td class="amount-cell">{{ formatCurrency(invoice.totalAmount) }}</td>
            <td class="discount-cell">
                <span v-if="invoice.discount > 0" class="discount-badge">
                  -{{ formatCurrency(invoice.discount) }}
                </span>
              <span v-else class="no-discount">-</span>
            </td>
            <td class="net-amount-cell">
              <strong>{{ formatCurrency(invoice.totalAmount - invoice.discount) }}</strong>
            </td>
            <td class="parent-cell">
              {{ invoice.parent.fullNameParent || "Non renseigné" }}
            </td>
            <td class="comment-cell">
                <span v-if="invoice.comment" class="comment-text">
                  {{ invoice.comment }}
                </span>
              <span v-else class="no-data">-</span>
            </td>
            <td class="payments-cell" @click.stop>
              <div class="payments-list">
                <div
                    v-for="(payment, index) in invoice.payments"
                    :key="index"
                    class="payment-item"
                >
                  <div class="payment-student">
                    <strong>{{ payment.student.fullName || "Non renseigné" }}</strong>
                    <span class="payment-details">
                        {{ payment.studyClass?.speciality || "Non renseignée" }} •
                        {{ payment.month || "Non renseigné" }}
                      </span>
                  </div>
                  <div class="payment-types">
                    <span class="type-badge payment-type">{{ payment.paymentType || "N/A" }}</span>
                    <span class="type-badge service-type">{{ payment.serviceType || "N/A" }}</span>
                  </div>
                </div>
              </div>
            </td>
            <td v-if="isAdmin" class="actions-cell" @click.stop>
              <button
                  class="delete-btn"
                  @click="deleteInvoice(invoice)"
                  title="Supprimer la facture"
              >
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4h12M5.333 4V2.667a1.333 1.333 0 0 1 1.334-1.334h2.666a1.333 1.333 0 0 1 1.334 1.334V4m2 0v9.333a1.333 1.333 0 0 1-1.334 1.334H4.667a1.333 1.333 0 0 1-1.334-1.334V4h9.334Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </td>
          </tr>
          <tr v-if="filteredInvoices.length === 0">
            <td :colspan="isAdmin ? 8 : 7" class="empty-state">
              <div class="empty-content">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M9 12h6M9 16h6M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                  <path d="M13 2v7h7"/>
                </svg>
                <p class="empty-title">Aucune facture trouvée</p>
                <p class="empty-subtitle">Essayez de modifier vos filtres de recherche</p>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "ListInvoice",
  components: { Alert },
  props: {
    isAdmin: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      invoices: [],
      globalPaymentSearchInput: "",
      countResult: 0,
      messageAlert: null,
      typeAlert: null,

      selectedMonth: "",
      selectedYear: "",
      months: [
        { text: "Janvier", value: 1 },
        { text: "Février", value: 2 },
        { text: "Mars", value: 3 },
        { text: "Avril", value: 4 },
        { text: "Mai", value: 5 },
        { text: "Juin", value: 6 },
        { text: "Juillet", value: 7 },
        { text: "Août", value: 8 },
        { text: "Septembre", value: 9 },
        { text: "Octobre", value: 10 },
        { text: "Novembre", value: 11 },
        { text: "Décembre", value: 12 },
      ],
      years: [],

      selectedPaymentType: "",
      selectedServiceType: "",
      paymentTypes: ["Espèces", "Carte bancaire", "Chèque"],
      serviceTypes: ["soutien", "arabe"],
    };
  },
  computed: {
    filteredInvoices() {
      const searchInput = this.globalPaymentSearchInput.toLowerCase();

      let filtered = this.invoices.filter((invoice) => {
        const dateStr = this.formatDate(invoice.invoiceDate);
        return (
            invoice.id.toString().includes(searchInput) ||
            (invoice.parent &&
                invoice.parent.fullNameParent &&
                invoice.parent.fullNameParent.toLowerCase().includes(searchInput)) ||
            dateStr.includes(searchInput) ||
            (invoice.comment && invoice.comment.toLowerCase().includes(searchInput))
        );
      });

      if (this.selectedMonth || this.selectedYear) {
        filtered = filtered.filter((invoice) => {
          const d = new Date(invoice.invoiceDate);
          const invoiceMonth = d.getMonth() + 1;
          const invoiceYear = d.getFullYear();

          const matchMonth =
              !this.selectedMonth ||
              invoiceMonth === parseInt(this.selectedMonth, 10);
          const matchYear =
              !this.selectedYear ||
              invoiceYear === parseInt(this.selectedYear, 10);

          return matchMonth && matchYear;
        });
      }

      if (this.selectedPaymentType) {
        filtered = filtered.filter(
            (invoice) =>
                Array.isArray(invoice.payments) &&
                invoice.payments.some((p) =>
                    this.eqci(p.paymentType, this.selectedPaymentType)
                )
        );
      }

      if (this.selectedServiceType) {
        filtered = filtered.filter(
            (invoice) =>
                Array.isArray(invoice.payments) &&
                invoice.payments.some((p) =>
                    this.eqci(p.serviceType, this.selectedServiceType)
                )
        );
      }

      this.countResult = filtered.length;
      return filtered;
    },

    totalNet() {
      return this.filteredInvoices.reduce((acc, invoice) => {
        return acc + (Number(invoice.totalAmount) - Number(invoice.discount));
      }, 0);
    },

    hasActiveFilters() {
      return this.selectedMonth || this.selectedYear ||
          this.selectedPaymentType || this.selectedServiceType ||
          this.globalPaymentSearchInput;
    }
  },
  methods: {
    async fetchInvoices() {
      this.axios
          .get(this.$routing.generate("all_invoices"))
          .then((response) => {
            const mapPayment = (t) => {
              if (!t) return t;
              const s = String(t).toLowerCase();
              if (s.includes("esp")) return "Espèces";
              if (s.includes("carte")) return "Carte bancaire";
              if (s.includes("ch")) return "Chèque";
              return t;
            };

            const mapService = (t) => {
              if (!t) return t;
              const s = String(t).toLowerCase();
              if (s.includes("souti")) return "soutien";
              if (s.includes("arab")) return "arabe";
              return t;
            };

            this.invoices = (response.data.allInvoices || []).map((inv) => ({
              ...inv,
              payments: (inv.payments || []).map((p) => ({
                ...p,
                paymentType: mapPayment(p.paymentType),
                serviceType: mapService(p.serviceType),
              })),
            }));

            this.setupYears();
          })
          .catch((error) => {
            console.error("Erreur lors de la récupération des factures :", error);
          });
    },

    goToInvoice(invoice) {
      const url = this.$routing.generate("app_invoice_show", {
        id: invoice.id,
      });
      window.open(url, "_blank");
    },

    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleDateString("fr-FR");
    },

    formatCurrency(amount) {
      if (amount === undefined || amount === null) return "0,00 €";
      return Number(amount).toLocaleString("fr-FR", {
        style: "currency",
        currency: "EUR",
      });
    },

    deleteInvoice(invoice) {
      if (confirm("Êtes-vous sûr de vouloir supprimer cette facture ?")) {
        this.axios
            .delete(this.$routing.generate("invoice_delete", { id: invoice.id }))
            .then(() => {
              this.fetchInvoices();
              this.messageAlert = "Facture supprimée avec succès.";
              this.typeAlert = "success";
            })
            .catch((error) => {
              console.error("Erreur lors de la suppression :", error);
              this.messageAlert = "Erreur lors de la suppression.";
              this.typeAlert = "danger";
            });
      }
    },

    setupYears() {
      const yearsSet = new Set();
      this.invoices.forEach((inv) => {
        const invYear = new Date(inv.invoiceDate).getFullYear();
        if (!Number.isNaN(invYear)) yearsSet.add(invYear);
      });
      this.years = Array.from(yearsSet).sort((a, b) => b - a);
    },

    eqci(a, b) {
      if (a == null || b == null) return false;
      return String(a).localeCompare(String(b), "fr", { sensitivity: "base" }) === 0;
    },

    clearFilters() {
      this.selectedMonth = "";
      this.selectedYear = "";
      this.selectedPaymentType = "";
      this.selectedServiceType = "";
      this.globalPaymentSearchInput = "";
    }
  },
  mounted() {
    this.fetchInvoices();
  },
};
</script>

<style scoped>
.invoice-page {
  padding: 2rem;
  background: #f8f9fa;
  min-height: 100vh;
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

.total-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.5rem 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(102, 126, 234, 0.2);
  min-width: 200px;
}

.total-label {
  font-size: 0.875rem;
  opacity: 0.9;
  margin-bottom: 0.5rem;
}

.total-amount {
  font-size: 1.75rem;
  font-weight: 700;
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
  opacity: 0.5;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
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

.filter-select {
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.938rem;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.clear-filters-btn {
  padding: 0.625rem 1.25rem;
  background: #f1f5f9;
  border: none;
  border-radius: 8px;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-filters-btn:hover {
  background: #e2e8f0;
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

.invoices-table {
  width: 100%;
  border-collapse: collapse;
}

.invoices-table thead {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}

.invoices-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.813rem;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.invoice-row {
  border-bottom: 1px solid #f1f5f9;
  cursor: pointer;
  transition: background-color 0.2s;
}

.invoice-row:hover {
  background-color: #f8fafc;
}

.invoices-table td {
  padding: 1rem;
  font-size: 0.938rem;
  color: #334155;
  vertical-align: top;
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

.amount-cell,
.discount-cell,
.net-amount-cell {
  font-family: 'SF Mono', 'Monaco', 'Courier New', monospace;
  white-space: nowrap;
}

.discount-badge {
  display: inline-block;
  padding: 0.25rem 0.625rem;
  background: #fef3c7;
  color: #92400e;
  border-radius: 6px;
  font-size: 0.813rem;
  font-weight: 600;
}

.no-discount,
.no-data {
  color: #94a3b8;
}

.comment-text {
  display: block;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.payments-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.payment-item {
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 3px solid #667eea;
}

.payment-student {
  margin-bottom: 0.5rem;
}

.payment-student strong {
  display: block;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.payment-details {
  font-size: 0.813rem;
  color: #64748b;
}

.payment-types {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.type-badge {
  padding: 0.25rem 0.625rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.payment-type {
  background: #dbeafe;
  color: #1e40af;
}

.service-type {
  background: #e0e7ff;
  color: #4338ca;
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
  .invoice-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .search-container {
    flex-direction: column;
    align-items: stretch;
  }

  .invoices-table {
    font-size: 0.875rem;
  }

  .invoices-table th,
  .invoices-table td {
    padding: 0.75rem 0.5rem;
  }
}
</style>