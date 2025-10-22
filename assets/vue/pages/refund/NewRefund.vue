<template>
  <div class="refund-container">
    <!-- Header avec gradient -->
    <div class="page-header">
      <div class="header-content">
        <h1 class="page-title">
          <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
          </svg>
          Nouveau remboursement
        </h1>
        <p class="page-subtitle">Sélectionnez un parent et ses paiements à rembourser</p>
      </div>
    </div>

    <!-- Alert moderne -->
    <transition name="alert-fade">
      <div v-if="messageAlert" :class="['alert-modern', `alert-${typeAlert}`]">
        <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="typeAlert === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          <path v-else-if="typeAlert === 'danger'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ messageAlert }}</span>
        <button @click="messageAlert = null" class="alert-close">×</button>
      </div>
    </transition>

    <!-- Étape 1: Recherche parent avec suggestions -->
    <div class="step-card">
      <div class="step-header">
        <div class="step-number">1</div>
        <div>
          <h2 class="step-title">Rechercher un parent</h2>
          <p class="step-desc">Commencez par taper le nom, email, téléphone ou le nom d'un élève</p>
        </div>
      </div>

      <!-- Search bar moderne avec icône -->
      <div class="search-wrapper">
        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
            ref="searchInput"
            v-model="parentSearch"
            type="text"
            placeholder="Rechercher un parent..."
            class="search-input"
            @focus="showSuggestions = true"
            @blur="handleBlur"
        />
        <button v-if="parentSearch" @click="clearSearch" class="search-clear">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Liste de suggestions -->
      <transition name="slide-fade">
        <div v-if="showSuggestions && parentSearch && filteredParents.length" class="suggestions-list">
          <div
              v-for="parent in filteredParents.slice(0, 8)"
              :key="parent.id"
              class="suggestion-item"
              @mousedown.prevent="selectParent(parent)"
          >
            <div class="suggestion-avatar">
              {{ getInitials(parent.fullNameParent) }}
            </div>
            <div class="suggestion-content">
              <div class="suggestion-name">{{ parent.fullNameParent }}</div>
              <div class="suggestion-details">
                <span v-if="parent.emailContact">{{ parent.emailContact }}</span>
                <span v-if="parent.phoneContact">• {{ parent.phoneContact }}</span>
              </div>
              <div v-if="parent.students && parent.students.length" class="suggestion-students">
                <svg class="students-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ parent.students.map(s => s.firstName).join(', ') }}
              </div>
            </div>
            <svg class="suggestion-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
          <div v-if="filteredParents.length > 8" class="suggestions-more">
            Et {{ filteredParents.length - 8 }} autre(s) parent(s)...
          </div>
        </div>
      </transition>

      <!-- Parent sélectionné -->
      <transition name="expand">
        <div v-if="selectedParent" class="selected-parent">
          <div class="selected-header">
            <div class="selected-avatar-lg">
              {{ getInitials(selectedParent.fullNameParent) }}
            </div>
            <div class="selected-info">
              <h3 class="selected-name">{{ selectedParent.fullNameParent }}</h3>
              <div class="selected-meta">
                <span v-if="selectedParent.emailContact">
                  <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  {{ selectedParent.emailContact }}
                </span>
                <span v-if="selectedParent.phoneContact">
                  <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  {{ selectedParent.phoneContact }}
                </span>
              </div>
              <div v-if="selectedParent.students && selectedParent.students.length" class="selected-students">
                <div class="student-badge" v-for="student in selectedParent.students" :key="student.id">
                  {{ student.firstName }} {{ student.lastName }}
                </div>
              </div>
            </div>
            <button @click="clearParent" class="btn-clear-parent">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Étape 2: Factures -->
    <transition name="expand">
      <div v-if="selectedParentId" class="step-card">
        <div class="step-header">
          <div class="step-number">2</div>
          <div>
            <h2 class="step-title">Sélectionner les paiements</h2>
            <p class="step-desc">Choisissez les paiements à rembourser dans les factures ci-dessous</p>
          </div>
        </div>

        <div v-if="loadingInvoices" class="loading-state">
          <div class="spinner"></div>
          <p>Chargement des factures...</p>
        </div>

        <div v-else-if="errorText" class="error-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p>{{ errorText }}</p>
        </div>

        <div v-else-if="!invoices.length" class="empty-state">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          <p>Aucune facture disponible pour ce parent</p>
        </div>

        <div v-else class="invoices-grid">
          <div v-for="inv in invoices" :key="inv.id" class="invoice-modern">
            <!-- Header facture -->
            <div class="invoice-top">
              <div class="invoice-main">
                <label class="invoice-checkbox">
                  <input
                      type="checkbox"
                      :checked="isInvoiceFullySelected(inv)"
                      @change="toggleInvoice(inv)"
                  />
                  <span class="checkbox-custom"></span>
                  <span class="invoice-id">Facture #{{ inv.id }}</span>
                </label>
                <div class="invoice-date">{{ formatDate(inv.invoiceDate) }}</div>
              </div>
              <div class="invoice-amounts">
                <div class="amount-badge amount-total">
                  <span class="amount-label">Total</span>
                  <span class="amount-value">{{ formatMoney(inv.totalAmount) }}</span>
                </div>
                <div class="amount-badge amount-paid">
                  <span class="amount-label">Payé</span>
                  <span class="amount-value">{{ formatMoney(sumPaid(inv)) }}</span>
                </div>
                <div v-if="remainToPay(inv) > 0" class="amount-badge amount-remaining">
                  <span class="amount-label">Reste</span>
                  <span class="amount-value">{{ formatMoney(remainToPay(inv)) }}</span>
                </div>
              </div>
            </div>

            <!-- Comment si présent -->
            <div v-if="inv.comment" class="invoice-comment">
              <svg class="comment-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              {{ inv.comment }}
            </div>

            <!-- Tableau paiements -->
            <div class="payments-table">
              <div class="table-header">
                <div class="th th-check"></div>
                <div class="th">Élève</div>
                <div class="th">Service</div>
                <div class="th th-amount">Montant</div>
                <div class="th">Type</div>
                <div class="th">Mois/Année</div>
              </div>
              <div
                  v-for="(pay, idx) in (Array.isArray(inv.payments) ? inv.payments : [])"
                  :key="`${inv.id}-${pay.id ?? idx}`"
                  :class="['table-row', { 'row-selected': isPaymentSelected(pay.id) }]"
                  @click="togglePayment(inv.id, pay)"
              >
                <div class="td td-check">
                  <label class="payment-checkbox" @click.stop>
                    <input
                        type="checkbox"
                        :checked="isPaymentSelected(pay.id)"
                        @change="togglePayment(inv.id, pay)"
                    />
                    <span class="checkbox-custom"></span>
                  </label>
                </div>
                <div class="td">
                  <div class="student-cell">
                    <div class="student-avatar-sm">{{ getInitials(studentLabel(pay.student)) }}</div>
                    {{ studentLabel(pay.student) }}
                  </div>
                </div>
                <div class="td">
                  <span class="service-tag">{{ pay.serviceType || '—' }}</span>
                </div>
                <div class="td td-amount">
                  <span class="amount-highlight">{{ formatMoney(pay.amountPaid) }}</span>
                </div>
                <div class="td">
                  <span class="payment-type-badge">{{ pay.paymentType || '—' }}</span>
                </div>
                <div class="td">
                  <span v-if="pay.month || pay.year">
                    {{ [pay.month, pay.year].filter(Boolean).join('/') }}
                  </span>
                  <span v-else class="text-muted">—</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Étape 3: Récapitulatif et remboursement -->
    <transition name="expand">
      <div v-if="selectedParentId && selectedPaymentIds.size > 0" class="step-card refund-card">
        <div class="step-header">
          <div class="step-number">3</div>
          <div>
            <h2 class="step-title">Créer le remboursement</h2>
            <p class="step-desc">Vérifiez le montant et complétez les informations</p>
          </div>
        </div>

        <!-- Stats cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon stat-icon-blue">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Paiements sélectionnés</div>
              <div class="stat-value">{{ selectedPaymentIds.size }}</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon stat-icon-green">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Montant total</div>
              <div class="stat-value">{{ formatMoney(totalSelected) }}</div>
            </div>
          </div>

          <div class="stat-card stat-card-methods">
            <div class="stat-icon stat-icon-purple">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
            </div>
            <div class="stat-content">
              <div class="stat-label">Méthodes de paiement</div>
              <div class="method-chips">
                <span v-for="(amt, type) in breakdownByType" :key="type" class="method-chip">
                  {{ type }}: {{ formatMoney(amt) }}
                </span>
                <span v-if="!Object.keys(breakdownByType).length" class="text-muted">Aucune</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Formulaire -->
        <div class="refund-form">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Montant à rembourser
              </label>
              <div class="input-with-max">
                <input
                    v-model.number="refundAmount"
                    type="number"
                    min="0"
                    step="0.01"
                    class="form-input"
                    :max="totalSelected"
                    :placeholder="formatMoney(totalSelected)"
                />
                <button @click="refundAmount = totalSelected" class="btn-max">MAX</button>
              </div>
              <div class="form-hint">Maximum: {{ formatMoney(totalSelected) }}</div>
            </div>

            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Méthode de remboursement
              </label>
              <select v-model="refundMethod" class="form-input">
                <option disabled value="">Choisir une méthode</option>
                <option value="Carte bancaire">💳 Carte bancaire</option>
                <option value="Chèque">📝 Chèque</option>
                <option value="Espèces">💵 Espèces</option>
                <option value="Virement">🏦 Virement</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">
              <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
              </svg>
              Commentaire (optionnel)
            </label>
            <textarea
                v-model="refundComment"
                rows="3"
                class="form-input"
                placeholder="Justifiez le remboursement si nécessaire..."
            ></textarea>
          </div>

          <div class="form-actions">
            <button
                class="btn-submit"
                :disabled="!canSubmitRefund"
                @click="submitRefund"
            >
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Créer le remboursement
            </button>
            <div v-if="!canSubmitRefund" class="form-warning">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              Sélectionnez au moins 1 paiement, choisissez une méthode et un montant valide
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "NewRefund",
  components: { Alert },
  props: {
    currentUser: { type: Object, required: true },
    parents: { type: Array, required: true },
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,

      parentSearch: "",
      selectedParentId: null,
      showSuggestions: false,

      invoices: [],
      loadingInvoices: false,
      errorText: null,

      // Sélection
      selectedPaymentIds: new Set(),

      // Form remboursement
      refundAmount: null,
      refundMethod: "",
      refundComment: "",

      // Timeout pour fermeture suggestions
      blurTimeout: null,
    };
  },
  watch: {
    selectedParentId(newVal) {
      this.invoices = [];
      this.errorText = null;
      this.selectedPaymentIds = new Set();
      this.resetRefundForm();
      if (newVal) this.fetchInvoices();
    },
    totalSelected(newVal) {
      // Auto-remplir le montant si vide
      if (!this.refundAmount || this.refundAmount > newVal) {
        this.refundAmount = newVal;
      }
    },
  },
  computed: {
    filteredParents() {
      const q = (this.parentSearch || "").trim().toLowerCase();
      if (!q) return [];

      return this.parentsSorted.filter((p) => {
        const hay = [
          p.fullNameParent,
          p.emailContact,
          p.phoneContact,
          p.fatherFirstName,
          p.fatherLastName,
          p.motherFirstName,
          p.motherLastName,
          ...(Array.isArray(p.students)
              ? p.students.map((s) => `${s.firstName || ""} ${s.lastName || ""}`.trim())
              : []),
        ]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();

        return hay.includes(q);
      });
    },
    parentsSorted() {
      return (this.parents || []).slice().sort((a, b) => {
        const aa = (a.fullNameParent || "").toLowerCase();
        const bb = (b.fullNameParent || "").toLowerCase();
        return aa.localeCompare(bb);
      });
    },
    selectedParent() {
      if (!this.selectedParentId) return null;
      return (this.parents || []).find((p) => p.id === this.selectedParentId) || null;
    },

    // Totaux / breakdown
    selectedPaymentsList() {
      const out = [];
      for (const inv of this.invoices) {
        const pays = Array.isArray(inv.payments) ? inv.payments : [];
        for (const pay of pays) {
          const id = pay?.id;
          if (id && this.selectedPaymentIds.has(id)) {
            out.push({ invoiceId: inv.id, payment: pay });
          }
        }
      }
      return out;
    },
    totalSelected() {
      return this.selectedPaymentsList.reduce(
          (acc, { payment }) => acc + Number(payment?.amountPaid || 0),
          0
      );
    },
    breakdownByType() {
      const agg = {};
      for (const { payment } of this.selectedPaymentsList) {
        const type = payment?.paymentType || "Autre";
        agg[type] = (agg[type] || 0) + Number(payment?.amountPaid || 0);
      }
      return agg;
    },
    canSubmitRefund() {
      return (
          this.selectedPaymentIds.size > 0 &&
          this.refundMethod &&
          typeof this.refundAmount === "number" &&
          this.refundAmount > 0 &&
          this.refundAmount <= this.totalSelected
      );
    },
  },
  mounted() {
    // Focus automatique sur le champ de recherche
    this.$nextTick(() => {
      if (this.$refs.searchInput) {
        this.$refs.searchInput.focus();
      }
    });
  },
  beforeUnmount() {
    // Nettoyer le timeout si le composant est détruit
    if (this.blurTimeout) {
      clearTimeout(this.blurTimeout);
    }
  },
  methods: {
    // =============================================
    // RECHERCHE ET SÉLECTION PARENT
    // =============================================

    selectParent(parent) {
      if (!parent || !parent.id) return;

      this.selectedParentId = parent.id;
      this.parentSearch = parent.fullNameParent || "";
      this.showSuggestions = false;

      // Scroll vers les factures
      this.$nextTick(() => {
        const invoicesSection = document.querySelector('.step-card:nth-child(3)');
        if (invoicesSection) {
          invoicesSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    },

    clearSearch() {
      this.parentSearch = "";
      this.showSuggestions = false;
      this.$nextTick(() => {
        if (this.$refs.searchInput) {
          this.$refs.searchInput.focus();
        }
      });
    },

    clearParent() {
      this.selectedParentId = null;
      this.parentSearch = "";
      this.invoices = [];
      this.selectedPaymentIds = new Set();
      this.resetRefundForm();
      this.errorText = null;
      this.messageAlert = null;

      this.$nextTick(() => {
        if (this.$refs.searchInput) {
          this.$refs.searchInput.focus();
        }
      });
    },

    handleBlur() {
      // Délai pour permettre le clic sur les suggestions
      this.blurTimeout = setTimeout(() => {
        this.showSuggestions = false;
      }, 200);
    },

    getInitials(name) {
      if (!name) return "?";
      const words = name.trim().split(/\s+/);
      if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
      }
      return (words[0][0] + words[words.length - 1][0]).toUpperCase();
    },

    // =============================================
    // RÉCUPÉRATION DES FACTURES
    // =============================================

    async fetchInvoices() {
      if (!this.selectedParentId) return;

      this.loadingInvoices = true;
      this.errorText = null;
      this.messageAlert = null;

      try {
        const params = { parentId: this.selectedParentId };
        const url = this.$routing.generate("app_search_invoice");
        const { data } = await this.axios.get(url, { params });

        // ApiResponseTrait => payload dans data.message
        const list = data?.message?.invoices;
        this.invoices = Array.isArray(list) ? list : [];

        // Filtrer les factures qui ont des paiements
        this.invoices = this.invoices.filter(inv => {
          const payments = Array.isArray(inv.payments) ? inv.payments : [];
          return payments.length > 0;
        });

        if (!this.invoices.length) {
          this.messageAlert = "Aucune facture avec paiements trouvée pour ce parent.";
          this.typeAlert = "info";

          // Auto-dismiss après 5 secondes
          setTimeout(() => {
            if (this.typeAlert === 'info') {
              this.messageAlert = null;
            }
          }, 5000);
        }
      } catch (e) {
        console.error("Erreur de récupération des factures:", e);
        this.errorText =
            e?.response?.data?.message?.text
            || e?.response?.data?.message
            || "Une erreur est survenue lors du chargement des factures.";
        this.messageAlert = this.errorText;
        this.typeAlert = "danger";
      } finally {
        this.loadingInvoices = false;
      }
    },

    // =============================================
    // SÉLECTION DES PAIEMENTS
    // =============================================

    isPaymentSelected(paymentId) {
      return paymentId && this.selectedPaymentIds.has(paymentId);
    },

    togglePayment(invoiceId, pay) {
      if (!pay?.id) {
        console.warn("Paiement sans ID, impossible de le sélectionner");
        return;
      }

      const id = pay.id;
      if (this.selectedPaymentIds.has(id)) {
        this.selectedPaymentIds.delete(id);
      } else {
        this.selectedPaymentIds.add(id);
      }

      // Force la réactivité
      this.selectedPaymentIds = new Set(this.selectedPaymentIds);

      // Ajuster le montant si nécessaire
      if (this.refundAmount > this.totalSelected) {
        this.refundAmount = this.totalSelected;
      }

      // Scroll vers le formulaire si c'est la première sélection
      if (this.selectedPaymentIds.size === 1) {
        this.$nextTick(() => {
          const refundSection = document.querySelector('.refund-card');
          if (refundSection) {
            setTimeout(() => {
              refundSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
          }
        });
      }
    },

    isInvoiceFullySelected(inv) {
      const pays = Array.isArray(inv.payments) ? inv.payments : [];
      if (!pays.length) return false;
      return pays.every(p => p?.id && this.selectedPaymentIds.has(p.id));
    },

    toggleInvoice(inv) {
      const pays = Array.isArray(inv.payments) ? inv.payments : [];
      const validPayments = pays.filter(p => p?.id);

      if (!validPayments.length) return;

      const allSelected = validPayments.every(p => this.selectedPaymentIds.has(p.id));

      if (allSelected) {
        // Désélectionner tous
        for (const p of validPayments) {
          this.selectedPaymentIds.delete(p.id);
        }
      } else {
        // Sélectionner tous
        for (const p of validPayments) {
          this.selectedPaymentIds.add(p.id);
        }
      }

      this.selectedPaymentIds = new Set(this.selectedPaymentIds);

      if (this.refundAmount > this.totalSelected) {
        this.refundAmount = this.totalSelected;
      }
    },

    // =============================================
    // FORMULAIRE DE REMBOURSEMENT
    // =============================================

    resetRefundForm() {
      this.refundAmount = null;
      this.refundMethod = "";
      this.refundComment = "";
    },

    async submitRefund() {
      if (!this.canSubmitRefund) {
        this.messageAlert = "Veuillez remplir tous les champs obligatoires.";
        this.typeAlert = "warning";
        return;
      }

      // Validation supplémentaire
      if (this.refundAmount > this.totalSelected) {
        this.messageAlert = "Le montant du remboursement ne peut pas dépasser le total sélectionné.";
        this.typeAlert = "warning";
        return;
      }

      const payload = {
        parentId: this.selectedParentId,
        paymentIds: Array.from(this.selectedPaymentIds),
        refundAmount: this.refundAmount,
        refundMethod: this.refundMethod,
        comment: (this.refundComment || "").trim(),
      };

      // Désactiver le bouton pendant la soumission
      const submitButton = document.querySelector('.btn-submit');
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const url = this.$routing.generate("app_refund_create");
        const { data } = await this.axios.post(url, payload);

        // Format ApiResponseTrait: data.message contient l'objet
        const msg = data?.message || {};
        const redirect = msg.redirect;
        const refundId = msg?.refund?.id;

        this.messageAlert = msg?.text || "Remboursement créé avec succès !";
        this.typeAlert = "success";

        // Attendre un peu pour que l'utilisateur voit le message
        setTimeout(() => {
          // Redirection -> priorité à l'URL renvoyée par l'API
          if (redirect) {
            window.location.assign(redirect);
          } else if (refundId) {
            const showUrl = this.$routing.generate("api_refund_show", { id: refundId });
            window.location.assign(showUrl);
          } else {
            // Fallback: recharger la page ou rediriger vers la liste
            window.location.reload();
          }
        }, 1500);
      } catch (e) {
        console.error("Erreur lors de la création du remboursement:", e);

        this.messageAlert =
            e?.response?.data?.message?.text ||
            e?.response?.data?.message ||
            e?.message ||
            "Erreur lors de la création du remboursement.";
        this.typeAlert = "danger";

        // Réactiver le bouton en cas d'erreur
        if (submitButton) {
          submitButton.disabled = false;
        }

        // Auto-dismiss après 8 secondes
        setTimeout(() => {
          if (this.typeAlert === 'danger') {
            this.messageAlert = null;
          }
        }, 8000);
      }
    },

    // =============================================
    // HELPERS DE FORMATAGE
    // =============================================

    formatDate(value) {
      if (!value) return "—";

      try {
        const d = typeof value === "string" ? new Date(value) : value;
        if (Number.isNaN(d?.getTime?.())) return "—";

        return d.toLocaleDateString("fr-FR", {
          year: "numeric",
          month: "2-digit",
          day: "2-digit",
        });
      } catch (e) {
        return "—";
      }
    },

    formatMoney(value) {
      if (value == null || value === "") return "—";

      const num = Number(value);
      if (Number.isNaN(num)) return String(value);

      return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "EUR",
        minimumFractionDigits: 2,
      }).format(num);
    },

    sumPaid(inv) {
      if (!inv || !Array.isArray(inv.payments)) return 0;
      return inv.payments.reduce((acc, p) => acc + Number(p?.amountPaid || 0), 0);
    },

    remainToPay(inv) {
      const total = Number(inv?.totalAmount || 0);
      const paid = this.sumPaid(inv);
      const remain = total - paid;
      return remain > 0 ? remain : 0;
    },

    studentLabel(stu) {
      if (!stu) return "—";

      const fn = (stu.firstName || "").trim();
      const ln = (stu.lastName || "").trim();
      const full = [fn, ln].filter(Boolean).join(" ");

      return full || (stu.id ? `Élève #${stu.id}` : "—");
    },

    // =============================================
    // MÉTHODES UTILITAIRES
    // =============================================

    parentOptionLabel(p) {
      const name = p.fullNameParent || `${p.fatherLastName || ""} ${p.fatherFirstName || ""}`.trim();
      const email = p.emailContact || p.fatherEmail || p.motherEmail || "";
      const students =
          Array.isArray(p.students) && p.students.length
              ? ` — élèves: ${p.students.map((s) => s.firstName).join(", ")}`
              : "";
      return `${name}${email ? " (" + email + ")" : ""}${students}`;
    },

    // Validation côté client
    validateForm() {
      const errors = [];

      if (!this.selectedParentId) {
        errors.push("Veuillez sélectionner un parent");
      }

      if (this.selectedPaymentIds.size === 0) {
        errors.push("Veuillez sélectionner au moins un paiement");
      }

      if (!this.refundAmount || this.refundAmount <= 0) {
        errors.push("Le montant doit être supérieur à 0");
      }

      if (this.refundAmount > this.totalSelected) {
        errors.push("Le montant ne peut pas dépasser le total sélectionné");
      }

      if (!this.refundMethod) {
        errors.push("Veuillez choisir une méthode de remboursement");
      }

      return errors;
    },

    // Debug helper (à retirer en production)
    debugLog(message, data) {
      if (process.env.NODE_ENV === 'development') {
        console.log(`[NewRefund] ${message}`, data);
      }
    },
  },
};
</script>

