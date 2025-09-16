<template>
  <div class="container mt-5">
    <h1 class="text-primary mb-4">Tableau de bord parent</h1>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" />

    <!-- En-tête semaine + filtres -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="week-badge" v-if="weekLabel">
        <span class="me-2">📆</span>
        <span>{{ weekLabel }}</span>
      </div>


      <div class="d-flex gap-2 align-items-center">
        <flatpickr v-model="filterStartDate" :config="flatpickrConfig" class="form-control" placeholder="Début" />
        <flatpickr v-model="filterEndDate"   :config="flatpickrConfig" class="form-control" placeholder="Fin" />
        <button class="btn btn-outline-primary" @click="fetchData">Filtrer</button>
      </div>
    </div>

    <!-- Sessions à venir -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">📅 Sessions à venir <span class="text-muted">({{ sessions.length }})</span></h5>

          <div class="d-flex gap-2 align-items-center">
            <select class="form-select" v-model="selectedChildId" style="width:auto">
              <option :value="0">Tous les enfants</option>
              <option v-for="c in children" :key="c.id" :value="c.id">
                {{ c.firstName }} {{ c.lastName }}
              </option>
            </select>

            <div class="btn-group">
              <button class="btn btn-outline-secondary" :class="{active:viewMode==='table'}" @click="viewMode='table'">Tableau</button>
              <button class="btn btn-outline-secondary" :class="{active:viewMode==='week'}"  @click="viewMode='week'">Semaine</button>
            </div>
          </div>
        </div>

        <!-- Vue tableau -->
        <div v-if="viewMode==='table'">
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
              <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Enfant</th>
                <th>Matière</th>
                <th>Professeur</th>
                <th>Salle</th>
                <th class="text-end">Statut</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(s, i) in filteredSessions" :key="i">
                <td>{{ formatDateLabelFR(s.start) }}</td>
                <td>{{ formatTimeFR(s.start) }} → {{ formatTimeFR(s.end) }}</td>
                <td>
  <span class="chip">
    <span class="chip-avatar">{{ initialsFromFullName(s.child) }}</span>
    <span class="chip-text">{{ s.child }}</span>
  </span>
                </td>

                <td>📘 {{ s.subject }}</td>
                <td>👤 {{ s.teacher }}</td>
                <td>📍 {{ s.room || '—' }}</td>
                <td class="text-end">
                  <span class="badge" :class="statusBadgeClass(s)">{{ statusText(s) }}</span>
                </td>
              </tr>
              <tr v-if="filteredSessions.length===0">
                <td colspan="6" class="text-center text-muted py-4">Aucune session sur la période et le filtre sélectionnés.</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Vue semaine (mini) -->
        <div v-else>
          <div class="row g-3">
            <div class="col-md-6 col-lg-4" v-for="(s, i) in filteredSessions" :key="'w-'+i">
              <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between">
                  <div class="fw-semibold">{{ s.subject }}</div>
                  <span class="badge text-bg-light">{{ s.dateLabel }}</span>
                </div>
                <div class="small text-muted mb-2">{{ s.startHour }} → {{ s.endHour }}</div>
                <div class="small"><span class="badge text-bg-light me-1">{{ s.child }}</span></div>
                <div class="small mt-1">👤 {{ s.teacher }}</div>
                <div class="small">📍 {{ s.room || '—' }}</div>
              </div>
            </div>
          </div>
          <div v-if="filteredSessions.length===0" class="text-center text-muted py-4">
            Aucune session sur la période et le filtre sélectionnés.
          </div>
        </div>
      </div>
    </div>

    <!-- Mes enfants -->
    <div class="mb-3 d-flex align-items-center gap-2">
      <h5 class="mb-0">👨‍👩‍👧‍👦 Mes enfants</h5>
      <button class="btn btn-primary ms-auto" @click="onAskChange"><span class="me-1">＋</span>Demander un changement</button>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-6" v-for="c in children" :key="c.id">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width:36px;height:36px;">
                {{ initials(c.firstName, c.lastName) }}
              </div>
              <div>
                <div class="fw-semibold">{{ c.firstName }} {{ c.lastName }}</div>
                <div><span class="badge text-bg-secondary">Niveau {{ c.level }}</span></div>
              </div>
            </div>

            <div v-if="c.mainClass" class="p-3 rounded" style="background:#eef5ff">
              <div class="small text-muted">Classe principale</div>
              <div class="fw-semibold">{{ c.mainClass.label }}</div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
              <div class="small text-muted">{{ c.registrations?.length || 0 }} inscription(s)</div>
              <button class="btn btn-outline-secondary btn-sm" @click="openRegistrations(c)">👁️ Voir les inscriptions</button>
            </div>
          </div>
        </div>
      </div>
      <div v-if="children.length===0" class="text-center text-muted py-4">Aucun enfant rattaché.</div>
    </div>

    <!-- Historique des demandes -->
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">🕘 Historique des demandes <span class="text-muted">({{ requests.length }})</span></h5>

        <div v-if="requests.length===0" class="text-muted">Aucune demande.</div>

        <div v-for="r in requests" :key="r.id" class="border rounded p-3 mb-2">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span class="badge text-bg-light">{{ r.student || '—' }}</span>
              <span class="badge text-bg-secondary">{{ r.level || '' }}</span>
            </div>
            <span class="badge"
                  :class="{
                    'text-bg-warning': r.status==='pending',
                    'text-bg-success': r.status==='approved',
                    'text-bg-danger':  r.status==='rejected'
                  }">
              {{ statusLabel(r.status) }}
            </span>
          </div>
          <div class="small text-muted mt-2">Changement demandé → {{ typeLabel(r.type) }}</div>
          <div class="small fst-italic mt-2" v-if="r.comment">“{{ r.comment }}”</div>
          <div class="small text-muted mt-2">Demandé le {{ formatDateTime(r.createdAt) }}</div>
        </div>
      </div>
    </div>

    <!-- Modal latéral : Inscriptions -->
    <div v-if="showRegModal" class="modal-backdrop">
      <div class="modal-dialog">
        <div class="modal-content" style="max-height: 90vh;">
          <div class="modal-header">
            <h5 class="modal-title">👁️ Inscriptions de {{ currentChild?.firstName }} {{ currentChild?.lastName }}</h5>
            <button class="btn-close" @click="closeRegistrations"></button>
          </div>
          <div class="modal-body">
            <div class="border rounded p-3 mb-2" v-for="r in currentChild?.registrations || []" :key="r.id">
              <div class="fw-semibold">{{ r.studyClass?.speciality || r.label }}</div>
              <div class="small text-muted">{{ r.studyClass?.day }} – {{ r.studyClass?.start }} → {{ r.studyClass?.end }}</div>
              <div class="small">Niveau {{ r.studyClass?.level }}</div>
              <div class="small">Professeur : {{ r.studyClass?.teacher || '—' }}</div>
              <span class="badge mt-2" :class="r.active ? 'text-bg-success' : 'text-bg-secondary'">
                {{ r.active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <div v-if="(currentChild?.registrations || []).length===0" class="text-muted">Aucune inscription.</div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeRegistrations">Fermer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Flatpickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { Chart, registerables } from 'chart.js';
import Alert from "../../ui/Alert.vue";
Chart.register(...registerables);

export default {
  components: { Alert, Flatpickr },
  data() {
    return {
      // Filtres
      filterStartDate: "",
      filterEndDate: "",
      flatpickrConfig: { enableTime: false, dateFormat: "Y-m-d" },

      // Données
      rangeLabel: { start: "", end: "" },
      rangeRaw: { start: "", end: "" },
      sessions: [],
      children: [],
      requests: [],

      // UI
      messageAlert: null,
      typeAlert: null,
      selectedChildId: 0,
      viewMode: 'table',

      // Modal inscriptions
      showRegModal: false,
      currentChild: null,
    };
  },
  computed: {
    weekLabel() {
      const sRaw = this.rangeRaw?.start || '';
      const eRaw = this.rangeRaw?.end   || '';
      const s = this.formatShortDateFR(sRaw);
      const e = this.formatShortDateFR(eRaw);
      return s && e ? `Semaine du ${s} au ${e}` : '';
    },
    filteredSessions() {
      if (!this.selectedChildId) return this.sessions;
      return this.sessions.filter(s => {
        const c = s.child || '';
        const target = this.children.find(x => x.id === this.selectedChildId);
        if (!target) return false;
        const full = `${target.firstName} ${target.lastName}`.trim();
        return c === full;
      });
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    formatShortDateFR(isoYmd) {
      if (!isoYmd) return '';
      const [Y, M, D] = isoYmd.split('-').map(Number);
      // on crée une date "neutre" en UTC pour éviter les décalages
      const d = new Date(Date.UTC(Y, M - 1, D, 12, 0, 0));
      return new Intl.DateTimeFormat('fr-FR', {
        timeZone: 'Europe/Paris',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      }).format(d);
    },
    statusText(s) {
      if (!s?.start || !s?.end) return '—';
      const now = new Date();           // ok car start/end sont en ISO avec offset
      const start = new Date(s.start);
      const end   = new Date(s.end);

      if (now < start) return 'À venir';
      if (now >= start && now <= end) return 'En cours';

      // Terminé
      if (s.presence === true)  return 'Terminé · Présent';
      if (s.presence === false) return 'Terminé · Absent';
      return 'Terminé';
    },
    statusBadgeClass(s) {
      if (!s?.start || !s?.end) return 'status-neutral';

      const now = new Date();
      const start = new Date(s.start);
      const end   = new Date(s.end);

      if (now < start) return 'status-upcoming';
      if (now >= start && now <= end) return 'status-ongoing';

      // finished
      if (s.presence === true)  return 'status-finished-present';
      if (s.presence === false) return 'status-finished-absent';
      return 'status-finished';
    },
    formatDateLabelFR(iso) {
      // fallback si pas de start ISO : on garde label existant
      if (!iso) return '';
      const d = new Date(iso);
      const fmt = new Intl.DateTimeFormat('fr-FR', {
        weekday: 'short', day: '2-digit', month: 'short',
        timeZone: 'Europe/Paris'
      });
      // Ex: "mer. 17 sept."
      const txt = fmt.format(d);
      // On met la première lettre en maj et on ajoute un point à la fin du mois s'il manque
      return txt.replace(/^./, c => c.toUpperCase());
    },
    formatTimeFR(iso) {
      if (!iso) return '';
      const d = new Date(iso);
      return new Intl.DateTimeFormat('fr-FR', {
        hour: '2-digit', minute: '2-digit',
        timeZone: 'Europe/Paris',
        hour12: false
      }).format(d);
    },
    initialsFromFullName(name) {
      if (!name) return '';
      const parts = name.trim().split(/\s+/);
      const first = (parts[0] || '')[0] || '';
      const last  = (parts[parts.length - 1] || '')[0] || '';
      return (first + last).toUpperCase();
    },
    // --- Fetch ---
    fetchData() {
      const params = {};
      if (this.filterStartDate) params.start = this.filterStartDate;
      if (this.filterEndDate)   params.end   = this.filterEndDate;

      this.axios
          .get(this.$routing.generate("app_dashboard_data_parent"), { params })
          .then(({ data }) => {
            // Avec ApiResponseTrait, le payload est dans data.message
            const payload = data?.message || {};
            this.rangeRaw = {           // <— garde brute pour le computed
              start: payload.range?.start || '',
              end:   payload.range?.end   || ''
            };
            this.rangeLabel = {
              start: (payload.range?.start || '').split('-').reverse().join('/'),
              end:   (payload.range?.end   || '').split('-').reverse().join('/'),
            };
            this.sessions = payload.sessions || [];
            this.children = payload.children || [];
            this.requests = payload.requests || [];

            this.messageAlert = null;
            this.typeAlert = null;
          })
          .catch(error => {
            const resp = error.response && error.response.data;
            if (resp) {
              if (resp.message && typeof resp.message === 'object' && 'text' in resp.message) {
                this.messageAlert = resp.message.text;
              } else if (typeof resp.message === 'string') {
                this.messageAlert = resp.message;
              } else {
                this.messageAlert = "Erreur lors du chargement des données.";
              }
            } else {
              this.messageAlert = "Erreur lors du chargement des données.";
            }
            this.typeAlert = "danger";
          });
    },

    // --- UI helpers ---
    initials(fn, ln) {
      return ((fn?.[0] || '') + (ln?.[0] || '')).toUpperCase();
    },
    openRegistrations(child) {
      this.currentChild = child;
      this.showRegModal = true;
    },
    closeRegistrations() {
      this.currentChild = null;
      this.showRegModal = false;
    },
    onAskChange() {
      // à brancher plus tard sur une route/modal de création de Demande
      this.messageAlert = "Fonction ‘Demander un changement’ à implémenter 😉";
      this.typeAlert = "info";
    },
    statusLabel(s) {
      return s === 'pending' ? 'En cours de traitement'
          : s === 'approved' ? 'Approuvée'
              : s === 'rejected' ? 'Refusée'
                  : s;
    },
    typeLabel(t) {
      return t === 'class_change' ? 'Nouvelle classe' : t;
    },
    formatDateTime(str) {
      // attend "Y-m-d H:i"
      if (!str) return '';
      const [d, h] = str.split(' ');
      const [Y, M, D] = d.split('-');
      return `${D}/${M}/${Y} à ${h}`;
    },
  }
};
</script>

<style scoped>
.card { border-radius: 10px; }
.modal-backdrop {
  position: fixed; inset: 0; background-color: rgba(0,0,0,.5);
  display: flex; align-items: center; justify-content: center; z-index: 1050;
}
.modal-dialog { max-width: 520px; width: 100%; margin: 0 12px; }
.modal-content { border-radius: 10px; overflow: hidden; }
.modal-header, .modal-footer { padding: 1rem; border-bottom: 1px solid #e9ecef; }
.modal-body { padding: 1rem; max-height: 70vh; overflow: auto; }
.btn-close { background: none; border: 0; font-size: 1.4rem; line-height: 1; }
/* Chip avatar + texte (style "badge" amélioré) */
.chip {
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  background: #f3f4f6;
  border-radius: 9999px;
  padding: .25rem .5rem;
  line-height: 1;
  font-weight: 500;
}
.chip-avatar {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #e5e7eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: .75rem;
  font-weight: 700;
}
.chip-text { white-space: nowrap; }
.week-badge{
  display:inline-flex; align-items:center; gap:.5rem;
  padding:.4rem .75rem; border-radius:9999px;
  background:#f7f8fb; border:1px solid #e5e7eb;
  color:#111827 !important; font-weight:600; line-height:1.1;
  /* pour éviter le texte “invisible” et les coupures */
  white-space: normal; word-break: keep-all;
}
.badge.status-upcoming{
  background:#eef6ff; color:#1d4ed8; border:1px solid #bfdbfe;
}
.badge.status-ongoing{
  background:#fff7ed; color:#c2410c; border:1px solid #fed7aa;
}
.badge.status-finished{
  background:#f3f4f6; color:#374151; border:1px solid #e5e7eb;
}
.badge.status-finished-present{
  background:#ecfdf5; color:#047857; border:1px solid #a7f3d0;
}
.badge.status-finished-absent{
  background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;
}
.badge.status-neutral{
  background:#f3f4f6; color:#6b7280; border:1px solid #e5e7eb;
}

</style>
