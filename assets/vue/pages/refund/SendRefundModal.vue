<template>
  <div
      class="modal fade"
      id="sendRefundModal"
      tabindex="-1"
      aria-labelledby="sendRefundModalLabel"
      aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header border-0 pb-0">
          <div>
            <h5 class="modal-title mb-1" id="sendRefundModalLabel">
              <i class="bi bi-envelope-check me-2"></i>Envoyer le remboursement
            </h5>
            <p class="text-muted small mb-0">Envoi par email au parent</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body pt-3">
          <!-- Remarque importante avec icône -->
          <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
              <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-4 flex-shrink-0" style="margin-top: 2px;"></i>
              <div>
                <h6 class="alert-heading mb-2 fw-bold">Action requise avant l'envoi</h6>
                <p class="mb-2 small">Avant de pouvoir envoyer le remboursement par email, vous devez :</p>
                <ol class="mb-0 small ps-3">
                  <li>Imprimer la fiche de remboursement</li>
                  <li>La faire signer par le parent</li>
                  <li>Conserver une copie signée</li>
                </ol>
              </div>
            </div>
          </div>

          <form @submit.prevent="submit" novalidate>
            <!-- Champ email amélioré -->
            <div class="mb-4">
              <label for="refund-email" class="form-label fw-semibold">
                <i class="bi bi-envelope me-1"></i>Adresse email du destinataire
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-at text-muted"></i>
                </span>
                <input
                    type="email"
                    id="refund-email"
                    v-model.trim="email"
                    class="form-control border-start-0 ps-0"
                    :class="{ 'is-invalid': emailTouched && !isEmailValid }"
                    placeholder="exemple@email.com"
                    required
                    @blur="emailTouched = true"
                />
              </div>
              <div class="form-text mt-2">
                <i class="bi bi-info-circle me-1"></i>
                Le PDF de la fiche de remboursement sera joint automatiquement
              </div>
              <div v-if="emailTouched && !isEmailValid" class="invalid-feedback d-block">
                Veuillez saisir une adresse email valide
              </div>
            </div>

            <!-- Checkbox de confirmation améliorée -->
            <div class="confirmation-section p-3 rounded mb-4" :class="{ 'confirmed': confirmed }">
              <div class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="confirmSigned"
                    v-model="confirmed"
                />
                <label class="form-check-label fw-semibold" for="confirmSigned">
                  <i class="bi bi-check-circle me-2" :class="confirmed ? 'text-success' : 'text-muted'"></i>
                  Je confirme avoir une copie signée
                </label>
              </div>
              <p class="text-muted small mb-0 mt-2 ms-4 ps-1">
                La fiche est imprimée, signée par le parent et une copie est conservée
              </p>
            </div>

            <!-- Boutons d'action -->
            <div class="d-flex gap-2 justify-content-end">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="bi bi-x-lg me-2"></i>Annuler
              </button>
              <button
                  type="submit"
                  class="btn btn-primary position-relative"
                  :disabled="!canSubmit"
              >
                <i class="bi bi-send me-2"></i>Envoyer l'email
                <span
                    v-if="!canSubmit"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                    style="font-size: 0.6rem;"
                >
                  !
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SendRefundModal",
  props: {
    refund: { type: Object, required: true },
  },
  data() {
    return {
      email:
          this.refund?.parent?.emailContact ||
          this.refund?.parent?.fatherEmail ||
          this.refund?.parent?.motherEmail ||
          "",
      confirmed: false,
      emailTouched: false,
    };
  },
  computed: {
    isEmailValid() {
      if (!this.email) return false;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(this.email);
    },
    canSubmit() {
      return this.isEmailValid && this.confirmed;
    }
  },
  methods: {
    submit() {
      this.emailTouched = true;
      if (!this.canSubmit) return;
      this.$emit("email-sent", this.email);
    },
  },
};
</script>

<style scoped>
.modal-content {
  border-radius: 12px;
  overflow: hidden;
}

.modal-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.5rem;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
}

.modal-body {
  padding: 1.5rem;
}

.alert-warning {
  background-color: #fff8e1;
  color: #856404;
}

.alert-heading {
  font-size: 0.95rem;
  color: #856404;
}

.input-group-text {
  border: 1px solid #dee2e6;
}

.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.input-group:focus-within .input-group-text {
  border-color: #86b7fe;
  background-color: #fff;
}

.confirmation-section {
  background-color: #f8f9fa;
  border: 2px dashed #dee2e6;
  transition: all 0.3s ease;
}

.confirmation-section.confirmed {
  background-color: #d1f2eb;
  border-color: #198754;
  border-style: solid;
}

.form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
}

.form-check-label {
  cursor: pointer;
  user-select: none;
}

.btn {
  padding: 0.5rem 1.25rem;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0b5ed7;
  border-color: #0a58ca;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(13, 110, 253, 0.25);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-secondary:hover {
  transform: translateY(-1px);
}

/* Animation pour le badge */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.badge {
  animation: pulse 2s infinite;
}
</style>