<style scoped>
/* =============================================
   VARIABLES & BASE
   ============================================= */
.refund-container {
  --color-primary: #6366f1;
  --color-primary-dark: #4f46e5;
  --color-primary-light: #818cf8;
  --color-success: #10b981;
  --color-danger: #ef4444;
  --color-warning: #f59e0b;
  --color-info: #3b82f6;

  --color-bg: #ffffff;
  --color-surface: #ffffff;
  --color-border: #e5e7eb;
  --color-text: #374151;
  --color-text-muted: #6b7280;
  --color-text-light: #9ca3af;

  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-xl: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);

  --radius-sm: 0.25rem;
  --radius-md: 0.375rem;
  --radius-lg: 0.5rem;
  --radius-xl: 0.75rem;

  --transition: all 0.2s ease;

  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
  background: var(--color-bg);
  min-height: 100vh;
}

/* =============================================
   HEADER
   ============================================= */
.page-header {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-md);
}

.header-content {
  position: relative;
  z-index: 1;
}

.page-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: white;
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.title-icon {
  width: 1.75rem;
  height: 1.75rem;
  stroke-width: 2;
}

.page-subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.875rem;
  margin: 0;
  font-weight: 400;
}

/* =============================================
   ALERTS
   ============================================= */
.alert-modern {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: var(--radius-md);
  margin-bottom: 1rem;
  font-weight: 400;
  font-size: 0.875rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid;
}

