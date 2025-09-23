<template>
  <div class="modal fade" id="newPaiementsModal" tabindex="-1" aria-labelledby="newPaiementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content shadow-lg border-0">
        <!-- Header avec design moderne -->
        <div class="modal-header bg-gradient-primary text-white position-relative">
          <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
              <i class="fas fa-credit-card fa-lg"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0" id="newPaiementsModalLabel">Nouveau Paiement</h5>
              <small class="opacity-75">Enregistrer un nouveau paiement</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <form id="newPaymentForm">

            <!-- Étape 1: Type de paiement avec icônes -->
            <div class="step-section mb-5">
              <div class="step-header">
                <span class="step-number">1</span>
                <h6 class="step-title">Type de paiement</h6>
              </div>
              <div class="payment-type-grid">
                <div class="payment-type-card"
                     :class="{ active: paymentType === 'arabe' }"
                     @click="paymentType = 'arabe'">
                  <input type="radio" class="d-none" id="arabe" v-model="paymentType" value="arabe" />
                  <div class="card-icon">
                    <i class="fas fa-language"></i>
                  </div>
                  <h6 class="card-title">Cours d'Arabe</h6>
                  <p class="card-description">Paiement pour les cours de langue arabe</p>
                </div>

                <div class="payment-type-card"
                     :class="{ active: paymentType === 'soutien' }"
                     @click="paymentType = 'soutien'">
                  <input type="radio" class="d-none" id="soutien" v-model="paymentType" value="soutien" />
                  <div class="card-icon">
                    <i class="fas fa-graduation-cap"></i>
                  </div>
                  <h6 class="card-title">Soutien Scolaire</h6>
                  <p class="card-description">Paiement pour le soutien scolaire</p>
                </div>

                <div class="payment-type-card"
                     :class="{ active: paymentType === 'livres' }"
                     @click="paymentType = 'livres'">
                  <input type="radio" class="d-none" id="livres" v-model="paymentType" value="livres" />
                  <div class="card-icon">
                    <i class="fas fa-book"></i>
                  </div>
                  <h6 class="card-title">Livres</h6>
                  <p class="card-description">Achat de livres scolaires</p>
                </div>
              </div>
            </div>

            <!-- Étape 2: Sélection parent -->
            <div class="step-section mb-5" v-if="paymentType">
              <div class="step-header">
                <span class="step-number">2</span>
                <h6 class="step-title">Sélectionner un parent</h6>
              </div>
              <div class="search-container">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-light">
                    <i class="fas fa-search text-muted"></i>
                  </span>
                  <input
                      type="text"
                      class="form-control border-start-0"
                      v-model="parentSearchQuery"
                      placeholder="Tapez le nom du parent..."
                      @focus="dropdownVisible = true"
                      @blur="hideDropdown"
                      @input="onParentInput"
                  />
                </div>

                <!-- Dropdown avec design amélioré -->
                <div class="search-dropdown" v-if="dropdownVisible && parentSearchQuery">
                  <div class="dropdown-content">
                    <div v-for="p in filteredParents" :key="p.id"
                         class="dropdown-item-custom"
                         @mousedown.prevent="selectParent(p)">
                      <div class="parent-info">
                        <div class="parent-avatar">
                          <i class="fas fa-user"></i>
                        </div>
                        <div class="parent-details">
                          <div class="parent-name">{{ p.fullNameParent }}</div>
                          <div class="parent-meta">{{ p.students.length }} enfant(s)</div>
                        </div>
                      </div>
                    </div>
                    <div v-if="filteredParents.length === 0" class="no-results">
                      <i class="fas fa-search text-muted"></i>
                      <span>Aucun parent trouvé</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Parent sélectionné -->
              <div v-if="selectedParent" class="selected-parent mt-3">
                <div class="alert alert-info d-flex align-items-center">
                  <i class="fas fa-check-circle me-2"></i>
                  <span><strong>Parent sélectionné :</strong> {{ selectedParent.fullNameParent }}</span>
                </div>
              </div>
            </div>

            <!-- Étape 3: Sélection des enfants -->
            <div v-if="selectedParent && selectedParent.students.length" class="step-section mb-5">
              <div class="step-header">
                <span class="step-number">3</span>
                <h6 class="step-title">
                  {{ isBooks ? 'Choisir l\'enfant' : 'Choisir les enfants' }}
                </h6>
              </div>

              <!-- Cas livres : sélection unique -->
              <div v-if="isBooks" class="students-grid">
                <div v-for="s in selectedParent.students" :key="s.id"
                     class="student-card"
                     :class="{ selected: selectedChildForBooks === s.id }"
                     @click="selectedChildForBooks = s.id; onBooksStudentChange()">
                  <input type="radio" class="d-none" :id="'s-radio-'+s.id"
                         :value="s.id" v-model="selectedChildForBooks" />
                  <div class="student-avatar">
                    <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="student-info">
                    <h6 class="student-name">{{ s.firstName }} {{ s.lastName }}</h6>
                    <div class="student-level">
                      <span class="badge bg-primary">{{ s.levelClass || 'Niveau non défini' }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Cas arabe/soutien : sélection multiple -->
              <div v-else class="students-accordion">
                <div v-for="s in selectedParent.students" :key="s.id" class="student-accordion-item">
                  <div class="student-header"
                       :class="{ active: selectedChildren.includes(s.id) }"
                       @click="toggleStudent(s.id)">
                    <div class="d-flex align-items-center">
                      <input type="checkbox" class="form-check-input me-3"
                             :value="s.id" v-model="selectedChildren"
                             @change="updateSelectedClasses(s.id, $event.target.checked)" />
                      <div class="student-info">
                        <h6 class="mb-1">{{ s.firstName }} {{ s.lastName }}</h6>
                        <small class="text-muted">{{ s.levelClass }}</small>
                      </div>
                    </div>
                    <i class="fas fa-chevron-down transition-icon"
                       :class="{ 'rotate-180': selectedChildren.includes(s.id) }"></i>
                  </div>

                  <!-- Matières pour soutien scolaire -->
                  <div v-if="paymentType==='soutien' && selectedChildren.includes(s.id)"
                       class="student-subjects">
                    <h6 class="subjects-title">Matières disponibles :</h6>
                    <div v-if="s.registrations.length" class="subjects-grid">
                      <div v-for="r in s.registrations" :key="r.studyClass.id" class="subject-card">
                        <input type="checkbox" class="form-check-input"
                               :id="'class-'+s.id+'-'+r.studyClass.id"
                               :value="r.studyClass" v-model="selectedClasses[s.id]"
                               @change="calculateTotal" />
                        <label class="subject-label" :for="'class-'+s.id+'-'+r.studyClass.id">
                          <i class="fas fa-book-open me-2"></i>
                          {{ r.studyClass.speciality }}
                          <span class="subject-price">25€/mois</span>
                        </label>
                      </div>
                    </div>
                    <div v-else class="no-subjects">
                      <i class="fas fa-exclamation-circle text-warning"></i>
                      <span>Aucune matière inscrite</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Étape 4: Catalogue de livres -->
            <div v-if="isBooks && selectedChildForBooks" class="step-section mb-5">
              <div class="step-header">
                <span class="step-number">4</span>
                <h6 class="step-title">
                  Catalogue de livres - Niveau :
                  <span class="text-primary">{{ currentStudentLevel || 'Non défini' }}</span>
                </h6>
              </div>

              <!-- Barre de recherche pour livres -->
              <div class="books-search mb-4">
                <div class="input-group">
                  <span class="input-group-text bg-light">
                    <i class="fas fa-search text-muted"></i>
                  </span>
                  <input type="text" class="form-control border-start-0"
                         placeholder="Rechercher un livre..."
                         v-model.trim="bookSearch">
                </div>
              </div>

              <!-- Liste des livres -->
              <div class="books-grid">
                <div v-for="b in filteredBooksByStudent" :key="b.id" class="book-card">
                  <div class="book-image">
                    <img v-if="b.imageName" :src="bookImage(b.imageName)" :alt="b.name" />
                    <div v-else class="book-placeholder">
                      <i class="fas fa-book fa-2x"></i>
                    </div>
                  </div>

                  <div class="book-content">
                    <div class="book-header">
                      <h6 class="book-title">{{ b.name }}</h6>
                      <div class="book-price">{{ asMoney(b.salePrice) }} €</div>
                    </div>

                    <p v-if="b.description" class="book-description">
                      {{ truncate(b.description, 80) }}
                    </p>

                    <div class="book-meta">
                      <span class="badge bg-light text-dark">{{ b.level || 'Tous niveaux' }}</span>
                    </div>

                    <div class="book-actions">
                      <div class="quantity-selector">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                @click="decreaseQty(b)"
                                :disabled="getSelectedQty(b.id) === 0">
                          <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantity-display">{{ getSelectedQty(b.id) }}</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                @click="increaseQty(b)">
                          <i class="fas fa-plus"></i>
                        </button>
                      </div>

                      <div v-if="getSelectedQty(b.id) > 0" class="line-total">
                        Total : <strong>{{ asMoney(lineTotal(b)) }} €</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Résumé des livres sélectionnés -->
              <div v-if="selectedBooks.length" class="books-summary mt-4">
                <div class="alert alert-primary">
                  <h6 class="alert-heading">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Livres sélectionnés ({{ selectedBooks.length }})
                  </h6>
                  <div class="selected-books-list">
                    <div v-for="item in selectedBooksDetails" :key="item.bookId" class="selected-book-item">
                      <span class="book-name">{{ item.name }}</span>
                      <span class="book-qty">× {{ item.quantity }}</span>
                      <span class="book-total">{{ asMoney(item.total) }} €</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Étape 5: Détails du paiement -->
            <div v-if="showDetails" class="step-section mb-4">
              <div class="step-header">
                <span class="step-number">{{ isBooks ? '5' : '4' }}</span>
                <h6 class="step-title">Détails du paiement</h6>
              </div>

              <div class="row">
                <!-- Colonne gauche : montants -->
                <div class="col-md-6">
                  <div class="payment-summary">
                    <div class="summary-row">
                      <span>Montant total :</span>
                      <span class="amount">{{ asMoney(totalAmount) }} €</span>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Remise (€)</label>
                      <input type="number" step="0.01" class="form-control"
                             v-model.number="discount" placeholder="0.00" />
                    </div>

                    <div class="summary-row discount" v-if="discount > 0">
                      <span>Remise :</span>
                      <span class="amount text-success">-{{ asMoney(discount) }} €</span>
                    </div>

                    <div class="summary-row total">
                      <span>Net à payer :</span>
                      <span class="amount">{{ asMoney(Math.max(0, totalAmount - discount)) }} €</span>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Montant payé (€) *</label>
                      <input type="number" step="0.01" class="form-control form-control-lg"
                             v-model.number="paidAmount" placeholder="0.00" required />
                    </div>
                  </div>
                </div>

                <!-- Colonne droite : méthode et détails -->
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Méthode de paiement *</label>
                    <div class="payment-methods">
                      <div class="payment-method"
                           :class="{ selected: paymentMethod === 'Espèces' }"
                           @click="paymentMethod = 'Espèces'">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Espèces</span>
                      </div>
                      <div class="payment-method"
                           :class="{ selected: paymentMethod === 'Carte bancaire' }"
                           @click="paymentMethod = 'Carte bancaire'">
                        <i class="fas fa-credit-card"></i>
                        <span>Carte</span>
                      </div>
                      <div class="payment-method"
                           :class="{ selected: paymentMethod === 'Chèque' }"
                           @click="paymentMethod = 'Chèque'">
                        <i class="fas fa-money-check"></i>
                        <span>Chèque</span>
                      </div>
                    </div>
                  </div>

                  <!-- Champs spécifiques soutien scolaire -->
                  <!-- Champs spécifiques soutien scolaire -->
                  <div v-if="paymentType==='soutien'" class="row">
                    <div class="col-8">
                      <label class="form-label">Mois (plusieurs) *</label>

                      <!-- version simple avec cases à cocher -->
                      <div class="d-flex flex-wrap gap-2">
                        <label v-for="m in months" :key="m" class="btn btn-outline-primary btn-sm">
                          <input
                              type="checkbox"
                              class="form-check-input me-2"
                              :value="m"
                              v-model="selectedMonths"
                          />
                          {{ m }}
                        </label>
                      </div>
                    </div>

                    <div class="col-4">
                      <label class="form-label">Année *</label>
                      <select v-model="selectedYear" class="form-select" required>
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                      </select>
                    </div>
                  </div>

                  <div class="mt-3">
                    <label class="form-label">Commentaire</label>
                    <textarea v-model="comment" class="form-control" rows="3"
                              placeholder="Ajouter un commentaire (optionnel)"></textarea>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Footer avec boutons améliorés -->
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
            <i class="fas fa-times me-2"></i>Annuler
          </button>
          <button type="submit" class="btn btn-primary btn-lg"
                  @click.prevent="submitPayment"
                  :disabled="isSubmitDisabled">
            <i class="fas fa-save me-2"></i>
            <span v-if="isSubmitDisabled">Compléter les informations</span>
            <span v-else>Enregistrer le paiement</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NewPaymentModal',
  props: {
    parents: { type: Array, required: true },
    books: { type: Array, required: true }
  },
  data() {
    return {
      dropdownVisible: false,
      parentSearchQuery: '',
      selectedParent: null,
      selectedChildren: [],
      selectedClasses: {},
      selectedChildForBooks: null,
      selectedBooks: [],
      bookSearch: '',
      totalAmount: 0,
      paidAmount: 0,
      discount: 0,
      selectedMonths: [],
      paymentMethod: '',
      paymentType: '',
      selectedYear: 2025,
      comment: '',
      availableYears: [2023, 2024, 2025, 2026],
      months: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
    };
  },
  computed: {
    showDetails() {
      if (!this.paymentType) return false;

      if (this.isBooks) {
        // Pour livres, on montre quand au moins 1 livre est sélectionné
        return this.selectedBooks.length > 0;
      }
      // Pour arabe / soutien : on montre dès qu’un parent est choisi
      // (tu peux durcir si tu veux attendre qu’au moins 1 enfant soit coché)
      return !!this.selectedParent;
    },
    isBooks() { return this.paymentType === 'livres'; },

    filteredParents() {
      const q = this.parentSearchQuery.trim().toLowerCase();
      return q ? this.parents.filter(p => (p.fullNameParent || '').toLowerCase().includes(q)) : this.parents;
    },

    currentStudent() {
      if (!this.selectedParent || !this.isBooks || !this.selectedChildForBooks) return null;
      return this.selectedParent.students.find(s => s.id === this.selectedChildForBooks) || null;
    },

    currentStudentLevel() {
      return this.currentStudent?.levelClass || null;
    },

    filteredBooksByStudent() {
      const level = this.currentStudentLevel;
      let list = Array.isArray(this.books) ? this.books : [];
      if (level) list = list.filter(b => (b.level || '') === level);
      const q = this.bookSearch.trim().toLowerCase();
      if (q) list = list.filter(b =>
          (b.name || '').toLowerCase().includes(q) ||
          (b.description || '').toLowerCase().includes(q)
      );
      return list;
    },

    selectedBooksDetails() {
      return this.selectedBooks.map(item => {
        const book = this.books.find(b => b.id === item.bookId);
        return {
          ...item,
          name: book?.name || 'Livre inconnu',
          total: item.quantity * item.unitPrice
        };
      });
    },

    isSubmitDisabled() {
      if (this.isBooks) {
        return !(this.selectedParent && this.selectedChildForBooks && this.selectedBooks.length > 0
            && this.paidAmount > 0 && this.paymentMethod);
      }
      if (this.paymentType === 'soutien') {
        return this.selectedMonths.length === 0            // au moins 1 mois
            || !this.paidAmount || this.paidAmount <= 0
            || !this.paymentMethod
            || !this.selectedChildren.length
            || Object.keys(this.selectedClasses).length === 0;
      }
      return !this.paidAmount || this.paidAmount <= 0 || !this.paymentMethod || !this.selectedChildren.length;
    },
  },

  watch: {
    selectedMonths() { if (this.paymentType === 'soutien') this.calculateTotal(); },
    paymentType(newVal) {
      this.discount = 0;
      this.paidAmount = 0;
      this.totalAmount = 0;

      if (newVal === 'livres') {
        this.selectedChildren = [];
        this.selectedClasses = {};
        this.selectedMonths = [];
      } else {
        this.selectedChildForBooks = null;
        this.selectedBooks = [];
        this.bookSearch = '';
      }
    },
    selectedClasses: { deep: true, handler() { if (!this.isBooks) this.calculateTotal(); } },
    selectedChildren() { if (!this.isBooks) this.calculateTotal(); },
    selectedBooks: {
      deep: true,
      handler() {
        if (this.isBooks) {
          const sum = this.selectedBooks.reduce((acc, it) => acc + (Number(it.unitPrice || 0) * Number(it.quantity || 0)), 0);
          this.totalAmount = Number(sum.toFixed(2));
          const paid = Math.max(0, this.totalAmount - Number(this.discount || 0));
          this.paidAmount = Number(paid.toFixed(2));
        }
      }
    },
    discount() {
      if (this.isBooks) {
        const paid = Math.max(0, this.totalAmount - Number(this.discount || 0));
        this.paidAmount = Number(paid.toFixed(2));
      }
    }
  },

  methods: {
    onParentInput() { this.dropdownVisible = true; },

    selectParent(p) {
      this.selectedParent = p;
      this.parentSearchQuery = p.fullNameParent;
      this.dropdownVisible = false;
      this.selectedChildren = [];
      this.selectedClasses = {};
      this.selectedChildForBooks = null;
      this.selectedBooks = [];
      this.totalAmount = this.paidAmount = this.discount = 0;
    },

    hideDropdown() {
      setTimeout(() => { this.dropdownVisible = false; }, 200);
    },

    toggleStudent(studentId) {
      const index = this.selectedChildren.indexOf(studentId);
      if (index > -1) {
        // on retire l’élève
        this.selectedChildren.splice(index, 1);
        // on clone l'objet pour déclencher la réactivité Vue 3
        const copy = { ...this.selectedClasses };
        delete copy[studentId];
        this.selectedClasses = copy;
      } else {
        // on ajoute l’élève
        this.selectedChildren.push(studentId);
        if (!this.selectedClasses[studentId]) {
          this.selectedClasses = { ...this.selectedClasses, [studentId]: [] };
        }
      }
      this.calculateTotal();
    },

    updateSelectedClasses(studentId, checked) {
      if (checked) {
        if (!this.selectedClasses[studentId]) {
          this.selectedClasses = { ...this.selectedClasses, [studentId]: [] };
        }
      } else {
        const copy = { ...this.selectedClasses };
        delete copy[studentId];
        this.selectedClasses = copy;
        this.calculateTotal();
      }
    },

    calculateTotal() {
      if (this.isBooks) return;

      // prix par matière et par mois (si besoin, rends-le dynamique)
      const pricePerClassPerMonth = 25;

      // nb total de matières cochées (tous élèves confondus)
      const totalClasses = Object.values(this.selectedClasses)
          .reduce((acc, classes) => acc + (classes?.length || 0), 0);

      // nb de mois (si pas encore choisi, on compte 0, donc total = 0)
      const monthsCount = this.paymentType === 'soutien' ? this.selectedMonths.length : 1;

      this.totalAmount = totalClasses * pricePerClassPerMonth * monthsCount;

      // si tu veux pré-remplir "Montant payé" avec net à payer :
      const net = Math.max(0, this.totalAmount - Number(this.discount || 0));
      this.paidAmount = Number(net.toFixed(2));
    },

    onBooksStudentChange() {
      this.selectedBooks = [];
      this.bookSearch = '';
    },

    getSelectedQty(bookId) {
      const it = this.selectedBooks.find(i => i.bookId === bookId);
      return it ? it.quantity : 0;
    },

    increaseQty(book) {
      this.setSelectedQty(book, this.getSelectedQty(book.id) + 1);
    },

    decreaseQty(book) {
      const current = this.getSelectedQty(book.id);
      if (current > 0) {
        this.setSelectedQty(book, current - 1);
      }
    },

    setSelectedQty(book, quantity) {
      let q = Number(quantity);
      if (!Number.isFinite(q) || q < 0) q = 0;

      const idx = this.selectedBooks.findIndex(i => i.bookId === book.id);
      if (q === 0) {
        if (idx !== -1) this.selectedBooks.splice(idx, 1);
        return;
      }

      const unit = Number(book.salePrice || 0);
      if (idx === -1) {
        this.selectedBooks.push({
          bookId: book.id,
          quantity: q,
          unitPrice: Number(unit.toFixed(2))
        });
      } else {
        this.selectedBooks[idx].quantity = q;
        this.selectedBooks[idx].unitPrice = Number(unit.toFixed(2));
      }
    },

    clearBook(bookId) {
      const idx = this.selectedBooks.findIndex(i => i.bookId === bookId);
      if (idx !== -1) this.selectedBooks.splice(idx, 1);
    },

    lineTotal(book) {
      const q = this.getSelectedQty(book.id);
      const unit = Number(book.salePrice || 0);
      return Number((q * unit).toFixed(2));
    },

    bookImage(name) {
      return `/uploads/books/${name}`;
    },

    truncate(s, n=100) {
      if(!s) return '';
      const t=String(s);
      return t.length>n? t.slice(0,n-1)+'…' : t;
    },

    asMoney(v) {
      const n = Number(v);
      return Number.isFinite(n) ? n.toFixed(2).replace('.', ',') : '0,00';
    },

    async submitPayment() {
      if (this.isSubmitDisabled) {
        alert('Veuillez remplir tous les champs requis.');
        return;
      }

      const base = {
        parent: this.selectedParent,
        paidAmount: Number(this.paidAmount),
        discount: Number(this.discount || 0),
        paymentMethod: this.paymentMethod,
        paymentType: this.paymentType,
        comment: this.comment,
        selectedYear: this.selectedYear,
      };

      let payload;

      if (this.isBooks) {
        payload = {
          ...base,
          selectedChildren: [{ id: this.selectedChildForBooks }],
          books: this.selectedBooks.map(b => ({
            bookId: b.bookId,
            quantity: b.quantity,
            unitPrice: b.unitPrice
          })),
        };
      } else {
        payload = {
          ...base,
          selectedMonths: this.paymentType === 'soutien' ? (this.selectedMonths || (this.selectedMonth ? [this.selectedMonth] : [])) : [],
          selectedMonth: null, // (optionnel si plus utilisé côté back)
          selectedChildren: this.selectedChildren.map(id => ({
            id,
            classes: this.selectedClasses[id] || []
          })),
        };
      }

      try {
        const { data } = await this.axios.post(this.$routing.generate("payment_new"), payload);

        // --- ouvrir la facture dans un nouvel onglet ---
        const invId  = data?.invoiceId;
        const invUrl = data?.invoiceUrl || (invId ? this.$routing.generate("app_invoice_show", { id: invId }) : null);
        if (invUrl) {
          window.open(invUrl, "_blank", "noopener");
        } else {
          console.warn('Aucune URL/ID de facture retourné:', data);
        }

        // Fermer le modal (optionnel)
        this.$bootstrap.Modal.getInstance(document.getElementById("newPaiementsModal"))?.hide();

        // Rafraîchir + reset
        this.$emit("payment-added");
        this.resetForm();
      } catch (e) {
        console.error(e);
        alert("Erreur lors de l'enregistrement du paiement.");
      }
    },

    resetForm() {
      Object.assign(this.$data, this.$options.data());
    }
  }
};
</script>

