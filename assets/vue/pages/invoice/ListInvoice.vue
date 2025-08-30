<template>
  <div>
    <div class="page-title">
      <h1>Factures</h1>
    </div>

    <div class="col-md-12 mb-5">
      <em>
        Retrouvez ici toutes les factures, ainsi que les informations associées, comme le nom du parent et les paiements liés.
      </em>
      <br />
    </div>

    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="typeAlert"
    />

    <div class="mt-4">
      <h4>
        Total Montant Net :
        <span class="text-primary">
          {{ formatCurrency(totalNet) }}
        </span>
      </h4>
    </div>

    <!-- Barre de recherche -->
    <div class="search-bar d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <div class="search-input-group d-flex align-items-center">
          <span class="search-icon m-3">
            <img src="/static/icons/search.svg" alt="Search Icon" />
          </span>
          <input
              type="text"
              class="form-control"
              placeholder="Nom du parent, Nom d'élève, Date de facture"
              v-model="globalPaymentSearchInput"
              style="width: 450px"
          />
        </div>
        <div class="search-result-counter ms-3">
          <span>{{ countResult }} factures trouvées</span>
        </div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="row mt-3">
      <div class="col-md-3">
        <label for="selectMonth" class="form-label">Filtrer par mois</label>
        <select
            id="selectMonth"
            class="form-select"
            v-model="selectedMonth"
        >
          <option value="">Tous les mois</option>
          <option
              v-for="(mois, index) in months"
              :key="index"
              :value="mois.value"
          >
            {{ mois.text }}
          </option>
        </select>
      </div>

      <div class="col-md-3">
        <label for="selectYear" class="form-label">Filtrer par année</label>
        <select
            id="selectYear"
            class="form-select"
            v-model="selectedYear"
        >
          <option value="">Toutes les années</option>
          <option
              v-for="year in years"
              :key="year"
              :value="year"
          >
            {{ year }}
          </option>
        </select>
      </div>

      <!-- Nouveau : Type de paiement -->
      <div class="col-md-3">
        <label for="selectPaymentType" class="form-label">Type de paiement</label>
        <select
            id="selectPaymentType"
            class="form-select"
            v-model="selectedPaymentType"
        >
          <option value="">Tous les types</option>
          <option v-for="pt in paymentTypes" :key="pt" :value="pt">
            {{ pt }}
          </option>
        </select>
      </div>

      <!-- Nouveau : Type de service -->
      <div class="col-md-3">
        <label for="selectServiceType" class="form-label">Type de service</label>
        <select
            id="selectServiceType"
            class="form-select"
            v-model="selectedServiceType"
        >
          <option value="">Tous les services</option>
          <option v-for="st in serviceTypes" :key="st" :value="st">
            {{ st }}
          </option>
        </select>
      </div>
    </div>

    <!-- Tableau des factures -->
    <div class="mt-4 table-responsive">
      <table class="table table-striped table-hover">
        <thead>
        <tr>
          <th>Date de Facture</th>
          <th>Montant Total</th>
          <th>Réduction</th>
          <th>Montant Net</th>
          <th>Nom du Parent</th>
          <th>Commentaire</th>
          <th>Paiements Associés</th>
          <th v-if="isAdmin">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="invoice in filteredInvoices" :key="invoice.id">
          <td role="button" @click="goToInvoice(invoice)">{{ formatDate(invoice.invoiceDate) }}</td>
          <td role="button" @click="goToInvoice(invoice)">{{ formatCurrency(invoice.totalAmount) }}</td>
          <td role="button" @click="goToInvoice(invoice)">{{ formatCurrency(invoice.discount) }}</td>
          <td role="button" @click="goToInvoice(invoice)">
            {{ formatCurrency(invoice.totalAmount - invoice.discount) }}
          </td>
          <td role="button" @click="goToInvoice(invoice)">{{ invoice.parent.fullNameParent || "Non renseigné" }}</td>
          <td>{{ invoice.comment || "N/A" }}</td>
          <td>
            <ul>
              <li v-for="(payment, index) in invoice.payments" :key="index">
                <strong>Élève :</strong>
                {{ payment.student.fullName || "Non renseigné" }}
                ({{ payment.studyClass?.speciality || "Non renseignée" }}) /
                ({{ payment.month || "Non renseigné" }})
                <br />
                <small>
                  {{ payment.paymentType || "Type N/A" }} — {{ payment.serviceType || "Service N/A" }}
                </small>
              </li>
            </ul>
          </td>
          <td v-if="isAdmin">
            <button
                class="btn btn-danger btn-sm"
                @click="deleteInvoice(invoice)"
            >
              Supprimer
            </button>
          </td>
        </tr>
        <tr v-if="filteredInvoices.length === 0">
          <td colspan="8" class="text-center">Aucune facture trouvée</td>
        </tr>
        </tbody>
      </table>
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

      // Filtres mois/année
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

      // Nouveaux filtres
      selectedPaymentType: "",
      selectedServiceType: "",
      paymentTypes: ["Espèces", "Carte bancaire", "Chèque"],
      serviceTypes: ["soutien", "arabe"],
    };
  },
  computed: {
    filteredInvoices() {
      const searchInput = this.globalPaymentSearchInput.toLowerCase();

      // 1) Filtre texte (id, parent, date formatée, commentaire)
      let filtered = this.invoices.filter((invoice) => {
        const dateStr = this.formatDate(invoice.invoiceDate); // 'jj/mm/aaaa'
        return (
            invoice.id.toString().includes(searchInput) ||
            (invoice.parent &&
                invoice.parent.fullNameParent &&
                invoice.parent.fullNameParent.toLowerCase().includes(searchInput)) ||
            dateStr.includes(searchInput) ||
            (invoice.comment && invoice.comment.toLowerCase().includes(searchInput))
        );
      });

      // 2) Filtre par mois / année
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

      // 3) Filtre par type de paiement (au moins un paiement de la facture doit matcher)
      if (this.selectedPaymentType) {
        filtered = filtered.filter(
            (invoice) =>
                Array.isArray(invoice.payments) &&
                invoice.payments.some((p) =>
                    this.eqci(p.paymentType, this.selectedPaymentType)
                )
        );
      }

      // 4) Filtre par type de service (au moins un paiement doit matcher)
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

    // Somme du net sur la liste filtrée
    totalNet() {
      return this.filteredInvoices.reduce((acc, invoice) => {
        return acc + (Number(invoice.totalAmount) - Number(invoice.discount));
      }, 0);
    },
  },
  methods: {
    async fetchInvoices() {
      this.axios
          .get(this.$routing.generate("all_invoices"))
          .then((response) => {
            // Normalisation légère (optionnelle) pour éviter les variations d'orthographe
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
      window.location.href = this.$routing.generate("app_invoice_show", {
        id: invoice.id,
      });
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

    // equals case-insensitive (et sans accent)
    eqci(a, b) {
      if (a == null || b == null) return false;
      return String(a).localeCompare(String(b), "fr", { sensitivity: "base" }) === 0;
    },
  },
  mounted() {
    this.fetchInvoices();
  },
};
</script>

<style scoped>
.page-title h1 {
  margin-bottom: 20px;
}

.search-bar {
  margin-bottom: 20px;
}

.table {
  width: 100%;
  margin-top: 20px;
}

.text-center {
  text-align: center;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

/* Gestion du contenu des cellules */
.table th,
.table td {
  word-break: break-word;
  white-space: normal;
  max-width: 200px; /* ajustable selon ton besoin */
}
</style>