.alert-success {
  background: #d1fae5;
  color: #065f46;
  border-color: #6ee7b7;
}

.alert-danger {
  background: #fee2e2;
  color: #991b1b;
  border-color: #fca5a5;
}

.alert-info {
  background: #dbeafe;
  color: #1e40af;
  border-color: #93c5fd;
}

.alert-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
}

.alert-close {
  margin-left: auto;
  background: none;
  border: none;
  font-size: 1.25rem;
  line-height: 1;
  cursor: pointer;
  color: currentColor;
  opacity: 0.6;
  transition: var(--transition);
  padding: 0.25rem;
}

.alert-close:hover {
  opacity: 1;
}

/* =============================================
   STEP CARDS
   ============================================= */
.step-card {
  background: var(--color-surface);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--color-border);
}

.step-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.step-number {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  font-weight: 600;
  flex-shrink: 0;
}

.step-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-text);
  margin: 0 0 0.25rem 0;
}

.step-desc {
  color: var(--color-text-muted);
  margin: 0;
  font-size: 0.875rem;
}

/* =============================================
   SEARCH BAR
   ============================================= */
.search-wrapper {
  position: relative;
  margin-bottom: 1rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.125rem;
  height: 1.125rem;
  color: var(--color-text-muted);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.75rem 3rem 0.75rem 3rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  transition: var(--transition);
  background: var(--color-surface);
  color: var(--color-text);
}

