<template>
  <div class="container registration-form">
    <h1 class="mb-4">Inscription Cours d’Arabe 2025/2026</h1>

    <form v-if="!demandSent" @submit.prevent="submitForm">
      <!-- STEPPER -->
      <div class="d-flex align-items-center justify-content-between wizard-steps mb-4">
        <template v-for="(label, idx) in stepLabels" :key="idx">
          <div class="d-flex align-items-center flex-fill">
            <div
                class="step-circle"
                :class="{ completed: currentStep > idx, active: currentStep === idx }"
            >
              <template v-if="currentStep > idx">
                <i class="bi bi-check-lg"></i>
              </template>
              <template v-else>{{ idx + 1 }}</template>
            </div>
            <div class="flex-fill step-name text-center">{{ label }}</div>
            <div
                v-if="idx < stepLabels.length - 1"
                class="step-bar"
                :class="{ completed: currentStep > idx + 1, active: currentStep === idx + 1 }"
            ></div>
          </div>
        </template>
      </div>

      <!-- ÉTAPE 1 : informations de l’enfant -->
      <div v-show="currentStep === 0">
        <h2 class="h5 mb-3">Information de l’enfant</h2>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="childLastName" class="form-label">Nom de l’enfant :</label>
            <input
                type="text"
                id="childLastName"
                class="form-control"
                v-model.trim="form.childLastName"
                required
            />
          </div>
          <div class="col-md-6 mb-3">
            <label for="childFirstName" class="form-label">Prénom de l’enfant :</label>
            <input
                type="text"
                id="childFirstName"
                class="form-control"
                v-model.trim="form.childFirstName"
                required
            />
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="endTime" class="form-label">Date de naissance </label>
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </span>
              <flatpickr
                  v-model="form.childDob"
                  :config="flatpickrDobConfig"
                  class="form-control bg-white"
                  id="childDob"
                  placeholder="JJ/MM/AAAA"
                  required
              />
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label for="childGender" class="form-label">Genre :</label>
            <select
                id="childGender"
                class="form-select"
                v-model="form.childGender"
                required
            >
              <option value="" disabled>-- choisir --</option>
              <option value="Garçon">Garçon</option>
              <option value="Fille">Fille</option>
            </select>
          </div>
        </div>
        <!-- Vérification inscription précédente -->
        <fieldset class="mb-4 p-3 border rounded">
          <legend class="fw-semibold">
            <i class="bi bi-info-circle-fill text-primary me-2"></i>
            Était-il inscrit au CCIB38 en 2024/2025 ?
          </legend>
          <div class="form-check form-check-inline">
            <input
                class="form-check-input"
                type="radio"
                id="wasEnrolledYes"
                value="oui"
                v-model="form.wasEnrolled2024"
            />
            <label class="form-check-label" for="wasEnrolledYes">oui</label>
          </div>
          <div class="form-check form-check-inline">
            <input
                class="form-check-input"
                type="radio"
                id="wasEnrolledNo"
                value="non"
                v-model="form.wasEnrolled2024"
            />
            <label class="form-check-label" for="wasEnrolledNo">non</label>
          </div>

          <!-- Niveau précédent s’il était inscrit -->
          <div v-if="form.wasEnrolled2024 === 'oui'" class="mt-3">
            <label for="previousLevel" class="form-label">Quel niveau avait-il en 2024/2025 ?</label>
            <select
                id="previousLevel"
                class="form-select"
                v-model="form.previousLevel"
            >
              <option value="" disabled>-- sélectionner le niveau précédent --</option>
              <option v-for="level in levels" :key="level" :value="level">
                {{ level }}
              </option>
            </select>
          </div>

          <!-- Frère ou sœur inscrit s’il ne l’était pas -->
          <div v-if="form.wasEnrolled2024 === 'non'" class="mt-3">
            <label class="form-label">Un frère ou une soeur inscrit au CCIB38 en 2024/2025 ?</label><br>
            <div class="form-check form-check-inline">
              <input
                  class="form-check-input"
                  type="radio"
                  id="siblingEnrolledYes"
                  value="oui"
                  v-model="form.siblingEnrolled"
              />
              <label class="form-check-label" for="siblingEnrolledYes">oui</label>
            </div>
            <div class="form-check form-check-inline">
              <input
                  class="form-check-input"
                  type="radio"
                  id="siblingEnrolledNo"
                  value="non"
                  v-model="form.siblingEnrolled"
              />
              <label class="form-check-label" for="siblingEnrolledNo">non</label>
            </div>
          </div>
        </fieldset>
        <alert
            v-if="messageAlert"
            :text="messageAlert"
            :type="typeAlert"
            :show-close-button="false"
        />
        <!-- Upload photo -->
