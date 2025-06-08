<template>
  <div class="invoice-container">
    <!-- Facture -->
    <div id="invoice-content" style="padding: 20px">
      <header class="invoice-header">
        <div class="header-left">
          <div class="logo">
            <img src="/static/icons/logoccib38.webp" alt="Logo de l'entreprise" />
          </div>
          <div class="company-info">
            <h2>Centre culturel Ibn Badis Grenoble</h2>
            <p class="school-year">
              Année scolaire {{ invoice.schoolYear || '2025/2026' }}
            </p>
          </div>
        </div>
        <div class="header-right">
          <h1>FACTURE</h1>
          <div class="invoice-number">
            N° {{ invoice.id }}
          </div>
        </div>
      </header>

      <section class="invoice-address">
        <div class="from">
          <h3>Émetteur :</h3>
          <p>Centre culturel Ibn Badis Grenoble</p>
          <p>18 Rue des Trembles</p>
          <p>38100, Grenoble, France</p>
        </div>
        <div class="to">
          <h3>Destinataire :</h3>
          <p>{{ invoice.parent.fullNameParent }}</p>
          <p>38000, Grenoble, France</p>
        </div>
      </section>

      <p>Date de facture : <span>{{ formatDate(invoice.invoiceDate) }}</span></p>

      <table class="invoice-table">
        <thead>
        <tr>
          <th>Mois</th>
          <th>Étudiant</th>
          <th>Niveau</th>
          <th>Classe</th>
          <th>Service</th>
          <th>Type de paiement</th>
          <th>Commentaire</th>
          <th>Montant</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="(payment, index) in invoice.payments"
            :key="index"
            :class="{
              'arab-service': payment.serviceType === 'arabe',
              'soutien-service': payment.serviceType === 'soutien'
            }"
        >
          <td>{{ payment.month || "Non spécifié" }}</td>
          <td>{{ payment.student?.fullName || "Non spécifié" }}</td>
          <td>{{ payment.student?.levelClass || "Non spécifié" }}</td>
          <td>{{ payment.studyClass?.speciality || "Non spécifié" }}</td>
          <td>{{ getServiceType(payment.serviceType) }}</td>
          <td>{{ payment.paymentType || "Non spécifié" }}</td>
          <td>{{ payment.comment || "Aucun" }}</td>
          <td>{{ formatCurrency(payment.amountPaid) }}</td>
        </tr>
        </tbody>
      </table>

      <section class="invoice-summary">
        <p>Sous-total : <span>{{ formatCurrency(invoice.totalAmount) }}</span></p>
        <p>Réduction : <span>-{{ formatCurrency(invoice.discount || 0) }}</span></p>
        <p>TVA (0%) : <span>{{ formatCurrency(0) }}</span></p>
        <p class="total">
          <strong>Total : <span>{{ formatCurrency(invoice.totalAmount - (invoice.discount || 0)) }}</span></strong>
        </p>
      </section>
    </div>
    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="typeAlert"
    />
    <!-- Boutons pour PDF et envoi -->
    <div class="invoice-actions">
      <div
          @click="generatePDF"
          class="d-flex align-items-center btn btn-primary"
          role="button"
      >
        <img src="/static/icons/downloads.webp" alt="" style="width: 20px" />
        <span class="d-sm-none d-md-block ms-2">Télécharger PDF</span>
      </div>
      <div
          class="d-flex align-items-center btn btn-primary"
          role="button"
          data-bs-toggle="modal"
          data-bs-target="#sendInvoiceModal"
      >
        <img src="/static/icons/email.webp" alt="" style="width: 20px" />
        <span class="d-sm-none d-md-block ms-2">Envoyer la facture par mail</span>
      </div>

      <send-invoice-modal
          :invoice="invoice"
          @email-sent="sendEmail"
      ></send-invoice-modal>
    </div>
  </div>
</template>

<script>
import html2pdf from "html2pdf.js";
import SendInvoiceModal from "./SendInvoiceModal.vue";
import Alert from "../../ui/Alert.vue";

