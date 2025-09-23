<!-- PaymentsDashboard.vue -->
<template>
  <div class="payments">
    <div class="page-title d-flex align-items-center justify-content-between">
      <h1 class="mb-0">Paiements</h1>
      <button
          class="btn btn-primary d-flex align-items-center"
          data-bs-auto-close="outside"
          data-bs-toggle="modal"
          data-bs-target="#newPaiementsModal"
      >
        <img src="/static/icons/new_icon.svg" alt="" />
        <span class="ms-2">Ajouter un paiement</span>
      </button>
    </div>

    <div class="text-muted mb-3">
      Retrouvez ici tous les paiements effectués. Recherchez, filtrez, visualisez.
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" />

    <!-- KPIs -->
    <div v-if="isAdmin" class="kpi-grid">
      <div class="kpi-card">
        <div class="kpi-title">Chiffre d'affaires (filtre)</div>
        <div class="kpi-value">{{ money(sumAmount(filteredPayments)) }}</div>
        <div class="kpi-foot">Tous types confondus</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-title">Nombre de paiements</div>
        <div class="kpi-value">{{ filteredPayments.length }}</div>
        <div class="kpi-foot">{{ payments.length }} au total</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-title">Panier moyen</div>
        <div class="kpi-value">{{ money(avgAmount(filteredPayments)) }}</div>
        <div class="kpi-foot">montant payé / paiement</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-title">Top type</div>
        <div class="kpi-value">{{ topTypeLabel }}</div>
        <div class="kpi-foot">dans la sélection</div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filters">
      <div class="search-input-group d-flex align-items-center">
        <span class="search-icon m-2">
          <img src="/static/icons/search.svg" alt="Search Icon" />
        </span>
        <input
            type="text"
            class="form-control"
            placeholder="Rechercher: parent, élève, méthode, classe…"
            v-model.trim="q"
        />
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-2">
          <label class="form-label">Type</label>
          <select v-model="typeFilter" class="form-select">
            <option value="all">Tous</option>
            <option value="soutien">Soutien</option>
            <option value="arabe">Arabe</option>
            <option value="livres">Livres</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Méthode</label>
          <select v-model="methodFilter" class="form-select">
            <option value="all">Toutes</option>
            <option v-for="m in paymentMethods" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Mois (soutien)</label>
          <select v-model="monthFilter" class="form-select">
            <option value="all">Tous</option>
            <option v-for="m in months" :key="m" :value="m">{{ m }}</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Année</label>
          <select v-model.number="yearFilter" class="form-select">
            <option value="all">Toutes</option>
            <option v-for="y in yearsAvailable" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Montant min</label>
          <input type="number" class="form-control" v-model.number="minAmount" placeholder="0" />
        </div>

        <div class="col-md-2">
          <label class="form-label">Montant max</label>
          <input type="number" class="form-control" v-model.number="maxAmount" placeholder="∞" />
        </div>
      </div>

      <div class="d-flex align-items-center justify-content-between mt-3">
        <small class="text-muted">{{ filteredPayments.length }} paiements trouvés</small>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" @click="resetFilters">
            Réinitialiser
          </button>
          <button class="btn btn-outline-primary btn-sm" @click="exportCsv">
            Exporter CSV
          </button>
        </div>
      </div>
    </div>

    <!-- Graphes -->