.search-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-clear {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: var(--color-text-light);
  border: none;
  border-radius: 50%;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  color: white;
  padding: 0;
}

.search-clear:hover {
  background: var(--color-text-muted);
}

.search-clear svg {
  width: 0.875rem;
  height: 0.875rem;
}

/* =============================================
   SUGGESTIONS
   ============================================= */
.suggestions-list {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  max-height: 24rem;
  overflow-y: auto;
  margin-top: 0.5rem;
}

.suggestion-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1rem;
  cursor: pointer;
  transition: var(--transition);
  border-bottom: 1px solid var(--color-border);
  font-size: 0.875rem;
}

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item:hover {
  background: #f9fafb;
}

.suggestion-avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.suggestion-content {
  flex: 1;
  min-width: 0;
}

.suggestion-name {
  font-weight: 500;
  color: var(--color-text);
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.suggestion-details {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-text-muted);
  font-size: 0.8125rem;
}

.suggestion-students {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin-top: 0.375rem;
  padding: 0.25rem 0.625rem;
  background: #f3f4f6;
  border-radius: var(--radius-sm);
  color: var(--color-text-muted);
  font-size: 0.75rem;
  font-weight: 500;
  width: fit-content;
}

.students-icon {
  width: 0.875rem;
  height: 0.875rem;
}