<style scoped>

/* Header avec gradient */
.bg-gradient-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.icon-wrapper {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Sections avec étapes */
.step-section {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.6s ease forwards;
}

.step-header {
  display: flex;
  align-items: center;
  margin-bottom: 1.5rem;
}

.step-number {
  width: 32px;
  height: 32px;
  background: var(--primary-color);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  margin-right: 1rem;
  font-size: 14px;
}

.step-title {
  margin: 0;
  font-weight: 600;
  color: var(--dark-color);
}

/* Cards de type de paiement */
.payment-type-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.payment-type-card {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: var(--border-radius);
  padding: 2rem 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.payment-type-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--primary-color);
  transform: scaleX(0);
  transition: var(--transition);
}

.payment-type-card:hover {
  border-color: var(--primary-color);
  transform: translateY(-2px);
  box-shadow: var(--box-shadow);
}

.payment-type-card:hover::before,
.payment-type-card.active::before {
  transform: scaleX(1);
}

.payment-type-card.active {
  border-color: var(--primary-color);
  background: #f8f9ff;
  box-shadow: var(--box-shadow);
}

.card-icon {
  width: 60px;
  height: 60px;
  background: #e3f2fd;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  transition: var(--transition);
}

.payment-type-card.active .card-icon {
  background: var(--primary-color);
  color: white;
}

