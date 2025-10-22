<template>
  <div
      class="modal fade"
      id="sendInvoiceModal"
      tabindex="-1"
      aria-labelledby="sendInvoiceModalLabel"
      aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sendInvoiceModalLabel">
            Envoyer la facture par email
          </h5>
          <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="sendEmail">
            <div class="mb-3">
              <label for="email" class="form-label">Adresse email :</label>
              <input
                  type="email"
                  id="email"
                  v-model="email"
                  class="form-control"
                  required
              />
            </div>
            <div class="text-end">
              <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
              >
                Annuler
              </button>
              <button type="submit" class="btn btn-primary">
                Envoyer
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
  name: "SendInvoiceModal",
  props: {
    invoice: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      email: this.invoice?.parent?.emailContact || "",
    };
  },
  methods: {
    async sendEmail() {
      try {
        this.$emit("email-sent", this.email);
        //this.closeModal();
      } catch (error) {
        console.error("Erreur lors de l'envoi de l'email :", error);
        alert("Erreur lors de l'envoi de l'email.");
      }
    },
    closeModal() {
    },
  },
};
</script>

<style scoped>
.modal-header {
  background-color: #f5f5f5;
}
.modal-title {
  font-size: 18px;
  font-weight: bold;
}
.modal-body {
  padding: 20px;
}
</style>