.suggestion-arrow {
  width: 1.125rem;
  height: 1.125rem;
  color: var(--color-text-light);
  flex-shrink: 0;
}

.suggestions-more {
  padding: 0.75rem 1rem;
  text-align: center;
  color: var(--color-text-muted);
  font-size: 0.8125rem;
  font-style: italic;
  background: #f9fafb;
}

/* =============================================
   SELECTED PARENT
   ============================================= */
.selected-parent {
  margin-top: 1.25rem;
  padding: 1.25rem;
  background: #f9fafb;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
}

.selected-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.selected-avatar-lg {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: var(--radius-md);
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.125rem;
  flex-shrink: 0;
}

.selected-info {
  flex: 1;
  min-width: 0;
}

.selected-name {
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text);
  margin: 0 0 0.625rem 0;
}

.selected-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 0.875rem;
}

.selected-meta span {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  color: var(--color-text-muted);
  font-size: 0.8125rem;
}

.meta-icon {
  width: 1rem;
  height: 1rem;
  color: var(--color-primary);
}

.selected-students {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.student-badge {
  padding: 0.375rem 0.75rem;
  background: white;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--color-text);
}

.btn-clear-parent {
  background: #fee2e2;
  border: none;
  border-radius: 50%;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  color: var(--color-danger);
  flex-shrink: 0;
}

