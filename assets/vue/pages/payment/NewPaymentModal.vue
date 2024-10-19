<!-- ListPayments.vue -->
<template>
  <div class="modal fade" id="newPaiementsModal" tabindex="-1" aria-labelledby="newPaiementsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="newPaiementsModalLabel">Ajouter un paiement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="newPaymentForm">
            <!-- Sélection du parent -->
            <div class="mb-3">
              <label for="parentSelect" class="form-label">Sélectionner un parent</label>
              <select v-model="selectedParent" class="form-select" id="parentSelect" required>
                <option v-for="parent in parents" :key="parent.id" :value="parent">{{ parent.fullNameParent }}</option>
              </select>
            </div>

            <!-- Affichage des informations du parent sélectionné -->
            <div v-if="selectedParent" class="mb-3">
              <h6>Informations du parent sélectionné :</h6>
              <p><strong>Nom du père :</strong> {{ selectedParent.fatherFirstName }} {{ selectedParent.fatherLastName }}</p>
              <p><strong>Email du père :</strong> {{ selectedParent.fatherEmail }}</p>
              <p><strong>Téléphone du père :</strong> {{ selectedParent.fatherPhone }}</p>
              <p><strong>Nom de la mère :</strong> {{ selectedParent.motherFirstName }} {{ selectedParent.motherLastName }}</p>
              <p><strong>Email de la mère :</strong> {{ selectedParent.motherEmail }}</p>
              <p><strong>Téléphone de la mère :</strong> {{ selectedParent.motherPhone }}</p>
            </div>

            <!-- Montant -->
            <div class="mb-3">
              <label for="amount" class="form-label">Montant</label>
              <input type="number" v-model="amount" class="form-control" id="amount" placeholder="Entrer le montant" required>
            </div>

            <!-- Choix du type de paiement -->
            <div class="mb-3">
              <label class="form-label">Type de paiement</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" v-model="paymentType" value="arab" id="arab">
                <label class="form-check-label" for="arab">Arabe</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" v-model="paymentType" value="soutien" id="soutien">
                <label class="form-check-label" for="soutien">Soutien scolaire</label>
              </div>
            </div>

            <!-- Cas Arabe : tranche de paiement -->
            <div v-if="paymentType === 'arab'" class="mb-3">
              <label for="arabTranche" class="form-label">Tranche de paiement (Arabe)</label>
              <input type="text" v-model="arabTranche" class="form-control" id="arabTranche" placeholder="Entrer la tranche de paiement">
            </div>

            <!-- Cas Soutien scolaire : mois -->
            <div v-if="paymentType === 'soutien'" class="mb-3">
              <label for="monthSelect" class="form-label">Mois de soutien scolaire</label>
              <select v-model="selectedMonth" class="form-select" id="monthSelect">
                <option disabled value="">Sélectionner un mois</option>
                <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
              </select>
            </div>

            <!-- Commentaire -->
            <div class="mb-3">
              <label for="comment" class="form-label">Commentaire</label>
              <textarea v-model="comment" class="form-control" id="comment" rows="3" placeholder="Ajouter un commentaire"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          <button type="submit" class="btn btn-primary" form="newPaymentForm" @click.prevent="submitPayment">Enregistrer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NewPaymentModal',
  props: {
    parents: {
      type: Array,
      required: true
    }
  },
  data () {
    return {
      selectedParent: null,
      amount: '',
      paymentType: '',
      arabTranche: '',
      selectedMonth: '',
      comment: '',
      months: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
    }
  },
  methods: {
    submitPayment() {
      if (this.selectedParent && this.amount && this.paymentType) {
        const paymentData = {
          parent: this.selectedParent,
          amount: this.amount,
          type: this.paymentType,
          tranche: this.paymentType === 'arab' ? this.arabTranche : null,
          month: this.paymentType === 'soutien' ? this.selectedMonth : null,
          comment: this.comment
        };
        // Logique pour soumettre le paiement ici
        console.log('Paiement soumis:', paymentData);
      } else {
        alert('Veuillez remplir tous les champs requis.');
      }
    }
  }
}
</script>

<style scoped>

</style>