export default {
  name: "InvoiceDesign",
  components: {
    Alert,
    SendInvoiceModal,
  },
  props: {
    invoice: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      emailPopup: false,
      email: this.invoice.parent?.email || "",
      messageAlert: null,
      typeAlert: null,
    };
  },
  methods: {
    formatDate(date) {
      if (!date) return "Non spécifiée";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    formatCurrency(amount) {
      return parseFloat(amount).toLocaleString("fr-FR", {
        style: "currency",
        currency: "EUR",
      });
    },
    getServiceType(type) {
      switch (type) {
        case "soutien":
          return "Soutien scolaire";
        case "arabe":
          return "Cours d'arabe";
        default:
          return "Service inconnu";
      }
    },
    // Génération du PDF
    generatePDF() {
      const element = document.getElementById("invoice-content");
      const opt = {
        margin: 1,
        filename: `facture_${this.invoice.id}.pdf`,
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
      };
      html2pdf().set(opt).from(element).save();
    },
    closeEmailPopup() {
      const modal = document.getElementById("sendInvoiceModal");
      const bootstrapModal = this.$bootstrap.Modal.getInstance(modal);
      bootstrapModal.hide();
    },
    // Envoi de l'email
    async sendEmail(email) {
      try {
        const formData = new FormData();
        formData.append("email", email);
        await this.axios.post(
            this.$routing.generate("invoice_send_email", { id: this.invoice.id }),
            formData
        );
        this.messageAlert = "Email envoyé avec succès !";
        this.typeAlert = "success";
        this.closeEmailPopup();
      } catch (error) {
        console.error("Erreur lors de l'envoi de l'email :", error);
        this.messageAlert = "Erreur lors de l'envoi de l'email.";
        this.typeAlert = "danger";
        this.closeEmailPopup();
      }
    },
  },
};
</script>

<style scoped>
.invoice-container {
  font-family: Arial, sans-serif;
  margin: 20px auto;
  padding: 20px;
  border: 1px solid #ddd;
  background-color: #fff;
  max-width: 900px;
  width: 90%;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #ddd;
  padding-bottom: 10px;
}

.logo img {
  max-width: 100px;
}

.invoice-title h1 {
  margin: 0;
  font-size: 24px;
  color: #333;
}

.invoice-address {
  display: flex;
  justify-content: space-between;
  margin: 20px 0;
}

.invoice-address h3 {
  margin-bottom: 5px;
}

.invoice-table {
  width: 100%;
  border-collapse: collapse;
  margin: 20px 0;
}

.invoice-table th,
.invoice-table td {
  border: 1px solid #ddd;
  padding: 10px;
  text-align: left;
}

.invoice-table th {
  background-color: #f5f5f5;
  font-weight: bold;
}

.invoice-summary {
  margin-top: 20px;
  text-align: right;
}

.invoice-summary p {
  margin: 5px 0;
}

.invoice-summary .total strong {
  font-size: 18px;
}

.invoice-actions {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}

.invoice-actions button,
.invoice-actions div[role="button"] {
  padding: 10px 20px;
  border: 2px solid #007bff;
  background-color: transparent;
  color: #007bff;
  cursor: pointer;
  border-radius: 50px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.invoice-actions button:hover,
.invoice-actions div[role="button"]:hover {
  background-color: #007bff;
  color: #fff;
  box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
}

.invoice-actions img {
  width: 20px;
  height: 20px;
  margin: 0;
}

.arab-service {
  background-color: #e8f8f5;
}

.soutien-service {
  background-color: #fbe9e7;
}

 .invoice-header {
   display           : flex;
   justify-content   : space-between;
   align-items       : center;
   padding           : 20px;
   border-radius     : 8px;
   background        : linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
   color             : #fff;
   margin-bottom     : 20px;
 }

.header-left {
  display           : flex;
  align-items       : center;
}

.logo {
  background-color  : #fff;
  padding           : 8px;
  border-radius     : 8px;
}

.logo img {
  display           : block;
}

.company-info {
  margin-left       : 16px;
}

.company-info h2 {
  margin            : 0;
  font-size         : 18px;
  font-weight       : 600;
}

.school-year {
  margin-top        : 4px;
  font-size         : 14px;
  font-style        : italic;
  opacity           : 0.9;
}

.header-right {
  text-align        : right;
}

.header-right h1 {
  margin            : 0;
  font-size         : 28px;
  text-transform    : uppercase;
  letter-spacing    : 1px;
}

.invoice-number {
  display           : inline-block;
  margin-top        : 8px;
  padding           : 6px 14px;
  border            : 1px solid rgba(255,255,255,0.7);
  border-radius     : 4px;
  font-size         : 14px;
  background-color  : rgba(255,255,255,0.1);
}
</style>

