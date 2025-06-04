<template>
  <div class="w-100">
    <nav-bar-guest />
    <div class="container py-4">
      <!-- titre général -->
      <h4 class="text-center mb-4">
        Étapes à suivre pour finaliser votre inscription au sein du Centre Culturel Ibn Badis de Grenoble
      </h4>

      <div class="wizard-steps d-flex justify-content-between align-items-start mb-4 position-relative">
        <template v-for="(step, idx) in stepLabels" :key="idx">
          <div class="step-item d-flex flex-column align-items-center position-relative flex-fill">
            <!-- Ligne entre les cercles -->
            <div
                v-if="idx < stepLabels.length - 1"
                class="step-bar"
                :class="{ completed: currentStep > idx + 1, active: currentStep === idx + 1 }"
            ></div>

            <!-- Cercle -->
            <div
                class="step-circle"
                :class="{ completed: currentStep > idx, active: currentStep === idx }"
            >
              <template v-if="currentStep > idx">
                <i class="bi bi-check-lg"></i>
              </template>
              <template v-else>{{ idx + 1 }}</template>
            </div>

            <!-- Label -->
            <div
                class="step-name mt-2"
                :class="{ 'text-success fw-semibold': currentStep > idx && step.label.toLowerCase().includes('formulaire') }"
            >
              {{ step.label }}
            </div>
          </div>
        </template>
      </div>


      <div class="card shadow-sm">
        <!-- En-tête -->
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Récapitulatif de l’inscription</h5>
        </div>

        <!-- Corps de la carte -->
        <div class="card-body">
          <!-- Section : Information de l'enfant -->
          <section class="mb-4">
            <h6 class="fw-semibold">Information de l'enfant</h6>
            <div class="row align-items-center">
              <!-- Colonne Photo -->
