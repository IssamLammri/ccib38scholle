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

    <!-- Filtres Mois / Année -->
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
    </div>

    <!-- Tableau des factures -->
    <div class="mt-4 table-responsive">
      <table class="table table-striped table-hover">
        <thead>
        <tr>
          <th>Date de Facture</th>
          <th>Montant Total</th>
          <th>Réduction</th>
          <!-- Nouvelle colonne -->
          <th>Montant Net</th>
          <th>Nom du Parent</th>
          <th>Commentaire</th>
          <th>Paiements Associés</th>
          <th  v-if="isAdmin" >Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="invoice in filteredInvoices" :key="invoice.id">
          <td role="button" @click="goToInvoice(invoice)">{{ formatDate(invoice.invoiceDate) }}</td>
          <td role="button" @click="goToInvoice(invoice)">{{ formatCurrency(invoice.totalAmount) }}</td>
          <td role="button" @click="goToInvoice(invoice)">{{ formatCurrency(invoice.discount) }}</td>
          <!-- Montant Net = Montant Total - Réduction -->
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
              </li>
            </ul>
          </td>
          <td  v-if="isAdmin">
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
  components: {
    Alert,
  },
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
    };
  },
  computed: {
    filteredInvoices() {
      const searchInput = this.globalPaymentSearchInput.toLowerCase();

      // Filtrage par recherche textuelle
      let filtered = this.invoices.filter((invoice) => {
        const dateStr = this.formatDate(invoice.invoiceDate); // Format 'jj/mm/aaaa'
        return (
            invoice.id.toString().includes(searchInput) ||
            (invoice.parent &&
                invoice.parent.fullNameParent &&
                invoice.parent.fullNameParent.toLowerCase().includes(searchInput)) ||
            dateStr.includes(searchInput) ||
            (invoice.comment && invoice.comment.toLowerCase().includes(searchInput))
        );
      });

      // Filtre par mois / année
      if (this.selectedMonth || this.selectedYear) {
        filtered = filtered.filter((invoice) => {
          const d = new Date(invoice.invoiceDate);
          const invoiceMonth = d.getMonth() + 1; // getMonth() renvoie 0 pour Janvier
          const invoiceYear = d.getFullYear();

          const matchMonth =
              !this.selectedMonth || invoiceMonth === parseInt(this.selectedMonth, 10);
          const matchYear =
              !this.selectedYear || invoiceYear === parseInt(this.selectedYear, 10);

          return matchMonth && matchYear;
        });
      }

      this.countResult = filtered.length;
      return filtered;
    },
    totalNet() {
      // On somme pour chaque facture : (totalAmount - discount)
      return this.filteredInvoices.reduce((acc, invoice) => {
        return acc + (invoice.totalAmount - invoice.discount);
      }, 0);
    },
  },
  methods: {
    async fetchInvoices() {
      this.axios
          .get(this.$routing.generate("all_invoices"))
          .then((response) => {
            this.invoices = response.data.allInvoices;
            this.setupYears(); // met à jour la liste des années
          })
          .catch((error) => {
            console.error("Erreur lors de la récupération des factures :", error);
          });
    },
    goToInvoice(invoice) {
      window.location.href = this.$routing.generate('app_invoice_show', { id: invoice.id });
    },
    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    formatCurrency(amount) {
      if (amount === undefined || amount === null) return "0,00 €";
      return parseFloat(amount).toLocaleString("fr-FR", {
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
        yearsSet.add(invYear);
      });

      // Convertit l'ensemble en tableau trié (du plus récent au plus ancien)
      this.years = Array.from(yearsSet).sort((a, b) => b - a);
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
.table th, .table td {
  word-break: break-word;
  white-space: normal;
  max-width: 200px; /* ajustable selon ton besoin */
}
</style>
