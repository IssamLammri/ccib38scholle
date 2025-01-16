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

    <!-- Tableau des factures -->
    <div class="mt-4">
      <table class="table table-striped table-hover">
        <thead>
        <tr>
          <th>Date de Facture</th>
          <th>Montant Total</th>
          <th>Réduction</th>
          <th>Nom du Parent</th>
          <th>Commentaire</th>
          <th>Paiements Associés</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="invoice in filteredInvoices" :key="invoice.id">
          <td role="button" @click="goToInvoice(invoice)" >{{ formatDate(invoice.invoiceDate) }}</td>
          <td role="button" @click="goToInvoice(invoice)">{{ formatCurrency(invoice.totalAmount) }}</td>
          <td role="button" @click="goToInvoice(invoice)" >{{ formatCurrency(invoice.discount) }}</td>
          <td role="button" @click="goToInvoice(invoice)">{{ invoice.parent.fullNameParent || "Non renseigné" }}</td>
          <td>{{ invoice.comment || "N/A" }}</td>
          <td>
            <ul>
              <li v-for="(payment, index) in invoice.payments" :key="index">
                <strong>Élève :</strong> {{ payment.student.fullName || "Non renseigné" }} ({{ payment.studyClass?.speciality || "Non renseignée" }} ) / ({{ payment.month || "Non renseigné" }}) <br />
              </li>
            </ul>
          </td>
          <td>
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
  data() {
    return {
      invoices: [],
      globalPaymentSearchInput: "",
      countResult: 0,
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    filteredInvoices() {
      const searchInput = this.globalPaymentSearchInput.toLowerCase();
      const filtered = this.invoices.filter(
          (invoice) =>
              invoice.id.toString().includes(searchInput) ||
              (invoice.parent &&
                  invoice.parent.fullNameParent &&
                  invoice.parent.fullNameParent.toLowerCase().includes(searchInput)) ||
              this.formatDate(invoice.invoiceDate).includes(searchInput) ||
              (invoice.comment && invoice.comment.toLowerCase().includes(searchInput))
      );
      this.countResult = filtered.length;
      return filtered;
    },
  },
  methods: {
    async fetchInvoices() {
      this.axios
          .get(this.$routing.generate("all_invoices"))
          .then((response) => {
            this.invoices = response.data.allInvoices;
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
</style>