.card-icon i {
  font-size: 24px;
  color: var(--primary-color);
}

.payment-type-card.active .card-icon i {
  color: white;
}

.card-title {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--dark-color);
}

.card-description {
  font-size: 14px;
  color: #6c757d;
  margin: 0;
  line-height: 1.4;
}

/* Recherche parent */
.search-container {
  position: relative;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1000;
  margin-top: 4px;
}

.dropdown-content {
  background: white;
  border-radius: var(--border-radius);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #e9ecef;
}

.dropdown-item-custom {
  padding: 1rem;
  cursor: pointer;
  transition: var(--transition);
  border-bottom: 1px solid #f1f3f4;
}

.dropdown-item-custom:last-child {
  border-bottom: none;
}

.dropdown-item-custom:hover {
  background: #f8f9fa;
}

.parent-info {
  display: flex;
  align-items: center;
}

.parent-avatar {
  width: 40px;
  height: 40px;
  background: var(--primary-color);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
}

.parent-name {
  font-weight: 600;
  color: var(--dark-color);
}

.parent-meta {
  font-size: 12px;
  color: #6c757d;
}

.no-results {
  padding: 2rem;
  text-align: center;
  color: #6c757d;
}

.no-results i {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 24px;
}

.selected-parent {
  animation: fadeIn 0.5s ease;
}

