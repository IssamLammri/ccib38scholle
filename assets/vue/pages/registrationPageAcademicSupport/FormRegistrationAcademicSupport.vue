<template>
  <div class="registration-container">
    <div class="main-card">
      <!-- Header -->
      <div class="card-header">
        <h1><i class="bi bi-mortarboard-fill me-2"></i>Inscription au soutien scolaire</h1>
        <p>Accompagnons votre enfant vers la réussite</p>
      </div>

      <div class="form-content">
        <!-- Stepper -->
        <div class="stepper">
          <div class="progress-bar" :style="{ width: progressPercent + '%' }"></div>

          <div
              v-for="(label, idx) in stepLabels"
              :key="idx"
              class="step"
              :class="{ active: currentStep === idx, completed: currentStep > idx }"
          >
            <div class="step-circle">
              <i v-if="currentStep > idx" class="bi bi-check-lg"></i>
              <span v-else>{{ idx + 1 }}</span>
            </div>
            <div class="step-label">{{ label }}</div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm">
          <fieldset :disabled="hasSubmitted">
            <!-- Étape 1: Informations élève -->
            <div class="form-section" :class="{ active: currentStep === 0 }">
              <h2 class="section-title">
                <i class="bi bi-person-circle"></i>
                Informations de l'élève
              </h2>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="studentFirstName">
                      <i class="bi bi-person"></i>
                      Prénom de l'élève
                    </label>
                    <input
                        id="studentFirstName"
                        type="text"
                        class="form-control"
                        v-model.trim="form.studentFirstName"
                        placeholder="Prénom de l'élève"
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="studentLastName">
                      <i class="bi bi-person-vcard"></i>
                      Nom de l'élève
                    </label>
                    <input
                        id="studentLastName"
                        type="text"
                        class="form-control"
                        v-model.trim="form.studentLastName"
                        placeholder="Nom de l'élève"
                    />
                  </div>
                </div>
              </div>

              <!-- AJOUT: Date de naissance avec Flatpickr -->
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="studentBirthDate">
                      <i class="bi bi-calendar-date"></i>
                      Date de naissance
                    </label>
                    <div class="input-group">
        <span class="input-group-text">
          <i class="bi bi-calendar-event"></i>
        </span>
                      <flatpickr
                          id="studentBirthDate"
                          v-model="form.studentBirthDate"
                          :config="flatpickrDobConfig"
                          class="form-control bg-white"
                          placeholder="JJ/MM/AAAA"
                          @input="dobTouched = true"
                      />
                    </div>
                    <div class="invalid-feedback" v-show="dobTouched && !birthdateValid">
                      Date de naissance invalide (ne doit pas être dans le futur).
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="level">
                  <i class="bi bi-mortarboard"></i>
                  Niveau d'étude
                </label>
                <select id="level" class="form-select" v-model="form.level">
                  <option value="">Choisir un niveau</option>
                  <option v-for="lvl in levels" :key="lvl" :value="lvl">{{ lvl }}</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">
                  <i class="bi bi-bookmarks"></i>
                  Matières de soutien
                </label>
                <div class="subjects-grid">
                  <div
                      v-for="s in subjects"
                      :key="s.value"
                      class="subject-card"
                      :class="{ selected: form.subjects.includes(s.value) }"
                      @click="toggleSubject(s.value)"
                  >
                    <div class="subject-label">
                      <div class="subject-icon">
                        <i class="bi bi-check"></i>
                      </div>
                      <i :class="s.icon + ' me-2'"></i>{{ s.label }}
                    </div>
                  </div>
                </div>
                <div v-show="subjectsError" class="alert-custom alert-danger-custom mt-2">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                  Veuillez sélectionner au moins une matière.
                </div>
              </div>
            </div>

            <!-- Étape 2: Responsables -->
            <div class="form-section" :class="{ active: currentStep === 1 }">
              <h2 class="section-title">
                <i class="bi bi-people"></i>
                Informations des responsables
              </h2>

              <!-- Parent principal -->
              <h3 class="h5 text-primary mb-3">
                <i class="bi bi-person-badge me-2"></i>Responsable principal
              </h3>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="parentFirstName">
                      <i class="bi bi-person"></i>
                      Prénom du parent
                    </label>
                    <input
                        id="parentFirstName"
                        type="text"
                        class="form-control"
                        v-model.trim="form.parentFirstName"
                        placeholder="Prénom du parent"
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="parentLastName">
                      <i class="bi bi-person-vcard"></i>
                      Nom du parent
                    </label>
                    <input
                        id="parentLastName"
                        type="text"
                        class="form-control"
                        v-model.trim="form.parentLastName"
                        placeholder="Nom du parent"
                    />
                  </div>
                </div>
              </div>

              <!-- Adresse -->
              <div class="form-group">
                <label class="form-label" for="address">
                  <i class="bi bi-geo-alt"></i>
                  Adresse postale
                </label>
                <input
                    id="address"
                    type="text"
                    class="form-control"
                    v-model.trim="form.address"
                    placeholder="N° et rue"
                />
              </div>

              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label" for="postalCode">
                      <i class="bi bi-mailbox"></i>
                      Code postal
                    </label>
                    <input
                        id="postalCode"
                        type="text"
                        class="form-control"
                        v-model.trim="form.postalCode"
                        placeholder="75000"
                    />
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label class="form-label" for="city">
                      <i class="bi bi-building"></i>
                      Ville
                    </label>
                    <input
                        id="city"
                        type="text"
                        class="form-control"
                        v-model.trim="form.city"
                        placeholder="Paris"
                    />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="phone">
                  <i class="bi bi-telephone"></i>
                  Téléphone du parent
                </label>
                <input
                    id="phone"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': phoneTouched && !phoneValid }"
                    v-model.trim="form.phone"
                    placeholder="06 12 34 56 78"
                    @input="phoneTouched = true"
                />
                <div class="invalid-feedback" v-show="phoneTouched && !phoneValid">Numéro de téléphone invalide.</div>
              </div>

              <!-- Mère -->
              <h3 class="h5 text-primary mb-3 mt-4">
                <i class="bi bi-person-heart me-2"></i>Mère (optionnel)
              </h3>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="motherFirstName">
                      <i class="bi bi-person"></i>
                      Prénom de la mère
                    </label>
                    <input
                        id="motherFirstName"
                        type="text"
                        class="form-control"
                        v-model.trim="form.motherFirstName"
                        placeholder="Prénom de la mère"
                    />
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" for="motherLastName">
                      <i class="bi bi-person-vcard"></i>
                      Nom de la mère
                    </label>
                    <input
                        id="motherLastName"
                        type="text"
                        class="form-control"
                        v-model.trim="form.motherLastName"
                        placeholder="Nom de la mère"
                    />
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="motherPhone">
                  <i class="bi bi-telephone"></i>
                  Téléphone de la mère
                </label>
                <input
                    id="motherPhone"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': motherPhoneTouched && !motherPhoneValid }"
                    v-model.trim="form.motherPhone"
                    placeholder="06 12 34 56 78"
                    @input="motherPhoneTouched = true"
                />
                <div class="invalid-feedback" v-show="motherPhoneTouched && !motherPhoneValid">Numéro de téléphone invalide.</div>
              </div>

              <!-- Email -->
              <div class="form-group">
                <label class="form-label" for="email">
                  <i class="bi bi-envelope"></i>
                  Email de contact
                </label>
                <input
                    id="email"
                    type="email"
                    class="form-control"
                    :class="{ 'is-invalid': emailTouched && !emailValid }"
                    v-model.trim="form.email"
                    placeholder="exemple@gmail.com"
                    @input="emailTouched = true"
                />
                <div class="invalid-feedback" v-show="emailTouched && !emailValid">Adresse email invalide.</div>
              </div>

              <!-- At least one phone -->
              <div
                  class="alert-custom alert-danger-custom"
                  v-show="triedNext && !atLeastOnePhone"
              >
                <i class="bi bi-exclamation-triangle-fill"></i>
                Veuillez renseigner au moins un numéro de téléphone.
              </div>
            </div>

            <!-- Étape 3: Validation -->
            <div class="form-section" :class="{ active: currentStep === 2 }">
              <h2 class="section-title">
                <i class="bi bi-check2-circle"></i>
                Validation de l'inscription
              </h2>

              <!-- Récap -->
              <div class="summary-card">
                <h3 class="h5 mb-3">
                  <i class="bi bi-journal-text me-2"></i>Récapitulatif de votre inscription
                </h3>
                <div class="summary-item">
                  <div class="summary-icon"><i class="bi bi-person-circle"></i></div>
                  <div>
                    <strong>Élève :</strong> {{ summaryStudent }}<br>
                    <small class="text-muted">Niveau : {{ form.level || '—' }}</small><br>
                    <small class="text-muted">
                      Né(e) le : {{ formattedBirthDate || '—' }}
                      <template v-if="studentAge !== null"> ({{ studentAge }} ans)</template>
                    </small>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="summary-icon"><i class="bi bi-bookmarks"></i></div>
                  <div>
                    <strong>Matières :</strong><br>
                    <small class="text-muted">{{ form.subjects.join(', ') || '—' }}</small>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="summary-icon"><i class="bi bi-people"></i></div>
                  <div>
                    <strong>Responsable :</strong> {{ form.parentFirstName }} {{ form.parentLastName }}<br>
                    <template v-if="form.motherFirstName || form.motherLastName">
                      <strong>Mère :</strong> {{ form.motherFirstName }} {{ form.motherLastName }}<br>
                    </template>
                    <small class="text-muted">Email : {{ form.email || '—' }}</small>
                  </div>
                </div>
                <div class="summary-item">
                  <div class="summary-icon"><i class="bi bi-telephone"></i></div>
                  <div>
                    <strong>Téléphones :</strong><br>
                    <small class="text-muted">{{ summaryPhones }}</small>
                  </div>
                </div>
              </div>

              <!-- Accords -->
              <div class="agreement-card" :class="{ checked: form.legalDeclaration }" @click="form.legalDeclaration = !form.legalDeclaration">
                <div class="agreement-header">
                  <input type="checkbox" class="agreement-checkbox" v-model="form.legalDeclaration" @click.stop>
                  <h4 class="agreement-title">
                    <i class="bi bi-file-earmark-check text-success me-2"></i>
                    Certification des informations
                  </h4>
                </div>
                <p class="mb-0">Je certifie l'exactitude de toutes les informations saisies dans ce formulaire.</p>
              </div>

              <div class="agreement-card" :class="{ checked: form.paymentTerms }" @click="form.paymentTerms = !form.paymentTerms">
                <div class="agreement-header">
                  <input type="checkbox" class="agreement-checkbox" v-model="form.paymentTerms" @click.stop>
                  <h4 class="agreement-title">
                    <i class="bi bi-credit-card text-info me-2"></i>
                    Tarification & Conditions de paiement
                  </h4>
                </div>
                <ul class="mb-0 ps-4">
                  <li>Prix du soutien scolaire : <strong>30 € / mois / enfant</strong></li>
                  <li>Tarif préférentiel : <strong>25 € / mois / enfant</strong> si 2+ enfants inscrits ou plusieurs matières</li>
                  <li>Paiement obligatoire du 1er trimestre (<strong>octobre</strong>, <strong>novembre</strong>, <strong>décembre</strong>) avant acceptation</li>
                  <li>Modes de paiement : <strong>cash</strong>, <strong>carte bancaire</strong> ou <strong>chèques</strong> échelonnés</li>
                </ul>
              </div>
            </div>

            <!-- Boutons navigation -->
            <div class="btn-nav">
              <button
                  type="button"
                  id="prevBtn"
                  class="btn-custom btn-outline-custom"
                  v-show="currentStep > 0"
                  @click="previousStep"
              >
                <i class="bi bi-arrow-left"></i>
                Précédent
              </button>
              <div v-show="currentStep === 0" style="flex:1"></div>

              <button
                  type="button"
                  id="nextBtn"
                  class="btn-custom btn-primary-custom"
                  v-show="currentStep < stepLabels.length - 1"
                  @click="nextStep"
              >
                Suivant
                <i class="bi bi-arrow-right"></i>
              </button>

              <button
                  type="submit"
                  id="submitBtn"
                  class="btn-custom btn-success-custom"
                  :disabled="isSubmitting || !finalValid || hasSubmitted"
                  v-show="currentStep === stepLabels.length - 1"
              >
                <template v-if="isSubmitting">
                  <div class="spinner"></div> Envoi en cours...
                </template>
                <template v-else-if="hasSubmitted">
                  <i class="bi bi-check2-circle"></i>
                  Inscription envoyée
                </template>
                <template v-else>
                  <i class="bi bi-check-lg"></i>
                  Envoyer l'inscription
                </template>
              </button>
            </div>
          </fieldset>
        </form>
      </div>
    </div>

    <!-- Modal succès -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="successModalLabel">
              <i class="bi bi-check-circle-fill me-2"></i>
              Inscription réussie !
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <i class="bi bi-emoji-smile display-4 text-success mb-3"></i>
              <p class="lead">Félicitations ! Votre inscription a été enregistrée avec succès.</p>
              <p>Notre équipe vous contactera très bientôt pour finaliser l'inscription de votre enfant.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal" @click="reloadPage">
              <i class="bi bi-person-plus"></i>
              Inscrire un autre enfant
            </button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
              <i class="bi bi-house"></i>
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { French } from 'flatpickr/dist/l10n/fr.js';

