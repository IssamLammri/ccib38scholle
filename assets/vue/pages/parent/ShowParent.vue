<template>
  <div class="sp-root" lang="fr">

    <!-- ░░ TOPBAR ░░ -->
    <div class="sp-topbar">
      <a :href="$routing.generate('app_parent_entity_index')" class="sp-btn sp-btn--ghost">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Retour
      </a>

      <div class="sp-topbar__center">
        <span class="sp-topbar__breadcrumb">Parents</span>
        <span class="sp-topbar__sep">/</span>
        <span class="sp-topbar__current">Fiche</span>
      </div>

      <div class="sp-topbar__actions">
        <!-- Sélecteur élève -->
        <div class="sp-student-select-wrap">
          <svg class="sp-select-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <select class="sp-student-select" v-model.number="selectedStudentId">
            <option :value="0">Sélectionner un élève…</option>
            <option v-for="s in normalizedStudents" :key="s.id" :value="s.id">
              {{ s.lastName }} {{ s.firstName }}
            </option>
          </select>
          <svg class="sp-select-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </div>

        <button
            class="sp-btn sp-btn--teal"
            @click="printCharter"
            :disabled="!selectedStudent"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Imprimer la charte
        </button>

        <a :href="$routing.generate('app_parent_entity_edit', { id: parent.id })" class="sp-btn sp-btn--primary">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Modifier
        </a>
      </div>
    </div>

    <!-- ░░ ALERT sélection ░░ -->
    <transition name="sp-alert">
      <div v-if="!selectedStudent" class="sp-info-banner">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Sélectionnez un élève dans la liste pour générer et imprimer sa charte personnalisée.
      </div>
    </transition>

    <!-- ░░ BODY ░░ -->
    <div class="sp-layout">

      <!-- ── SIDEBAR ── -->
      <aside class="sp-sidebar">
        <div class="sp-id-card">
          <div class="sp-avatar">{{ headerInitials }}</div>
          <div class="sp-id-card__name">{{ headerTitle }}</div>
          <div class="sp-id-card__sub">{{ headerSubtitle }}</div>
          <div class="sp-id-card__pills">
            <span class="sp-pill sp-pill--blue">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              {{ studentsCount }} enfant{{ studentsCount > 1 ? 's' : '' }}
            </span>
            <span v-if="primaryEmail" class="sp-pill sp-pill--slate">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              Email renseigné
            </span>
          </div>
        </div>

        <!-- Montants -->
        <div class="sp-amounts-card">
          <div class="sp-amounts-card__title">Montants dus</div>
          <div class="sp-amount-row">
            <span class="sp-amount-row__label">Arabe</span>
            <span class="sp-amount-row__value">{{ parent.amountDueArabic ?? 0 }} <small>EURO</small></span>
          </div>
          <div class="sp-amount-divider"></div>
          <div class="sp-amount-row">
            <span class="sp-amount-row__label">Soutien Solaire</span>
            <span class="sp-amount-row__value">{{ parent.amountDueSoutien ?? 0 }} <small>EURO</small></span>
          </div>

          <!-- Historique si présent -->
          <template v-if="parent.amountDueHistories && parent.amountDueHistories.length">
            <div class="sp-amount-divider"></div>
            <div class="sp-amounts-card__title" style="margin-top:4px">Historique</div>
            <div v-for="(h, i) in parent.amountDueHistories" :key="i" class="sp-history-row">
              <span class="sp-history-row__date">{{ formatDate(h.date) }}</span>
              <span class="sp-history-row__note">{{ h.comment || h.label || '—' }}</span>
            </div>
          </template>
        </div>

        <!-- Élève sélectionné -->
        <div v-if="selectedStudent" class="sp-selected-student-card">
          <div class="sp-selected-student-card__label">Élève sélectionné</div>
          <div class="sp-selected-student-avatar">
            {{ selectedStudent.firstName.charAt(0) }}{{ selectedStudent.lastName.charAt(0) }}
          </div>
          <div class="sp-selected-student-card__name">
            {{ selectedStudent.firstName }} {{ selectedStudent.lastName }}
          </div>
          <div class="sp-selected-student-card__meta">{{ displayOrNA(selectedStudent.level) }}</div>
          <div class="sp-selected-student-card__meta">Né(e) le {{ formatDate(selectedStudent.birthDate) }}</div>
        </div>
      </aside>

      <!-- ── MAIN ── -->
      <main class="sp-main">

        <!-- Section Père -->
        <section class="sp-section">
          <div class="sp-section__header">
            <div class="sp-section__icon sp-section__icon--blue">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h2 class="sp-section__title">Père</h2>
          </div>
          <div class="sp-info-grid">
            <div class="sp-field">
              <span class="sp-field__label">Nom complet</span>
              <span class="sp-field__value">{{ fatherFullName }}</span>
            </div>
            <div class="sp-field">
              <span class="sp-field__label">Email</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.fatherEmail)) }}</span>
            </div>
            <div class="sp-field">
              <span class="sp-field__label">Téléphone</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.fatherPhone)) }}</span>
            </div>
          </div>
        </section>

        <!-- Section Mère -->
        <section class="sp-section">
          <div class="sp-section__header">
            <div class="sp-section__icon sp-section__icon--rose">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h2 class="sp-section__title">Mère</h2>
          </div>
          <div class="sp-info-grid">
            <div class="sp-field">
              <span class="sp-field__label">Nom complet</span>
              <span class="sp-field__value">{{ motherFullName }}</span>
            </div>
            <div class="sp-field">
              <span class="sp-field__label">Email</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.motherEmail)) }}</span>
            </div>
            <div class="sp-field">
              <span class="sp-field__label">Téléphone</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.motherPhone)) }}</span>
            </div>
          </div>
        </section>

        <!-- Section Contact principal -->
        <section class="sp-section">
          <div class="sp-section__header">
            <div class="sp-section__icon sp-section__icon--green">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <h2 class="sp-section__title">Contact principal</h2>
          </div>
          <div class="sp-info-grid">
            <div class="sp-field">
              <span class="sp-field__label">Nom complet</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.fullNameParent)) }}</span>
            </div>
            <div class="sp-field">
              <span class="sp-field__label">Email</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.emailContact)) }}</span>
            </div>
            <div class="sp-field">
              <span class="sp-field__label">Téléphone</span>
              <span class="sp-field__value">{{ displayOrNA(clean(parent.phoneContact)) }}</span>
            </div>
          </div>
        </section>

        <!-- Section Étudiants -->
        <section class="sp-section sp-section--flat">
          <div class="sp-section__header">
            <div class="sp-section__icon sp-section__icon--amber">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h2 class="sp-section__title">Étudiants associés</h2>
            <span class="sp-badge-count">{{ studentsCount }}</span>
          </div>

          <div v-if="normalizedStudents.length === 0" class="sp-empty">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <p>Aucun étudiant associé à ce parent.</p>
          </div>

          <div v-else class="sp-students-list">
            <div
                v-for="(s, i) in normalizedStudents"
                :key="s.id"
                class="sp-student-row"
                :style="`animation-delay: ${i * 60}ms`"
            >
              <div class="sp-student-avatar">{{ s.lastName.charAt(0) }}{{ s.firstName.charAt(0) }}</div>
              <div class="sp-student-info">
                <span class="sp-student-info__name">{{ s.lastName }} {{ s.firstName }}</span>
                <span class="sp-student-info__meta">Né(e) le {{ formatDate(s.birthDate) }}</span>
              </div>
              <span class="sp-student-level">{{ s.level || 'N/D' }}</span>
              <a
                  class="sp-btn-view"
                  :href="$routing.generate('app_student_show', { id: s.id })"
              >
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Voir la fiche
              </a>
            </div>
          </div>
        </section>

      </main>
    </div>

    <!-- ░░░░░░░░░░░░░░░░
         CHARTE (masquée)
         ░░░░░░░░░░░░░░░░ -->
    <div id="charter-sheet" class="sp-charter">
      <div class="sp-charter__header">
        <div class="sp-charter__branding">
          <img class="sp-charter__logo" src="/static/icons/logoccib38.webp" alt="Logo CCIB" loading="lazy" />
          <div>
            <h1 class="sp-charter__h1">Charte de l'Élève et du CCIB38</h1>
            <p class="sp-charter__subtitle">Centre Culturel Ibn Badis Grenoble</p>
          </div>
        </div>
        <div class="sp-charter__meta">
          <div class="sp-charter__meta-row">
            <span class="sp-charter__meta-label">Date d'émission</span>
            <span class="sp-charter__meta-value">{{ today }}</span>
          </div>
          <div class="sp-charter__meta-row">
            <span class="sp-charter__meta-label">N° Dossier</span>
            <span class="sp-charter__meta-value">{{ String(parent.id).padStart(6, '0') }}</span>
          </div>
        </div>
      </div>

      <div class="sp-charter__body">

        <!-- 1. Responsable légal -->
        <div class="sp-charter__section">
          <h2 class="sp-charter__section-title">
            <span class="sp-charter__num">1</span>
            Coordonnées du Responsable Légal
          </h2>
          <div class="sp-charter__info-grid">
            <div>
              <div class="sp-charter__field">
                <span class="sp-charter__field-label">Nom complet</span>
                <span class="sp-charter__field-value">{{ headerTitle }}</span>
              </div>
              <div class="sp-charter__field">
                <span class="sp-charter__field-label">Adresse e-mail</span>
                <span class="sp-charter__field-value">{{ primaryEmail || 'Non renseigné' }}</span>
              </div>
              <div class="sp-charter__field">
                <span class="sp-charter__field-label">Téléphone</span>
                <span class="sp-charter__field-value">{{ primaryPhone || 'Non renseigné' }}</span>
              </div>
            </div>
            <div>
              <span class="sp-charter__field-label">Adresse postale</span>
              <div class="sp-charter__address-box">{{ selectedStudentPostalAddress || 'Non renseignée' }}</div>
            </div>
          </div>
        </div>

        <!-- 2. Élève -->
        <div class="sp-charter__section">
          <h2 class="sp-charter__section-title">
            <span class="sp-charter__num">2</span>
            Élève Concerné par cette Charte
          </h2>
          <div v-if="selectedStudent" class="sp-charter__student-block">
            <div class="sp-charter__student-avatar">
              {{ selectedStudent.firstName.charAt(0) }}{{ selectedStudent.lastName.charAt(0) }}
            </div>
            <div>
              <div class="sp-charter__student-name">{{ selectedStudent.firstName }} {{ selectedStudent.lastName }}</div>
              <div class="sp-charter__student-meta">
                Né(e) le {{ formatDate(selectedStudent.birthDate) }}
                &nbsp;·&nbsp;
                Niveau : {{ displayOrNA(selectedStudent.level) }}
              </div>
            </div>
          </div>
          <div v-else class="sp-charter__no-student">Aucun élève sélectionné.</div>
        </div>

        <!-- 3. Règlement -->
        <div class="sp-charter__section">
          <h2 class="sp-charter__section-title">
            <span class="sp-charter__num">3</span>
            Règlement et Engagements Réciproques
          </h2>
          <p class="sp-charter__intro">
            Cette charte définit les engagements mutuels entre l'élève, ses parents et l'établissement
            afin de garantir un environnement d'apprentissage optimal et respectueux.
          </p>

          <div class="sp-charter__rules-grid">
            <div class="sp-charter__rule-cat">
              <h3 class="sp-charter__rule-title">Ponctualité et Assiduité</h3>
              <ul class="sp-charter__rule-list">
                <li><strong>Présence obligatoire :</strong> La présence à tous les cours est impérative pour assurer une progression pédagogique continue.</li>
                <li><strong>Gestion des retards :</strong> Un retard supérieur à 20 minutes ne permettra pas l'accès à la salle de classe, sauf justification validée par la direction.</li>
              </ul>
            </div>
            <div class="sp-charter__rule-cat">
              <h3 class="sp-charter__rule-title">Respect et Comportement</h3>
              <ul class="sp-charter__rule-list">
                <li><strong>Respect mutuel :</strong> Le respect envers les enseignants, le personnel et les autres étudiants est fondamental.</li>
                <li><strong>Comportement en classe :</strong> Une attitude calme et participative est attendue. Les appareils personnels doivent être éteints.</li>
                <li><strong>Préservation du matériel :</strong> Chaque élève s'engage à prendre soin du matériel et des locaux.</li>
              </ul>
            </div>
            <div class="sp-charter__rule-cat">
              <h3 class="sp-charter__rule-title">Engagement Scolaire</h3>
              <ul class="sp-charter__rule-list">
                <li><strong>Matériel requis :</strong> L'étudiant doit se présenter avec l'intégralité de son matériel scolaire. En cas d'oubli répété, l'enseignant peut refuser l'accès au cours.</li>
                <li><strong>Devoirs et travaux :</strong> Les travaux assignés doivent être réalisés dans les délais impartis.</li>
              </ul>
            </div>
            <div class="sp-charter__rule-cat">
              <h3 class="sp-charter__rule-title">Communication École-Famille</h3>
              <ul class="sp-charter__rule-list">
                <li><strong>Suivi personnalisé :</strong> En cas de difficultés récurrentes, un entretien tripartite sera organisé.</li>
                <li><strong>Information régulière :</strong> Les parents seront informés des progrès et difficultés de leur enfant.</li>
              </ul>
            </div>
          </div>

          <div class="sp-charter__payment">
            <h3 class="sp-charter__payment-title">Modalités Financières</h3>
            <div class="sp-charter__term"><strong>Paiement trimestriel :</strong> Les frais de scolarité sont exigibles au début de chaque trimestre.</div>
            <div class="sp-charter__term"><strong>Droit de rétractation :</strong> Un remboursement intégral est possible après le premier cours uniquement.</div>
            <div class="sp-charter__term sp-charter__term--important"><strong>Aucun remboursement :</strong> Une fois le deuxième cours entamé, tout trimestre commencé est dû intégralement.</div>
          </div>

          <div class="sp-charter__sanctions">
            <h3 class="sp-charter__sanctions-title">Mesures Disciplinaires</h3>
            <p>Le non-respect répété peut entraîner des sanctions graduelles : avertissement oral, avertissement écrit, convocation des parents, et en dernier recours, l'exclusion définitive.</p>
          </div>
        </div>

        <!-- Signatures -->
        <div class="sp-charter__signatures">
          <div class="sp-charter__sig-location">
            Établi à <strong>Grenoble</strong>, le <strong>{{ today }}</strong>
          </div>
          <div class="sp-charter__sig-blocks">
            <div class="sp-charter__sig-block">
              <div class="sp-charter__sig-title">Signature du Responsable Légal</div>
              <div class="sp-charter__sig-area"></div>
              <div class="sp-charter__sig-name">{{ headerTitle }}</div>
              <div class="sp-charter__sig-note">(Précédée de « Lu et approuvé »)</div>
            </div>
            <div class="sp-charter__sig-block">
              <div class="sp-charter__sig-title">Cachet et signature du centre</div>
              <div class="sp-charter__sig-area"></div>
              <div class="sp-charter__sig-name">Centre Culturel Ibn Badis Grenoble</div>
              <div class="sp-charter__sig-note">Direction Pédagogique</div>
            </div>
          </div>
          <div class="sp-charter__footer-text">
            Cette charte, signée par les deux parties, engage moralement et pédagogiquement l'élève et sa famille.
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: "ShowParent",
  props: {
    parent:   { type: Object, required: true },
    students: { type: Array,  required: true },
  },
  data() {
    return {
      selectedStudentId: 0,
    };
  },
  computed: {
    today() {
      return new Date().toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit", year: "numeric" });
    },
    fatherFullName() {
      const ln = this.clean(this.parent.fatherLastName);
      const fn = this.clean(this.parent.fatherFirstName);
      return ln || fn ? `${ln} ${fn}`.trim() : "Non disponible";
    },
    motherFullName() {
      const ln = this.clean(this.parent.motherLastName);
      const fn = this.clean(this.parent.motherFirstName);
      return ln || fn ? `${ln} ${fn}`.trim() : "Non disponible";
    },
    primaryEmail() {
      return this.clean(this.parent.emailContact) || this.clean(this.parent.fatherEmail) || this.clean(this.parent.motherEmail) || "";
    },
    primaryPhone() {
      return this.clean(this.parent.phoneContact) || this.clean(this.parent.fatherPhone) || this.clean(this.parent.motherPhone) || "";
    },
    headerTitle() {
      return this.clean(this.parent.fullNameParent) || this.fatherFullName || "Fiche Parent";
    },
    headerSubtitle() {
      const email = this.primaryEmail || "Email non renseigné";
      const phone = this.primaryPhone || "Tél. non renseigné";
      return `${email} · ${phone}`;
    },
    headerInitials() {
      const parts = this.headerTitle.split(" ").filter(Boolean);
      return parts.slice(0, 2).map(p => p[0]?.toUpperCase() || "").join("") || "P";
    },
    studentsCount() { return Array.isArray(this.students) ? this.students.length : 0; },
    normalizedStudents() {
      if (!Array.isArray(this.students)) return [];
      return this.students.map(s => ({
        id:         s.id,
        lastName:   this.clean(s.lastName)   || "—",
        firstName:  this.clean(s.firstName)  || "—",
        birthDate:  s.birthDate || null,
        level:      this.clean(s.level)      || null,
        address:    this.clean(s.address)    || "",
        postalCode: this.clean(s.postalCode) || "",
        city:       this.clean(s.city)       || "",
      }));
    },
    selectedStudent() {
      if (!this.selectedStudentId) return null;
      return this.normalizedStudents.find(s => s.id === this.selectedStudentId) || null;
    },
    selectedStudentPostalAddress() {
      if (this.selectedStudent) {
        const lines = [];
        if (this.selectedStudent.address) lines.push(this.selectedStudent.address);
        const cpVille = [this.selectedStudent.postalCode, this.selectedStudent.city].filter(Boolean).join(" ");
        if (cpVille) lines.push(cpVille);
        if (lines.length) return lines.join("\n");
      }
      return this.clean(this.parent.address) || this.clean(this.parent.postalAddress) || "";
    },
  },
  methods: {
    clean(v) {
      if (v === null || v === undefined) return "";
      const s = String(v).trim();
      return s.toLowerCase() === "vide" ? "" : s;
    },
    displayOrNA(v) {
      return v && String(v).trim() !== "" ? v : "Non renseigné(e)";
    },
    formatDate(date) {
      if (!date) return "Non disponible";
      try { return new Date(date).toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit", year: "numeric" }); }
      catch { return "Non disponible"; }
    },
    printCharter() {
      if (!this.selectedStudent) return;
      const el = document.getElementById("charter-sheet");
      if (!el) return;
      const win = window.open("", "_blank");
      const html = `<html><head><meta charset="utf-8"/><title>Charte - ${this.selectedStudent.lastName} ${this.selectedStudent.firstName}</title><style>${this.getPrintCSS()}</style></head><body>${el.outerHTML}</body></html>`;
      win.document.write(html);
      win.document.close();
      win.focus();
      setTimeout(() => { win.print(); win.close(); }, 250);
    },
    getPrintCSS() {
      return `
        @page { size: A4; margin: 15mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; color: #2c3e50; line-height: 1.45; margin: 0; padding: 0; font-size: 13px; }
        .sp-charter { display: block !important; }
        .sp-charter__header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 14px; border-bottom: 2px solid #34495e; margin-bottom: 18px; }
        .sp-charter__branding { display: flex; align-items: center; gap: 12px; }
        .sp-charter__logo { height: 44px; width: auto; }
        .sp-charter__h1 { font-size: 19px; font-weight: 700; margin: 0; }
        .sp-charter__subtitle { font-size: 11px; color: #7f8c8d; margin: 3px 0 0; }
        .sp-charter__meta { text-align: right; font-size: 11px; }
        .sp-charter__meta-row { margin-bottom: 3px; }
        .sp-charter__meta-label { font-weight: 600; color: #7f8c8d; }
        .sp-charter__meta-value { color: #2c3e50; margin-left: 5px; }
        .sp-charter__section { margin-bottom: 16px; }
        .sp-charter__section-title { font-size: 15px; font-weight: 700; color: #34495e; margin: 0 0 10px; display: flex; align-items: center; gap: 8px; }
        .sp-charter__num { background: #3498db; color: #fff; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
        .sp-charter__info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .sp-charter__field { margin-bottom: 8px; }
        .sp-charter__field-label { font-weight: 600; color: #7f8c8d; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 2px; }
        .sp-charter__field-value { font-size: 13px; color: #2c3e50; }
        .sp-charter__address-box { border: 1px solid #bdc3c7; border-radius: 4px; padding: 8px; min-height: 42px; white-space: pre-line; font-size: 12px; background: #f8f9fa; margin-top: 4px; }
        .sp-charter__student-block { display: flex; align-items: center; gap: 12px; border: 1px solid #3498db; border-radius: 6px; padding: 12px; background: linear-gradient(135deg, #f8f9fa, #e3f2fd); }
        .sp-charter__student-avatar { width: 38px; height: 38px; border-radius: 50%; background: #3498db; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
        .sp-charter__student-name { font-size: 15px; font-weight: 700; color: #2c3e50; }
        .sp-charter__student-meta { font-size: 11px; color: #7f8c8d; margin-top: 3px; }
        .sp-charter__intro { font-style: italic; color: #7f8c8d; font-size: 12px; margin-bottom: 12px; text-align: justify; }
        .sp-charter__rules-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
        .sp-charter__rule-cat { page-break-inside: avoid; }
        .sp-charter__rule-title { font-size: 13px; font-weight: 600; color: #34495e; margin: 0 0 5px; }
        .sp-charter__rule-list { margin: 0; padding-left: 14px; list-style: none; }
        .sp-charter__rule-list li { font-size: 11px; line-height: 1.4; margin-bottom: 4px; position: relative; }
        .sp-charter__rule-list li::before { content: "•"; color: #3498db; font-weight: 700; position: absolute; left: -10px; }
        .sp-charter__payment { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 11px; margin: 12px 0; page-break-inside: avoid; }
        .sp-charter__payment-title { font-size: 13px; color: #856404; font-weight: 600; margin: 0 0 7px; }
        .sp-charter__term { font-size: 11px; margin-bottom: 5px; line-height: 1.4; }
        .sp-charter__term--important { font-weight: 600; color: #721c24; }
        .sp-charter__sanctions { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; padding: 11px; page-break-inside: avoid; }
        .sp-charter__sanctions-title { font-size: 13px; color: #721c24; font-weight: 600; margin: 0 0 5px; }
        .sp-charter__sanctions p { font-size: 11px; margin: 0; line-height: 1.4; color: #721c24; }
        .sp-charter__signatures { margin-top: 22px; page-break-inside: avoid; }
        .sp-charter__sig-location { text-align: right; font-size: 11px; margin-bottom: 16px; }
        .sp-charter__sig-blocks { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .sp-charter__sig-block { text-align: center; }
        .sp-charter__sig-title { font-weight: 600; font-size: 11px; color: #34495e; margin-bottom: 12px; }
        .sp-charter__sig-area { height: 48px; border-bottom: 1px solid #2c3e50; margin: 12px 0 8px; }
        .sp-charter__sig-name { font-weight: 600; font-size: 11px; color: #2c3e50; }
        .sp-charter__sig-note { font-size: 9px; color: #7f8c8d; font-style: italic; margin-top: 3px; }
        .sp-charter__footer-text { margin-top: 16px; padding-top: 12px; border-top: 1px solid #ecf0f1; font-size: 10px; color: #7f8c8d; font-style: italic; text-align: center; }
      `;
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap');

/* ═══════════ ROOT ═══════════ */
.sp-root {
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  color: #1a2332;
  background: #f4f6f9;
  min-height: 100vh;
  padding: 0 0 48px;
}

/* ═══════════ TOPBAR ═══════════ */
.sp-topbar {
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
}
.sp-topbar__center { display: flex; align-items: center; gap: 6px; font-size: .85rem; }
.sp-topbar__breadcrumb { color: #7a8899; }
.sp-topbar__sep { color: #e4e8ef; font-size: 1rem; }
.sp-topbar__current { font-weight: 600; }
.sp-topbar__actions { display: flex; align-items: center; gap: 8px; }

/* Sélecteur élève inline */
.sp-student-select-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.sp-select-icon {
  position: absolute;
  left: 10px;
  color: #7a8899;
  pointer-events: none;
  z-index: 1;
}
.sp-student-select {
  appearance: none;
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .83rem;
  font-weight: 500;
  color: #1a2332;
  background: #f4f6f9;
  border: 1.5px solid #e4e8ef;
  border-radius: 8px;
  padding: 0 34px 0 32px;
  height: 36px;
  cursor: pointer;
  transition: border-color .15s;
  outline: none;
  min-width: 220px;
}
.sp-student-select:focus { border-color: #3b6ef5; background: #fff; }
.sp-select-arrow {
  position: absolute;
  right: 10px;
  color: #7a8899;
  pointer-events: none;
}

/* ═══════════ BUTTONS ═══════════ */
.sp-btn {
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
.sp-btn--ghost { background: transparent; color: #7a8899; border: 1px solid transparent; }
.sp-btn--ghost:hover { background: #f4f6f9; color: #1a2332; }
.sp-btn--primary { background: #3b6ef5; color: #fff; border: 1px solid #3b6ef5; box-shadow: 0 2px 8px rgba(59,110,245,.3); }
.sp-btn--primary:hover { background: #2d5de0; text-decoration: none; color: #fff; }
.sp-btn--teal { background: #0fb87a; color: #fff; border: 1px solid #0fb87a; box-shadow: 0 2px 8px rgba(15,184,122,.25); }
.sp-btn--teal:hover { background: #0aa36c; }
.sp-btn:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

/* ═══════════ INFO BANNER ═══════════ */
.sp-info-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 16px 28px 0;
  padding: 12px 16px;
  background: #ebf0ff;
  border: 1px solid #c5d5fb;
  border-left: 4px solid #3b6ef5;
  border-radius: 10px;
  font-size: .87rem;
  font-weight: 500;
  color: #2d5de0;
}
.sp-alert-enter-active, .sp-alert-leave-active { transition: all .25s ease; }
.sp-alert-enter-from, .sp-alert-leave-to { opacity: 0; transform: translateY(-6px); }

/* ═══════════ LAYOUT ═══════════ */
.sp-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 24px;
  max-width: 1280px;
  margin: 24px auto 0;
  padding: 0 28px;
}
@media (max-width: 900px) {
  .sp-layout { grid-template-columns: 1fr; }
  .sp-sidebar { display: none; }
}

/* ═══════════ SIDEBAR ═══════════ */
.sp-sidebar { display: flex; flex-direction: column; gap: 16px; }

.sp-id-card {
  background: #ffffff;
  border: 1px solid #e4e8ef;
  border-radius: 14px;
  padding: 24px 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
  text-align: center;
}
.sp-avatar {
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
.sp-id-card__name { font-size: 1.05rem; font-weight: 700; margin-bottom: 4px; }
.sp-id-card__sub { font-size: .76rem; color: #7a8899; margin-bottom: 16px; word-break: break-word; }
.sp-id-card__pills { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
.sp-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: .76rem;
  font-weight: 600;
}
.sp-pill--blue  { background: #ebf0ff; color: #3b6ef5; }
.sp-pill--slate { background: #f1f5fb; color: #5a6a7e; }

.sp-amounts-card {
  background: #ffffff;
  border: 1px solid #e4e8ef;
  border-radius: 14px;
  padding: 18px 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
}
.sp-amounts-card__title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #7a8899; margin-bottom: 10px; }
.sp-amount-row { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; }
.sp-amount-row__label { font-size: .84rem; color: #7a8899; font-weight: 500; }
.sp-amount-row__value { font-size: 1.05rem; font-weight: 700; }
.sp-amount-row__value small { font-size: .7rem; color: #7a8899; margin-left: 2px; }
.sp-amount-divider { height: 1px; background: #e4e8ef; margin: 8px 0; }
.sp-history-row { display: flex; flex-direction: column; gap: 1px; padding: 4px 0; }
.sp-history-row__date { font-size: .72rem; color: #7a8899; }
.sp-history-row__note { font-size: .78rem; color: #1a2332; font-weight: 500; }

.sp-selected-student-card {
  background: #ffffff;
  border: 1.5px solid #3b6ef5;
  border-radius: 14px;
  padding: 18px 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(59,110,245,.08);
  text-align: center;
}
.sp-selected-student-card__label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #3b6ef5; margin-bottom: 10px; }
.sp-selected-student-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b6ef5, #764ba2);
  color: #fff;
  font-size: 1.1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 10px;
}
.sp-selected-student-card__name { font-weight: 700; font-size: .95rem; margin-bottom: 4px; }
.sp-selected-student-card__meta { font-size: .78rem; color: #7a8899; }

/* ═══════════ MAIN / SECTIONS ═══════════ */
.sp-main { display: flex; flex-direction: column; gap: 20px; }

.sp-section {
  background: #ffffff;
  border: 1px solid #e4e8ef;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 16px rgba(0,0,0,.05);
}
.sp-section--flat { padding-bottom: 0; overflow: hidden; }

.sp-section__header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.sp-section__icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.sp-section__icon--blue  { background: #ebf0ff; color: #3b6ef5; }
.sp-section__icon--rose  { background: #ffeef7; color: #e84393; }
.sp-section__icon--green { background: #e4faf3; color: #0fb87a; }
.sp-section__icon--amber { background: #fff8eb; color: #f5a623; }
.sp-section__title { font-size: 1rem; font-weight: 700; margin: 0; }
.sp-badge-count {
  margin-left: auto;
  background: #fff8eb;
  color: #f5a623;
  font-size: .78rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
}

/* Info grid (lecture seule) */
.sp-info-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
@media (max-width: 680px) { .sp-info-grid { grid-template-columns: 1fr; } }

.sp-field { display: flex; flex-direction: column; gap: 5px; }
.sp-field__label {
  font-size: .76rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .4px;
  color: #7a8899;
}
.sp-field__value {
  font-size: .92rem;
  font-weight: 500;
  color: #1a2332;
  background: #f4f6f9;
  border: 1.5px solid #e4e8ef;
  border-radius: 8px;
  padding: 9px 13px;
  min-height: 40px;
  display: flex;
  align-items: center;
}

/* ═══════════ STUDENTS LIST ═══════════ */
.sp-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 40px;
  color: #7a8899;
  font-size: .9rem;
}
.sp-students-list { display: flex; flex-direction: column; }
.sp-student-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 24px;
  border-top: 1px solid #e4e8ef;
  animation: sp-slide-in .3s ease both;
  transition: background .15s;
}
.sp-student-row:hover { background: #f4f6f9; }
@keyframes sp-slide-in {
  from { opacity: 0; transform: translateX(-10px); }
  to   { opacity: 1; transform: translateX(0); }
}
.sp-student-avatar {
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
}
.sp-student-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.sp-student-info__name { font-weight: 600; font-size: .9rem; }
.sp-student-info__meta { font-size: .78rem; color: #7a8899; }
.sp-student-level {
  font-size: .78rem;
  font-weight: 600;
  padding: 4px 10px;
  background: #e4faf3;
  color: #0fb87a;
  border-radius: 20px;
}
.sp-btn-view {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 14px;
  font-family: 'DM Sans', 'Segoe UI', sans-serif;
  font-size: .78rem;
  font-weight: 600;
  color: #3b6ef5;
  background: #ebf0ff;
  border-radius: 7px;
  text-decoration: none;
  transition: all .15s;
  flex-shrink: 0;
}
.sp-btn-view:hover { background: #d1dcfd; text-decoration: none; color: #2d5de0; }

/* ═══════════ CHARTE (cachée) ═══════════ */
.sp-charter { display: none; }

@media print {
  body * { visibility: hidden !important; }
  #charter-sheet, #charter-sheet * { visibility: visible !important; }
  #charter-sheet { position: absolute; left: 0; top: 0; width: 100%; }
  .sp-charter { display: block !important; }
}
</style>