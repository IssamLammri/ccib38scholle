<template>
  <div class="mobile-container" lang="fr">
    <h1 class="page-title">📊 Présences par étudiant</h1>

    <!-- TOP BAR -->
    <div class="toolbar">
      <div class="toolbar-left">
        <div v-if="!isLoading" class="page-info">
          Page <b>{{ meta.page }}</b> / <b>{{ meta.pages }}</b>
          — Total: <b>{{ meta.total }}</b>
        </div>
        <div v-else class="loading-text">⏳ Chargement…</div>
      </div>

      <select v-model="classType" @change="onClassTypeChange" class="select mt-4">
        <option :value="null">🏷️ Tous les types</option>
        <option v-for="t in classTypes" :key="t" :value="t">{{ t }}</option>
      </select>

      <input
          v-model="search"
          class="search-input mt-4"
          type="text"
          placeholder="🔎 Rechercher nom / prénom..."
          @input="onSearchInput"
      />

      <div class="date-filters">
        <div class="date-input-wrapper">
          <label class="date-label">📅 Du</label>
          <input class="date-input" type="date" v-model="from" @change="goToPage(1)" />
        </div>
        <div class="date-input-wrapper">
          <label class="date-label">📅 Au</label>
          <input class="date-input" type="date" v-model="to" @change="goToPage(1)" />
        </div>
      </div>

      <div class="toolbar-right">
        <!-- Filtre classe -->
        <select
            v-if="enableClassFilter"
            v-model.number="studyClassId"
            @change="goToPage(1)"
            class="select"
        >
          <option :value="null">📚 Toutes les classes</option>
          <option v-for="c in classes" :key="c.id" :value="c.id">
            {{ c.name }} ({{ c.level }})
          </option>
        </select>

        <select v-model.number="limit" @change="goToPage(1)" class="select">
          <option :value="10">10 / page</option>
          <option :value="30">30 / page</option>
          <option :value="50">50 / page</option>
          <option :value="100">100 / page</option>
        </select>
      </div>
    </div>

    <!-- LIST -->
    <div v-if="!isLoading && students.length === 0" class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>Aucun étudiant trouvé</h3>
      <p>Essayez de changer les filtres ou ajoutez des étudiants.</p>
    </div>

    <div v-else-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des données...</p>
    </div>

    <div v-else class="students-grid">
      <div
          v-for="st in students"
          :key="st.studentId + '_' + (st.classType ?? 'all')"
          class="student-card"
          @click="openHistory(st)"
      >
        <!-- En-tête étudiant -->
        <div class="student-header">
          <div class="student-avatar">
            {{ getInitials(st.firstName, st.lastName) }}
          </div>
          <div class="student-info">
            <div class="student-name">
              {{ st.firstName }} {{ st.lastName }}
            </div>
            <div class="student-id">#{{ st.studentId }}</div>
          </div>
        </div>

        <!-- Classe info -->
        <div class="class-info-box">
          <!-- Service -->
          <div class="info-row" v-if="st.classType">
            <span class="info-label">🏷️ Service:</span>
            <span class="info-value">{{ st.classType }}</span>
          </div>

          <div class="info-row">
            <span class="info-label">📚 Classe:</span>
            <span class="info-value">{{ st.className ?? '—' }}</span>
          </div>

          <div class="info-row" v-if="st.classLevel">
            <span class="info-label">🎓 Niveau:</span>
            <span class="info-value">{{ st.classLevel }}</span>
          </div>

          <div class="info-row" v-if="st.level">
            <span class="info-label">📈 Niveau élève:</span>
            <span class="info-value">{{ st.level }}</span>
          </div>
        </div>

        <!-- Stats présences -->
        <div class="stats-container">
          <div class="stat-box stat-present">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-value">{{ num(st.presents) }}</div>
              <div class="stat-label">Présent</div>
            </div>
          </div>

          <div class="stat-box stat-absent">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
              <div class="stat-value">{{ num(st.absents) }}</div>
              <div class="stat-label">Absent</div>
            </div>
          </div>

          <div class="stat-box stat-unmarked">
            <div class="stat-icon">🕓</div>
            <div class="stat-content">
              <div class="stat-value">{{ num(st.notMarked) }}</div>
              <div class="stat-label">Non marqué</div>
            </div>
          </div>
        </div>

        <!-- Taux de présence -->
        <div class="attendance-bar-container">
          <div class="attendance-info">
            <span class="attendance-label">Taux de présence</span>
            <span class="attendance-percentage">{{ attendanceRate(st) }}%</span>
          </div>
          <div class="attendance-bar">
            <div
                class="attendance-fill"
                :style="{ width: attendanceRate(st) + '%' }"
                :class="getAttendanceClass(attendanceRate(st))"
            ></div>
          </div>
        </div>

        <!-- Total sessions -->
        <div class="total-sessions">
          📌 Total sessions: <b>{{ num(st.total) }}</b>
        </div>

        <!-- Click indicator -->
        <div class="click-indicator">
          Cliquer pour voir l'historique →
        </div>
      </div>
    </div>

    <!-- PAGINATION -->
    <div class="pagination" v-if="students.length > 0">
      <button
          class="btn-pagination"
          @click="prevPage"
          :disabled="isLoading || !meta.hasPrev"
      >
        ◀ Précédent
      </button>
      <div class="pagination-info">
        Page {{ meta.page }} / {{ meta.pages }}
      </div>
      <button
          class="btn-pagination"
          @click="nextPage"
          :disabled="isLoading || !meta.hasNext"
      >
        Suivant ▶
      </button>
    </div>

    <!-- MODAL HISTORY -->
    <Transition name="modal">
      <div v-if="history.open" class="modal-backdrop" @click.self="closeHistory">
        <div class="modal-container">
          <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
              <div class="modal-student-info">
                <div class="modal-avatar">
                  {{ getInitials(history.student?.firstName, history.student?.lastName) }}
                </div>
                <div>
                  <div class="modal-title">
                    {{ history.student?.firstName }} {{ history.student?.lastName }}
                  </div>
                  <div class="modal-subtitle">
                    📚 {{ history.student?.className ?? '—' }}
                    <span v-if="history.student?.classLevel">({{ history.student?.classLevel }})</span>
                  </div>
                </div>
              </div>
              <button class="btn-close" @click="closeHistory">✖</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
              <div v-if="history.loading" class="modal-loading">
                <div class="spinner"></div>
                <p>Chargement de l'historique...</p>
              </div>

              <div v-else-if="history.items.length === 0" class="modal-empty">
                <div class="empty-icon">📋</div>
                <h3>Aucun historique</h3>
                <p>Cet étudiant n'a pas encore de sessions enregistrées.</p>
              </div>

              <div v-else class="history-timeline">
                <div
                    v-for="(it, idx) in history.items"
                    :key="it.presenceId ?? (it.sessionId + '_' + it.startTime)"
                    class="history-card"
                >
                  <!-- Timeline dot -->
                  <div class="timeline-dot" :class="getStatusClass(it)"></div>

                  <!-- Card content -->
                  <div class="history-card-content">
                    <div class="history-header">
                      <div class="history-class">
                        📚 <b>{{ it.className ?? history.student?.className ?? '—' }}</b>
                        <span class="history-class-id" v-if="it.studyClassId">
                          (#{{ it.studyClassId }})
                        </span>
                      </div>
                      <div class="status-badge" :class="getBadgeClass(it)">
                        <span v-if="it.isPresent === true">✅ Présent</span>
                        <span v-else-if="it.isPresent === false">❌ Absent</span>
                        <span v-else>🕓 Non renseigné</span>
                      </div>
                    </div>

                    <div class="history-details">
                      <div class="history-time">
                        🕒 {{ formatDate(it.startTime) }} → {{ formatDate(it.endTime) }}
                      </div>
                      <div class="history-teacher" v-if="it.teacherFirstName || it.teacherLastName">
                        👨‍🏫 {{ it.teacherFirstName ?? '' }} {{ it.teacherLastName ?? '' }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Pagination historique -->
              <div class="modal-pagination" v-if="history.items.length > 0">
                <button
                    class="btn-pagination"
                    @click="historyPrev"
                    :disabled="history.loading || !history.meta.hasPrev"
                >
                  ◀ Précédent
                </button>
                <div class="pagination-info">
                  Page {{ history.meta.page }} / {{ history.meta.pages }}
                </div>
                <button
                    class="btn-pagination"
                    @click="historyNext"
                    :disabled="history.loading || !history.meta.hasNext"
                >
                  Suivant ▶
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script>
export default {
  name: "PresencesByStudentComplete",

  data() {
    // Calculer les dates par défaut (dernier mois)
    const today = new Date();
    const oneMonthAgo = new Date(today);
    oneMonthAgo.setMonth(today.getMonth() - 1);

    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    return {
      isLoading: false,
      students: [],
      meta: { page: 1, limit: 30, total: 0, pages: 1, hasNext: false, hasPrev: false },
      page: 1,
      limit: 30,
      from: formatDate(oneMonthAgo), // Dernier mois
      to: formatDate(today),          // Aujourd'hui
      search: "",
      searchTimer: null,
      enableClassFilter: true,
      classType: null,
      classTypes: ["Arabe", "Soutien scolaire", "Autre"],
      classes: [],
      studyClassId: null,
      history: {
        open: false,
        loading: false,
        student: null,
        items: [],
        page: 1,
        limit: 20,
        meta: { page: 1, limit: 20, total: 0, pages: 1, hasNext: false, hasPrev: false },
      },
    };
  },

  methods: {
    async fetchList() {
      this.isLoading = true;
      try {
        const url = this.$routing.generate("api_presences_sessions_by_student");
        const params = {
          page: this.page,
          limit: this.limit,
        };
        if (this.enableClassFilter && this.studyClassId) {
          params.studyClassId = this.studyClassId;
        }
        if (this.classType) {
          params.classType = this.classType;
        }
        if (this.search && this.search.trim()) {
          params.q = this.search.trim();
        }
        if (this.from) params.from = this.from;
        if (this.to) params.to = this.to;

        const res = await this.axios.get(url, { params });
        const payload = res?.data || {};
        this.students = payload?.students ?? [];
        this.meta = payload?.meta ?? this.meta;
      } finally {
        this.isLoading = false;
      }
    },

    goToPage(p) {
      this.page = p;
      this.fetchList();
    },

    nextPage() {
      if (this.meta.hasNext) this.goToPage(this.page + 1);
    },

    prevPage() {
      if (this.meta.hasPrev) this.goToPage(this.page - 1);
    },

    async openHistory(st) {
      this.history.open = true;
      this.history.student = { ...st };
      this.history.page = 1;
      this.history.items = [];
      await this.fetchHistory();
    },

    closeHistory() {
      this.history.open = false;
      this.history.student = null;
      this.history.items = [];
      this.history.page = 1;
      this.history.meta = { page: 1, limit: this.history.limit, total: 0, pages: 1, hasNext: false, hasPrev: false };
    },

    async fetchHistory() {
      if (!this.history.student?.studentId) return;

      this.history.loading = true;
      try {
        const url = this.$routing.generate("api_presences_student_history", {
          studentId: this.history.student.studentId,
        });

        const params = {
          page: this.history.page,
          limit: this.history.limit,
        };

        if (this.history.student?.classType) {
          params.classType = this.history.student.classType;
        }

        if (this.from) params.from = this.from;
        if (this.to) params.to = this.to;

        const res = await this.axios.get(url, { params });
        const payload = res?.data || {};

        this.history.items = payload?.items ?? [];
        this.history.meta = payload?.meta ?? this.history.meta;
      } finally {
        this.history.loading = false;
      }
    },

    historyNext() {
      if (this.history.meta.hasNext) {
        this.history.page++;
        this.fetchHistory();
      }
    },

    historyPrev() {
      if (this.history.meta.hasPrev) {
        this.history.page--;
        this.fetchHistory();
      }
    },

    async fetchClasses() {
      if (!this.enableClassFilter) return;
      try {
        const url = this.$routing.generate("api_studyclass_index");
        const params = {};
        params.schoolYear = "2025/2026";
        if (this.classType) params.classType = this.classType;

        const res = await this.axios.get(url, { params });
        const payload = res?.data || {};
        this.classes = payload?.items ?? payload ?? [];
      } catch (e) {
        this.classes = [];
      }
    },

    num(v) {
      const n = Number(v);
      return Number.isFinite(n) ? n : 0;
    },

    onClassTypeChange() {
      this.studyClassId = null;
      this.goToPage(1);
      this.fetchClasses();
    },

    onSearchInput() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => {
        this.goToPage(1);
      }, 350);
    },

    attendanceRate(st) {
      const presents = this.num(st.presents);
      const absents = this.num(st.absents);
      const totalMarked = presents + absents;
      if (totalMarked === 0) return 0;
      return Math.round((presents / totalMarked) * 100);
    },

    getAttendanceClass(rate) {
      if (rate >= 90) return 'excellent';
      if (rate >= 75) return 'good';
      if (rate >= 50) return 'average';
      return 'low';
    },

    getInitials(firstName, lastName) {
      const f = (firstName || '').trim()[0] || '';
      const l = (lastName || '').trim()[0] || '';
      return (f + l).toUpperCase() || '?';
    },

    formatDate(dt) {
      if (!dt) return "—";
      const d = new Date(dt);
      if (Number.isNaN(d.getTime())) return String(dt);
      return d.toLocaleString("fr-FR", {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    getBadgeClass(it) {
      if (it.isPresent === true) return "badge-present";
      if (it.isPresent === false) return "badge-absent";
      return "badge-null";
    },

    getStatusClass(it) {
      if (it.isPresent === true) return "status-present";
      if (it.isPresent === false) return "status-absent";
      return "status-null";
    },
  },

  async mounted() {
    await this.fetchClasses();
    await this.fetchList();
  },
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.mobile-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
  padding: 1.5rem 1rem 3rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1e293b;
  margin: 0 0 1.5rem 0;
  letter-spacing: -0.02em;
}

/* Barre d'outils améliorée */
.toolbar {
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  flex-wrap: wrap;
  padding: 1.5rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.toolbar-left {
  flex: 1;
  min-width: 200px;
}

.page-info {
  font-size: 0.95rem;
  color: #475569;
}

.loading-text {
  font-size: 0.95rem;
  color: #94a3b8;
}

.toolbar-right {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.select {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: #fff;
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 150px;
}

.select:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

/* Champ de recherche stylisé */
.search-input {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: #fff;
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
  transition: all 0.2s;
  min-width: 300px;
  flex: 1;
}

.search-input:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.search-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

.search-input::placeholder {
  color: #94a3b8;
}

/* Filtres de dates stylisés */
.date-filters {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.date-input-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.date-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding-left: 0.25rem;
}

.date-input {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: #fff;
  font-size: 0.9rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 160px;
  font-family: inherit;
}

.date-input:hover {
  border-color: #667eea;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.date-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
}

/* Style du calendrier pour Chrome/Edge/Safari */
.date-input::-webkit-calendar-picker-indicator {
  cursor: pointer;
  filter: opacity(0.6);
  transition: filter 0.2s;
}

.date-input:hover::-webkit-calendar-picker-indicator {
  filter: opacity(1);
}

/* LOADING & EMPTY STATES */
.loading-state,
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(102, 126, 234, 0.2);
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #475569;
  margin: 0 0 0.5rem;
}

.empty-state p {
  color: #94a3b8;
  font-size: 0.95rem;
  margin: 0;
}

/* STUDENTS GRID */
.students-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.student-card {
  background: white;
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  border: 2px solid transparent;
}

.student-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
  border-color: #667eea;
}

.student-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.25rem;
  padding-bottom: 1.25rem;
  border-bottom: 2px solid #f1f5f9;
}

.student-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.student-info {
  flex: 1;
  min-width: 0;
}

.student-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.student-id {
  font-size: 0.85rem;
  color: #94a3b8;
  font-weight: 500;
}

.class-info-box {
  background: #f8fafc;
  border-radius: 12px;
  padding: 0.875rem;
  margin-bottom: 1.25rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.375rem 0;
}

.info-row:not(:last-child) {
  border-bottom: 1px solid #e2e8f0;
}

.info-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.info-value {
  font-size: 0.875rem;
  color: #1e293b;
  font-weight: 600;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.stat-box {
  background: #f8fafc;
  border-radius: 12px;
  padding: 0.875rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  transition: all 0.2s;
}

.stat-box:hover {
  transform: scale(1.05);
}

.stat-icon {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-present {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
}

.stat-absent {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
}

.stat-unmarked {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.attendance-bar-container {
  margin-bottom: 1rem;
}

.attendance-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.attendance-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 600;
}

.attendance-percentage {
  font-size: 1.125rem;
  font-weight: 800;
  color: #1e293b;
}

.attendance-bar {
  height: 12px;
  background: #e2e8f0;
  border-radius: 999px;
  overflow: hidden;
}

.attendance-fill {
  height: 100%;
  transition: width 0.3s ease, background 0.3s ease;
  border-radius: 999px;
}

.attendance-fill.excellent {
  background: linear-gradient(90deg, #10b981, #059669);
}

.attendance-fill.good {
  background: linear-gradient(90deg, #3b82f6, #2563eb);
}

.attendance-fill.average {
  background: linear-gradient(90deg, #f59e0b, #d97706);
}

.attendance-fill.low {
  background: linear-gradient(90deg, #ef4444, #dc2626);
}

.total-sessions {
  font-size: 0.9rem;
  color: #475569;
  padding: 0.75rem;
  background: #fef3c7;
  border-radius: 10px;
  text-align: center;
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.click-indicator {
  text-align: center;
  font-size: 0.8rem;
  color: #94a3b8;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* PAGINATION */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin: 2rem 0;
}

.btn-pagination {
  padding: 0.75rem 1.5rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  background: white;
  font-size: 0.95rem;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-pagination:hover:not(:disabled) {
  background: #667eea;
  color: white;
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-pagination:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination-info {
  font-size: 0.95rem;
  color: #475569;
  font-weight: 600;
  min-width: 120px;
  text-align: center;
}

/* MODAL */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 9999;
  overflow-y: auto;
}

.modal-container {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-content {
  background: white;
  border-radius: 24px;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}

.modal-header {
  padding: 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.modal-student-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.modal-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: white;
  margin-bottom: 0.25rem;
}

.modal-subtitle {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 500;
}

.btn-close {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.modal-loading,
.modal-empty {
  text-align: center;
  padding: 3rem 1.5rem;
}

.modal-empty h3 {
  font-size: 1.125rem;
  font-weight: 700;
  color: #475569;
  margin: 0 0 0.5rem;
}

.modal-empty p {
  color: #94a3b8;
  font-size: 0.9rem;
  margin: 0;
}

/* HISTORY TIMELINE */
.history-timeline {
  position: relative;
}

.history-card {
  position: relative;
  padding-left: 2.5rem;
  margin-bottom: 1.5rem;
}

.history-card:last-child {
  margin-bottom: 0;
}

.timeline-dot {
  position: absolute;
  left: 0;
  top: 0.5rem;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.timeline-dot.status-present {
  background: #10b981;
}

.timeline-dot.status-absent {
  background: #ef4444;
}

.timeline-dot.status-null {
  background: #94a3b8;
}

.history-card:not(:last-child) .timeline-dot::after {
  content: '';
  position: absolute;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: calc(100% + 1.5rem);
  background: linear-gradient(180deg, #e2e8f0 0%, transparent 100%);
}

.history-card-content {
  background: #f8fafc;
  border-radius: 16px;
  padding: 1.25rem;
  border: 2px solid #e2e8f0;
  transition: all 0.2s;
}

.history-card-content:hover {
  background: white;
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.history-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 0.875rem;
}

.history-class {
  font-size: 1rem;
  color: #1e293b;
  font-weight: 600;
  flex: 1;
  min-width: 0;
}

.history-class-id {
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 500;
}

.status-badge {
  padding: 0.5rem 0.875rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
  white-space: nowrap;
  flex-shrink: 0;
}

.badge-present {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
}

.badge-absent {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
}

.badge-null {
  background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
  color: #475569;
}

.history-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.history-time,
.history-teacher {
  font-size: 0.9rem;
  color: #64748b;
  font-weight: 500;
}

.modal-pagination {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f1f5f9;
}

/* TRANSITIONS */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .modal-content,
.modal-leave-active .modal-content {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.9) translateY(20px);
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .students-grid {
    grid-template-columns: 1fr;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .toolbar-left,
  .toolbar-right {
    width: 100%;
  }

  .toolbar-right {
    flex-direction: column;
  }

  .select,
  .search-input {
    width: 100%;
    min-width: unset;
  }

  .date-filters {
    flex-direction: column;
    width: 100%;
  }

  .date-input-wrapper {
    width: 100%;
  }

  .date-input {
    width: 100%;
    min-width: unset;
  }

  .stats-container {
    grid-template-columns: 1fr;
  }

  .pagination {
    flex-wrap: wrap;
  }

  .modal-container {
    max-width: 100%;
  }

  .modal-content {
    border-radius: 16px 16px 0 0;
  }

  .history-card {
    padding-left: 2rem;
  }

  .timeline-dot {
    width: 12px;
    height: 12px;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .students-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1025px) {
  .students-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .page-title {
    font-size: 2.5rem;
  }
}
</style>