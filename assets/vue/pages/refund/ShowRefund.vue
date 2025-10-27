<template>
  <div class="refund-container">
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-3" />

    <!-- Header -->
    <div class="refund-header">
      <div class="header-left">
        <div class="logo"><img src="/static/icons/logoccib38.webp" alt="Logo" /></div>
        <div class="company-info">
          <h2>Centre Culturel Ibn Badis Grenoble</h2>
          <div class="school-year">Remboursement</div>
        </div>
      </div>
      <div class="header-right">
        <h1>Remboursement</h1>
        <div class="refund-number">#{{ refund?.id ?? '—' }}</div>
        <div class="badges">
          <span class="badge" :class="`badge-${statusClass}`">{{ prettyStatus(refund?.status) }}</span>
          <span class="badge badge-info" v-if="refund?.method">{{ refund.method }}</span>
        </div>
      </div>
    </div>

    <!-- Parent & Détails -->
    <div class="meta-grid">
      <div class="meta-card">
        <h3 class="meta-title">Parent</h3>
        <div class="meta-line"><strong>Nom :</strong> {{ parentFullName }}</div>
        <div class="meta-line"><strong>Email :</strong> {{ parentEmail || '—' }}</div>
        <div class="meta-line"><strong>Téléphone :</strong> {{ parentPhone || '—' }}</div>
      </div>

      <div class="meta-card">
        <h3 class="meta-title">Détails</h3>
        <div class="meta-line"><strong>Date :</strong> {{ formatDate(refund?.refundDate) }}</div>
        <div class="meta-line"><strong>Montant remboursé :</strong> {{ formatCurrency(refund?.amount) }}</div>
        <div class="meta-line"><strong>Méthode :</strong> {{ refund?.method || '—' }}</div>
        <div class="meta-line"><strong>Référence :</strong> {{ refund?.reference || '—' }}</div>
      </div>

      <div class="meta-card" v-if="refund?.comment">
        <h3 class="meta-title">Commentaire</h3>
        <div class="meta-line">{{ refund.comment }}</div>
      </div>
    </div>

    <!-- Factures liées -->
    <div class="block-section">
      <h3 class="block-title">Factures associées</h3>

      <div v-if="!Array.isArray(refund?.invoices) || !refund.invoices.length" class="muted">
        Aucune facture liée à ce remboursement.
      </div>

      <div v-else class="invoice-list">
        <div class="invoice-card" v-for="inv in refund.invoices" :key="`${inv.id}-${inv.invoiceDate}`">
          <div class="invoice-head">
            <div class="inv-id">
              <strong>Facture #{{ inv.id }}</strong>
              <span class="pipe">•</span>
              <span>{{ formatDate(inv.invoiceDate) }}</span>
            </div>
            <div class="inv-money">
              <span><strong>Total :</strong> {{ formatCurrency(inv.totalAmount) }}</span>
              <span class="pipe">•</span>
              <span><strong>Commentaire :</strong> {{ inv.comment || '—' }}</span>
              <button class="open-invoice-btn" @click="goToInvoice(inv)" title="Ouvrir la facture">
                Voir la facture
              </button>
            </div>
          </div>

          <div class="table-wrapper" v-if="Array.isArray(inv.payments) && inv.payments.length">
            <table class="table">
              <thead>
              <tr>
                <th>Élève</th>
                <th>Service</th>
                <th>Montant payé</th>
                <th>Type</th>
                <th>Mois/Année</th>
                <th>Livres</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(p, i) in inv.payments" :key="p.id ?? i">
                <td>{{ studentLabel(p.student) }}</td>
                <td>{{ p.serviceType || '—' }}</td>
                <td>{{ formatCurrency(p.amountPaid) }}</td>
                <td>{{ p.paymentType || '—' }}</td>
                <td>
                  <span v-if="p.month || p.year">{{ [p.month, p.year].filter(Boolean).join('/') }}</span>
                  <span v-else>—</span>
                </td>
                <td>
                  <template v-if="Array.isArray(p.bookItems) && p.bookItems.length">
                    {{ p.bookItems.map(b => b?.book?.name || `#${b?.book?.id || ''}`).join(', ') }}
                  </template>
                  <template v-else>—</template>
                </td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <td colspan="2"><strong>Total payé (facture)</strong></td>
                <td><strong>{{ formatCurrency(sumPaid(inv)) }}</strong></td>
                <td colspan="3"></td>
              </tr>
              </tfoot>
            </table>
          </div>

          <div v-else class="muted small">Aucun paiement pour cette facture.</div>
        </div>
      </div>
    </div>

    <!-- Résumé -->
    <div class="summary">
      <div class="sum-item">
        <div class="sum-title">Montant remboursé</div>
        <div class="sum-value">{{ formatCurrency(refund?.amount) }}</div>
      </div>
      <div class="sum-item">
        <div class="sum-title">Nombre de factures</div>
        <div class="sum-value">{{ refund?.invoices?.length || 0 }}</div>
      </div>
      <div class="sum-item">
        <div class="sum-title">Statut</div>
        <div class="sum-value">
          <span class="badge" :class="`badge-${statusClass}`">{{ prettyStatus(refund?.status) }}</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="refund-actions">
      <button type="button" class="print-btn" @click="printRefund">
        <img src="/static/icons/printer.webp" alt="" />
        <span class="d-sm-none d-md-block ms-2">Imprimer</span>
      </button>

      <!-- Bouton identique facture : Bootstrap modal -->
      <button
          type="button"
          class="print-btn"
          data-bs-toggle="modal"
          data-bs-target="#sendRefundModal"
      >
        <img src="/static/icons/email.webp" alt="" />
        <span class="d-sm-none d-md-block ms-2">Envoyer par mail</span>
      </button>
    </div>

    <!-- Modal Bootstrap rendu en bas comme pour la facture -->
    <send-refund-modal :refund="refund" @email-sent="sendRefundEmail" />
  </div>
