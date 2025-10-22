<template>
  <div class="refund-container">
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-3" />

    <!-- Header -->
    <div class="refund-header">
      <div class="header-left">
        <div class="logo"> <img src="/static/icons/logoccib38.webp" alt="Logo" /></div>
        <div class="company-info">
          <h2>Centre Culturel Ibn Badis Grenoble</h2>
          <div class="school-year">Remboursement</div>
        </div>
      </div>
      <div class="header-right">
        <h1>Remboursement</h1>
        <div class="refund-number">#{{ refund?.id ?? '—' }}</div>
        <div class="badges">
          <span class="badge" :class="`badge-${statusClass}`">{{ refund?.status || 'pending' }}</span>
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
        <div class="sum-value"><span class="badge" :class="`badge-${statusClass}`">{{ refund?.status || 'pending' }}</span></div>
      </div>
    </div>

    <!-- Actions -->
    <div class="refund-actions">
      <div @click="printRefund" class="d-flex align-items-center btn btn-primary" role="button">
        <img src="/static/icons/printer.webp" alt="" />
        <span class="d-sm-none d-md-block ms-2">Imprimer</span>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "RefundShow",
  components: { Alert },
  props: {
    refund: { type: Object, required: true },
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    // parent reconstruit à partir des champs disponibles
    parentFullName() {
      const f = this.refund?.parent;
      if (!f) return '—';
      const dad = [f.fatherLastName, f.fatherFirstName].filter(Boolean).join(' ').trim();
      const mom = [f.motherLastName, f.motherFirstName].filter(Boolean).join(' ').trim();
      return [dad, mom].filter(Boolean).join(' - ') || '—';
    },
    parentEmail() {
      const p = this.refund?.parent || {};
      return p.fatherEmail || p.motherEmail || '';
    },
    parentPhone() {
      const p = this.refund?.parent || {};
      return p.fatherPhone || p.motherPhone || '';
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
    printRefund() {
      const container = document.querySelector(".refund-container");
      if (!container) return;
      const styles = Array.from(document.querySelectorAll("link[rel=stylesheet], style"))
          .map(node => node.outerHTML).join("\n");
      const w = window.open("", "_blank");
      w.document.write(`
        <!DOCTYPE html><html><head><meta charset="utf-8">
        <title>Remboursement #${this.refund?.id ?? ""}</title>
        ${styles}
        <style>.refund-actions, .alert { display:none !important; }
        .refund-container { box-shadow:none !important; border:0 !important; }</style>
        </head><body>${container.outerHTML}</body></html>
      `);
      w.document.close();
      w.onload = () => { w.focus(); w.print(); w.close(); };
    },
    sumPaid(inv) {
      if (!inv || !Array.isArray(inv.payments)) return 0;
      return inv.payments.reduce((acc, p) => acc + Number(p?.amountPaid || 0), 0);
    },
    studentLabel(student) {
      // ton payload a "student": [] ou {}
      if (!student || (Array.isArray(student) && !student.length)) return '—';
      const s = Array.isArray(student) ? student[0] : student;
      const fn = (s?.firstName || '').trim();
      const ln = (s?.lastName || '').trim();
      const full = [fn, ln].filter(Boolean).join(' ');
      return full || (s?.id ? `#${s.id}` : '—');
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
  },
};
</script>

<style scoped>
.refund-container {
  font-family: Arial, sans-serif;
  margin: 20px auto;
  padding: 20px;
  border: 1px solid #ddd;
  background-color: #fff;
  max-width: 980px;
  width: 90%;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.refund-header {
  display:flex; justify-content:space-between; align-items:center;
  padding:20px; border-radius:8px;
  background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
  color:#fff; margin-bottom:20px;
}
.header-left { display:flex; align-items:center; gap:16px; }
.logo { background:#fff; padding:8px; border-radius:8px; }
.logo img { display:block; max-width:100px; }
.company-info h2 { margin:0; font-size:18px; font-weight:600; }
.school-year { margin-top:4px; font-size:14px; font-style:italic; opacity:.9; }
.header-right { text-align:right; }
.header-right h1 { margin:0; font-size:28px; text-transform:uppercase; letter-spacing:1px; }
.refund-number { display:inline-block; margin-top:8px; padding:6px 14px;
  border:1px solid rgba(255,255,255,0.7); border-radius:4px; font-size:14px;
  background:rgba(255,255,255,0.1);
}
.badges { margin-top:6px; display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap; }

.meta-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-bottom:16px; }
.meta-card { border:1px solid #eee; border-radius:8px; padding:12px; background:#fafafa; }
.meta-title { margin:0 0 6px 0; font-size:16px; font-weight:700; color:#2a5298; }
.meta-line { margin:3px 0; }

.block-section { margin-top:24px; }
.block-title { margin:0 0 8px 0; font-size:18px; font-weight:700; color:#2a5298; }

.invoice-list { display:grid; gap:12px; }
.invoice-card { border:1px solid #eee; border-radius:8px; padding:10px; background:#fff; }
.invoice-head { display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap; }
.inv-id { display:flex; gap:8px; align-items:center; }
.inv-money { display:flex; gap:8px; align-items:center; }
.pipe { opacity:.5; }

.table-wrapper { overflow:auto; margin-top:8px; }
.table { width:100%; border-collapse: collapse; font-size:.95rem; }
.table th, .table td { border-bottom:1px solid #eee; padding:.6rem .5rem; text-align:left; vertical-align:top; }
.table th { background:#fafafa; }

.summary { display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-top:16px; margin-bottom:8px; }
.sum-item { border:1px solid #eee; border-radius:8px; padding:10px; background:#fafafa; text-align:center; }
.sum-title { font-size:.85rem; color:#666; }
.sum-value { font-size:1.1rem; font-weight:700; margin-top:.25rem; }

.refund-actions { display:flex; gap:12px; justify-content:flex-end; margin-top:20px; }
.refund-actions div[role="button"] {
  padding:10px 16px; border:2px solid #007bff; background:transparent; color:#007bff;
  cursor:pointer; border-radius: 50px; transition: all .3s ease;
  display:flex; align-items:center; gap:10px; box-shadow: 0 2px 5px rgba(0,0,0,.1);
}
.refund-actions div[role="button"]:hover{
  background:#007bff; color:#fff; box-shadow: 0 4px 10px rgba(0,123,255,.3);
}
.refund-actions img { width:20px; height:20px; margin:0; }

.badge { display:inline-block; padding:.2rem .5rem; border-radius:999px; font-size:.8rem; border:1px solid transparent; }
.badge-success { background:#e8f8f5; color:#11695f; border-color:#bfe9e3; }
.badge-warning { background:#fff8e1; color:#8a6d3b; border-color:#ffe9a8; }
.badge-danger  { background:#fdecea; color:#b71c1c; border-color:#f5c6c4; }
.badge-info    { background:#e8f0fe; color:#283593; border-color:#c5d0fb; }

.muted { color:#666; }
.small { font-size:.9em; }
@media print {
  .refund-actions, .alert { display: none !important; }
  .refund-container { box-shadow: none !important; border: 0 !important; }
}
</style>