<!--        <div class="mb-4 text-center photo-upload">-->
<!--          &lt;!&ndash; Cercle contenant soit l'icône par défaut, soit l'aperçu &ndash;&gt;-->
<!--          <div class="profile-placeholder mb-2">-->
<!--            <template v-if="photoPreview">-->
<!--              <img :src="photoPreview"-->
<!--                   alt="Photo de l’enfant"-->
<!--                   class="rounded-circle preview-img"/>-->
<!--            </template>-->
<!--            <template v-else>-->
<!--              <i class="bi bi-person-circle default-icon"></i>-->
<!--            </template>-->
<!--          </div>-->

<!--          &lt;!&ndash; Label stylé comme un bouton, input file caché &ndash;&gt;-->
<!--          <label class="btn btn-outline-primary btn-sm">-->
<!--            Importer la photo de l’élève-->
<!--            <input-->
<!--                type="file"-->
<!--                accept="image/*"-->
<!--                name="childPhoto"-->
<!--                @change="onPhotoChange"-->
<!--                class="d-none"-->
<!--            />-->
<!--          </label>-->
<!--        </div>-->
      </div>

      <!-- ÉTAPE 2 : informations des parents -->
      <div v-show="currentStep === 1">
        <h2 class="h5 mb-3">Information des parents</h2>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="fatherLastName" class="form-label">Nom du père :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input
                  type="text"
                  id="fatherLastName"
                  class="form-control"
                  v-model.trim="form.fatherLastName"
                  required
              />
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label for="fatherFirstName" class="form-label">Prénom du père :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input
                  type="text"
                  id="fatherFirstName"
                  class="form-control"
                  v-model.trim="form.fatherFirstName"
                  required
              />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="motherLastName" class="form-label">Nom de la mère :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input
                  type="text"
                  id="motherLastName"
                  class="form-control"
                  v-model.trim="form.motherLastName"
                  required
              />
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label for="motherFirstName" class="form-label">Prénom de la mère :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input
                  type="text"
                  id="motherFirstName"
                  class="form-control"
                  v-model.trim="form.motherFirstName"
                  required
              />
            </div>
          </div>
        </div>

        <div class="mb-3">
          <label for="contactEmail" class="form-label">E-mail de contact :</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
            <input
                type="email"
                id="contactEmail"
                class="form-control"
                v-model.trim="form.contactEmail"
                required
            />
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="fatherPhone" class="form-label">Téléphone du père :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
              <input
                  type="tel"
                  id="fatherPhone"
                  class="form-control"
                  v-model.trim="form.fatherPhone"
                  required
              />
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <label for="motherPhone" class="form-label">Téléphone de la mère :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-telephone"></i></span>
              <input
                  type="tel"
                  id="motherPhone"
                  class="form-control"
                  v-model.trim="form.motherPhone"
                  required
              />
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="address" class="form-label">Adresse postale :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
              <input
                  type="text"
                  id="address"
                  class="form-control"
                  v-model.trim="form.address"
                  required
              />
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <label for="postalCode" class="form-label">Code postal :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-mailbox"></i></span>
              <input
                  type="text"
                  id="postalCode"
                  class="form-control"
                  v-model.trim="form.postalCode"
                  required
              />
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <label for="city" class="form-label">Ville :</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-building"></i></span>
              <input
                  type="text"
                  id="city"
                  class="form-control"
                  v-model.trim="form.city"
                  required
              />
            </div>
          </div>
        </div>
      </div>



      <!-- ÉTAPE 3 : autorisations -->
      <div v-show="currentStep === 2">
        <h2 class="h5 mb-3">Autorisations & consentements</h2>

        <!-- Personnes autorisées -->
        <fieldset class="mb-4 p-3 border rounded">
          <legend class="fw-semibold">
            <i class="bi bi-person-check-fill text-primary me-2"></i>
            Personnes autorisées à récupérer l’enfant après le cours
          </legend>
          <div class="d-flex gap-3 flex-wrap">
            <div class="form-check">
              <input
                  type="checkbox"
                  id="authFather"
                  class="form-check-input"
                  value="Père"
                  v-model="form.authorized"
              />
              <label for="authFather" class="form-check-label">
                <i class="bi bi-person-fill me-1"></i>Père
              </label>
            </div>
            <div class="form-check">
              <input
                  type="checkbox"
                  id="authMother"
                  class="form-check-input"
                  value="Mère"
                  v-model="form.authorized"
              />
              <label for="authMother" class="form-check-label">
                <i class="bi bi-person-fill me-1"></i>Mère
              </label>
            </div>
            <div class="form-check">
              <input
                  type="checkbox"
                  id="authOther"
                  class="form-check-input"
                  value="Autre"
                  v-model="form.authorized"
              />
              <label for="authOther" class="form-check-label">
                <i class="bi bi-people-fill me-1"></i>Autre
              </label>
            </div>
          </div>
          <div v-if="form.authorized.includes('Autre')" class="mt-3">
            <div class="row g-2">
              <div class="col">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-person-lines-fill"></i></span>
                  <input
                      type="text"
                      class="form-control"
                      placeholder="Nom & prénom"
                      v-model="form.authorizedOtherName"
                  />
                </div>
              </div>
              <div class="col">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-shield-fill-exclamation"></i></span>
                  <input
                      type="text"
                      class="form-control"
                      placeholder="Lien avec l’enfant"
                      v-model="form.authorizedOtherRelation"
                  />
                </div>
              </div>
            </div>
          </div>
        </fieldset>

        <!-- Partir seul -->
        <fieldset class="mb-4 p-3 border rounded">
          <legend class="fw-semibold">
            <i class="bi bi-person-run text-success me-2"></i>
            J’autorise mon enfant à partir seul après l’école ?
          </legend>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="leaveYes"
                class="form-check-input"
                value="oui"
                v-model="form.leaveAlone"
            />
            <label for="leaveYes" class="form-check-label">
              oui
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="leaveNo"
                class="form-check-input"
                value="non"
                v-model="form.leaveAlone"
            />
            <label for="leaveNo" class="form-check-label">
              non
            </label>
          </div>
        </fieldset>

        <!-- Droit à l'image -->
        <fieldset class="mb-4 p-3 border rounded">
          <legend class="fw-semibold">
            <i class="bi bi-camera-reels-fill text-info me-2"></i>
            Droit à l’image
          </legend>
          <p class="mb-2">
            j’autorise l’école à utiliser photos/vidéos de mon enfant
          </p>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="imgYes"
                class="form-check-input"
                value="oui"
                v-model="form.imageRights"
            />
            <label for="imgYes" class="form-check-label">
              oui
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="imgNo"
                class="form-check-input"
                value="non"
                v-model="form.imageRights"
            />
            <label for="imgNo" class="form-check-label">
              non
            </label>
          </div>
        </fieldset>

        <!-- Informations médicales -->
        <fieldset class="mb-4 p-3 border rounded">
          <legend class="fw-semibold">
            <i class="bi bi-file-medical-fill text-danger me-2"></i>
            Informations médicales à nous transmettre
          </legend>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="medInfoYes"
                class="form-check-input"
                value="oui"
                v-model="form.medicalInfo"
            />
            <label for="medInfoYes" class="form-check-label">oui</label>
          </div>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="medInfoNo"
                class="form-check-input"
                value="non"
                v-model="form.medicalInfo"
            />
            <label for="medInfoNo" class="form-check-label">non</label>
          </div>

          <!-- Nouveau : textarea si "oui" -->
          <div v-if="form.medicalInfo === 'oui'" class="mt-3">
            <label for="medicalDetails" class="form-label">
              Merci de préciser (traitements, allergies, etc.)
            </label>
            <textarea
                id="medicalDetails"
                class="form-control"
                rows="4"
                v-model.trim="form.medicalDetails"
                placeholder="Entrez ici les informations médicales de votre enfant…"
                required
            ></textarea>
          </div>
        </fieldset>

        <!-- Traitement médical -->
        <fieldset class="mb-4 p-3 border rounded">
          <legend class="fw-semibold">
            <i class="bi bi-prescription2-fill text-danger me-2"></i>
            L’enfant suit-il un traitement médical ?
            <small class="d-block text-muted">(aucun traitement sans ordonnance)</small>
          </legend>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="treatYes"
                class="form-check-input"
                value="oui"
                v-model="form.medicalTreatment"
            />
            <label for="treatYes" class="form-check-label">
              oui
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input
                type="radio"
                id="treatNo"
                class="form-check-input"
                value="non"
                v-model="form.medicalTreatment"
            />
            <label for="treatNo" class="form-check-label">
              non
            </label>
          </div>
        </fieldset>
      </div>


      <!-- ÉTAPE 4 : récapitulatif & validation -->
      <div v-show="currentStep === 3">
        <h2 class="h5 mb-3">Validation</h2>

        <!-- Carte récap -->
        <div class="card mb-4 shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-journal-text me-2"></i>Récapitulatif</h5>
            <ul class="list-group list-group-flush">
              <li class="list-group-item">
                <i class="bi bi-person-circle text-primary me-2"></i>
                <strong>Enfant :</strong> {{ form.childFirstName }} {{ form.childLastName }}, né(e) le {{ form.childDob }}, {{ form.childGender }}, niveau {{ form.previousLevel }}
              </li>
              <li class="list-group-item">
                <i class="bi bi-people-fill text-primary me-2"></i>
                <strong>Parents :</strong>
                {{ form.fatherLastName }} {{ form.fatherFirstName }} & {{ form.motherLastName }} {{ form.motherFirstName }},
                <i class="bi bi-envelope me-1"></i> {{ form.contactEmail }},
                <i class="bi bi-telephone me-1"></i> {{ form.fatherPhone }} / {{ form.motherPhone }}
              </li>
              <li class="list-group-item">
                <i class="bi bi-geo-alt me-2"></i>
                <strong>Adresse :</strong> {{ form.address }}, {{ form.postalCode }} {{ form.city }}
              </li>
              <li class="list-group-item">
                <i class="bi bi-person-check text-primary me-2"></i>
                <strong>Autorisé(s) :</strong> {{ form.authorized.join(', ') }}
                <span v-if="form.authorizedOtherName">– {{ form.authorizedOtherName }} ({{ form.authorizedOtherRelation }})</span>
              </li>
              <li class="list-group-item">
                <i class="bi bi-box-arrow-right me-2"></i>
                <strong>Partir seul :</strong> {{ form.leaveAlone }}
              </li>
              <li class="list-group-item">
                <i class="bi bi-camera-video-fill me-2"></i>
                <strong>Droit à l’image :</strong> {{ form.imageRights }}
              </li>
              <li class="list-group-item">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Changement de situation :</strong> {{ form.commitmentSitu ? 'Oui' : 'Non' }}
              </li>
              <li class="list-group-item">
                <i class="bi bi-file-medical me-2"></i>
                <strong>Info médicale :</strong> {{ form.medicalInfo }}
                &nbsp;|&nbsp; <strong>Traitement :</strong> {{ form.medicalTreatment }}
              </li>
            </ul>
          </div>
        </div>

        <!-- Engagement de situation -->
        <div class="form-check form-switch mb-3">
          <input
              class="form-check-input"
              type="checkbox"
              id="commitmentSitu"
              v-model="form.commitmentSitu"
              required
          />
          <label class="form-check-label" for="commitmentSitu">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
            Changement de situation : je m’engage à informer l’école de tout changement…
          </label>
        </div>


        <!-- Validation légale -->
        <div class="form-check form-switch mb-3">
          <input
              class="form-check-input"
              type="checkbox"
              id="legalDeclaration"
              v-model="form.legalDeclaration"
              required
          />
          <label class="form-check-label" for="legalDeclaration">
            <i class="bi bi-file-earmark-check-fill text-success me-2"></i>
            Je soussigné(e), responsable légal(e) de l’enfant, déclare exacts les renseignements fournis et autorise le CCIB38 à prendre, si besoin, toutes mesures nécessaires (traitement médical, hospitalisation, intervention chirurgicale).          </label>
        </div>

        <!-- Conditions de paiement -->
        <div class="form-check form-switch mb-4">
          <input
              class="form-check-input"
              type="checkbox"
              id="paymentTerms"
              v-model="form.paymentTerms"
              required
          />
          <label class="form-check-label" for="paymentTerms">
            <i class="bi bi-credit-card-fill text-info me-2"></i>
            J’accepte que l’inscription de mon enfant ne soit validée qu’à réception du règlement (carte bancaire, espèces ou chèque en plusieurs fois) ; la date limite pour valider l’inscription est fixée au 30 mai 2025.<br>
            <small class="text-muted">
              Grille tarifaire : 1 enfant : 230 € | 2 enfants : 430 € | 3 enfants : 600 € | frais de dossier : 10 € par enfant<br>
              Permanences paiement : mercredi. 16h–17h15, samedi. 9h15–12h & 14h–17h15, dimanche. 9h15–12h & 14h–17h15.<br>
              (*) paiement possible en carte bancaire, espèce ou chèque en plusieurs fois.
            </small>
          </label>
        </div>

      </div>


      <!-- BOUTONS DE NAVIGATION -->
      <div class="d-flex justify-content-between mt-4">
        <button
            type="button"
            class="btn btn-outline-secondary"
            @click="prevStep"
            :disabled="currentStep === 0"
        >
          Précédent
        </button>

        <!-- Suivant désactivé tant que la validation de l’étape échoue -->
        <button
            v-if="currentStep < stepLabels.length - 1"
            type="button"
            class="btn btn-primary"
            @click="nextStep"
            :disabled="!stepValid"
        >
          Suivant
        </button>

        <button
            v-else
            type="submit"
            class="btn btn-success"
        >
          Envoyer la demande d'inscription
        </button>
      </div>
    </form>

    <!-- successModal.vue (extrait du template) -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center py-4">
            <h5>🎉 Demande d'inscription enregistrée !</h5>
            <p>Votre demande a bien été enregistrée pour l’année 2025/2026.</p>
            <p>Vous allez recevoir un email récapitulatif de votre demande, contenant un bouton pour suivre l'avancement de votre inscription.</p>
          </div>
          <div class="modal-footer justify-content-around">
            <!-- bouton pour réinscrire un autre enfant -->
            <button
                type="button"
                class="btn btn-outline-primary"
                data-bs-dismiss="modal"
                @click="reloadPage"
            >
              Inscrire un autre enfant
            </button>

            <!-- bouton pour voir la demande, inchangé -->
            <a
                v-if="requestToken"
                :href="`/cours-arabe/inscription-show/${requestToken}`"
                class="btn btn-primary"
            >
              Voir ma demande
            </a>
          </div>
        </div>
      </div>
    </div>



  </div>