<!--    <div class="charts row g-3">-->
<!--      <div class="col-lg-8">-->
<!--        <div class="card p-3 h-100">-->
<!--          <div class="d-flex align-items-center justify-content-between">-->
<!--            <h6 class="mb-2">CA par mois</h6>-->
<!--            <small class="text-muted">Filtre appliqué</small>-->
<!--          </div>-->
<!--          <canvas ref="barCanvas" height="140"></canvas>-->
<!--        </div>-->
<!--      </div>-->
<!--      <div class="col-lg-4">-->
<!--        <div class="card p-3 h-100">-->
<!--          <h6 class="mb-2">Répartition par type</h6>-->
<!--          <canvas ref="doughnutCanvas" height="140"></canvas>-->
<!--        </div>-->
<!--      </div>-->
<!--    </div>-->

    <!-- TABLEAUX -->
    <section class="mt-4">
      <ul class="nav nav-pills gap-2 mb-3">
        <li class="nav-item"><button class="btn" :class="tabBtn('all')" @click="activeTab='all'">Tous</button></li>
        <li class="nav-item"><button class="btn" :class="tabBtn('soutien')" @click="activeTab='soutien'">Soutien</button></li>
        <li class="nav-item"><button class="btn" :class="tabBtn('arabe')" @click="activeTab='arabe'">Arabe</button></li>
        <li class="nav-item"><button class="btn" :class="tabBtn('livres')" @click="activeTab='livres'">Livres</button></li>
      </ul>

      <!-- Tableau générique (tous) -->
      <div v-if="activeTab==='all'" class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Type</th>
            <th>Montant</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Classe</th>
            <th>Méthode</th>
            <th>Mois</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(p, i) in filteredPayments" :key="p.id || i" :class="rowClass(p)">
            <td>{{ i + 1 }}</td>
            <td>{{ d(p.paymentDate) }}</td>
            <td class="text-capitalize">
              <span class="badge" :class="badgeClass(p.serviceType)">{{ labelType(p.serviceType) }}</span>
            </td>
            <td>{{ money(p.amountPaid) }}</td>
            <td>{{ p.parent?.fullNameParent }}</td>
            <td>{{ p.student ? (p.student.firstName+' '+p.student.lastName) : '—' }}</td>
            <td>
              <span v-if="p.studyClass">{{ p.studyClass.name }} ({{ p.studyClass.speciality }})</span>
              <span v-else>—</span>
            </td>
            <td>{{ p.paymentType }}</td>
            <td>{{ p.month || '—' }}</td>
            <td class="text-end">
              <button v-if="isAdmin" class="btn btn-sm btn-outline-danger" @click="deletePayment(p)">Supprimer</button>
            </td>
          </tr>
          <tr v-if="!filteredPayments.length">
            <td colspan="10" class="text-center text-muted py-4">Aucun résultat</td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Tableau soutien -->
      <div v-if="activeTab==='soutien'" class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Classe</th>
            <th>Spécialité</th>
            <th>Méthode</th>
            <th>Mois</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(p, i) in filteredPayments.filter(p => p.serviceType==='soutien')" :key="p.id || i" class="row-soutien">
            <td>{{ i + 1 }}</td>
            <td>{{ d(p.paymentDate) }}</td>
            <td>{{ money(p.amountPaid) }}</td>
            <td>{{ p.parent?.fullNameParent }}</td>
            <td>{{ p.student?.firstName }} {{ p.student?.lastName }}</td>
            <td>{{ p.studyClass?.name || '—' }}</td>
            <td>{{ p.studyClass?.speciality || '—' }}</td>
            <td>{{ p.paymentType }}</td>
            <td>{{ p.month || '—' }}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-danger" @click="deletePayment(p)">Supprimer</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Tableau arabe -->
      <div v-if="activeTab==='arabe'" class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Montant</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Méthode</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(p, i) in filteredPayments.filter(p => p.serviceType==='arabe')" :key="p.id || i" class="row-arabe">
            <td>{{ i + 1 }}</td>
            <td>{{ d(p.paymentDate) }}</td>
            <td>{{ money(p.amountPaid) }}</td>
            <td>{{ p.parent?.fullNameParent }}</td>
            <td>{{ p.student?.firstName }} {{ p.student?.lastName }}</td>
            <td>{{ p.paymentType }}</td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-danger" @click="deletePayment(p)">Supprimer</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Tableau livres -->
      <div v-if="activeTab==='livres'" class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Date</th>
            <th>Parent</th>
            <th>Élève</th>
            <th>Méthode</th>
            <th>Montant</th>
            <th>Articles</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="(p, i) in filteredPayments.filter(p => p.serviceType==='livres')"
              :key="p.id || i"
              class="row-livres"
          >
            <td>{{ i + 1 }}</td>
            <td>{{ d(p.paymentDate) }}</td>
            <td>{{ p.parent?.fullNameParent }}</td>
            <td>{{ p.student?.firstName }} {{ p.student?.lastName }}</td>
            <td>{{ p.paymentType }}</td>
            <td>{{ money(p.amountPaid) }}</td>
            <td>
              <div v-if="(p.bookItems || []).length">
                <div v-for="(bi, j) in p.bookItems" :key="`bi-${i}-${j}`" class="small">
                  • {{ bi.book?.name || 'Livre' }} × {{ bi.quantity }} — {{ money(bi.lineTotal) }}
                </div>
              </div>
              <span v-else class="text-muted">—</span>
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-danger" @click="deletePayment(p)">Supprimer</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Modal création -->
    <new-payment-modal
        :parents="parents"
        :books="books"
        @payment-added="handlePaymentAdded"
    />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";
import NewPaymentModal from "./NewPaymentModal.vue";
import { Chart, BarController, BarElement, CategoryScale, LinearScale, DoughnutController, ArcElement, Tooltip, Legend } from "chart.js";

Chart.register(BarController, BarElement, CategoryScale, LinearScale, DoughnutController, ArcElement, Tooltip, Legend);

