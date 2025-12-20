<template>
  <div class="dashboard-container">
    <!-- Header avec gradient -->
    <div class="dashboard-header">
      <div class="header-content">
        <h1 class="dashboard-title">
          <span class="icon-wrapper">📊</span>
          Tableau de bord
        </h1>
        <div class="header-stats">
          <div class="stat-pill" v-if="totals">
            <span class="stat-label">Sessions</span>
            <span class="stat-value">{{ totals.sessionsCount }}</span>
          </div>
          <div class="stat-pill" v-if="totals">
            <span class="stat-label">Heures</span>
            <span class="stat-value">{{ Number(totals.hoursSum).toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="filters-section">
      <div class="filters-header">
        <h2 class="filters-title">🔍 Filtres</h2>
        <button class="btn-reset" @click="resetFilters" :disabled="loading">
          <span class="reset-icon">↻</span>
          Réinitialiser
        </button>
      </div>

      <div class="filters-grid">
        <!-- Date Range -->
        <div class="filter-group date-range-group">
          <div class="filter-item">
            <label class="filter-label">📅 Du</label>
            <Flatpickr
                v-model="filterStartDate"
                :config="flatpickrConfig"
                class="filter-input date-input"
                placeholder="Date de début"
            />
          </div>
          <div class="filter-item">
            <label class="filter-label">📅 Au</label>
            <Flatpickr
                v-model="filterEndDate"
                :config="flatpickrConfig"
                class="filter-input date-input"
                placeholder="Date de fin"
            />
          </div>
        </div>

        <!-- Type & Year -->
        <div class="filter-item">
          <label class="filter-label">📚 Type de cours</label>
          <select v-model="filterClassType" class="filter-input filter-select">
            <option value="">Tous les types</option>
            <option v-for="t in available.classTypes" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>

        <div class="filter-item">
          <label class="filter-label">🎓 Année scolaire</label>
          <select v-model="filterSchoolYear" class="filter-input filter-select">
            <option value="">Toutes les années</option>
            <option v-for="y in available.schoolYears" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>

        <!-- Teacher & Speciality -->
        <div class="filter-item">
          <label class="filter-label">👨‍🏫 Professeur</label>
          <select v-model.number="filterTeacherId" class="filter-input filter-select">
            <option :value="null">Tous les professeurs</option>
            <option v-for="t in available.teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>

        <div class="filter-item">
          <label class="filter-label">🎯 Spécialité</label>
          <select v-model="filterSpeciality" class="filter-input filter-select">
            <option value="">Toutes les spécialités</option>
            <option v-for="s in available.specialities" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des données...</p>
    </div>

    <!-- Results Table -->
    <div v-else class="results-section">
      <div class="card-modern">
        <div class="card-header-modern">
          <h3 class="card-title">👥 Heures & sessions par professeur</h3>
          <span class="card-badge" v-if="byTeacher.length">{{ byTeacher.length }} professeur(s)</span>
        </div>

        <div class="table-container">
          <table class="modern-table" v-if="byTeacher && byTeacher.length > 0">
            <thead>
            <tr>
              <th class="th-name">Professeur</th>
              <th class="th-number">Heures</th>
              <th class="th-number">Sessions</th>
              <th class="th-chart">Répartition</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(t, index) in byTeacher" :key="t.teacherId" class="table-row" :style="{'--row-index': index}" role="button"  @click="openTeacherSessions(t)">
              <td class="td-name">
                <div class="teacher-cell">
                  <div class="teacher-avatar">{{ getInitials(t.teacherName) }}</div>
                  <span class="teacher-name">{{ t.teacherName }}</span>
                </div>
              </td>
              <td class="td-number">
                <span class="number-badge hours">{{ Number(t.hoursSum).toFixed(2) }}h</span>
              </td>
              <td class="td-number">
                <span class="number-badge sessions">{{ t.sessionsCount }}</span>
              </td>
              <td class="td-chart">
                <div class="chart-bar">
                  <div class="chart-fill" :style="{width: getPercentage(t.hoursSum) + '%'}"></div>
                </div>
              </td>
            </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-else class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Aucune donnée disponible</h3>
            <p>Essayez de modifier vos filtres pour voir des résultats</p>
          </div>
        </div>
      </div>
      <!-- Tableau des parents impayés -->
      <div class="card-modern" style="margin-top: 2rem;">
        <div class="card-header-modern">
          <h3 class="card-title">💶 Suivi des Paiements & Impayés</h3>
          <span class="card-badge" v-if="unpaidTotals">
            {{ unpaidTotals.parentsCount }} familles concernées
          </span>
        </div>

        <div class="unpaid-summary-banner" v-if="unpaidTotals">
          <div class="summary-item">
            <span class="lbl">Total Dû (Année)</span>
            <span class="val">{{ unpaidTotals.totalDue.toFixed(2) }} €</span>
          </div>
          <div class="summary-item">
            <span class="lbl">Déjà Versé</span>
            <span class="val success">{{ unpaidTotals.totalPaid.toFixed(2) }} €</span>
          </div>
          <div class="summary-item">
            <span class="lbl">Reste à recouvrer</span>
            <span class="val danger">{{ unpaidTotals.totalRemaining.toFixed(2) }} €</span>
          </div>
        </div>

        <div class="table-container" v-if="!unpaidLoading">
          <table class="modern-table" v-if="unpaidParents && unpaidParents.length">
            <thead>
            <tr>
              <th>Parent</th>
              <th>Contact</th>

              <th class="th-center bg-gray-50">
                <span class="th-sub">🕋 Arabe</span>
              </th>

              <th class="th-center bg-gray-50">
                <span class="th-sub">📚 Soutien</span>
              </th>

              <th class="th-number">Total Dû</th>
              <th class="th-number">Versé Global</th>
              <th class="th-number">Reste</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="(p, index) in unpaidParents"
                :key="p.parentId"
                class="table-row"
                :style="{'--row-index': index}"
            >
              <td>
                <div class="parent-name">{{ p.parentName }}</div>
              </td>

              <td>
                <div class="parent-contact">
                  <div v-if="p.email" class="contact-row">
                    <span class="icon">📧</span> {{ p.email }}
                  </div>
                  <div v-if="p.phone" class="contact-row">
                    <span class="icon">📞</span> {{ p.phone }}
                  </div>
                </div>
              </td>

              <td class="td-center bg-gray-50">
                <div class="service-detail" :class="{ 'inactive': p.arabChildrenCount === 0 }">
                  <div class="badge-count" v-if="p.arabChildrenCount > 0">
                    {{ p.arabChildrenCount }} enf.
                  </div>

                  <div class="price-split" v-if="p.arabChildrenCount > 0 || p.paidArabe > 0">
                    <div class="split-row">
                      <span class="lbl">Dû:</span>
                      <span class="val">{{ Number(p.dueArabe).toFixed(2) }}€</span>
                    </div>
                    <div class="split-row success" v-if="p.paidArabe > 0">
                      <span class="lbl">Reçu:</span>
                      <span class="val">{{ Number(p.paidArabe).toFixed(2) }}€</span>
                    </div>
                  </div>
                  <div v-else class="dash">-</div>
                </div>
              </td>

              <td class="td-center bg-gray-50">
                <div class="service-detail" :class="{ 'inactive': p.soutienRegistrationsCount === 0 }">
                  <div class="badge-count soutien" v-if="p.soutienRegistrationsCount > 0">
                    {{ p.soutienRegistrationsCount }} inscr.
                  </div>

                  <div class="price-split" v-if="p.soutienRegistrationsCount > 0 || p.paidSoutien > 0">
                    <div class="split-row">
                      <span class="lbl">Dû:</span>
                      <span class="val">{{ Number(p.dueSoutien).toFixed(2) }}€</span>
                    </div>
                    <div class="split-row success" v-if="p.paidSoutien > 0">
                      <span class="lbl">Reçu:</span>
                      <span class="val">{{ Number(p.paidSoutien).toFixed(2) }}€</span>
                    </div>
                  </div>
                  <div v-else class="dash">-</div>
                </div>
              </td>

              <td class="td-number">
        <span class="font-bold">
          {{ Number(p.totalDue).toFixed(2) }} €
        </span>
              </td>

              <td class="td-number">
        <span class="number-badge paid-badge">
          {{ Number(p.totalPaid).toFixed(2) }} €
        </span>
              </td>

              <td class="td-number">
        <span
            class="number-badge unpaid-badge"
            :class="{'unpaid-high': p.remaining >= 200}"
        >
          {{ Number(p.remaining).toFixed(2) }} €
        </span>
              </td>
            </tr>
            </tbody>
          </table>

          <div v-else class="empty-state">
            <div class="empty-icon">✅</div>
            <h3>Tout est en ordre !</h3>
            <p>Aucun impayé détecté pour l'année {{ filterSchoolYear || defaultSchoolYear }}.</p>
          </div>
        </div>

        <div v-else class="loading-state" style="box-shadow:none; border-radius:0;">
          <div class="spinner"></div>
          <p>Analyse des paiements en cours...</p>
        </div>

        <div v-if="unpaidError" class="error-message" style="margin-top:1rem">
          <span class="error-icon">⚠️</span>
          {{ unpaidError }}
        </div>
      </div>


      <!-- Error Message -->
      <div v-if="error" class="error-message">
        <span class="error-icon">⚠️</span>
        {{ error }}
      </div>
    </div>
    <!-- Modal Sessions Prof -->
    <div v-if="showSessionsModal" class="modal-overlay" @click.self="closeSessionsModal" @keyup.esc="closeSessionsModal" tabindex="0">
      <div class="modal-card">
        <div class="modal-header">
          <h3>📅 Sessions — {{ sessionsModal.teacherName || 'Professeur' }}</h3>
          <button class="modal-close" @click="closeSessionsModal">✕</button>
        </div>

        <div class="modal-body">
          <div v-if="sessionsModal.loading" class="modal-loading">
            <div class="spinner"></div>
            Chargement…
          </div>

          <div v-else>
            <div class="modal-summary">
              <span class="badge">Total: {{ sessionsModal.total.count }} sessions</span>
              <span class="badge">{{ Number(sessionsModal.total.hours).toFixed(2) }} h</span>
            </div>

            <ul v-if="sessionsModal.items.length" class="session-list">
              <li v-for="s in sessionsModal.items" :key="s.id" class="session-item">
                <div class="session-time">
                  <div class="session-date">{{ formatDateWithWeekday(s.startTime) }}</div>
                  <div class="session-hours">{{ formatTime(s.startTime) }} → {{ formatTime(s.endTime) }} ({{ Number(s.hours).toFixed(2) }}h)</div>
                </div>
                <div class="session-meta">
                  <div class="meta-line">
                    <span class="pill">{{ s.studyClass.classType }}</span>
                    <span class="pill">{{ s.studyClass.speciality }}</span>
                    <span class="pill weak">{{ s.studyClass.schoolYear }}</span>
                  </div>
                  <div class="meta-line weak">
                    {{ s.studyClass.name }} — Niveau: {{ s.studyClass.level }}
                  </div>
                </div>
              </li>
            </ul>

            <div v-else class="empty-state">
              <div class="empty-icon">📭</div>
              <h3>Aucune session sur cette période</h3>
            </div>
          </div>

          <div v-if="sessionsModal.error" class="error-message" style="margin-top:1rem">
            <span class="error-icon">⚠️</span>
            {{ sessionsModal.error }}
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-reset" @click="closeSessionsModal">Fermer</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from "axios";
import Flatpickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

export default {
  components: { Flatpickr },

  data() {
    return {
      showSessionsModal: false,
      sessionsModal: {
        teacherId: null,
        teacherName: null,
        loading: false,
        error: null,
        total: { count: 0, hours: 0 },
        items: [],
      },
      filterStartDate: "",
      filterEndDate: "",
      filterClassType: "",
      filterSpeciality: "",
      filterSchoolYear: "",
      defaultSchoolYear: "2025/2026",
      filterTeacherId: null,

      totals: null,
      byTeacher: [],
      available: { classTypes: [], specialities: [], teachers: [], schoolYears: [] },

      loading: false,
      error: null,

      flatpickrConfig: {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y"
      },

      _debounceTimer: null,
      unpaidLoading: false,
      unpaidError: null,
      unpaidParents: [],
      unpaidTotals: null,
    };
  },

  computed: {
    maxHours() {
      if (!this.byTeacher || this.byTeacher.length === 0) return 1;
      return Math.max(...this.byTeacher.map(t => Number(t.hoursSum)));
    }
  },

  created() {
    const { start, end } = this.getPreviousMonthRange();
    this.filterStartDate = start;
    this.filterEndDate = end;
    this.filterSchoolYear = this.defaultSchoolYear;
  },

  mounted() {
    //this.fetchStatistics();
    //this.fetchInfosNotPayed();
  },


  watch: {
    filterStartDate() { this.debouncedFetch(); },
    filterEndDate() { this.debouncedFetch(); },
    filterClassType() { this.debouncedFetch(); },
    filterSpeciality() { this.debouncedFetch(); },

    filterSchoolYear() {
      this.debouncedFetch();
      this.fetchInfosNotPayed();
    },

    filterTeacherId() { this.debouncedFetch(); },
  },


  methods: {
    fetchInfosNotPayed() {
      this.unpaidLoading = true;
      this.unpaidError = null;

      const params = {};
      const schoolYear = this.filterSchoolYear || this.defaultSchoolYear;
      if (schoolYear) {
        params.schoolYear = schoolYear;
      }

      axios
          .get("/dashboard/api/unpaid-parents", { params })
          .then(({ data }) => {
            this.unpaidParents = data.parents || [];
            this.unpaidTotals = data.totals || null;
          })
          .catch(() => {
            this.unpaidError = "Impossible de récupérer les informations d'impayés.";
          })
          .finally(() => {
            this.unpaidLoading = false;
          });
    },
    openTeacherSessions(row) {
      if (!row || !row.teacherId) return;
      this.showSessionsModal = true;
      this.sessionsModal.teacherId = row.teacherId;
      this.sessionsModal.teacherName = row.teacherName || 'Professeur';
      this.sessionsModal.loading = true;
      this.sessionsModal.error = null;
      this.sessionsModal.items = [];
      this.sessionsModal.total = { count: 0, hours: 0 };

      const params = {
        teacherId: row.teacherId,
        startDate: this.filterStartDate,
        endDate: this.filterEndDate,
      };
      if (this.filterClassType)  params.classType  = this.filterClassType;
      if (this.filterSpeciality) params.speciality = this.filterSpeciality;
      if (this.filterSchoolYear) params.schoolYear = this.filterSchoolYear;

      axios
          .get("/dashboard/api/teacher-sessions", { params })
          .then(({ data }) => {
            this.sessionsModal.items = data.sessions || [];
            this.sessionsModal.total = data.total || { count: 0, hours: 0 };
            if (!this.sessionsModal.teacherName && data.teacherName) {
              this.sessionsModal.teacherName = data.teacherName;
            }
          })
          .catch(() => {
            this.sessionsModal.error = "Impossible de récupérer les sessions.";
          })
          .finally(() => {
            this.sessionsModal.loading = false;
          });
    },

    closeSessionsModal() {
      this.showSessionsModal = false;
    },

    formatDate(iso) {
      const d = new Date(iso);
      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },

    formatTime(iso) {
      const d = new Date(iso);
      const hh = String(d.getHours()).padStart(2,'0');
      const ii = String(d.getMinutes()).padStart(2,'0');
      return `${hh}h${ii}`;
    },

    pad(n) { return String(n).padStart(2, "0"); },

    formatYmd(d) {
      return `${d.getFullYear()}-${this.pad(d.getMonth() + 1)}-${this.pad(d.getDate())}`;
    },

    getPreviousMonthRange() {
      const today = new Date();
      const year = today.getFullYear();
      const month = today.getMonth();

      const prevMonth = month === 0 ? 11 : month;
      const prevYear = month === 0 ? year - 1 : year;

      const start = new Date(prevYear, prevMonth, 1);
      const end = new Date(prevYear, prevMonth + 1, 0);

      return { start: this.formatYmd(start), end: this.formatYmd(end) };
    },

    resetFilters() {
      const { start, end } = this.getPreviousMonthRange();
      this.filterStartDate = start;
      this.filterEndDate = end;
      this.filterClassType = "";
      this.filterSpeciality = "";
      this.filterSchoolYear = this.defaultSchoolYear;
      this.filterTeacherId = null;
    },

    debouncedFetch(delay = 300) {
      clearTimeout(this._debounceTimer);
      this._debounceTimer = setTimeout(() => this.fetchStatistics(), delay);
    },

    fetchStatistics() {
      this.loading = true;
      this.error = null;

      const params = {};
      if (this.filterStartDate)  params.startDate  = this.filterStartDate;
      if (this.filterEndDate)    params.endDate    = this.filterEndDate;
      if (this.filterClassType)  params.classType  = this.filterClassType;
      if (this.filterSpeciality) params.speciality = this.filterSpeciality;
      if (this.filterSchoolYear) params.schoolYear = this.filterSchoolYear;
      if (this.filterTeacherId)  params.teacherId  = this.filterTeacherId;

      axios
          .get("/dashboard/api/stats", { params })
          .then(({ data }) => {
            this.totals = data.totals;
            this.byTeacher = data.byTeacher || [];
            this.available.classTypes = data.available?.classTypes || [];
            this.available.specialities = data.available?.specialities || [];
            this.available.teachers = data.available?.teachers || [];
            this.available.schoolYears = data.available?.schoolYears || [];
          })
          .catch(() => {
            this.error = "Impossible de récupérer les statistiques.";
          })
          .finally(() => {
            this.loading = false;
          });
    },
    getInitials(name) {
      return name
          .split(' ')
          .map(n => n[0])
          .join('')
          .toUpperCase()
          .slice(0, 2);
    },

    getPercentage(hours) {
      return (Number(hours) / this.maxHours) * 100;
    },

    formatDateWithWeekday(iso) {
      const d = new Date(iso);
      // 0=Dimanche, 1=Lundi, ... 6=Samedi
      const weekdays = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
      const dayName = weekdays[d.getDay()];

      const dd = String(d.getDate()).padStart(2,'0');
      const mm = String(d.getMonth()+1).padStart(2,'0');
      const yyyy = d.getFullYear();

      return `${dayName} ${dd}/${mm}/${yyyy}`;
    },

  },
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.dashboard-container {
  min-height: 100vh;
  padding: 2rem;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Header */
.dashboard-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.dashboard-title {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 0;
}

.icon-wrapper {
  font-size: 2.5rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.header-stats {
  display: flex;
  gap: 1rem;
}

.stat-pill {
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 50px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
}

.stat-label {
  font-size: 0.75rem;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
}

/* Filters Section */
.filters-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.filters-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.btn-reset {
  background: linear-gradient(135deg, #00695c 0%, #26a69a 100%);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 50px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  box-shadow: 0 5px 15px rgba(0, 105, 92, 0.3);
}

.btn-reset:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 105, 92, 0.4);
}

.btn-reset:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.reset-icon {
  font-size: 1.2rem;
  display: inline-block;
  transition: transform 0.3s ease;
}

.btn-reset:hover:not(:disabled) .reset-icon {
  transform: rotate(180deg);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.date-range-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  grid-column: span 2;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4a5568;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-input {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
  width: 100%;
}

.filter-input:focus {
  outline: none;
  border-color: #66bb6a;
  box-shadow: 0 0 0 3px rgba(102, 187, 106, 0.1);
}

.filter-select {
  cursor: pointer;
}

/* Loading State */
.loading-state {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(102, 187, 106, 0.2);
  border-top-color: #2e7d32;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Results Section */
.results-section {
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.card-modern {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.card-header-modern {
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  color: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.card-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Table */
.table-container {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: #f7fafc;
}

.modern-table th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 700;
  color: #4a5568;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.th-number {
  text-align: right;
}

.th-chart {
  text-align: right;
}

.table-row {
  border-bottom: 1px solid #e2e8f0;
  transition: all 0.3s ease;
  animation: slideIn 0.5s ease backwards;
  animation-delay: calc(var(--row-index) * 0.05s);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.table-row:hover {
  background: #f7fafc;
  transform: scale(1.01);
}

.modern-table td {
  padding: 1.25rem 1.5rem;
}

.teacher-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.teacher-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.teacher-name {
  font-weight: 600;
  color: #2d3748;
}

.td-number {
  text-align: left;
}

.number-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.875rem;
}

.number-badge.hours {
  background: linear-gradient(135deg, #2e7d3220, #66bb6a20);
  color: #2e7d32;
}

.number-badge.sessions {
  background: linear-gradient(135deg, #00695c20, #26a69a20);
  color: #00695c;
}

.td-chart {
  text-align: right;
  width: 200px;
}

.chart-bar {
  height: 8px;
  background: #e2e8f0;
  border-radius: 50px;
  overflow: hidden;
}

.chart-fill {
  height: 100%;
  background: linear-gradient(90deg, #2e7d32 0%, #66bb6a 100%);
  border-radius: 50px;
  transition: width 0.5s ease;
}

/* Empty State */
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.empty-state h3 {
  color: #2d3748;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #718096;
}

/* Error Message */
.error-message {
  background: linear-gradient(135deg, #fc8181 0%, #f56565 100%);
  color: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 10px 30px rgba(245, 101, 101, 0.3);
}

.error-icon {
  font-size: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }

  .dashboard-title {
    font-size: 1.75rem;
  }

  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }

  .date-range-group {
    grid-column: span 1;
    grid-template-columns: 1fr;
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .td-chart {
    display: none;
  }
}
/* Modal overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: grid;
  place-items: center;
  z-index: 999;
}

/* Modal card */
.modal-card {
  width: min(900px, 92vw);
  max-height: 88vh;
  background: #ffffff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 30px 80px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
}

/* Header / footer */
.modal-header, .modal-footer {
  padding: 1rem 1.25rem;
  background: #f7fafc;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #2d3748;
}

.modal-close {
  border: none;
  background: transparent;
  font-size: 1.25rem;
  cursor: pointer;
}

.modal-body {
  padding: 1rem 1.25rem;
  overflow: auto;
}

.modal-loading {
  display: flex;
  align-items: center;
  gap: .75rem;
}

/* Summary */
.modal-summary {
  display: flex;
  gap: .5rem;
  margin-bottom: .75rem;
}

.badge {
  background: #e6fffa;
  color: #00695c;
  padding: .35rem .6rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: .85rem;
}

/* Sessions list */
.session-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.session-item {
  padding: .9rem;
  border: 1px solid #edf2f7;
  border-radius: 12px;
  margin-bottom: .75rem;
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 1rem;
  background: #fff;
}

.session-time .session-date {
  font-weight: 700;
  color: #2d3748;
}
.session-time .session-hours {
  color: #4a5568;
  font-size: .95rem;
  margin-top: .25rem;
}

.session-meta .meta-line { margin-bottom: .25rem; }
.pill {
  display: inline-block;
  background: #eefbf1;
  color: #2e7d32;
  padding: .25rem .5rem;
  border-radius: 999px;
  font-size: .8rem;
  font-weight: 600;
  margin-right: .35rem;
}
.weak { color: #718096; }

.clickable { cursor: pointer; }
.clickable:hover { background: #f0fff4; }

@media (max-width: 640px) {
  .session-item { grid-template-columns: 1fr; }
}

.session-date {
  font-weight: 700;
  color: #2d3748;
}

.parent-name {
  font-weight: 600;
  color: #2d3748;
}

.parent-contact {
  font-size: 0.85rem;
  color: #4a5568;
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.number-badge.unpaid-badge {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #b91c1c;
}

.number-badge.unpaid-badge.unpaid-high {
  background: linear-gradient(135deg, #fecaca, #fca5a5);
  color: #7f1d1d;
}

/* --- NOUVEAUX STYLES POUR LE TABLEAU DES PAIEMENTS --- */

/* Bandeau de résumé sous le titre */
.unpaid-summary-banner {
  display: flex;
  gap: 2rem;
  padding: 1rem 2rem;
  background: #f8fafc;
  border-bottom: 1px solid #edf2f7;
  flex-wrap: wrap;
}

.summary-item {
  display: flex;
  flex-direction: column;
}

.summary-item .lbl {
  font-size: 0.75rem;
  text-transform: uppercase;
  color: #718096;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.summary-item .val {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2d3748;
}

.summary-item .val.success { color: #059669; }
.summary-item .val.danger { color: #e53e3e; }


/* Styles des cellules */
.bg-gray-50 {
  background-color: #f8fafc; /* Légère teinte pour séparer les services */
}

.th-center, .td-center {
  text-align: center;
}

.th-sub {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

/* Cellule de contact */
.contact-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #4a5568;
  margin-bottom: 0.15rem;
}
.contact-row .icon { opacity: 0.7; }

/* Cellule Service (Arabe / Soutien) */
.service-detail {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.service-detail.inactive {
  opacity: 0.4;
}

.badge-count {
  background-color: #e2e8f0;
  color: #4a5568;
  font-size: 0.75rem;
  padding: 0.15rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
  white-space: nowrap;
}

.badge-count.soutien {
  background-color: #e0f2fe; /* Bleu très clair pour soutien */
  color: #0369a1;
}

.service-price {
  font-weight: 700;
  color: #2d3748;
  font-size: 0.95rem;
}

.dash {
  color: #cbd5e0;
  font-weight: bold;
}

.font-bold {
  font-weight: 700;
  color: #2d3748;
}

/* Badges Monétaires */
.number-badge.paid-badge {
  background-color: #ecfdf5;
  color: #047857; /* Vert foncé */
}

.number-badge.unpaid-badge {
  background-color: #fff1f2;
  color: #be123c; /* Rouge */
  box-shadow: 0 0 0 1px rgba(190, 18, 60, 0.1);
}

.number-badge.unpaid-high {
  background-color: #ffe4e6;
  color: #9f1239;
  font-weight: 800;
  box-shadow: 0 0 0 1px rgba(159, 18, 57, 0.2);
}

.price-split {
  margin-top: 0.5rem;
  font-size: 0.85rem;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  align-items: center;
}

.split-row {
  display: flex;
  justify-content: space-between;
  width: 100%;
  min-width: 80px;
  gap: 0.5rem;
}

.split-row .lbl {
  color: #718096;
  font-size: 0.75rem;
}

.split-row .val {
  font-weight: 600;
  color: #2d3748;
}

.split-row.success .val {
  color: #059669; /* Vert pour l'argent reçu */
}

.split-row.success .lbl {
  color: #059669;
  opacity: 0.8;
}
</style>