.btn-clear-parent:hover {
  background: var(--color-danger);
  color: white;
}

.btn-clear-parent svg {
  width: 1rem;
  height: 1rem;
}

/* =============================================
   LOADING & ERROR STATES
   ============================================= */
.loading-state, .error-state, .empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 2rem;
  text-align: center;
  color: var(--color-text-muted);
}

.spinner {
  width: 2.5rem;
  height: 2.5rem;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 0.875rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state svg, .empty-state svg {
  width: 3rem;
  height: 3rem;
  color: var(--color-text-light);
  margin-bottom: 0.875rem;
}

.error-state p, .empty-state p, .loading-state p {
  font-size: 0.875rem;
  margin: 0;
}

/* =============================================
   INVOICES GRID
   ============================================= */
.invoices-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.invoice-modern {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.invoice-top {
  padding: 1.25rem;
  background: #f9fafb;
  border-bottom: 1px solid var(--color-border);
}

.invoice-main {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.875rem;
}

.invoice-checkbox {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.9375rem;
  color: var(--color-text);
}

.invoice-checkbox input[type="checkbox"] {
  display: none;
}

.checkbox-custom {
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid var(--color-border);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
  flex-shrink: 0;
}

.invoice-checkbox input[type="checkbox"]:checked + .checkbox-custom {
  background: var(--color-primary);
  border-color: var(--color-primary);
}

.invoice-checkbox input[type="checkbox"]:checked + .checkbox-custom::after {
  content: '✓';
  color: white;
  font-size: 0.875rem;
  font-weight: 700;
}

.invoice-id {
  color: var(--color-primary);
}

.invoice-date {
  color: var(--color-text-muted);
  font-size: 0.8125rem;
}

.invoice-amounts {
  display: flex;
  flex-wrap: wrap;
  gap: 0.625rem;
}

.amount-badge {
  display: flex;
  flex-direction: column;
  padding: 0.375rem 0.75rem;
  border-radius: var(--radius-sm);
  border: 1px solid;
}

.amount-badge.amount-total {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.amount-badge.amount-paid {
  background: #d1fae5;
  border-color: #6ee7b7;
}

.amount-badge.amount-remaining {
  background: #fef3c7;
  border-color: #fbbf24;
}

.amount-label {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  font-weight: 600;
  color: var(--color-text-muted);
  margin-bottom: 0.125rem;
}

.amount-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text);
}

.invoice-comment {
  display: flex;
  align-items: flex-start;
  gap: 0.625rem;
  padding: 0.875rem 1.25rem;
  background: #eff6ff;
  border-top: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
  color: var(--color-text-muted);
  font-size: 0.8125rem;
  font-style: italic;
}

.comment-icon {
  width: 1rem;
  height: 1rem;
  color: var(--color-info);
  flex-shrink: 0;
  margin-top: 0.125rem;
}

/* =============================================
   PAYMENTS TABLE
   ============================================= */
.payments-table {
  display: flex;
  flex-direction: column;
}

.table-header {
  display: grid;
  grid-template-columns: 3rem 1.5fr 1.5fr 1fr 1fr 1fr;
  gap: 0.875rem;
  padding: 0.875rem 1.25rem;
  background: #f9fafb;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  color: var(--color-text-muted);
  border-bottom: 1px solid var(--color-border);
}

.th {
  display: flex;
  align-items: center;
}

.th-check {
  justify-content: center;
}

.th-amount {
  justify-content: flex-end;
}

.table-row {
  display: grid;
  grid-template-columns: 3rem 1.5fr 1.5fr 1fr 1fr 1fr;
  gap: 0.875rem;
  padding: 0.875rem 1.25rem;
  border-bottom: 1px solid var(--color-border);
  transition: var(--transition);
  cursor: pointer;
  font-size: 0.8125rem;
}

.table-row:hover {
  background: #f9fafb;
}

.table-row.row-selected {
  background: #eff6ff;
}

.td {
  display: flex;
  align-items: center;
  color: var(--color-text);
}

.td-check {
  justify-content: center;
}

.td-amount {
  justify-content: flex-end;
}

.payment-checkbox {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.payment-checkbox input[type="checkbox"] {
  display: none;
}

.payment-checkbox .checkbox-custom {
  width: 1.125rem;
  height: 1.125rem;
}

.student-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.student-avatar-sm {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.6875rem;
  flex-shrink: 0;
}

.service-tag {
  padding: 0.25rem 0.625rem;
  background: #eff6ff;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-primary);
}

.amount-highlight {
  font-weight: 600;
  color: var(--color-primary);
  font-size: 0.875rem;
}

.payment-type-badge {
  padding: 0.25rem 0.625rem;
  background: #f3f4f6;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 500;
}

.text-muted {
  color: var(--color-text-light);
}

/* =============================================
   REFUND CARD
   ============================================= */
.refund-card {
  background: #f0fdf4;
  border-color: #86efac;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 1.25rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.stat-card-methods {
  grid-column: span 2;
}

.stat-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 1.25rem;
  height: 1.25rem;
  color: white;
}

.stat-icon-blue {
  background: var(--color-info);
}

.stat-icon-green {
  background: var(--color-success);
}

.stat-icon-purple {
  background: #a78bfa;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text);
}

.method-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.method-chip {
  padding: 0.375rem 0.75rem;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: var(--radius-sm);
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--color-primary);
}

