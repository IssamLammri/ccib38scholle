<template>
  <div class="ep-root" lang="fr">

    <!-- ░░ TOPBAR ░░ -->
    <div class="ep-topbar">
      <a :href="$routing.generate('app_parent_entity_index')" class="ep-btn ep-btn--ghost">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Retour
      </a>

      <div class="ep-topbar__center">
        <span class="ep-topbar__breadcrumb">Parents</span>
        <span class="ep-topbar__sep">/</span>
        <span class="ep-topbar__current">Modifier</span>
      </div>

      <div class="ep-topbar__actions">
        <a :href="$routing.generate('app_parent_entity_show', { id: parent.id })" class="ep-btn ep-btn--ghost">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          Aperçu
        </a>
        <button class="ep-btn ep-btn--outline" @click="resetForm" :disabled="saving">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.33"/></svg>
          Réinitialiser
        </button>
        <button class="ep-btn ep-btn--primary" @click="submit" :disabled="saving">
          <span v-if="saving" class="ep-spinner"></span>
          <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
        </button>
      </div>
    </div>

    <!-- ░░ ALERT ░░ -->
    <transition name="ep-alert">
      <div v-if="messageAlert" class="ep-alert" :class="`ep-alert--${typeAlert}`">
        <svg v-if="typeAlert === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else-if="typeAlert === 'danger'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ messageAlert }}</span>
        <button class="ep-alert__close" @click="messageAlert = null">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </transition>

    <!-- ░░ BODY ░░ -->
    <div class="ep-layout">

      <!-- ── SIDEBAR identité ── -->
      <aside class="ep-sidebar">
        <div class="ep-id-card">
          <div class="ep-avatar">{{ headerInitials }}</div>
          <div class="ep-id-card__name">
            {{ form.fatherLastName || form.motherLastName || 'Famille' }}
          </div>
          <div class="ep-id-card__sub">{{ headerSubtitle }}</div>
          <div class="ep-id-card__pills">
            <span class="ep-pill ep-pill--blue">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              {{ studentsCount }} enfant{{ studentsCount > 1 ? 's' : '' }}
            </span>
            <span v-if="form.familyStatus" class="ep-pill ep-pill--green">
              {{ form.familyStatus === 'Married' ? 'Mariés' : 'Divorcés' }}
            </span>
          </div>
        </div>

        <!-- mini résumé montants -->
        <div class="ep-amounts-summary">
          <div class="ep-amount-row">
            <span class="ep-amount-row__label">Arabe</span>
            <span class="ep-amount-row__value">{{ form.amountDueArabic }} <small>EURO</small></span>
          </div>
          <div class="ep-amount-divider"></div>
          <div class="ep-amount-row">
            <span class="ep-amount-row__label">Soutien Scolaire</span>
            <span class="ep-amount-row__value">{{ form.amountDueSoutien }} <small>EURO</small></span>
          </div>
        </div>
      </aside>

      <!-- ── MAIN contenu ── -->
      <main class="ep-main">

        <!-- Section Père -->
        <section class="ep-section">
          <div class="ep-section__header">
            <div class="ep-section__icon ep-section__icon--blue">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h2 class="ep-section__title">Père</h2>
          </div>
          <div class="ep-form-grid">
            <div class="ep-field">
              <label class="ep-label">Nom</label>
              <input v-model.trim="form.fatherLastName" class="ep-input" placeholder="Nom de famille" />
            </div>
            <div class="ep-field">
              <label class="ep-label">Prénom</label>
              <input v-model.trim="form.fatherFirstName" class="ep-input" placeholder="Prénom" />
            </div>
            <div class="ep-field">
              <label class="ep-label">Email</label>
              <div class="ep-input-icon-wrap">
                <svg class="ep-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input v-model.trim="form.fatherEmail" type="email" class="ep-input ep-input--icon" placeholder="email@exemple.com" />
              </div>
            </div>
            <div class="ep-field">
              <label class="ep-label">Téléphone</label>
              <div class="ep-input-icon-wrap">
                <svg class="ep-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <input v-model.trim="form.fatherPhone" class="ep-input ep-input--icon" placeholder="+212 6XX XXX XXX" />
              </div>
            </div>
          </div>
        </section>

        <!-- Section Mère -->
        <section class="ep-section">
          <div class="ep-section__header">
            <div class="ep-section__icon ep-section__icon--rose">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h2 class="ep-section__title">Mère</h2>
          </div>
          <div class="ep-form-grid">
            <div class="ep-field">
              <label class="ep-label">Nom</label>
              <input v-model.trim="form.motherLastName" class="ep-input" placeholder="Nom de famille" />
            </div>
            <div class="ep-field">
              <label class="ep-label">Prénom</label>
              <input v-model.trim="form.motherFirstName" class="ep-input" placeholder="Prénom" />
            </div>
            <div class="ep-field">
              <label class="ep-label">Email</label>
              <div class="ep-input-icon-wrap">
                <svg class="ep-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input v-model.trim="form.motherEmail" type="email" class="ep-input ep-input--icon" placeholder="email@exemple.com" />
              </div>
            </div>
            <div class="ep-field">
              <label class="ep-label">Téléphone</label>
              <div class="ep-input-icon-wrap">
                <svg class="ep-input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.38 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6l.92-.92a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <input v-model.trim="form.motherPhone" class="ep-input ep-input--icon" placeholder="+212 6XX XXX XXX" />
              </div>
            </div>
          </div>
        </section>

        <!-- Section Famille & Montants -->
        <section class="ep-section">
          <div class="ep-section__header">
            <div class="ep-section__icon ep-section__icon--green">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <h2 class="ep-section__title">Famille & Scolarité</h2>
          </div>
          <div class="ep-form-grid ep-form-grid--3">

            <div class="ep-field">
              <label class="ep-label">Statut familial</label>
              <div class="ep-select-wrap">
                <select v-model="form.familyStatus" class="ep-select">
                  <option value="">— Choisir —</option>
                  <option value="Married">Mariés</option>
                  <option value="Divorced">Divorcés</option>
                </select>
                <svg class="ep-select-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
              </div>
            </div>

            <!-- Montant arabe -->
            <div class="ep-field">
              <label class="ep-label">Montant dû — Arabe</label>
              <div class="ep-amount-field">
                <div class="ep-amount-display">
                  <span class="ep-amount-display__num">{{ form.amountDueArabic }}</span>
                  <span class="ep-amount-display__unit">EURO</span>
                </div>
                <button type="button" class="ep-btn-edit" @click="openDueModal('arabic')" :disabled="saving">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Modifier
                </button>
              </div>
              <div v-if="form.amountDueArabicComment" class="ep-comment-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                {{ form.amountDueArabicComment }}
              </div>
            </div>

            <!-- Montant soutien -->
            <div class="ep-field">
              <label class="ep-label">Montant dû — Soutien</label>
              <div class="ep-amount-field">
                <div class="ep-amount-display">
                  <span class="ep-amount-display__num">{{ form.amountDueSoutien }}</span>
                  <span class="ep-amount-display__unit">EURO</span>
                </div>
                <button type="button" class="ep-btn-edit" @click="openDueModal('soutien')" :disabled="saving">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Modifier
                </button>
              </div>
              <div v-if="form.amountDueSoutienComment" class="ep-comment-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                {{ form.amountDueSoutienComment }}
              </div>
            </div>

          </div>
        </section>

        <!-- Section Étudiants -->
        <section class="ep-section ep-section--flat">
          <div class="ep-section__header">
            <div class="ep-section__icon ep-section__icon--amber">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h2 class="ep-section__title">Étudiants associés</h2>
            <span class="ep-badge-count">{{ studentsCount }}</span>
          </div>

          <div v-if="normalizedStudents.length === 0" class="ep-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p>Aucun étudiant associé à ce parent.</p>
          </div>

          <div v-else class="ep-students-list">
            <div v-for="(s, i) in normalizedStudents" :key="s.id" class="ep-student-row" :style="`animation-delay: ${i * 60}ms`">
              <div class="ep-student-avatar">{{ (s.lastName || '?').charAt(0) }}{{ (s.firstName || '?').charAt(0) }}</div>
              <div class="ep-student-info">
                <span class="ep-student-info__name">{{ s.lastName }} {{ s.firstName }}</span>
                <span class="ep-student-info__meta">{{ formatDate(s.birthDate) }}</span>
              </div>
              <div class="ep-student-level">{{ s.level || 'N/D' }}</div>
            </div>
          </div>
        </section>

      </main>
    </div>

    <!-- ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░
         MODAL — Modification montant
         ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ -->
    <transition name="ep-modal">
      <div v-if="dueModal.open" class="ep-modal-overlay" @click.self="closeDueModal">
        <div class="ep-modal" role="dialog" aria-modal="true">

          <!-- Header modal -->
          <div class="ep-modal__head">
            <div class="ep-modal__head-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
              <h3 class="ep-modal__title">Modifier {{ dueModal.label }}</h3>
              <p class="ep-modal__sub">Valeur actuelle : <strong>{{ dueModal.current }} EURO</strong></p>
            </div>
            <button class="ep-modal__close" @click="closeDueModal" aria-label="Fermer">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <!-- Tabs mode -->
          <div class="ep-modal__tabs">
            <button
                class="ep-tab" :class="{ 'ep-tab--active': dueModal.mode === 'absolute' }"
                @click="dueModal.mode = 'absolute'">
              Valeur fixe
            </button>
            <button
                class="ep-tab" :class="{ 'ep-tab--active': dueModal.mode === 'percent' }"
                @click="dueModal.mode = 'percent'">
              Pourcentage
            </button>
          </div>

          <div class="ep-modal__body">

            <!-- Mode absolute -->
            <div v-if="dueModal.mode === 'absolute'" class="ep-field">
              <label class="ep-label">Nouvelle valeur (EURO)</label>
              <input v-model.number="dueModal.absoluteValue" type="number" min="0" class="ep-input ep-input--lg" placeholder="0" />
            </div>

            <!-- Mode percent -->
            <div v-else class="ep-field">
              <label class="ep-label">Variation en %</label>
              <div class="ep-percent-wrap">
                <button class="ep-percent-btn" @click="dueModal.percentValue = Math.max(-100, dueModal.percentValue - 5)">−5</button>
                <input v-model.number="dueModal.percentValue" type="number" class="ep-input ep-input--lg ep-input--center" placeholder="0" />
                <button class="ep-percent-btn" @click="dueModal.percentValue = dueModal.percentValue + 5">+5</button>
              </div>
              <p class="ep-hint">Calcul : valeur × (1 + %/100)</p>
            </div>

            <!-- Commentaire -->
            <div class="ep-field">
              <label class="ep-label">
                Commentaire
                <span class="ep-label__required">obligatoire</span>
              </label>
              <textarea
                  v-model.trim="dueModal.comment"
                  class="ep-textarea"
                  rows="3"
                  placeholder="Ex : remise exceptionnelle, correction de facture…"
              ></textarea>
            </div>

            <!-- Preview -->
            <transition name="ep-fade">
              <div v-if="dueModal.preview !== null" class="ep-preview-block">
                <div class="ep-preview-block__label">Résultat calculé</div>
                <div class="ep-preview-block__value">
                  {{ dueModal.preview }} <span>EURO</span>
                </div>
                <div class="ep-preview-block__delta" :class="dueModal.preview > dueModal.current ? 'up' : 'down'">
                  <template v-if="dueModal.preview !== dueModal.current">
                    {{ dueModal.preview > dueModal.current ? '▲' : '▼' }}
                    {{ Math.abs(dueModal.preview - dueModal.current) }} EURO
                  </template>
                  <template v-else>
                    Identique à la valeur actuelle
                  </template>
                </div>
              </div>
            </transition>

            <transition name="ep-fade">
              <div v-if="dueModal.error" class="ep-modal-error">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {{ dueModal.error }}
              </div>
            </transition>
          </div>

          <!-- Footer modal -->
          <div class="ep-modal__foot">
            <button class="ep-btn ep-btn--ghost" @click="closeDueModal">Annuler</button>
            <button class="ep-btn ep-btn--primary" @click="confirmDueModal" :disabled="!canConfirmDueModal">
              Confirmer
            </button>
          </div>

        </div>
      </div>
    </transition>

  </div>