</template>

<script>
import html2pdf from "html2pdf.js";
import Alert from "../../ui/Alert.vue";
import SendRefundModal from "./SendRefundModal.vue";

export default {
  name: "RefundShow",
  components: { Alert, SendRefundModal },
  props: { refund: { type: Object, required: true } },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
      sending: false,
    };
  },
  computed: {
    parentFullName() {
      const f = this.refund?.parent;
      if (!f) return "—";
      const full =
          f.fullNameParent || f.fullName ||
          [f.fatherLastName, f.fatherFirstName].filter(Boolean).join(" ").trim() ||
          [f.motherLastName, f.motherFirstName].filter(Boolean).join(" ").trim();
      return full || "—";
    },
    parentEmail() {
      const p = this.refund?.parent || {};
      return p.emailContact || p.fatherEmail || p.motherEmail || "";
    },
    parentPhone() {
      const p = this.refund?.parent || {};
      return p.fatherPhone || p.motherPhone || "";
    },
    statusClass() {
      const s = (this.refund?.status || "").toLowerCase();
      if (s.includes("cancel")) return "danger";
      if (s.includes("process")) return "success";
      if (s.includes("pending")) return "warning";
      return "info";
    },
  },
  methods: {
    prettyStatus(s) {
      const map = { pending: "En attente", processed: "Traité", canceled: "Annulé" };
      return map[s] || s || "—";
    },
    sumPaid(inv) {
      if (!inv || !Array.isArray(inv.payments)) return 0;
      return inv.payments.reduce((acc, p) => acc + Number(p?.amountPaid || 0), 0);
    },
    studentLabel(student) {
      if (!student || (Array.isArray(student) && !student.length)) return "—";
      const s = Array.isArray(student) ? student[0] : student;
      const fn = (s?.firstName || "").trim();
      const ln = (s?.lastName || "").trim();
      const full = [fn, ln].filter(Boolean).join(" ");
      return full || (s?.id ? `#${s.id}` : "—");
    },
    formatDate(date) {
      if (!date) return "—";
      try { return new Date(date).toLocaleDateString("fr-FR"); }
      catch { return "—"; }
    },
    formatCurrency(amount) {
      const n = Number(amount || 0);
      return n.toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },
    goToInvoice(inv) {
      const url = this.$routing
          ? this.$routing.generate("app_invoice_show", { id: inv.id })
          : `/app/invoice/${inv.id}`;
      window.open(url, "_blank");
    },

    /* ---------- Impression ---------- */
    buildPrintHtml() {
      const r = this.refund || {};
      const invoices = Array.isArray(r.invoices) ? r.invoices : [];
      return `<!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8" />
<title>Remboursement #${r.id ?? ""}</title>
<style>
@page { size: A4; margin: 18mm; }
body { font-family: Arial, sans-serif; color:#111; }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.logo img { height: 60px; }
.org { font-size:14px; }
.doc-title { text-align:right; }
.doc-title h1 { margin:0; font-size:22px; text-transform:uppercase; }
.doc-title .num { margin-top:4px; font-size:12px; color:#555; }
.grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin:12px 0 18px; }
.card { border:1px solid #999; padding:10px; border-radius:6px; }
.card h3 { margin:0 0 6px 0; font-size:14px; }
.reason-card { border:2px dashed #444; background:#f9f9f9; padding:10px; border-radius:8px; margin-bottom:18px; }
.reason-card h3 { margin:0 0 6px 0; font-size:14px; color:#222; }
.reason-text { white-space:pre-line; font-size:13px; color:#333; }
table { width:100%; border-collapse:collapse; margin-top:4px; }
th, td { border:1px solid #aaa; padding:6px; font-size:12px; }
th { background:#f4f4f4; text-align:left; }
.summary { display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-top:10px; }
.summary .card strong { font-size:14px; }
.footer { margin-top:28mm; display:flex; justify-content:space-between; }
.sig { width:55%; border:1px dashed #777; padding:10px; min-height:80px; }
.sig-title { font-size:12px; color:#444; margin-bottom:6px; }
.stamp { width:38%; border:1px dashed #bbb; padding:10px; min-height:80px; text-align:center; color:#777; }
.small { font-size:12px; color:#555; }
</style></head>
<body>
  <div class="header">
    <div class="logo"><img src="/static/icons/logoccib38.webp" /></div>
    <div class="org"><strong>Centre Culturel Ibn Badis Grenoble</strong><br/>Document: Remboursement</div>
    <div class="doc-title"><h1>Remboursement</h1><div class="num">N° ${r.id ?? "—"}</div></div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Parent</h3>
      <div><strong>Nom :</strong> ${this.parentFullName}</div>
      <div><strong>Email :</strong> ${this.parentEmail || "—"}</div>
      <div><strong>Téléphone :</strong> ${this.parentPhone || "—"}</div>
    </div>
    <div class="card">
      <h3>Détails</h3>
      <div><strong>Date :</strong> ${this.formatDate(r.refundDate)}</div>
      <div><strong>Montant remboursé :</strong> ${this.formatCurrency(r.amount)}</div>
      <div><strong>Méthode :</strong> ${r.method || "—"}</div>
      <div><strong>Statut :</strong> ${this.prettyStatus(r.status)}</div>
      <div><strong>Référence :</strong> ${r.reference || "—"}</div>
    </div>
  </div>

  ${r.comment ? `<div class="reason-card"><h3>Raison du remboursement</h3><div class="reason-text">${r.comment}</div></div>` : ""}

  <div class="card">
    <h3>Factures associées</h3>
    ${
          (Array.isArray(invoices) && invoices.length)
              ? `<table><thead><tr>
             <th style="width:18%">Facture</th>
             <th style="width:20%">Date</th>
             <th style="width:20%">Total facture</th>
             <th style="width:22%">Total payé (liste paiements)</th>
             <th>Commentaire</th>
           </tr></thead>
           <tbody>
             ${invoices.map(inv=>{
                const paid=(Array.isArray(inv.payments)?inv.payments:[]).reduce((a,p)=>a+Number(p?.amountPaid||0),0);
                return `<tr>
                 <td>#${inv.id}</td>
                 <td>${this.formatDate(inv.invoiceDate)}</td>
                 <td>${this.formatCurrency(inv.totalAmount)}</td>
                 <td>${this.formatCurrency(paid)}</td>
                 <td>${inv.comment||"—"}</td>
               </tr>`;
              }).join("")}
           </tbody></table>`
              : `<div class="small">Aucune facture liée à ce remboursement.</div>`
      }
  </div>

  <div class="summary">
    <div class="card"><strong>Montant remboursé</strong><br/>${this.formatCurrency(r.amount)}</div>
    <div class="card"><strong>Nombre de factures</strong><br/>${invoices.length}</div>
    <div class="card"><strong>Méthode</strong><br/>${r.method || "—"}</div>
  </div>

  <div class="footer">
    <div class="sig">
      <div class="sig-title">Signature du parent (lu et approuvé) :</div>
      <div style="height:40px"></div>
      <div class="small">Nom lisible : .......................................................</div>
      <div class="small">Date : .... / .... / ..........</div>
    </div>
    <div class="stamp">Cachet/visa de l'établissement</div>
  </div>
</body></html>`;
    },

    printRefund() {
      try {
        const html = this.buildPrintHtml();
        const iframe = document.createElement('iframe');
        iframe.setAttribute('aria-hidden', 'true');
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);

        const doc = iframe.contentDocument || iframe.contentWindow?.document;
        if (!doc) throw new Error('Impossible d’accéder au document de l’iframe');

        doc.open(); doc.write(html); doc.close();

        iframe.onload = () => {
          try { iframe.contentWindow?.focus(); iframe.contentWindow?.print(); }
          finally { setTimeout(() => document.body.removeChild(iframe), 400); }
        };
      } catch (err) {
        console.error('Impression échouée:', err);
        this.messageAlert = "Impossible d’ouvrir l’aperçu d’impression (bloqueur de fenêtres ?)";
        this.typeAlert = "danger";
      }
    },

    /* ---------- PDF + Envoi email ---------- */
    _mountTempBodyForPdf() {
      const html = this.buildPrintHtml();
      const bodyStart = html.indexOf("<body>");
      const bodyEnd   = html.lastIndexOf("</body>");
      const inner     = (bodyStart !== -1 && bodyEnd !== -1) ? html.slice(bodyStart + 6, bodyEnd) : html;

      const host = document.createElement("div");
      host.style.position = "fixed";
      host.style.left = "-99999px";
      host.style.top = "0";
      host.style.width = "794px";
      host.style.background = "#fff";
      host.setAttribute("id", "refund-print-host");
      host.innerHTML = inner;

      document.body.appendChild(host);
      return host;
    },

    async _makeRefundPdfBlob() {
      const host = this._mountTempBodyForPdf();
      try {
        const opt = {
          margin: 8,
          filename: `remboursement_${this.refund?.id || "document"}.pdf`,
          image: { type: "jpeg", quality: 0.98 },
          html2canvas: { scale: 2 },
          jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
        };
        const worker = html2pdf().set(opt).from(host);
        const pdfBlob = await new Promise((resolve) => {
          worker.toPdf().get('pdf').then((pdf) => resolve(pdf.output('blob')));
        });
        return pdfBlob;
      } finally {
        host.remove();
      }
    },

    async sendRefundEmail(emailTo) {
      if (!emailTo) return;
      this.sending = true;
      try {
        const pdfBlob = await this._makeRefundPdfBlob();

        const formData = new FormData();
        formData.append("email", emailTo);
        formData.append("refundId", String(this.refund?.id || ""));
        formData.append("attachment", pdfBlob, `remboursement_${this.refund?.id || "document"}.pdf`);

        const url = this.$routing
            ? this.$routing.generate("refund_send_email", { id: this.refund.id })
            : `/refunds/${this.refund.id}/send-email`;

        await this.axios.post(url, formData);

        this.messageAlert = "Email envoyé avec succès !";
        this.typeAlert = "success";

        // Ferme le modal Bootstrap comme pour la facture
        const modalEl = document.getElementById("sendRefundModal");
        const inst = this.$bootstrap?.Modal?.getInstance?.(modalEl);
        inst?.hide?.();
      } catch (e) {
        console.error("Erreur lors de l'envoi de l'email :", e);
        this.messageAlert = "Erreur lors de l'envoi de l'email.";
        this.typeAlert = "danger";
      } finally {
        this.sending = false;
      }
    },
  },
};
</script>