/* =============================================
   REFUND FORM
   ============================================= */
.refund-form {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.25rem;
  margin-bottom: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-weight: 500;
  color: var(--color-text);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.label-icon {
  width: 1rem;
  height: 1rem;
  color: var(--color-primary);
}

.form-input {
  padding: 0.75rem 1rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  transition: var(--transition);
  background: var(--color-surface);
  color: var(--color-text);
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

textarea.form-input {
  resize: vertical;
  min-height: 80px;
  line-height: 1.5;
}

select.form-input {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 1.125rem;
  padding-right: 2.75rem;
}

.input-with-max {
  display: flex;
  gap: 0.625rem;
  align-items: stretch;
}

.input-with-max .form-input {
  flex: 1;
}

.btn-max {
  padding: 0 1rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-weight: 600;
  font-size: 0.8125rem;
  cursor: pointer;
  transition: var(--transition);
}

.btn-max:hover {
  background: var(--color-primary-dark);
}

.form-hint {
  margin-top: 0.375rem;
  font-size: 0.75rem;
  color: var(--color-text-muted);
}

/* =============================================
   FORM ACTIONS
   ============================================= */
.form-actions {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
}

.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  padding: 0.875rem 1.5rem;
  background: var(--color-success);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}

.btn-submit:hover:not(:disabled) {
  background: #059669;
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-submit svg {
  width: 1.25rem;
  height: 1.25rem;
}

.form-warning {
  display: flex;
  align-items: flex-start;
  gap: 0.625rem;
  padding: 0.875rem 1rem;
  background: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: var(--radius-md);
  color: #92400e;
  font-size: 0.8125rem;
  font-weight: 400;
}

.form-warning svg {
  width: 1.125rem;
  height: 1.125rem;
  flex-shrink: 0;
  margin-top: 0.125rem;
  color: #d97706;
}

/* =============================================
   ANIMATIONS
   ============================================= */
.alert-fade-enter-active, .alert-fade-leave-active,
.slide-fade-enter-active, .slide-fade-leave-active,
.expand-enter-active, .expand-leave-active {
  transition: all 0.3s ease;
}

.alert-fade-enter-from, .alert-fade-leave-to {
  opacity: 0;
  transform: translateY(-0.5rem);
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-0.5rem);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(0.5rem);
}

.expand-enter-from, .expand-leave-to {
  opacity: 0;
  transform: translateY(0.75rem);
}

/* =============================================
   RESPONSIVE
   ============================================= */
@media (max-width: 1200px) {
  .stat-card-methods {
    grid-column: span 1;
  }
}

@media (max-width: 768px) {
  .refund-container {
    padding: 1rem;
  }

  .page-header {
    padding: 1.25rem;
  }

  .page-title {
    font-size: 1.25rem;
  }

  .step-card {
    padding: 1.25rem;
  }

  .table-header,
  .table-row {
    grid-template-columns: 2.5rem 1fr 1fr;
    font-size: 0.75rem;
  }

  .table-header .th:nth-child(4),
  .table-header .th:nth-child(5),
  .table-header .th:nth-child(6),
  .table-row .td:nth-child(4),
  .table-row .td:nth-child(5),
  .table-row .td:nth-child(6) {
    display: none;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .stat-card-methods {
    grid-column: span 1;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .invoice-amounts {
    flex-direction: column;
  }

  .selected-header {
    flex-direction: column;
  }

  .btn-clear-parent {
    align-self: flex-end;
    margin-top: -2.5rem;
  }
}

@media (max-width: 480px) {
  .refund-container {
    padding: 0.75rem;
  }

  .page-header {
    padding: 1rem;
  }

  .page-title {
    font-size: 1.125rem;
    gap: 0.5rem;
  }

  .title-icon {
    width: 1.5rem;
    height: 1.5rem;
  }

  .page-subtitle {
    font-size: 0.8125rem;
  }

  .step-card {
    padding: 1rem;
  }

  .step-number {
    width: 1.75rem;
    height: 1.75rem;
    font-size: 0.75rem;
  }

  .step-title {
    font-size: 1rem;
  }

  .step-desc {
    font-size: 0.8125rem;
  }

  .suggestion-item {
    flex-direction: column;
    align-items: flex-start;
    padding: 0.75rem;
  }

  .suggestion-arrow {
    display: none;
  }

  .selected-parent {
    padding: 1rem;
  }

  .selected-avatar-lg {
    width: 3rem;
    height: 3rem;
    font-size: 1rem;
  }

  .selected-name {
    font-size: 0.9375rem;
  }

  .selected-meta {
    flex-direction: column;
    gap: 0.5rem;
  }

  .invoice-top {
    padding: 1rem;
  }

  .invoice-main {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.625rem;
  }

  .invoice-checkbox {
    font-size: 0.875rem;
  }

  .table-header,
  .table-row {
    grid-template-columns: 2.25rem 1fr;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
  }

  .table-header .th:nth-child(3),
  .table-row .td:nth-child(3) {
    display: none;
  }

  .stat-value {
    font-size: 1.25rem;
  }

  .refund-form {
    padding: 1.25rem;
  }

  .btn-submit {
    font-size: 0.875rem;
    padding: 0.75rem 1.25rem;
  }

  .method-chips {
    flex-direction: column;
  }

  .method-chip {
    width: 100%;
  }
}

/* =============================================
   SCROLLBAR
   ============================================= */
.suggestions-list::-webkit-scrollbar {
  width: 6px;
}

.suggestions-list::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: var(--radius-sm);
}

.suggestions-list::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: var(--radius-sm);
  transition: var(--transition);
}

.suggestions-list::-webkit-scrollbar-thumb:hover {
  background: var(--color-text-muted);
}

/* =============================================
   PRINT
   ============================================= */
@media print {
  .refund-container {
    background: white;
    padding: 0;
  }

  .page-header {
    background: white;
    color: black;
    box-shadow: none;
  }

  .btn-submit,
  .btn-clear-parent,
  .search-clear,
  .alert-close {
    display: none;
  }

  .step-card,
  .invoice-modern {
    box-shadow: none;
    border: 1px solid #ccc;
    page-break-inside: avoid;
  }
}
</style>