export default {
  name: 'RegistrationAcademicSupport',
  components: { Flatpickr },
  data() {
    return {
      // UI state
      currentStep: 0,
      hasSubmitted: false,
      flatpickrDobConfig: {
        locale: French,
        dateFormat: 'Y-m-d',   // valeur de v-model => 2025-09-18 (pour le back)
        altInput: true,        // champ de saisie “humain”
        altFormat: 'd/m/Y',    // affichage => 18/09/2025
        maxDate: 'today',      // pas de date future
        allowInput: true
      },
      isSubmitting: false,
      triedNext: false,
      emailTouched: false,
      phoneTouched: false,
      motherPhoneTouched: false,
      dobTouched: false, // AJOUT

      // Labels
      stepLabels: ['Élève & Matières', 'Responsables', 'Validation'],

      // Reference data
      levels: ['CP','CE1','CE2','CM1','CM2','6ème','5ème','4ème','3ème','2nde','1ère','Terminale'],
      subjects: [
        { value: 'Math', label: 'Mathématiques', icon: 'bi bi-calculator' },
        { value: 'Français', label: 'Français', icon: 'bi bi-book' },
        { value: 'Anglais', label: 'Anglais', icon: 'bi bi-globe' },
        { value: 'Physique/Chimie', label: 'Physique/Chimie', icon: 'bi bi-gear' },
        { value: 'SVT', label: 'SVT', icon: 'bi bi-tree' },
        { value: 'Informatique', label: 'Informatique', icon: 'bi bi-laptop' },
        { value: 'ST', label: 'ST', icon: 'bi bi-translate' },
      ],

      // Form model
      form: {
        studentFirstName: '',
        studentLastName: '',
        studentBirthDate: '',     // AJOUT
        level: '',
        subjects: [],
        parentFirstName: '',
        parentLastName: '',
        phone: '',
        motherFirstName: '',
        motherLastName: '',
        motherPhone: '',
        email: '',
        address: '',              // AJOUT (déjà utilisé dans ton template)
        postalCode: '',           // AJOUT
        city: '',                 // AJOUT
        legalDeclaration: false,
        paymentTerms: false,
      },
    };
  },
  computed: {
    todayIso() {
      const d = new Date();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${d.getFullYear()}-${m}-${day}`;
    },
    birthdateValid() {
      if (!this.form.studentBirthDate) return false;
      const d = new Date(this.form.studentBirthDate);
      if (isNaN(d.getTime())) return false;
      const today = new Date(this.todayIso);
      return d <= today;
    },
    formattedBirthDate() {
      if (!this.form.studentBirthDate) return '';
      const [y, m, d] = this.form.studentBirthDate.split('-');
      if (!y || !m || !d) return '';
      return `${d}/${m}/${y}`; // affichage FR
    },
    studentAge() {
      if (!this.birthdateValid) return null;
      const [y, m, d] = this.form.studentBirthDate.split('-');
      const dob = new Date(`${y}-${m}-${d}`);
      const today = new Date();
      let age = today.getFullYear() - dob.getFullYear();
      const mm = today.getMonth() - dob.getMonth();
      if (mm < 0 || (mm === 0 && today.getDate() < dob.getDate())) age--;
      return age >= 0 ? age : null;
    },
    progressPercent() {
      const steps = this.stepLabels.length - 1;
      return (this.currentStep / steps) * 100;
    },
    subjectsError() {
      return this.triedNext && this.currentStep === 0 && this.form.subjects.length === 0;
    },
    emailValid() {
      if (!this.form.email) return false;
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email);
    },
    phoneValid() {
      if (!this.form.phone) return true;
      return /^(?:0|\+33\s?)[1-9](?:[ .-]?\d{2}){4}$/.test(this.form.phone);
    },
    motherPhoneValid() {
      if (!this.form.motherPhone) return true;
      return /^(?:0|\+33\s?)[1-9](?:[ .-]?\d{2}){4}$/.test(this.form.motherPhone);
    },
    atLeastOnePhone() {
      return !!(this.form.phone || this.form.motherPhone);
    },
    summaryStudent() {
      const fn = this.form.studentFirstName || '';
      const ln = this.form.studentLastName || '';
      return `${fn} ${ln}`.trim() || '—';
    },
    summaryPhones() {
      const a = this.form.phone || '—';
      const b = this.form.motherPhone ? ` / ${this.form.motherPhone}` : '';
      return a + b;
    },
    // Validation per step
    step0Valid() {
      return !!(
          this.form.studentFirstName &&
          this.form.studentLastName &&
          this.birthdateValid &&          // AJOUT: la date devient obligatoire et valide
          this.form.level &&
          this.form.subjects.length > 0
      );
    },
    step1Valid() {
      const namesOk = !!(this.form.parentFirstName && this.form.parentLastName);
      const emailOk = this.emailValid;
      const phonesPresenceOk = this.atLeastOnePhone;
      const phoneFormatsOk = this.phoneValid && this.motherPhoneValid;
      return namesOk && emailOk && phonesPresenceOk && phoneFormatsOk;
    },
    step2Valid() {
      return !!(this.form.legalDeclaration && this.form.paymentTerms);
    },
    finalValid() {
      return this.step0Valid && this.step1Valid && this.step2Valid;
    },
  },
  methods: {
    toggleSubject(value) {
      const i = this.form.subjects.indexOf(value);
      if (i > -1) this.form.subjects.splice(i, 1); else this.form.subjects.push(value);
    },
    previousStep() {
      if (this.currentStep > 0) this.currentStep--;
      this.triedNext = false;
      this.scrollToTop();
    },
    nextStep() {
      this.triedNext = true;
      if (this.currentStep === 0 && !this.step0Valid) return;
      if (this.currentStep === 1 && !this.step1Valid) return;
      if (this.currentStep < this.stepLabels.length - 1) {
        this.currentStep++;
        this.triedNext = false;
        this.scrollToTop();
      }
    },
    scrollToTop() {
      this.$el.querySelector('.main-card')?.scrollIntoView({ behavior: 'smooth' });
    },
    reloadPage() {
      window.location.reload();
    },
    submitForm() {
      this.triedNext = true;
      if (!this.finalValid || this.isSubmitting) return;

      this.isSubmitting = true;

      const payload = {
        student_first_name: this.form.studentFirstName,
        student_last_name: this.form.studentLastName,
        student_birth_date: this.form.studentBirthDate,  // AJOUT
        level: this.form.level,
        subjects: this.form.subjects,
        parent_first_name: this.form.parentFirstName,
        parent_last_name: this.form.parentLastName,
        email: this.form.email,
        phone: this.form.phone || null,
        mother_first_name: this.form.motherFirstName || null,
        mother_last_name: this.form.motherLastName || null,
        mother_phone: this.form.motherPhone || null,
        address: this.form.address || null,              // AJOUT
        postal_code: this.form.postalCode || null,       // AJOUT
        city: this.form.city || null,                    // AJOUT
        accepted_payment_terms: this.form.paymentTerms === true,
      };

      const url = this.$routing?.generate
          ? this.$routing.generate('app_registration_academic_support_request')
          : '/api/app_registration_academic_support_request';

      (this.axios || window.axios)
          .post(url, payload)
          .then(() => {
            // marquer comme envoyé pour bloquer le bouton
            this.hasSubmitted = true;

            // Afficher le modal de succès
            const modalEl = document.getElementById('successModal');
            const Modal = this.$bootstrap?.Modal || window.bootstrap?.Modal;
            if (Modal && modalEl) {
              const modal = new Modal(modalEl);
              modal.show();
            } else {
              alert('Inscription enregistrée !');
            }
          })
          .catch((err) => {
            console.error('Erreur lors de l\'inscription :', err);
          })
          .finally(() => {
            this.isSubmitting = false;
          });
    },
  },
};
</script>

<style scoped>
.registration-container {
  --primary-color: #4f46e5;
  --primary-dark: #3730a3;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-900: #111827;
  /* couleur de texte par défaut dans le composant */
  color: var(--gray-900);
}

.registration-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 20px 15px;
  min-height: 100vh;
}

.main-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, var(--primary-color, #4f46e5) 0%, var(--primary-dark, #3730a3) 100%);
  color: white;
  padding: 2rem;
  text-align: center;
}

.card-header h1 { margin: 0; font-size: 2rem; font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.card-header p { margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 1.1rem; }

.form-content { padding: 2rem; }

/* Stepper */
.stepper { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; position: relative; }
.progress-bar { position: absolute; top: 20px; left: 0; height: 2px; background: var(--primary-color); z-index: 1; transition: width 0.3s ease; }
.stepper::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: var(--gray-200); z-index: 1; }
.step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; flex: 1; text-align: center; }
.step-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; background: white; border: 3px solid var(--gray-200); color: var(--gray-600); transition: all 0.3s ease; margin-bottom: 0.5rem; }
.step.active .step-circle { background: var(--primary-color); border-color: var(--primary-color); color: white; transform: scale(1.1); }
.step.completed .step-circle { background: var(--success-color); border-color: var(--success-color); color: white; }
.step-label { font-size: 0.875rem; font-weight: 500; color: var(--gray-600); margin-top: 0.5rem; }
.step.active .step-label { color: var(--primary-color); font-weight: 600; }
.step.completed .step-label { color: var(--success-color); }

/* Sections */
.form-section { animation: fadeIn 0.3s ease-in; display: none; }
.form-section.active { display: block; }
@keyframes fadeIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }

.section-title { color: var(--gray-900); font-size: 1.5rem; font-weight: 600; margin-bottom: 2rem; display: flex; align-items: center; }
.section-title i { margin-right: 0.75rem; color: var(--primary-color); }

/* Inputs */
.form-group { margin-bottom: 1.5rem; }
.form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--gray-700); font-size: 0.875rem; }
.form-label i { margin-right: 0.5rem; color: var(--primary-color); width: 16px; }
.form-control, .form-select { width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--gray-200); border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
.form-control:focus, .form-select:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.form-control.is-invalid { border-color: var(--danger-color); }
.invalid-feedback { display: block; color: var(--danger-color); font-size: 0.875rem; margin-top: 0.5rem; }

/* Subjects */
.subjects-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem; }
.subject-card { border: 2px solid var(--gray-200); border-radius: 12px; padding: 1rem; cursor: pointer; transition: all 0.3s ease; background: white; position: relative; }
.subject-card:hover { border-color: var(--primary-color); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15); }
.subject-card.selected { border-color: var(--primary-color); background: rgba(79, 70, 229, 0.05); }
.subject-label { display: flex; align-items: center; font-weight: 500; color: var(--gray-700); }
.subject-icon { position: absolute; top: 0.5rem; right: 0.5rem; width: 20px; height: 20px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; opacity: 0; transition: opacity 0.3s ease; }
.subject-card.selected .subject-icon { opacity: 1; }

/* Summary */
.summary-card { background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 16px; padding: 2rem; margin-bottom: 2rem; }
.summary-item { display: flex; align-items: flex-start; margin-bottom: 1.5rem; }
.summary-item:last-child { margin-bottom: 0; }
.summary-icon { width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 1rem; flex-shrink: 0; }

/* Agreements */
.agreement-card { border: 2px solid var(--gray-200); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; cursor: pointer; transition: all 0.3s ease; background: white; }
.agreement-card:hover { border-color: var(--primary-color); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1); }
.agreement-card.checked { border-color: var(--success-color); background: rgba(16, 185, 129, 0.05); }
.agreement-header { display: flex; align-items: center; margin-bottom: 1rem; }
.agreement-checkbox { width: 20px; height: 20px; margin-right: 1rem; accent-color: var(--success-color); }
.agreement-title { margin: 0; font-size: 1.1rem; font-weight: 600; color: var(--gray-900); }

/* Buttons */
.btn-nav { display: flex; justify-content: space-between; margin-top: 3rem; gap: 1rem; }
.btn-custom { padding: 0.75rem 2rem; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; min-height: 48px; }
.btn-primary-custom { background: var(--primary-color); color: white; }
.btn-primary-custom:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
.btn-success-custom { background: var(--success-color); color: white; }
.btn-success-custom:hover:not(:disabled) { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.btn-outline-custom { background: transparent; color: var(--gray-600); border: 2px solid var(--gray-300); }
.btn-outline-custom:hover:not(:disabled) { background: var(--gray-50); border-color: var(--gray-400); }
.btn-custom:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* Spinner */
.spinner { width: 18px; height: 18px; border: 2px solid rgba(255, 255, 255, 0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Alerts */
.alert-custom { padding: 1rem; border-radius: 12px; margin-top: 1rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 500; }
.alert-danger-custom { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); border: 1px solid rgba(239, 68, 68, 0.2); }

/* Responsive */
@media (max-width: 768px) {
  .registration-container { padding: 10px; }
  .form-content { padding: 1.5rem; }
  .card-header { padding: 1.5rem; }
  .card-header h1 { font-size: 1.5rem; }
  .subjects-grid { grid-template-columns: 1fr; }
  .btn-nav { flex-direction: column; }
  .btn-custom { width: 100%; justify-content: center; }
  .stepper { margin-bottom: 2rem; }
  .step-label { font-size: 0.75rem; }
  .step-circle { width: 35px; height: 35px; }
  .summary-card { padding: 1.5rem; }
  .summary-item { flex-direction: column; align-items: flex-start; }
  .summary-icon { margin-bottom: 0.5rem; margin-right: 0; }
}

@media (max-width: 480px) {
  .step-label { display: none; }
  .agreement-header { flex-direction: column; align-items: flex-start; }
  .agreement-checkbox { margin-bottom: 0.5rem; margin-right: 0; }
}

.text-primary { color: var(--primary-color) !important; }
.text-muted { color: var(--gray-600) !important; }
/* Flatpickr: make the visible input look like .form-control */
:deep(.flatpickr-alt) {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid var(--gray-200);
  border-radius: 12px;
  font-size: 1rem;
  background: white;
  transition: all 0.3s ease;
}

/* Focus ring identique */
:deep(.flatpickr-alt:focus) {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

/* Erreur via le wrapper input-group */
:deep(.input-group.has-error .flatpickr-alt) {
  border-color: var(--danger-color);
}

/* Harmoniser input-group (icône + input) comme tes autres champs */
:deep(.input-group .input-group-text) {
  border: 2px solid var(--gray-200);
  border-right: 0;
  border-radius: 12px 0 0 12px;
  background: var(--gray-50);
  padding: 0.75rem 0.9rem;
}
:deep(.input-group .flatpickr-alt) {
  border-left: 0;
  border-radius: 0 12px 12px 0;
}

/* Z-index pour que le calendrier passe au-dessus des cartes/modals si besoin */
:deep(.flatpickr-calendar) { z-index: 2000; }

</style>