/* Sélection des enfants */
.students-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.student-card {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  cursor: pointer;
  transition: var(--transition);
  text-align: center;
}

.student-card:hover {
  border-color: var(--primary-color);
  transform: translateY(-2px);
  box-shadow: var(--box-shadow);
}

.student-card.selected {
  border-color: var(--primary-color);
  background: #f8f9ff;
  box-shadow: var(--box-shadow);
}

.student-avatar {
  width: 50px;
  height: 50px;
  background: #e3f2fd;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.student-card.selected .student-avatar {
  background: var(--primary-color);
  color: white;
}

.student-avatar i {
  font-size: 20px;
  color: var(--primary-color);
}

.student-card.selected .student-avatar i {
  color: white;
}

.student-name {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.student-level .badge {
  font-size: 12px;
}

/* Accordéon des étudiants */
.students-accordion {
  margin-top: 1rem;
}

.student-accordion-item {
  background: white;
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
  border: 1px solid #e9ecef;
  overflow: hidden;
  transition: var(--transition);
}

.student-header {
  padding: 1.5rem;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
  transition: var(--transition);
}

.student-header:hover {
  background: #e9ecef;
}

.student-header.active {
  background: var(--primary-color);
  color: white;
}

.transition-icon {
  transition: var(--transition);
}

.rotate-180 {
  transform: rotate(180deg);
}

.student-subjects {
  padding: 1.5rem;
  background: white;
  animation: slideDown 0.3s ease;
}

.subjects-title {
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--dark-color);
}