</template>

<script>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import Alert from "../../ui/Alert.vue";

export default {
  name: 'RegistrationArabicCourse',
  components: {Alert, Flatpickr /* …autres composants… */ },
  data() {
    return {
      currentStep: 0,
      demandSent: false,
      photoPreview: null,
      requestToken: null,
      stepLabels: [
        "Information de l’enfant",
        "Information des parents",
        "Autorisations",
        "Validation"
      ],
      flatpickrDobConfig: {
        locale: 'fr',
        dateFormat: 'd/m/Y',
      },
      messageAlert: '',
      typeAlert: 'danger',
      form: {
        childFirstName: '',
        childPhotoFile: null,
        wasEnrolled2024: '',
        previousLevel: '',
        siblingEnrolled: '',
        childLastName: '',
        childDob: '',
        childGender: '',
        childLevel: '',
        fatherLastName: '',
        fatherFirstName: '',
        motherLastName: '',
        motherFirstName: '',
        contactEmail: '',
        fatherPhone: '',
        motherPhone: '',
        address: '',
        postalCode: '',
        city: '',
        authorized: [],
        authorizedOtherName: '',
        authorizedOtherRelation: '',
        leaveAlone: '',
        imageRights: '',
        medicalInfo: '',
        medicalDetails: '',
        medicalTreatment: '',
        commitmentSitu: false,
        legalDeclaration: false,
        paymentTerms: false
      },
      levels: [
        'GS',
        'CP',
        'N0',
        'N1_1',
        'N1_2',
        'N2_1',
        'N2_2',
        'N3_1',
        'N3_2',
        'Adult/Arabe',
        'ADO/Coran',
      ]
    };
  },
  watch: {
    'form.childDob'(newVal) {
      if (!newVal) {
        this.messageAlert = ''
        return
      }
      // parse "JJ/MM/AAAA"
      const [d, m, y] = newVal.split('/')
      const picked = new Date(`${y}-${m}-${d}`)
      const limit = new Date('2019-12-31')
      if (picked > limit) {
        this.messageAlert =
            "L’âge de votre enfant ne permet pas d’être inscrit(e) " +
            "pour l’année 2025/2026 au sein de notre centre."
      } else {
        this.messageAlert = ''
      }
    }
  },
  computed: {
    stepValid() {
      const f = this.form;

      if (this.currentStep === 0) {
        return this.validateStep1();
      }

      // Étape 2 : infos des parents
      if (this.currentStep === 1) {
        return !!(
            f.fatherLastName &&
            f.fatherFirstName &&
            f.motherLastName &&
            f.motherFirstName &&
            f.contactEmail &&
            (f.fatherPhone || f.motherPhone) &&
            f.address &&
            f.postalCode &&
            f.city
        );
      }

      // Étape 3 : autorisations & consentements
      if (this.currentStep === 2) {
        // au moins une personne autorisée
        const hasAuthorized = f.authorized.length > 0;
        // si "Autre" est coché, on exige nom + relation
        const otherOk =
            !f.authorized.includes('Autre') ||
            (f.authorizedOtherName && f.authorizedOtherRelation);
        // radio obligatoires
        const hasLeave = f.leaveAlone === 'oui' || f.leaveAlone === 'non';
        const hasImage = f.imageRights === 'oui' || f.imageRights === 'non';
        const hasMedInfo =
            f.medicalInfo === 'oui' || f.medicalInfo === 'non';
        const medDetailsOk = f.medicalInfo === 'non' || !!f.medicalDetails;
        const hasTreatment =
            f.medicalTreatment === 'oui' || f.medicalTreatment === 'non';

        return (
            hasAuthorized &&
            otherOk &&
            hasLeave &&
            hasImage &&
            hasMedInfo &&
            medDetailsOk &&
            hasTreatment
        );
      }

      // Étape 4 : validation finale
      if (this.currentStep === 3) {
        return !!(f.legalDeclaration && f.paymentTerms);
      }

      return false;
    }
  },
  methods: {
    validateStep1() {
      const {
        childLastName,
        childFirstName,
        childDob,
        childGender,
        wasEnrolled2024,
        previousLevel,
        siblingEnrolled
      } = this.form;

      // 1) Tous les champs de base doivent être remplis
      const baseFieldsFilled = [
        childLastName,
        childFirstName,
        childDob,
        childGender
      ].every(Boolean);

      if (!baseFieldsFilled) {
        return false;
      }
      if (childDob) {
        const [day, month, year] = childDob.split('/');
        const dobDate = new Date(`${year}-${month}-${day}`);
        const today = new Date();
        const age = today.getFullYear() - dobDate.getFullYear();
        if (age < 6) {

          return false;
        }
      }

      // 2) Validation conditionnelle selon l’inscription 2024/2025
      if (wasEnrolled2024 === 'oui') {
        return Boolean(previousLevel);
      }

      if (wasEnrolled2024 === 'non') {
        return Boolean(siblingEnrolled);
      }

      // Si on n'a pas répondu à la question “était-il inscrit ?”, on bloque
      return false;
    },
    reloadPage() {
      window.location.reload();
    },
    onPhotoChange(event) {
      const file = event.target.files[0];
      const MAX_SIZE = 2 * 1024 * 1024; // 2 Mo

      if (!file) {
        this.photoPreview = null;
        this.form.childPhotoFile = null;
        return;
      }

      if (file.size > MAX_SIZE) {
        // Alerter l’utilisateur
        return alert('La photo ne doit pas dépasser 2 Mo.');
      }

      this.form.childPhotoFile = file;
      const reader = new FileReader();
      reader.onload = e => {
        this.photoPreview = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    nextStep() {
      if (this.stepValid && this.currentStep < this.stepLabels.length - 1) {
        this.currentStep++;
      }
    },
    prevStep() {
      if (this.currentStep > 0) {
        this.currentStep--;
      }
    },
    submitForm() {
      const formData = new FormData();
      if (this.form.childPhotoFile) {
        formData.append('childPhoto', this.form.childPhotoFile);
      }
      Object.entries(this.form).forEach(([key, val]) => {
        if (val === null) return;
        if (val instanceof File) return;

        if (Array.isArray(val)) {
          // sérialise le tableau en JSON
          formData.append(key, JSON.stringify(val));
        } else if (val instanceof File) {
          formData.append(key, val);
        } else {
          formData.append(key, val);
        }
      });

      const url = this.$routing.generate('app_registration_arabic_course_request');
      this.axios.post(url, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
          .then(({ data }) => {
            this.demandSent = true;
            // stocker le token pour le bouton
            this.requestToken = data.token;

            // afficher le modal
            const modalEl = document.getElementById('successModal');
            const modal = new this.$bootstrap.Modal(modalEl);
            modal.show();
          })
          .catch(err => {
            console.error('Erreur lors de l’inscription :', err);
          });
    }
  }
};
</script>

<style scoped>
.registration-form {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.wizard-steps {
  font-family: Arial, sans-serif;
}
.step-circle {
  width: 2rem;
  height: 2rem;
  border: 2px solid #ccc;
  border-radius: 50%;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #ccc;
  transition: all .2s;
}
.step-circle.active {
  border-color: #0062cc;
  color: #0062cc;
}
.step-circle.completed {
  background: #28a745;
  border-color: #28a745;
  color: #fff;
}
.step-bar {
  height: 2px;
  background: #ccc;
  margin: 0 .5rem;
  flex: 1;
  transition: all .2s;
}
.step-bar.active {
  background: #0062cc;
}
.step-bar.completed {
  background: #28a745;
}
.step-name {
  font-size: .85rem;
  color: #666;
}
.summary {
  background: #f1f1f1;
  padding: 15px;
  border-radius: 4px;
}

/* Si vous souhaitez changer la couleur pour “oui/non” */
.form-check-input[value="oui"]:checked {
  background-color: var(--bs-success);
  border-color: var(--bs-success);
}
.form-check-input[value="non"]:checked {
  background-color: var(--bs-danger);
  border-color: var(--bs-danger);
}

.photo-upload .profile-placeholder {
  width: 150px;
  height: 150px;
  margin: 0 auto;
  position: relative;
}
.photo-upload .default-icon {
  font-size: 5rem;
  color: #6c757d;
  line-height: 150px;
}
.photo-upload .preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border: 2px solid #dee2e6;
}


</style>
