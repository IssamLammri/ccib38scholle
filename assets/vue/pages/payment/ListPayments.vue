<template>
  <div>
    <div class="page-title">
      <h1>Paiements</h1>
    </div>
    <div class="col-md-12 mb-5">
      <em>
        Retrouvez ici tous les paiements effectués. Vous pouvez consulter les détails ou les gérer.
      </em>
      <br>
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
              placeholder="Nom du parent, Nom d'élève, Type de paiement"
              v-model="globalPaymentSearchInput"
              style="width: 450px"
          />
        </div>
        <div class="search-result-counter ms-3">
          <span>{{ countResult }} paiements trouvés</span>
        </div>
      </div>

      <div>
        <div
            class="d-flex align-items-center btn btn-primary"
            role="button"
            data-bs-auto-close="outside"
            data-bs-toggle="modal"
            data-bs-target="#newPaiementsModal"
        >
          <img src="/static/icons/new_icon.svg" alt="" />
          <span class="d-sm-none d-md-block ms-2">Ajouter un paiement</span>
        </div>
      </div>
    </div>

    <new-payment-modal
        :parents="parents"
        @payment-added="handlePaymentAdded"
    ></new-payment-modal>

    <section>
      <h2>Paiements - Soutien scolaire</h2>
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Montant</th>
          <th>Parent</th>
          <th>Élève</th>
          <th>Classe</th>
          <th>Spécialité</th>
          <th>Méthode</th>
          <th>Mois</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(payment, index) in filteredSoutienPayments" :key="'soutien-' + index">
          <td>{{ index + 1 }}</td>
          <td>{{ formatDate(payment.paymentDate) }}</td>
          <td>{{ formatCurrency(payment.amountPaid) }}</td>
          <td>{{ payment.parent.fullNameParent }}</td>
          <td>{{ payment.student.firstName }} {{ payment.student.lastName }}</td>
          <td>{{ payment.studyClass?.name || 'N/A' }}</td>
          <td>{{ payment.studyClass?.speciality || 'N/A' }}</td>
          <td>{{ payment.paymentType }}</td>
          <td>{{ payment.month || 'N/A' }}</td>
          <td>
            <button class="btn btn-danger btn-sm" @click="deletePayment(payment)">Supprimer</button>
          </td>
        </tr>
        </tbody>
      </table>
    </section>

    <section>
      <h2>Paiements - Arabe</h2>
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Montant</th>
          <th>Parent</th>
          <th>Élève</th>
          <th>Méthode</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(payment, index) in filteredArabPayments" :key="'arabe-' + index">
          <td>{{ index + 1 }}</td>
          <td>{{ formatDate(payment.paymentDate) }}</td>
          <td>{{ formatCurrency(payment.amountPaid) }}</td>
          <td>{{ payment.parent.fullNameParent }}</td>
          <td>{{ payment.student.firstName }} {{ payment.student.lastName }}</td>
          <td>{{ payment.paymentType }}</td>
          <td>
            <button class="btn btn-danger btn-sm" @click="deletePayment(payment)">Supprimer</button>
          </td>
        </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>


<script>
import Alert from "../../ui/Alert.vue";
import NewPaymentModal from "./NewPaymentModal.vue";

export default {
  name: "ListPayments",
  components: {
    Alert,
    NewPaymentModal,
  },
  data() {
    return {
      payments: [],
      parents: [],
      globalPaymentSearchInput: "",
      countResult: 0,
      messageAlert: null,
      typeAlert: null,
      modalNewPayment: null,
    };
  },
  computed: {
    filteredSoutienPayments() {
      return this.payments
          .filter(payment => payment.serviceType === 'soutien') // Filtrer les paiements de type "soutien"
          .filter(payment => this.matchesSearch(payment)); // Appliquer la recherche
    },
    filteredArabPayments() {
      return this.payments
          .filter(payment => payment.serviceType === 'arabe') // Filtrer les paiements de type "arabe"
          .filter(payment => this.matchesSearch(payment)); // Appliquer la recherche
    },
  },
  methods: {
    matchesSearch(payment) {
      if (!this.globalPaymentSearchInput) return true; // Aucun filtre si la barre de recherche est vide
      const searchTerm = this.globalPaymentSearchInput.toLowerCase();

      return (
          payment.parent.fullNameParent.toLowerCase().includes(searchTerm) ||
          payment.paymentType.toLowerCase().includes(searchTerm) ||
          (payment.student.firstName && payment.student.firstName.toLowerCase().includes(searchTerm)) ||
          (payment.student.lastName && payment.student.lastName.toLowerCase().includes(searchTerm))
      );
    },
    async fetchPayments() {
      this.axios
          .get(this.$routing.generate("all_payments"))
          .then((response) => {
            this.payments = response.data.payments;
            this.parents = response.data.parents;
          })
          .catch((error) => {
            console.error("Erreur lors de la récupération des paiements :", error);
          });
    },
    handlePaymentAdded() {
      this.fetchPayments();
      this.messageAlert = "Paiement enregistré avec succès !";
      this.typeAlert = "success";
      this.modalNewPayment.hide();
      console.log("Payment added");
    },
    formatDate(date) {
      if (!date) return "";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    formatCurrency(amount) {
      if (amount === undefined || amount === null) return "";
      return amount.toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },
    deletePayment(payment) {
      if (confirm("Êtes-vous sûr de vouloir supprimer ce paiement ?")) {
        this.axios
            .delete(this.$routing.generate("payment_delete", { id: payment.id }))
            .then(() => {
              this.fetchPayments();
              this.messageAlert = "Paiement supprimé avec succès.";
              this.typeAlert = "success";
            })
            .catch((error) => {
              console.error("Erreur lors de la suppression :", error);
              this.messageAlert = "Erreur lors de la suppression.";
              this.typeAlert = "danger";
            });
      }
    }
  },
  mounted() {
    this.fetchPayments();
     this.modalNewPayment = new this.$bootstrap.Modal(document.getElementById("newPaiementsModal"));
  },
};
</script>

<style scoped>
.page-title h1 {
  margin-bottom: 20px;
}

.search-payments-counter {
  margin-left: 10px;
}
</style>