.subjects-grid {
  display: grid;
  gap: 0.75rem;
}

.subject-card {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  transition: var(--transition);
}

.subject-card:hover {
  background: #e9ecef;
}

.subject-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  margin-left: 0.75rem;
  cursor: pointer;
  font-weight: 500;
}

.subject-price {
  background: var(--primary-color);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.no-subjects {
  text-align: center;
  padding: 2rem;
  color: #6c757d;
}

/* Catalogue de livres */
.books-search {
  max-width: 400px;
}

.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
  margin-top: 1rem;
}

.book-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: var(--border-radius);
  overflow: hidden;
  transition: var(--transition);
  display: flex;
  flex-direction: column;
}

.book-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--box-shadow);
  border-color: var(--primary-color);
}

.book-image {
  height: 120px;
  overflow: hidden;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.book-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.book-placeholder {
  color: #6c757d;
  text-align: center;
}

.book-content {
  padding: 1.5rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.book-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.book-title {
  font-weight: 600;
  margin: 0;
  color: var(--dark-color);
  line-height: 1.3;
  flex-grow: 1;
  margin-right: 1rem;
}

.book-price {
  background: var(--success-color);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.book-description {
  color: #6c757d;
  font-size: 14px;
  line-height: 1.4;
  margin-bottom: 1rem;
  flex-grow: 1;
}

.book-meta {
  margin-bottom: 1rem;
}

.book-actions {
  margin-top: auto;
}

.quantity-selector {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.quantity-selector button {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dee2e6;
}

.quantity-display {
  font-weight: 600;
  font-size: 18px;
  min-width: 30px;
  text-align: center;
  color: var(--primary-color);
}

.line-total {
  text-align: center;
  padding: 0.75rem;
  background: #f8f9ff;
  border-radius: 8px;
  color: var(--primary-color);
  font-size: 14px;
}

/* Résumé des livres */
.books-summary {
  animation: fadeIn 0.5s ease;
}

.selected-books-list {
  margin-top: 1rem;
}

.selected-book-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

.selected-book-item:last-child {
  border-bottom: none;
}

.book-name {
  flex-grow: 1;
  font-weight: 500;
}

.book-qty {
  margin: 0 1rem;
  opacity: 0.8;
}

.book-total {
  font-weight: 600;
}

/* Détails du paiement */
.payment-summary {
  background: #f8f9fa;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #dee2e6;
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-row.total {
  font-size: 18px;
  font-weight: 600;
  color: var(--primary-color);
  border-top: 2px solid var(--primary-color);
  margin-top: 1rem;
  padding-top: 1rem;
}

.summary-row.discount .amount {
  color: var(--success-color);
}

.amount {
  font-weight: 600;
}

/* Méthodes de paiement */
.payment-methods {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 1rem;
  margin-top: 0.5rem;
}

.payment-method {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: var(--border-radius);
  padding: 1rem;
  text-align: center;
  cursor: pointer;
  transition: var(--transition);
}

.payment-method:hover {
  border-color: var(--primary-color);
  transform: translateY(-2px);
}

.payment-method.selected {
  border-color: var(--primary-color);
  background: #f8f9ff;
  color: var(--primary-color);
}

.payment-method i {
  display: block;
  font-size: 24px;
  margin-bottom: 0.5rem;
  color: #6c757d;
}

.payment-method.selected i {
  color: var(--primary-color);
}

.payment-method span {
  font-size: 14px;
  font-weight: 500;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
  }
  to {
    opacity: 1;
    max-height: 500px;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .modal-dialog.modal-xl {
    margin: 0.5rem;
  }

  .payment-type-grid {
    grid-template-columns: 1fr;
  }

  .books-grid {
    grid-template-columns: 1fr;
  }

  .students-grid {
    grid-template-columns: 1fr;
  }

  .payment-methods {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 576px) {
  .modal-body {
    padding: 1rem;
  }

  .step-header {
    flex-direction: column;
    text-align: center;
    margin-bottom: 1rem;
  }

  .step-number {
    margin-right: 0;
    margin-bottom: 0.5rem;
  }
}

/* Améliorations des formulaires */
.form-control:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.form-select:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-color) 0%, #0b5ed7 100%);
  border: none;
  transition: var(--transition);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
}

.btn-primary:disabled {
  background: #6c757d;
  transform: none;
  box-shadow: none;
}

/* Scrollbar personnalisée */
.dropdown-content::-webkit-scrollbar {
  width: 6px;
}

.dropdown-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.dropdown-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.dropdown-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>