<template>
  <div class="modal fade" id="newPaiementsModal" tabindex="-1" aria-labelledby="newPaiementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content shadow-lg border-0">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="newPaiementsModalLabel">Ajouter un paiement</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="newPaymentForm" class="p-3">
            <!-- Choix du type de paiement -->
            <div class="mb-4 text-center">
              <div class="btn-group d-flex justify-content-center" role="group" aria-label="Paiement pour">
                <input
                    type="radio"
                    class="btn-check"
                    id="arab"
                    v-model="paymentType"
                    value="arab"
                    autocomplete="off"
                />
                <label class="btn btn-primary btn-sm" for="arab">Arabe</label>

                <input
                    type="radio"
                    class="btn-check"
                    id="soutien"
                    v-model="paymentType"
                    value="soutien"
                    autocomplete="off"
                />
                <label class="btn btn-primary btn-sm" for="soutien">Soutien scolaire</label>
              </div>
            </div>

            <div class="mb-4">
              <label for="parentSelect" class="form-label fw-bold">Sélectionner un parent</label>
              <div class="position-relative">
                <input
                    type="text"
                    id="parentSearch"
                    class="form-control"
                    v-model="parentSearchQuery"
                    placeholder="Rechercher un parent..."
                    @focus="dropdownVisible = true"
                    @blur="hideDropdown"
                />
                <!-- Dropdown des parents -->
                <ul
                    class="list-group position-absolute w-100 mt-1"
                    v-if="dropdownVisible"
                    style="z-index: 1000; max-height: 400px; overflow-y: auto;background-color: #ffffff"
                >
                  <li
                      v-for="parent in filteredParents"
                      :key="parent.id"
                      class="list-group-item list-group-item-action"
                      @mousedown.prevent="selectParent(parent)"
                  >
                    {{ parent.fullNameParent }}
                  </li>
                  <li
                      v-if="filteredParents.length === 0"
                      class="list-group-item text-center text-muted"
                  >
                    Aucun parent trouvé
                  </li>
                </ul>
              </div>
            </div>

            <!-- Enfants et Matières -->
            <div v-if="selectedParent && selectedParent.students.length" class="mb-4">
              <h6 class="fw-bold">Sélectionner les enfants :</h6>
              <div v-for="student in selectedParent.students" :key="student.id" class="mb-3">
                <div class="form-check">
                  <input
                      type="checkbox"
                      class="form-check-input"
                      :id="'student-' + student.id"
                      :value="student.id"
                      v-model="selectedChildren"
                      @change="updateSelectedClasses(student.id, $event.target.checked)"
                  />
                  <label class="form-check-label fw-bold" :for="'student-' + student.id">
                    {{ student.firstName }} {{ student.lastName }} ({{ student.levelClass }})
                  </label>
                </div>

                <!-- Afficher les matières uniquement si paymentType est 'soutien' -->
                <ul
                    v-if="paymentType === 'soutien' && selectedChildren.includes(student.id) && student.registrations.length"
                    class="list-group list-group-flush ms-4"
                >
                  <h6 class="text-muted">Les matières dans lesquelles {{ student.firstName }} est inscrit.</h6>
                  <li
                      v-for="registration in student.registrations"
                      :key="registration.studyClass.id"
                      class="list-group-item"
                  >
                    <div class="form-check">
                      <input
                          type="checkbox"
                          class="form-check-input"
                          :id="'class-' + student.id + '-' + registration.studyClass.id"
                          :value="registration.studyClass"
                          v-model="selectedClasses[student.id]"
                          @change="calculateTotal"
                      />
                      <label class="form-check-label" :for="'class-' + student.id + '-' + registration.studyClass.id">
                        {{ registration.studyClass.speciality }}
                      </label>
                    </div>
                  </li>
                </ul>
                <ul
                    v-if="selectedChildren.includes(student.id) && student.registrations.length === 0"
                    class="list-group list-group-flush ms-4"
                >
                  <h6 class="text-muted">Aucune matière trouvée pour {{ student.firstName }}.</h6>
                </ul>
              </div>
            </div>

            <!-- Montant total calculé -->
            <div class="mb-4">
              <h6 class="fw-bold">Montant total à payer : <span class="text-primary">{{ totalAmount }} €</span></h6>
            </div>

            <!-- Montant payé -->
            <div class="mb-4">
              <label for="paidAmount" class="form-label fw-bold">Montant payé (€)</label>
              <input
                  type="number"
                  v-model="paidAmount"
                  class="form-control"
                  id="paidAmount"
                  placeholder="Entrer le montant payé"
              />
            </div>

            <!-- Remise -->
            <div class="mb-4">
              <label for="discount" class="form-label fw-bold">Remise (€)</label>
              <input
                  type="number"
                  v-model="discount"
                  class="form-control"
                  id="discount"
                  placeholder="Entrer une remise (facultatif)"
              />
            </div>

            <!-- Type de paiement -->
            <div class="mb-4">
              <label for="paymentTypeSelect" class="form-label fw-bold">Type de paiement</label>
              <select
                  v-model="paymentMethod"
                  class="form-select"
                  id="paymentTypeSelect"
              >
                <option disabled value="">Sélectionner un type de paiement</option>
                <option value="Espèces">Espèces</option>
                <option value="Carte bancaire">Carte bancaire</option>
                <option value="Chèque">Chèque</option>
              </select>
            </div>

            <!-- Cas Soutien scolaire : mois -->
            <div v-if="paymentType === 'soutien'" class="mb-3">
              <label for="monthSelect" class="form-label">Mois de soutien scolaire</label>
              <select v-model="selectedMonth" class="form-select" id="monthSelect">
                <option disabled value="">Sélectionner un mois</option>
                <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
              </select>
            </div>

            <!-- Cas Soutien scolaire : année -->
            <div v-if="paymentType === 'soutien'" class="mb-3">
              <label for="yearSelect" class="form-label">Année de soutien scolaire</label>
              <select v-model="selectedYear" class="form-select" id="yearSelect">
                <option disabled value="">Sélectionner une année</option>
                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>

            <!-- Commentaire -->
            <div class="mb-4">
              <label for="comment" class="form-label fw-bold">Commentaire</label>
              <textarea v-model="comment" class="form-control" id="comment" rows="3" placeholder="Ajouter un commentaire"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
          <button
              type="submit"
              class="btn btn-primary"
              form="newPaymentForm"
              @click.prevent="submitPayment"
              :disabled="isSubmitDisabled"
          >
            Enregistrer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>