<style scoped>
/* styles identiques à ta version précédente (raccourcis ici pour la lisibilité) */
.refund-container{font-family:Arial,sans-serif;margin:20px auto;padding:20px;border:1px solid #ddd;background:#fff;max-width:980px;width:90%;box-shadow:0 4px 10px rgba(0,0,0,.1)}
.refund-header{display:flex;justify-content:space-between;align-items:center;padding:20px;border-radius:8px;background:linear-gradient(90deg,#1e3c72 0%,#2a5298 100%);color:#fff;margin-bottom:20px}
.header-left{display:flex;align-items:center;gap:16px}
.logo{background:#fff;padding:8px;border-radius:8px}
.logo img{display:block;max-width:100px}
.company-info h2{margin:0;font-size:18px;font-weight:600}
.school-year{margin-top:4px;font-size:14px;font-style:italic;opacity:.9}
.header-right{text-align:right}
.header-right h1{margin:0;font-size:28px;text-transform:uppercase;letter-spacing:1px}
.refund-number{display:inline-block;margin-top:8px;padding:6px 14px;border:1px solid rgba(255,255,255,.7);border-radius:4px;font-size:14px;background:rgba(255,255,255,.1)}
.badges{margin-top:6px;display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap}
.meta-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}
.meta-card{border:1px solid #eee;border-radius:8px;padding:12px;background:#fafafa}
.meta-title{margin:0 0 6px 0;font-size:16px;font-weight:700;color:#2a5298}
.meta-line{margin:3px 0}
.block-section{margin-top:24px}
.block-title{margin:0 0 8px 0;font-size:18px;font-weight:700;color:#2a5298}
.invoice-list{display:grid;gap:12px}
.invoice-card{border:1px solid #eee;border-radius:8px;padding:10px;background:#fff}
.invoice-head{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
.inv-id{display:flex;gap:8px;align-items:center}
.inv-money{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.pipe{opacity:.5}
.open-invoice-btn{padding:4px 10px;border:1px solid #2a5298;background:#fff;color:#2a5298;border-radius:999px;cursor:pointer;font-size:12px}
.open-invoice-btn:hover{background:#2a5298;color:#fff}
.table-wrapper{overflow:auto;margin-top:8px}
.table{width:100%;border-collapse:collapse;font-size:.95rem}
.table th,.table td{border-bottom:1px solid #eee;padding:.6rem .5rem;text-align:left;vertical-align:top}
.table th{background:#fafafa}
.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:16px;margin-bottom:8px}
.sum-item{border:1px solid #eee;border-radius:8px;padding:10px;background:#fafafa;text-align:center}
.sum-title{font-size:.85rem;color:#666}
.sum-value{font-size:1.1rem;font-weight:700;margin-top:.25rem}
.refund-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:20px;position:relative;z-index:10}
.print-btn{display:inline-flex;align-items:center;gap:10px;padding:10px 16px;border:2px solid #007bff;background:transparent;color:#007bff;border-radius:50px;cursor:pointer;box-shadow:0 2px 5px rgba(0,0,0,.1);transition:all .3s ease;position:relative;z-index:2}
.print-btn:hover{background:#007bff;color:#fff;box-shadow:0 4px 10px rgba(0,123,255,.3)}
.print-btn img{width:20px;height:20px;margin:0;pointer-events:none}
.badge{display:inline-block;padding:.2rem .5rem;border-radius:999px;font-size:.8rem;border:1px solid transparent}
.badge-info{background:#e8f0fe;color:#283593;border-color:#c5d0fb}
.muted{color:#666}
.small{font-size:.9em}
@media print{.refund-actions,.alert{display:none!important}.refund-container{box-shadow:none!important;border:0!important}}
</style>
