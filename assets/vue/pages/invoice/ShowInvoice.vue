<template>
  <div class="invoice-container">
    <div id="invoice-content" style="padding: 20px">
      <!-- En-tête -->
      <header class="invoice-header">
        <div class="header-left">
          <div class="logo">
            <img src="/static/icons/logoccib38.webp" alt="Logo" />
          </div>
          <div class="company-info">
            <h2>Centre culturel Ibn Badis Grenoble</h2>
            <p class="school-year">
              Année scolaire {{ schoolYear || '—' }}
            </p>
          </div>
        </div>
        <div class="header-right">
          <h1>FACTURE</h1>
          <div class="invoice-number">N° {{ invoice.id }}</div>
        </div>
      </header>

      <!-- Adresses -->
      <section class="invoice-address">
        <div class="from">
          <h3>Émetteur :</h3>
          <p>Centre culturel Ibn Badis Grenoble</p>
          <p>18 Rue des Trembles</p>
          <p>38100, Grenoble, France</p>
        </div>
        <div class="to">
          <h3>Destinataire :</h3>
          <p class="fw-bold">{{ invoice.parent?.fullNameParent }}</p>
          <p v-if="invoice.parent?.emailContact">
            ✉️ {{ invoice.parent.emailContact }}
          </p>
          <p v-if="invoice.parent?.phoneContact">
            ☎️ {{ invoice.parent.phoneContact }}
          </p>
        </div>
      </section>

      <p>Date de facture : <span>{{ formatDate(invoice.invoiceDate) }}</span></p>

      <!-- BLOC SOUTIEN -->
      <section v-if="groups.soutien?.length" class="block-section soutien">
        <h3 class="block-title"><i class="fas fa-graduation-cap me-2"></i>Soutien scolaire</h3>
        <table class="invoice-table">
          <thead>
          <tr>
            <th>Mois</th>
            <th>Année</th>
            <th>Élève</th>
            <th>Niveau</th>
            <th>Matière</th>
            <th>Type de paiement</th>
            <th>Commentaire</th>
            <th class="text-end">Montant</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(p, i) in groups.soutien" :key="'soutien-'+i" class="soutien-service">
            <td>{{ p.month || '—' }}</td>
            <td>{{ p.year || '—' }}</td>
            <td>{{ p.student?.fullName || '—' }}</td>
            <td>{{ p.student?.levelClass || '—' }}</td>
            <td>{{ p.studyClass?.speciality || '—' }}</td>
            <td>{{ p.paymentType || '—' }}</td>
            <td>{{ p.comment || '—' }}</td>
            <td class="text-end">{{ formatCurrency(p.amountPaid) }}</td>
          </tr>
          </tbody>
          <tfoot>
          <tr>
            <td colspan="7" class="text-end fw-bold">Sous-total (Soutien scolaire)</td>
            <td class="text-end fw-bold">{{ formatCurrency(subtotals.soutien) }}</td>
          </tr>
          </tfoot>
        </table>
      </section>

      <!-- BLOC ARABE -->
      <section v-if="groups.arabe?.length" class="block-section arabe">
        <h3 class="block-title"><i class="fas fa-language me-2"></i>Cours d'arabe</h3>
        <table class="invoice-table">
          <thead>
          <tr>
            <th>Élève</th>
            <th>Niveau</th>
            <th>Type de paiement</th>
            <th>Commentaire</th>
            <th class="text-end">Montant</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(p, i) in groups.arabe" :key="'arabe-'+i" class="arab-service">
            <td>{{ p.student?.fullName || '—' }}</td>
            <td>{{ p.student?.levelClass || '—' }}</td>
            <td>{{ p.paymentType || '—' }}</td>
            <td>{{ p.comment || '—' }}</td>
            <td class="text-end">{{ formatCurrency(p.amountPaid) }}</td>
          </tr>
          </tbody>
          <tfoot>
          <tr>
            <td colspan="4" class="text-end fw-bold">Sous-total (Cours d'arabe)</td>
            <td class="text-end fw-bold">{{ formatCurrency(subtotals.arabe) }}</td>
          </tr>
          </tfoot>
        </table>
      </section>

      <!-- BLOC LIVRES -->
      <section v-if="groups.livres?.length" class="block-section livres">
        <h3 class="block-title"><i class="fas fa-book me-2"></i>Livres</h3>

        <!-- Une “commande” livres = 1 paiement (élève) avec N lignes bookItems -->
        <div v-for="(p, pi) in groups.livres" :key="'livres-'+pi" class="books-payment">
          <div class="books-payment-header">
            <div>
              <div><strong>Élève :</strong> {{ p.student?.fullName || '—' }}</div>
              <div><strong>Niveau :</strong> {{ p.student?.levelClass || '—' }}</div>
            </div>
            <div class="text-end">
              <div><strong>Paiement :</strong> {{ p.paymentType || '—' }}</div>
              <div v-if="p.comment"><strong>Commentaire :</strong> {{ p.comment }}</div>
            </div>
          </div>

          <table class="invoice-table">
            <thead>
            <tr>
              <th>Livre</th>
              <th>Niveau</th>
              <th class="text-end">PU</th>
              <th class="text-end">Qté</th>
              <th class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(bi, biIndex) in (p.bookItems || [])"
                :key="`bi-${pi}-${biIndex}`">

            <td>{{ bi.book?.name || '—' }}</td>
              <td>{{ bi.book?.level || '—' }}</td>
              <td class="text-end">{{ formatCurrency(bi.unitPrice) }}</td>
              <td class="text-end">{{ bi.quantity }}</td>
              <td class="text-end">{{ formatCurrency(bi.lineTotal) }}</td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
              <td colspan="4" class="text-end fw-bold">Sous-total commande</td>
              <td class="text-end fw-bold">{{ formatCurrency(sumLineTotals(p.bookItems)) }}</td>
            </tr>
            </tfoot>
          </table>
        </div>

        <!-- Sous-total global livres -->
        <div class="text-end fw-bold mt-2">
          Sous-total (Livres) : {{ formatCurrency(subtotals.livres) }}
        </div>
      </section>

      <!-- Récap global -->
      <section class="invoice-summary">
        <p v-if="hasSubtotals('soutien')">Sous-total (Soutien scolaire) : <span>{{ formatCurrency(subtotals.soutien) }}</span></p>
        <p v-if="hasSubtotals('arabe')">Sous-total (Cours d'arabe) : <span>{{ formatCurrency(subtotals.arabe) }}</span></p>
        <p v-if="hasSubtotals('livres')">Sous-total (Livres) : <span>{{ formatCurrency(subtotals.livres) }}</span></p>
        <p>Réduction : <span>-{{ formatCurrency(invoice.discount || 0) }}</span></p>
        <p>TVA (0%) : <span>{{ formatCurrency(0) }}</span></p>
        <p class="total">
          <strong>Total : <span>{{ formatCurrency(grandTotal) }}</span></strong>
        </p>
      </section>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" />

    <!-- Actions -->
    <!-- Actions -->
    <div class="invoice-actions">
      <div @click="printInvoice" class="d-flex align-items-center btn btn-primary" role="button">
        <img src="/static/icons/printer.webp" alt="" />
        <span class="d-sm-none d-md-block ms-2">Imprimer</span>
      </div>

      <div class="d-flex align-items-center btn btn-primary" role="button"
           data-bs-toggle="modal" data-bs-target="#sendInvoiceModal">
        <img src="/static/icons/email.webp" alt="" />
        <span class="d-sm-none d-md-block ms-2">Envoyer la facture par mail</span>
      </div>

      <send-invoice-modal :invoice="invoice" @email-sent="sendEmail" />
    </div>
  </div>
</template>

<script>
import html2pdf from "html2pdf.js";
import SendInvoiceModal from "./SendInvoiceModal.vue";
import Alert from "../../ui/Alert.vue";

export default {
  name: "InvoiceDesign",
  components: { Alert, SendInvoiceModal },
  props: { invoice: { type: Object, required: true } },
  data() {
    return {
      emailPopup: false,
      email: this.invoice.parent?.emailContact || this.invoice.parent?.email || "",
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    schoolYear() {
      // Essaye d’extraire l’année scolaire depuis les paiements soutien si dispo
      const p = (this.invoice.payments || []).find(p => p.studyClass?.schoolYear);
      return p?.studyClass?.schoolYear || '2025/2026';
    },
    groups() {
      const g = { soutien: [], arabe: [], livres: [] };
      (this.invoice.payments || []).forEach(p => {
        const t = (p.serviceType || '').toLowerCase();
        if (t === 'soutien') g.soutien.push(p);
        else if (t === 'arabe') g.arabe.push(p);
        else if (t === 'livres') g.livres.push(p);
      });
      return g;
    },
    subtotals() {
      const sum = arr => arr.reduce((a, p) => a + Number(p.amountPaid || 0), 0);
      return {
        soutien: sum(this.groups.soutien),
        arabe: sum(this.groups.arabe),
        livres: sum(this.groups.livres),
      };
    },
    grandTotal() {
      // Par cohérence, on se base sur invoice.totalAmount - discount si fourni
      const gross = Number(this.invoice.totalAmount || 0);
      const discount = Number(this.invoice.discount || 0);
      // fallback si totalAmount absent → somme des subtotaux
      const base = gross > 0 ? gross : (this.subtotals.soutien + this.subtotals.arabe + this.subtotals.livres);
      return Math.max(0, base - discount);
    },
  },
  methods: {
    printInvoice() {
      const el = document.getElementById("invoice-content");
      if (!el) return;

      // Récupère les liens et styles de la page
      const styles = Array.from(document.querySelectorAll("link[rel=stylesheet], style"))
          .map(node => node.outerHTML)
          .join("\n");

      const w = window.open("", "_blank");
      w.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <meta charset="utf-8">
        <title>Facture ${this.invoice?.id ?? ""}</title>
        ${styles}
      </head>
      <body>
        ${el.outerHTML}
      </body>
    </html>
  `);
      w.document.close();
      w.onload = () => {
        w.focus();
        w.print();
        w.close();
      };
    },
    hasSubtotals(k) {
      return Number(this.subtotals[k] || 0) > 0;
    },
    sumLineTotals(items) {
      if (!Array.isArray(items)) return 0;
      return items.reduce((a, it) => a + Number(it.lineTotal || (Number(it.unitPrice||0) * Number(it.quantity||0))), 0);
    },
    formatDate(date) {
      if (!date) return "Non spécifiée";
      try { return new Date(date).toLocaleDateString("fr-FR"); }
      catch { return "Non spécifiée"; }
    },
    formatCurrency(amount) {
      const n = Number(amount || 0);
      return n.toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },
    generatePDF() {
      const element = document.getElementById("invoice-content");
      const opt = {
        margin: 8,
        filename: `facture_${this.invoice.id}.pdf`,
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
      };
      html2pdf().set(opt).from(element).save();
    },
    closeEmailPopup() {
      const modal = document.getElementById("sendInvoiceModal");
      const bootstrapModal = this.$bootstrap?.Modal?.getInstance?.(modal);
      bootstrapModal?.hide?.();
    },
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
  max-width: 980px;
  width: 90%;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.invoice-header {
  display:flex; justify-content:space-between; align-items:center;
  padding:20px; border-radius:8px;
  background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
  color:#fff; margin-bottom:20px;
}
.header-left { display:flex; align-items:center; }
.logo { background:#fff; padding:8px; border-radius:8px; }
.logo img { display:block; max-width:100px; }
.company-info { margin-left:16px; }
.company-info h2 { margin:0; font-size:18px; font-weight:600; }
.school-year { margin-top:4px; font-size:14px; font-style:italic; opacity:.9; }
.header-right { text-align:right; }
.header-right h1 { margin:0; font-size:28px; text-transform:uppercase; letter-spacing:1px; }
.invoice-number { display:inline-block; margin-top:8px; padding:6px 14px;
  border:1px solid rgba(255,255,255,0.7); border-radius:4px; font-size:14px;
  background:rgba(255,255,255,0.1);
}

.invoice-address { display:flex; justify-content:space-between; margin:20px 0; }
.invoice-address h3 { margin-bottom:5px; }
.fw-bold { font-weight:600; }

.block-section { margin-top:24px; }
.block-title { margin:0 0 8px 0; font-size:18px; font-weight:700; color:#2a5298; }
.books-payment { margin-top:12px; }
.books-payment-header{
  display:flex; justify-content:space-between; gap:16px;
  padding:10px 12px; background:#f6f8ff; border:1px solid #e0e7ff; border-radius:6px;
}

.invoice-table { width:100%; border-collapse:collapse; margin: 12px 0 8px; }
.invoice-table th, .invoice-table td {
  border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top;
}
.invoice-table th { background-color: #f5f5f5; font-weight: bold; }
.text-end { text-align:right; }

.invoice-summary { margin-top: 24px; text-align: right; }
.invoice-summary p { margin: 5px 0; }
.invoice-summary .total strong { font-size: 18px; }

.invoice-actions { display:flex; gap:12px; justify-content:flex-end; margin-top:20px; }
.invoice-actions div[role="button"] {
  padding:10px 16px; border:2px solid #007bff; background:transparent; color:#007bff;
  cursor:pointer; border-radius: 50px; transition: all .3s ease;
  display:flex; align-items:center; gap:10px; box-shadow: 0 2px 5px rgba(0,0,0,.1);
}
.invoice-actions div[role="button"]:hover{
  background:#007bff; color:#fff; box-shadow: 0 4px 10px rgba(0,123,255,.3);
}
.invoice-actions img { width:20px; height:20px; margin:0; }

.arab-service { background-color:#e8f8f5; }
.soutien-service { background-color:#fbe9e7; }
@media print {
  .invoice-actions, .modern-alert, .alert, [role="button"] { display: none !important; }
  .invoice-container { box-shadow: none !important; border: 0 !important; }
}
</style>