export default {
  name: "PaymentsDashboard",
  components: { Alert, NewPaymentModal },
  props: {
    books: { type: Array, required: true },
    currentUser: { type: Object, required: true }
  },
  data() {
    return {
      payments: [],
      parents: [],
      messageAlert: null,
      typeAlert: null,

      // Filtres
      q: "",
      typeFilter: "all",
      methodFilter: "all",
      monthFilter: "all",
      yearFilter: "all",
      minAmount: null,
      maxAmount: null,

      activeTab: "all",

      // Graphes
      barChart: null,
      doughnutChart: null,

      months: [
        "Janvier","Février","Mars","Avril","Mai","Juin",
        "Juillet","Août","Septembre","Octobre","Novembre","Décembre"
      ],
    };
  },
  computed: {
    paymentMethods() {
      const set = new Set(this.payments.map(p => p.paymentType).filter(Boolean));
      return Array.from(set);
    },
    yearsAvailable() {
      const set = new Set(
          this.payments
              .map(p => this.safeYear(p.paymentDate))
              .filter(Boolean)
      );
      return Array.from(set).sort((a,b)=>a-b);
    },
    filteredPayments() {
      const s = this.q.toLowerCase();
      return this.payments.filter(p => {
        // texte libre
        const hay = [
          p.parent?.fullNameParent,
          p.paymentType,
          p.student?.firstName, p.student?.lastName,
          p.studyClass?.name, p.studyClass?.speciality
        ].map(x => (x || "").toString().toLowerCase()).join(" ");
        if (s && !hay.includes(s)) return false;

        // type
        if (this.typeFilter !== "all" && p.serviceType !== this.typeFilter) return false;

        // méthode
        if (this.methodFilter !== "all" && p.paymentType !== this.methodFilter) return false;

        // année
        const y = this.safeYear(p.paymentDate);
        if (this.yearFilter !== "all" && y !== this.yearFilter) return false;

        // mois (uniquement utile surtout pour soutien, mais on laisse générique)
        if (this.monthFilter !== "all") {
          const m = p.month || this.months[(new Date(p.paymentDate).getMonth())] || null;
          if (m !== this.monthFilter) return false;
        }

        // montant
        const amt = Number(p.amountPaid || 0);
        if (this.minAmount != null && amt < this.minAmount) return false;
        return !(this.maxAmount != null && amt > this.maxAmount);


      });
    },
    topTypeLabel() {
      if (!this.filteredPayments.length) return "—";
      const counts = this.groupBy(this.filteredPayments, p => p.serviceType || "inconnu");
      const [type] = Object.entries(counts).sort((a,b)=>b[1]-a[1])[0];
      return this.labelType(type);
    },
    isAdmin() {
      return this.currentUser?.roles?.includes("ROLE_ADMIN");
    }
  },
  methods: {
    tabBtn(name) {
      // renvoie les classes Bootstrap selon l’onglet actif
      return this.activeTab === name ? 'btn-primary active' : 'btn-outline-secondary';
    },
    async fetchPayments() {
      try {
        const { data } = await this.axios.get(this.$routing.generate("all_payments"));
        this.payments = data.payments || [];
        this.parents  = data.parents  || [];
        this.$nextTick(this.buildCharts);
      } catch (e) {
        console.error(e);
        this.messageAlert = "Erreur lors du chargement des paiements.";
        this.typeAlert = "danger";
      }
    },

    // --- Utils affichage
    d(date) {
      if (!date) return "—";
      return new Date(date).toLocaleDateString("fr-FR");
    },
    money(v) {
      const n = Number(v || 0);
      return n.toLocaleString("fr-FR", { style: "currency", currency: "EUR" });
    },
    labelType(t) {
      switch (t) {
        case "soutien": return "Soutien";
        case "arabe":   return "Arabe";
        case "livres":  return "Livres";
        default:        return "—";
      }
    },
    badgeClass(t) {
      return {
        soutien: "bg-info",
        arabe: "bg-warning text-dark",
        livres: "bg-success"
      }[t] || "bg-secondary";
    },
    rowClass(p) {
      return {
        "row-soutien": p.serviceType === "soutien",
        "row-arabe":   p.serviceType === "arabe",
        "row-livres":  p.serviceType === "livres",
      };
    },
    sumAmount(list) {
      return list.reduce((a,p)=>a + Number(p.amountPaid || 0), 0);
    },
    avgAmount(list) {
      const n = list.length || 1;
      return this.sumAmount(list) / n;
    },
    safeYear(date) {
      if (!date) return null;
      const d = new Date(date);
      return Number.isFinite(d.getTime()) ? d.getFullYear() : null;
    },
    groupBy(arr, keyFn) {
      return arr.reduce((acc, x) => {
        const k = keyFn(x);
        acc[k] = (acc[k] || 0) + 1;
        return acc;
      }, {});
    },

    // --- Graphes (Chart.js)
    buildCharts() {
      this.destroyCharts();

      // Bar: CA par mois sur l'année sélectionnée ou toutes (on agrège par YYYY-MM)
      const map = new Map(); // "YYYY-MM" -> total
      this.filteredPayments.forEach(p => {
        const d = new Date(p.paymentDate);
        if (!Number.isFinite(d.getTime())) return;
        const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}`;
        map.set(key, (map.get(key) || 0) + Number(p.amountPaid || 0));
      });
      const labels = Array.from(map.keys()).sort();
      const values = labels.map(k => map.get(k));

      this.barChart = new Chart(this.$refs.barCanvas.getContext("2d"), {
        type: "bar",
        data: {
          labels,
          datasets: [{ label: "Montant (€)", data: values }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true }
          }
        }
      });

      // Doughnut: Répartition par type
      const byType = ["soutien","arabe","livres"].map(t => ({
        t,
        total: this.filteredPayments
            .filter(p => p.serviceType === t)
            .reduce((a,p)=>a+Number(p.amountPaid||0),0)
      }));
      this.doughnutChart = new Chart(this.$refs.doughnutCanvas.getContext("2d"), {
        type: "doughnut",
        data: {
          labels: byType.map(x => this.labelType(x.t)),
          datasets: [{ data: byType.map(x => x.total) }]
        },
        options: { responsive: true, maintainAspectRatio: false }
      });
    },
    destroyCharts() {
      if (this.barChart) { this.barChart.destroy(); this.barChart = null; }
      if (this.doughnutChart) { this.doughnutChart.destroy(); this.doughnutChart = null; }
    },

    // --- Actions
    resetFilters() {
      this.q = "";
      this.typeFilter = "all";
      this.methodFilter = "all";
      this.monthFilter = "all";
      this.yearFilter = "all";
      this.minAmount = null;
      this.maxAmount = null;
    },
    exportCsv() {
      const rows = [
        ["Date","Type","Montant","Parent","Élève","Classe","Spécialité","Méthode","Mois","Année"]
      ];
      this.filteredPayments.forEach(p => {
        rows.push([
          this.d(p.paymentDate),
          this.labelType(p.serviceType),
          Number(p.amountPaid || 0).toFixed(2).replace('.',','),
          p.parent?.fullNameParent || "",
          p.student ? `${p.student.firstName||""} ${p.student.lastName||""}`.trim() : "",
          p.studyClass?.name || "",
          p.studyClass?.speciality || "",
          p.paymentType || "",
          p.month || "",
          this.safeYear(p.paymentDate) || ""
        ]);
      });
      const csv = rows.map(r => r.map(x => `"${String(x).replaceAll('"','""')}"`).join(";")).join("\n");
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const url  = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url; a.download = "paiements.csv"; a.click();
      URL.revokeObjectURL(url);
    },
    async deletePayment(payment) {
      if (!confirm("Supprimer ce paiement ?")) return;
      try {
        await this.axios.delete(this.$routing.generate("payment_delete", { id: payment.id }));
        await this.fetchPayments();
        this.messageAlert = "Paiement supprimé avec succès.";
        this.typeAlert = "success";
      } catch (e) {
        console.error(e);
        this.messageAlert = "Erreur lors de la suppression.";
        this.typeAlert = "danger";
      }
    },
    handlePaymentAdded() {
      this.fetchPayments();
      this.messageAlert = "Paiement enregistré avec succès !";
      this.typeAlert = "success";
      // on laisse la fermeture du modal au composant enfant
    },
  },
  watch: {
    // rebâtir les graphes quand les filtres changent
    filteredPayments() {
      this.$nextTick(this.buildCharts);
    }
  },
  mounted() {
    this.fetchPayments();
  },
  beforeUnmount() {
    this.destroyCharts();
  }
};
</script>

<style scoped>
.payments { max-width: 1800px; margin: 0 auto; }
.page-title img { width: 18px; height: 18px; }

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
  gap: 12px;
  margin: 10px 0 18px;
}
.kpi-card {
  background: #fff; border: 1px solid #e9ecef; border-radius: 12px;
  padding: 14px 16px;
}
.kpi-title { font-size: 12px; color:#6c757d; text-transform: uppercase; letter-spacing: .4px; }
.kpi-value { font-size: 22px; font-weight: 700; color:#1f2937; }
.kpi-foot  { font-size: 12px; color:#9aa3af; }

.filters {
  background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:12px 14px; margin-bottom:16px;
}
.search-input-group .form-control { border: none; box-shadow: none; }
.search-input-group { border:1px solid #e9ecef; border-radius:10px; background:#f8fafc; padding-right:8px; }

.charts .card { border:1px solid #e9ecef; border-radius:12px; }

.nav .btn { border-radius: 20px; }
.nav .btn.active, .nav .btn:focus { background:#0d6efd; color:#fff; }
.nav .btn:not(.active) { background:#f1f5f9; color:#334155; }

.table thead th { white-space: nowrap; }
.row-soutien { background:#f1f9ff; }
.row-arabe   { background:#fff7e6; }
.row-livres  { background:#eefcf1; }
</style>
