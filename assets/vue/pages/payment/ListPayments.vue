<!-- ListPayments.vue -->
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
        <img src="/static/icons/search.svg" alt="Search Icon">
      </span>
          <input
              type="text"
              class="form-control"
              placeholder="Rechercher les sessions du jour et à venir"
              v-model="globalPaymentSearchInput"
              @input="searchPaymentsUpdated"
              style="width: 450px"
          />
        </div>
        <div class="search-result-counter ms-3">
          <span>{{ countResult }} paiements trouvés</span>
        </div>
      </div>

      <div>
        <div
            class="d-flex align-items-center"
            role="button"
            data-bs-auto-close="outside"
            data-bs-toggle="modal"
            data-bs-target="#newPaiementsModal"
        >
          <img
              src="/static/icons/new_icon.svg"
              alt=""
          >
          <span class="d-sm-none d-md-block ms-2">Ajouter un paiement</span>
        </div>
      </div>
    </div>
    <new-payment-modal
        :parents="parents">
    </new-payment-modal>

    <section>
      <table class="table table-striped table-hover">
        <thead>
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Montant</th>
          <th>Payeur</th>
          <th>Méthode</th>
          <!-- Vous pouvez ajouter d'autres colonnes si nécessaire -->
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(payment, index) in filteredPayments" :key="payment.id">
          <td>{{ index + 1 }}</td>
          <td>{{ formatDate(payment.paymentDate) }}</td>
          <td>{{ formatCurrency(payment.amountPaid) }}</td>
          <td>{{ payment.parent.fullNameParent }}</td>
          <td>{{ payment.paymentType }}</td>
          <td>
            <!-- Actions comme voir, éditer, supprimer -->
            <button class="btn btn-primary btn-sm" @click="viewPayment(payment)">Voir</button>
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
  name: 'ListPayments',
  components: {
    Alert,
    NewPaymentModal
  },
  props: {
    payments: {
      type: Array,
      required: true
    },
    parents: {
      type: Array,
      required: true
    }
  },
  data () {
    return {
      globalPaymentSearchInput: '',
      countResult: 0,
      messageAlert: null,
      typeAlert: null
    }
  },
  computed: {
    filteredPayments() {
      if (this.globalPaymentSearchInput) {
        const searchTerm = this.globalPaymentSearchInput.toLowerCase();
        const filtered = this.payments.filter(payment => {
          return (
              payment.parent.fullNameParent.toLowerCase().includes(searchTerm) ||
              payment.paymentType.toLowerCase().includes(searchTerm) ||
              payment.student.firstName.toLowerCase().includes(searchTerm) ||
              payment.student.lastName.toLowerCase().includes(searchTerm)
          );
        });
        this.countResult = filtered.length;
        return filtered;
      } else {
        this.countResult = this.payments.length;
        return this.payments;
      }
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return '';
      // Formater la date selon vos besoins
      return new Date(date).toLocaleDateString('fr-FR');
    },
    formatCurrency(amount) {
      if (amount === undefined || amount === null) return '';
      // Formater la monnaie selon vos besoins
      return amount.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
    },
    viewPayment(payment) {
      // Implémenter la fonction pour voir les détails du paiement
      // Par exemple, naviguer vers une page de détails du paiement
      // this.$router.push({ name: 'PaymentDetails', params: { id: payment.id } });
      alert(`Voir les détails du paiement ID: ${payment.id}`);
    },
    deletePayment(payment) {
      // Implémenter la fonction pour supprimer le paiement
      if (confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
        this.$emit('payment-deleted', payment.id);
      }
    },
    searchPaymentsUpdated() {
      // La mise à jour de countResult est gérée dans la propriété calculée
    }
  }
}
</script>

<style scoped>
.page-title h1 {
  margin-bottom: 20px;
}
.search-payments-counter {
  margin-left: 10px;
}
</style>