<!--              <div class="col-md-4 text-center">-->
<!--                <template v-if="registration.childPhotoFilename">-->
<!--                  <img-->
<!--                      :src="photoUrl"-->
<!--                      alt="Photo de l’enfant"-->
<!--                      class="img-fluid rounded mb-2"-->
<!--                      style="max-height: 180px; object-fit: cover;"-->
<!--                  />-->
<!--                </template>-->
<!--                <template v-else>-->
<!--                  <div class="text-muted mb-2">-->
<!--                    <i class="bi bi-person-circle" style="font-size: 4rem;"></i>-->
<!--                  </div>-->
<!--                  <p class="small text-muted">Pas de photo</p>-->
<!--                </template>-->
<!--              </div>-->
              <!-- Colonne Données -->
              <div class="col-md-8 ps-md-4">
                <div class="row">
                  <div class="col-sm-6 mb-2">
                    <p class="mb-1"><strong>Nom :</strong> {{ registration.childLastName }} {{ registration.childFirstName }}</p>
                  </div>
                  <div class="col-sm-6 mb-2">
                    <p class="mb-1"><strong>Date de naissance :</strong> {{ formatDate(registration.childDob) }}</p>
                  </div>
                  <div class="col-sm-6 mb-2">
                    <p><strong>Genre :</strong> {{ registration.childGender }}</p>
                  </div>
                  <div class="col-sm-6 mb-2">
                    <p><strong>Niveau :</strong> {{ registration.childLevel }}</p>
                  </div>
                  <div class="col-sm-6 mb-2">
                    <p><i class="bi bi-envelope-fill me-1"></i><strong>Email de contact:</strong> {{ registration.contactEmail }}</p>
                  </div>
                </div>
              </div>
            </div>
          </section>
          <hr />

          <!-- Section : Information des parents -->
          <section class="mb-4">
            <h6 class="fw-semibold">Information des parents</h6>
            <div class="row">
              <div class="col-md-6 border-end">
                <p class="mb-1"><strong>Père :</strong> {{ registration.fatherLastName }} {{ registration.fatherFirstName }}</p>
                <p class="mb-1"><i class="bi bi-telephone-fill me-1"></i><strong>Tél. Père :</strong> {{ registration.fatherPhone }}</p>
              </div>
              <div class="col-md-6 ps-md-4">
                <p class="mb-1"><strong>Mère :</strong> {{ registration.motherLastName }} {{ registration.motherFirstName }}</p>
                <p><i class="bi bi-telephone-fill me-1"></i><strong>Tél. Mère :</strong> {{ registration.motherPhone }}</p>
              </div>
            </div>
          </section>
          <hr />

          <!-- Section : Adresse -->
          <section class="mb-4">
            <h6 class="fw-semibold">Adresse</h6>
            <p class="mb-0">
              <i class="bi bi-geo-alt-fill me-1"></i>
              {{ registration.address }}, {{ registration.postalCode }} {{ registration.city }}
            </p>
          </section>
          <hr />

          <!-- Section : Autorisations -->
          <section class="mb-4">
            <h6 class="fw-semibold">Autorisations</h6>
            <div class="row">
              <div class="col-md-6 border-end">
                <p class="mb-1"><strong>Personnes autorisées :</strong> {{ registration.authorized.join(', ') }}
                  <span v-if="registration.authorizedOtherName">
                    – {{ registration.authorizedOtherName }} ({{ registration.authorizedOtherRelation }})
                  </span>
                </p>
                <p><strong>Droit à l’image :</strong> {{ registration.imageRights }}</p>
              </div>
              <div class="col-md-6 ps-md-4">
                <p class="mb-1"><strong>Partir seul :</strong> {{ registration.leaveAlone }}</p>
              </div>
            </div>
          </section>
          <hr />

          <!-- Section : Informations médicales -->
          <section class="mb-4">
            <h6 class="fw-semibold">Informations médicales</h6>
            <div class="row">
              <div class="col-md-6 border-end">
                <p class="mb-1"><strong>Info transmises :</strong> {{ registration.medicalInfo }}</p>
                <p><strong>Traitement :</strong> {{ registration.medicalTreatment }}</p>
              </div>
              <div class="col-md-6 ps-md-4">
                <div v-if="registration.medicalInfo === 'oui'">
                  <p class="mb-1"><strong>Détails :</strong></p>
                  <p class="small text-muted">{{ registration.medicalDetails }}</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Engagements finaux -->
        <div class="card-body border-top">
          <div class="row text-center">
            <div class="col-md-4 mb-2 mb-md-0">
              <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
              <strong>Changement de situation :</strong><br />
              {{ registration.commitmentSitu ? 'Oui' : 'Non' }}
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
              <i class="bi bi-file-earmark-check-fill text-success me-1"></i>
              <strong>Déclaration légale :</strong><br />
              {{ registration.legalDeclaration ? 'Acceptée' : 'Refusée' }}
            </div>
            <div class="col-md-4">
              <i class="bi bi-credit-card-fill text-info me-1"></i>
              <strong>Paiement :</strong><br />
              {{ registration.paymentTerms ? 'Acceptées' : 'Non acceptées' }}
            </div>
          </div>
        </div>

        <!-- Date de soumission -->
        <div class="card-footer text-muted text-end">
          Soumis le {{ formatDateTime(registration.createdAt) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import NavBarGuest from "../components/NavBarGuest.vue";

export default {
  name: 'RegistrationPageArabicCourseShow',
  components: { NavBarGuest },
  props: {
    registration: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
    };
  },
  computed: {
    currentStep() {
      switch (this.registration.stepRegistration) {
        case 'registration':
          return 0;
        case 'waiting':
          return 1;
        case 'payment':
          return 2;
        case 'validation':
          return 3;
        case 'distribution':
          return 4;
        case 'accountCreation':
          return 5;
        default:
          return 1; // Par défaut, on considère que l'on est à l'étape du formulaire
      }
    },
    // Génère dynamiquement les étapes, avec "Liste d’attente" si nécessaire
    stepLabels() {
      return [
        { label: 'Formulaire', full: 'Remplir le formulaire et valider' },
        { label: 'Liste d’attente', full: 'Gestion de la liste d’attente' },
        { label: 'Paiement',   full: 'Paiement des droits d\'inscription' },
        { label: 'Validation', full: 'Validation par le service CCIB38' },
        { label: 'Répartition', full: 'Distribution des élèves en classe' },
        { label: 'Création de compte',      full: 'Création et validation du compte sur la plateforme' }
      ];
    },
    // URL de la photo si nécessaire
    photoUrl() {
      return `/uploads/${this.registration.childPhotoFilename}`;
    }
  },
  mounted() {
    // initialisation des tooltips Bootstrap
    this.$nextTick(() => {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltipTriggerList.forEach(el => new Tooltip(el));
    });
  },
  methods: {
    formatDate(d) {
      return new Date(d).toLocaleDateString();
    },
    formatDateTime(d) {
      return new Date(d).toLocaleString();
    }
  }
};
</script>

<style scoped>
.card-header {
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}
.card-body section h6 {
  margin-bottom: 1rem;
  font-size: 1rem;
}
.card-body hr {
  margin: 1rem 0;
  border-color: #e9ecef;
}
.card-body p {
  margin-bottom: 0.5rem;
}
.card-footer {
  background-color: #f8f9fa;
  font-size: 0.875rem;
}
.wizard-steps {
  position: relative;
  gap: 0.5rem;
}
.step-item {
  position: relative;
  text-align: center;
}

.step-circle {
  width: 36px;
  height: 36px;
  border: 2px solid #ccc;
  border-radius: 50%;
  background-color: #e9ecef;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  z-index: 1;
}
.step-circle.active {
  background-color: #fff;
  border-color: #0d6efd;
  color: #0d6efd;
}
.step-circle.completed {
  background-color: #28a745;
  border-color: #28a745;
  color: #fff;
}

/* Barre horizontale entre les cercles */
.step-bar {
  position: absolute;
  top: 18px; /* Alignée au milieu du cercle */
  left: 50%;
  width: 100%;
  height: 2px;
  background-color: #dee2e6;
  z-index: 0;
}
.step-bar.active {
  background-color: #0d6efd;
}
.step-bar.completed {
  background-color: #28a745;
}

.step-name {
  font-size: 0.9rem;
  color: #333;
}
</style>