</template>

<script>
export default {
  name: "EditParent",
  props: {
    parent:    { type: Object, required: true },
    students:  { type: Array,  required: true },
    csrfToken: { type: String, required: true },
    updateUrl: { type: String, required: true },
  },
  data() {
    return {
      saving: false,
      messageAlert: null,
      typeAlert: null,
      form: this.makeFormFromParent(this.parent),
      initialSnapshot: null,
      dueModal: {
        open: false,
        target: null,
        label: "",
        current: 0,
        mode: "absolute",
        absoluteValue: 0,
        percentValue: 0,
        comment: "",
        preview: null,
        error: null,
      },
    };
  },
  created() {
    this.initialSnapshot = JSON.stringify(this.form);
  },
  computed: {
    studentsCount() { return Array.isArray(this.students) ? this.students.length : 0; },
    normalizedStudents() {
      if (!Array.isArray(this.students)) return [];
      return this.students.map(s => ({
        id: s.id,
        lastName:  this.clean(s.lastName)  || "—",
        firstName: this.clean(s.firstName) || "—",
        birthDate: s.birthDate || null,
        level:     this.clean(s.level)     || null,
      }));
    },
    headerSubtitle() {
      const email = this.clean(this.form.fatherEmail) || this.clean(this.form.motherEmail) || "Email non renseigné";
      const phone = this.clean(this.form.fatherPhone) || this.clean(this.form.motherPhone) || "Tel non renseigné";
      return `${email} · ${phone}`;
    },
    headerInitials() {
      const a = (this.clean(this.form.fatherLastName) || "P").charAt(0).toUpperCase();
      const b = (this.clean(this.form.motherLastName) || "").charAt(0).toUpperCase();
      return (a + b).trim() || "P";
    },
    canConfirmDueModal() {
      if (!this.dueModal.open) return false;
      if ((this.dueModal.comment || "").trim().length < 3) return false;
      const preview = this.computeDuePreview();
      return preview !== Number(this.dueModal.current ?? 0);
    },
  },
  watch: {
    "dueModal.mode"()           { this.syncPreview(); },
    "dueModal.absoluteValue"()  { this.syncPreview(); },
    "dueModal.percentValue"()   { this.syncPreview(); },
    "dueModal.comment"()        { this.dueModal.error = null; },
  },
  methods: {
    makeFormFromParent(p) {
      return {
        fatherLastName:  this.clean(p.fatherLastName),
        fatherFirstName: this.clean(p.fatherFirstName),
        fatherEmail:     this.clean(p.fatherEmail),
        fatherPhone:     this.clean(p.fatherPhone),
        motherLastName:  this.clean(p.motherLastName),
        motherFirstName: this.clean(p.motherFirstName),
        motherEmail:     this.clean(p.motherEmail),
        motherPhone:     this.clean(p.motherPhone),
        familyStatus:    this.clean(p.familyStatus),
        amountDueArabic:  Number(p.amountDueArabic  ?? 0),
        amountDueSoutien: Number(p.amountDueSoutien ?? 0),
        amountDueArabicComment:  "",
        amountDueSoutienComment: "",
      };
    },
    resetForm() {
      this.form = JSON.parse(this.initialSnapshot);
      this.messageAlert = "Formulaire réinitialisé.";
      this.typeAlert = "info";
    },
    clean(v) {
      if (v === null || v === undefined) return "";
      const s = String(v).trim();
      return s.toLowerCase() === "vide" ? "" : s;
    },
    formatDate(date) {
      if (!date) return "Non disponible";
      try { return new Date(date).toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit", year: "numeric" }); }
      catch { return "Non disponible"; }
    },
    openDueModal(target) {
      const isArabic = target === "arabic";
      const current = isArabic ? this.form.amountDueArabic : this.form.amountDueSoutien;
      Object.assign(this.dueModal, {
        open: true, target,
        label:  isArabic ? "Montant dû (Arabe)" : "Montant dû (Soutien)",
        current: Number(current ?? 0),
        mode: "absolute",
        absoluteValue: Number(current ?? 0),
        percentValue: 0,
        comment: "",
        preview: Number(current ?? 0),
        error: null,
      });
    },
    closeDueModal() { this.dueModal.open = false; },
    syncPreview() {
      if (!this.dueModal.open) return;
      this.dueModal.preview = this.computeDuePreview();
      this.dueModal.error = null;
    },
    computeDuePreview() {
      const cur = Number(this.dueModal.current ?? 0);
      if (this.dueModal.mode === "absolute") {
        const v = Number(this.dueModal.absoluteValue ?? 0);
        return Number.isFinite(v) ? Math.max(0, Math.round(v)) : cur;
      }
      const p = Number(this.dueModal.percentValue ?? 0);
      if (!Number.isFinite(p)) return cur;
      return Math.max(0, Math.round(cur + (cur * p) / 100));
    },
    confirmDueModal() {
      const comment = (this.dueModal.comment || "").trim();
      if (comment.length < 3) { this.dueModal.error = "Commentaire requis (min. 3 caractères)."; return; }
      const newValue = this.computeDuePreview();
      if (newValue === Number(this.dueModal.current ?? 0)) { this.dueModal.error = "La valeur doit être différente de la valeur actuelle."; return; }
      if (this.dueModal.target === "arabic") {
        this.form.amountDueArabic = newValue;
        this.form.amountDueArabicComment = comment;
      } else {
        this.form.amountDueSoutien = newValue;
        this.form.amountDueSoutienComment = comment;
      }
      this.closeDueModal();
    },
    async submit() {
      this.saving = true;
      this.messageAlert = null;
      try {
        const res  = await fetch(this.updateUrl, {
          method: "PATCH",
          headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": this.csrfToken },
          body: JSON.stringify(this.form),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data?.message || "Erreur lors de la sauvegarde.");
        this.typeAlert = "success";
        this.messageAlert = data?.text || "Parent mis à jour avec succès.";
        this.initialSnapshot = JSON.stringify(this.form);
        this.form.amountDueArabicComment  = "";
        this.form.amountDueSoutienComment = "";
      } catch (e) {
        this.typeAlert = "danger";
        this.messageAlert = e?.message || "Erreur inconnue.";
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

/* ═══════════════════════════════════════════════
   ROOT
═══════════════════════════════════════════════ */
.ep-root {
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  color: #1a2332;
  background: #f4f6f9;
  min-height: 100vh;
  padding: 0 0 48px;
}

/* ═══════════════════════════════════════════════
   TOPBAR
═══════════════════════════════════════════════ */
.ep-topbar {
  position: sticky;
  top: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 0 28px;
  height: 60px;
  background: #ffffff;
  border-bottom: 1px solid #e4e8ef;
  backdrop-filter: blur(8px);
}
.ep-topbar__center {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: .85rem;
}
.ep-topbar__breadcrumb { color: #7a8899; }
.ep-topbar__sep { color: #e4e8ef; font-size: 1rem; }
.ep-topbar__current { font-weight: 600; }
.ep-topbar__actions { display: flex; align-items: center; gap: 8px; }

/* ═══════════════════════════════════════════════
   BUTTONS
═══════════════════════════════════════════════ */
.ep-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 0 16px;
  height: 36px;
  border-radius: 10px;
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .84rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .15s ease;
  text-decoration: none;
  white-space: nowrap;
  border: none;
}
.ep-btn--ghost {
  background: transparent;
  color: #7a8899;
  border: 1px solid transparent;
}
.ep-btn--ghost:hover { background: #f4f6f9; color: #1a2332; }
.ep-btn--outline {
  background: transparent;
  color: #1a2332;
  border: 1px solid #e4e8ef;
}
.ep-btn--outline:hover { background: #f4f6f9; }
.ep-btn--primary {
  background: #3b6ef5;
  color: #fff;
  border: 1px solid #3b6ef5;
  box-shadow: 0 2px 8px rgba(59,110,245,.3);
}
.ep-btn--primary:hover { background: #2d5de0; box-shadow: 0 4px 12px rgba(59,110,245,.4); }
.ep-btn:disabled { opacity: .5; cursor: not-allowed; transform: none !important; }

/* ═══════════════════════════════════════════════
   ALERT
═══════════════════════════════════════════════ */
.ep-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 16px 28px 0;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: .88rem;
  font-weight: 500;
}
.ep-alert span { flex: 1; }
.ep-alert__close { background: none; border: none; cursor: pointer; opacity: .6; padding: 0; color: inherit; display: flex; }
.ep-alert__close:hover { opacity: 1; }
.ep-alert--success { background: #e4faf3; color: #0a8a5a; border: 1px solid #b5f0db; }
.ep-alert--danger  { background: #fff2f2; color: #c0392b; border: 1px solid #ffc8c8; }
.ep-alert--info    { background: #ebf0ff; color: #2d5de0; border: 1px solid #c5d5fb; }

.ep-alert-enter-active, .ep-alert-leave-active { transition: all .25s ease; }
.ep-alert-enter-from, .ep-alert-leave-to { opacity: 0; transform: translateY(-8px); }

/* ═══════════════════════════════════════════════
   LAYOUT
═══════════════════════════════════════════════ */
.ep-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 24px;
  max-width: 1280px;
  margin: 24px auto 0;
  padding: 0 28px;
}
@media (max-width: 900px) {
  .ep-layout { grid-template-columns: 1fr; }
  .ep-sidebar { display: none; }
}

/* ═══════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════ */
.ep-sidebar {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.ep-id-card {
  background: #ffffff;
  border: 1px solid #e4e8ef;
  border-radius: 14px;
  padding: 24px 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
  text-align: center;
}
.ep-avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b6ef5, #764ba2);
  color: #fff;
  font-size: 1.6rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 14px;
  box-shadow: 0 4px 16px rgba(59,110,245,.3);
  letter-spacing: 1px;
}
.ep-id-card__name {
  font-size: 1.05rem;
  font-weight: 700;
  margin-bottom: 4px;
}
.ep-id-card__sub {
  font-size: .77rem;
  color: #7a8899;
  margin-bottom: 16px;
  word-break: break-word;
}
.ep-id-card__pills { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
.ep-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: .76rem;
  font-weight: 600;
}
.ep-pill--blue  { background: #ebf0ff; color: #3b6ef5; }
.ep-pill--green { background: #e4faf3; color: #0fb87a; }

.ep-amounts-summary {
  background: #ffffff;
  border: 1px solid #e4e8ef;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
}
.ep-amount-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
}
.ep-amount-row__label { font-size: .84rem; color: #7a8899; font-weight: 500; }
.ep-amount-row__value { font-size: 1.05rem; font-weight: 700; }
.ep-amount-row__value small { font-size: .7rem; color: #7a8899; margin-left: 2px; font-weight: 500; }
.ep-amount-divider { height: 1px; background: #e4e8ef; margin: 8px 0; }

/* ═══════════════════════════════════════════════
   SECTIONS
═══════════════════════════════════════════════ */
.ep-main { display: flex; flex-direction: column; gap: 20px; }

.ep-section {
  background: #ffffff;
  border: 1px solid #e4e8ef;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
}
.ep-section--flat { padding-bottom: 0; overflow: hidden; }

.ep-section__header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}
.ep-section__icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.ep-section__icon--blue  { background: #ebf0ff; color: #3b6ef5; }
.ep-section__icon--rose  { background: #ffeef7; color: #e84393; }
.ep-section__icon--green { background: #e4faf3; color: #0fb87a; }
.ep-section__icon--amber { background: #fff8eb; color: #f5a623; }

.ep-section__title {
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
}
.ep-badge-count {
  margin-left: auto;
  background: #fff8eb;
  color: #f5a623;
  font-size: .78rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
}

/* ═══════════════════════════════════════════════
   FORM
═══════════════════════════════════════════════ */
.ep-form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}
.ep-form-grid--3 {
  grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 680px) {
  .ep-form-grid, .ep-form-grid--3 { grid-template-columns: 1fr; }
}

.ep-field { display: flex; flex-direction: column; gap: 6px; }

.ep-label {
  font-size: .8rem;
  font-weight: 600;
  color: #7a8899;
  text-transform: uppercase;
  letter-spacing: .4px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.ep-label__required {
  font-size: .7rem;
  font-weight: 600;
  background: #fff3cd;
  color: #856404;
  padding: 2px 7px;
  border-radius: 20px;
  text-transform: none;
  letter-spacing: 0;
}

.ep-input, .ep-select, .ep-textarea {
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .9rem;
  color: #1a2332;
  background: #f4f6f9;
  border: 1.5px solid #e4e8ef;
  border-radius: 8px;
  padding: 10px 14px;
  width: 100%;
  box-sizing: border-box;
  transition: border-color .15s, box-shadow .15s;
  outline: none;
}
.ep-input:focus, .ep-select:focus, .ep-textarea:focus {
  border-color: #3b6ef5;
  box-shadow: 0 0 0 3px rgba(59,110,245,.12);
  background: #fff;
}
.ep-input--lg   { font-size: 1.05rem; font-weight: 600; height: 46px; text-align: center; }
.ep-input--center { text-align: center; }
.ep-input--icon { padding-left: 38px; }

.ep-input-icon-wrap { position: relative; }
.ep-input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #7a8899;
  pointer-events: none;
}

.ep-select-wrap { position: relative; }
.ep-select { appearance: none; padding-right: 36px; cursor: pointer; }
.ep-select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #7a8899;
  pointer-events: none;
}

.ep-textarea { resize: vertical; min-height: 90px; }

/* Amount field */
.ep-amount-field {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f4f6f9;
  border: 1.5px solid #e4e8ef;
  border-radius: 8px;
  padding: 8px 10px;
}
.ep-amount-display { flex: 1; display: flex; align-items: baseline; gap: 4px; }
.ep-amount-display__num { font-size: 1.15rem; font-weight: 700; }
.ep-amount-display__unit { font-size: .75rem; color: #7a8899; font-weight: 500; }

.ep-btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .78rem;
  font-weight: 600;
  color: #3b6ef5;
  background: #ebf0ff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all .15s;
  white-space: nowrap;
  flex-shrink: 0;
}
.ep-btn-edit:hover { background: #d1dcfd; }
.ep-btn-edit:disabled { opacity: .4; cursor: not-allowed; }

.ep-comment-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: .76rem;
  color: #7a8899;
  background: #f4f6f9;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid #e4e8ef;
  margin-top: 2px;
}

/* ═══════════════════════════════════════════════
   STUDENTS LIST
═══════════════════════════════════════════════ */
.ep-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 40px;
  color: #7a8899;
  font-size: .9rem;
}

.ep-students-list { display: flex; flex-direction: column; }
.ep-student-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 24px;
  border-top: 1px solid #e4e8ef;
  animation: ep-slide-in .3s ease both;
  transition: background .15s;
}
.ep-student-row:hover { background: #f4f6f9; }

@keyframes ep-slide-in {
  from { opacity: 0; transform: translateX(-10px); }
  to   { opacity: 1; transform: translateX(0); }
}

.ep-student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #fff8eb, #fde08d);
  color: #b07a00;
  font-size: .85rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  letter-spacing: .5px;
}
.ep-student-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.ep-student-info__name { font-weight: 600; font-size: .9rem; }
.ep-student-info__meta { font-size: .78rem; color: #7a8899; }
.ep-student-level {
  font-size: .78rem;
  font-weight: 600;
  padding: 4px 10px;
  background: #e4faf3;
  color: #0fb87a;
  border-radius: 20px;
}

/* ═══════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════ */
.ep-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 20, 40, .5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 16px;
}
.ep-modal {
  width: min(520px, 100%);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,.14);
  overflow: hidden;
}

.ep-modal__head {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 20px 20px 0;
}
.ep-modal__head-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: #ebf0ff;
  color: #3b6ef5;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.ep-modal__title { font-size: 1rem; font-weight: 700; margin: 0 0 3px; }
.ep-modal__sub   { font-size: .83rem; color: #7a8899; margin: 0; }
.ep-modal__close {
  margin-left: auto;
  background: #f4f6f9;
  border: 1px solid #e4e8ef;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #7a8899;
  transition: all .15s;
  flex-shrink: 0;
}
.ep-modal__close:hover { background: #fce8e8; color: #c0392b; border-color: #ffc8c8; }

/* Tabs */
.ep-modal__tabs {
  display: flex;
  gap: 0;
  padding: 16px 20px 0;
  border-bottom: 1px solid #e4e8ef;
}
.ep-tab {
  padding: 8px 18px;
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .85rem;
  font-weight: 600;
  color: #7a8899;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  transition: all .15s;
  margin-bottom: -1px;
}
.ep-tab--active { color: #3b6ef5; border-bottom-color: #3b6ef5; }
.ep-tab:hover:not(.ep-tab--active) { color: #1a2332; }

.ep-modal__body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.ep-modal__foot {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid #e4e8ef;
  background: #f4f6f9;
}

/* Percent controls */
.ep-percent-wrap { display: flex; gap: 8px; align-items: center; }
.ep-percent-btn {
  width: 42px;
  height: 46px;
  border-radius: 8px;
  background: #f4f6f9;
  border: 1.5px solid #e4e8ef;
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .15s;
  flex-shrink: 0;
}
.ep-percent-btn:hover { border-color: #3b6ef5; background: #ebf0ff; color: #3b6ef5; }
.ep-hint { font-size: .78rem; color: #7a8899; margin: 4px 0 0; }

/* Preview block */
.ep-preview-block {
  background: linear-gradient(135deg, #ebf0ff, #e0e8fe);
  border: 1px solid #c5d5fb;
  border-radius: 10px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.ep-preview-block__label { font-size: .78rem; font-weight: 600; color: #3b6ef5; flex: 1; }
.ep-preview-block__value { font-size: 1.4rem; font-weight: 800; color: #3b6ef5; }
.ep-preview-block__value span { font-size: .75rem; font-weight: 600; }
.ep-preview-block__delta { font-size: .8rem; font-weight: 700; }
.ep-preview-block__delta.up   { color: #0fb87a; }
.ep-preview-block__delta.down { color: #e74c3c; }

.ep-modal-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff2f2;
  border: 1px solid #ffc8c8;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: .84rem;
  font-weight: 600;
  color: #c0392b;
}

/* ═══════════════════════════════════════════════
   TRANSITIONS
═══════════════════════════════════════════════ */
.ep-modal-enter-active, .ep-modal-leave-active { transition: all .2s ease; }
.ep-modal-enter-from .ep-modal, .ep-modal-leave-to .ep-modal { transform: scale(.96) translateY(10px); opacity: 0; }
.ep-modal-enter-from, .ep-modal-leave-to { opacity: 0; }

.ep-fade-enter-active, .ep-fade-leave-active { transition: opacity .2s, transform .2s; }
.ep-fade-enter-from, .ep-fade-leave-to { opacity: 0; transform: translateY(4px); }

/* Spinner */
.ep-spinner {
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255,255,255,.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: ep-spin .6s linear infinite;
  flex-shrink: 0;
}
@keyframes ep-spin { to { transform: rotate(360deg); } }
</style>