<script>
import vSelect from "vue-next-select";
import "vue-next-select/dist/index.css";

export default {
  name: 'NewPaymentModal',
  components: { vSelect },
  props: {
    parents: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      dropdownVisible: false,
      searchQuery: "",
      selectedParent: null,
      selectedChildren: [],
      selectedClasses: {},
      totalAmount: 0,
      paidAmount: 0,
      discount: 0,
      paymentMethod: '',
      paymentType: 'soutien',
      selectedMonth: '',
      comment: '',
      parentSearchQuery: '',
      selectedYear: 2025,
      availableYears: [2023, 2024, 2025, 2026],
      months: [
        'Janvier',
        'Février',
        'Mars',
        'Avril',
        'Mai',
        'Juin',
        'Juillet',
        'Août',
        'Septembre',
        'Octobre',
        'Novembre',
        'Décembre',
      ],
    };
  },
  computed: {
    isSubmitDisabled() {
      if (this.paymentType === 'soutien') {
        return (
            !this.selectedMonth || // Mois non sélectionné
            !this.paidAmount || // Montant payé manquant ou égal à 0
            this.paidAmount <= 0 ||
            !this.paymentMethod || // Type de paiement non sélectionné
            !this.selectedChildren.length || // Aucun enfant sélectionné
            Object.keys(this.selectedClasses).length === 0
        );
      }
      return (
          !this.paidAmount ||
          this.paidAmount <= 0 ||
          !this.paymentMethod || // Type de paiement non sélectionné
          !this.selectedChildren.length ||
          Object.keys(this.selectedClasses).length === 0
      );
    },
    filteredParents() {
      // Filtrer les parents selon la recherche
      if (!this.parentSearchQuery) {
        return this.parents;
      }
      return this.parents.filter((parent) =>
          parent.fullNameParent
              .toLowerCase()
              .includes(this.parentSearchQuery.toLowerCase())
      );
    },
  },
  methods: {
    selectParent(parent) {
      this.selectedParent = parent; // Définit le parent sélectionné
      this.parentSearchQuery = parent.fullNameParent; // Remplit le champ de recherche avec le nom complet
      this.dropdownVisible = false; // Cache la liste déroulante
    },
    hideDropdown() {
      setTimeout(() => {
        this.dropdownVisible = false; // Délai pour permettre la sélection avant de cacher
      }, 200);
    },
    changeParent(newParent) {
      if (newParent) {
        this.selectedChildren = [];
        this.selectedClasses = {};
        this.totalAmount = 0;
        this.paidAmount = 0;
      }
    },
    updateSelectedClasses(studentId, isChecked) {
      if (isChecked) {
        if (!this.selectedClasses[studentId]) {
          this.selectedClasses[studentId] = [];
        }
      } else {
        delete this.selectedClasses[studentId];
        this.calculateTotal();
      }
    },
    calculateTotal() {
      this.totalAmount = 0;
      Object.values(this.selectedClasses).forEach((classes) => {
        this.totalAmount += classes.length * 25;
      });
    },
    resetForm() {
      this.selectedParent = null;
      this.selectedChildren = [];
      this.selectedClasses = {};
      this.totalAmount = 0;
      this.paidAmount = 0;
      this.discount = 0;
      this.paymentMethod = '';
      this.paymentType = 'soutien';
      this.selectedMonth = '';
      this.comment = '';
      this.parentSearchQuery = '';
    },
    submitPayment() {
      if (!this.isSubmitDisabled) {
        const paymentData = {
          parent: this.selectedParent,
          totalAmount: this.totalAmount,
          paidAmount: this.paidAmount,
          discount: this.discount,
          paymentMethod: this.paymentMethod,
          paymentType: this.paymentType,
          selectedMonth: this.selectedMonth,
          selectedYear: this.selectedYear, // Ajout de l'année
          comment: this.comment,
          selectedChildren: this.selectedChildren.map((id) => ({
            id,
            classes: this.selectedClasses[id] || [],
          })),
        };

        this.axios
            .post(this.$routing.generate("payment_new"), paymentData)
            .then(() => {
              this.$emit("payment-added");
              this.resetForm();
            })
            .catch((error) => {
              console.error("Erreur lors de l'enregistrement :", error);
              alert("Erreur lors de l'enregistrement du paiement.");
            });
      } else {
        alert("Veuillez remplir tous les champs requis.");
      }
    }
  }
};

</script>

<style scoped>
.modal-content {
  border-radius: 10px;
  overflow: hidden;
}

.modal-header {
  padding: 1.5rem;
}

.modal-body {
  background-color: #f8f9fa;
}

.btn-primary {
  background-color: #669ff3;
  border-color: #669FF3FF;
}

.btn-primary:hover {
  background-color: #5146c5;
}

.list-group-item {
  background-color: transparent;
  border: none;
}

.list-group-item .form-check-label {
  font-weight: normal;
}

.list-group-item {
  cursor: pointer;
}
.list-group-item:hover {
  background-color: #3d93ea;
}
.btn-primary:disabled {
  background-color: #d6d6d6;
  border-color: #d6d6d6;
  cursor: not-allowed;
}
</style>
