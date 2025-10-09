<template>
  <div class="mobile-container mt-5" lang="fr">
    <!-- En-tête fixe -->
    <div class="header-sticky">
      <div class="header-content">
        <div>
          <h1 class="page-title">Mes sessions</h1>
          <p class="session-count" v-if="!isLoading">
            {{ allSessionsSorted.length }} session{{ allSessionsSorted.length > 1 ? 's' : '' }}
          </p>
        </div>
        <button class="btn-refresh" @click="refresh" :disabled="isLoading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': isLoading }"></i>
        </button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="content-wrapper">
      <!-- État de chargement -->
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Chargement des sessions…</p>
      </div>

      <!-- État vide -->
      <div v-else-if="allSessionsSorted.length === 0" class="empty-state">
        <i class="fas fa-calendar-check"></i>
        <h2>Aucune session</h2>
        <p>Il n'y a pas de sessions à afficher</p>
      </div>

      <!-- Liste des sessions -->
      <div v-else class="sessions-list">
        <div
            v-for="s in allSessionsSorted"
            :key="s.id"
            class="session-card"
            :class="{ 'is-current': getSessionStatus(s) === 'En cours' }"
        >
          <!-- Badge statut -->
          <div class="status-badge" :class="badgeClass(getSessionStatus(s))">
            <span class="status-dot"></span>
            {{ getSessionStatus(s) }}
          </div>

          <!-- Horaires principaux -->
          <div class="time-block">
            <div class="time-display">
              <i class="far fa-clock"></i>
              <span class="time-range">
                {{ formatTimeLocal(s.startTime) }} - {{ formatTimeLocal(s.endTime) }}
              </span>
            </div>
            <div v-if="s.durationHours != null" class="duration">
              {{ formatDuration(s.durationHours) }}
            </div>
          </div>

          <!-- Date de la session -->
          <div class="session-date">
            <i class="fas fa-calendar-alt"></i>
            {{ formatDateLocal(s.startTime) }}
          </div>

          <!-- Informations de la classe -->
          <div class="class-info">
            <div class="class-name">
              <i class="fas fa-graduation-cap"></i>
              {{ s.studyClass?.name || 'Classe inconnue' }}
            </div>
            <div v-if="s.studyClass?.speciality" class="speciality">
              {{ s.studyClass.speciality }}
            </div>
          </div>

          <!-- Détails secondaires -->
          <div class="details-grid">
            <div class="detail-item">
              <i class="fas fa-door-open"></i>
              <span>{{ s.room?.name || 'Salle non définie' }}</span>
            </div>
            <div class="detail-item">
              <i class="fas fa-user-tie"></i>
              <span>{{ s.teacher?.firstName }} {{ s.teacher?.lastName }}</span>
            </div>
          </div>

          <!-- Action unique -->
          <a
              :href="$routing.generate('app_session_show', { id: s.id })"
              class="btn-action btn-primary"
          >
            <i class="fas fa-clipboard-check"></i>
            <span>Gérer les présences</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Alerte -->
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="alert-floating" />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

const PARIS_TZ = "Europe/Paris";

export default {
  name: "SessionTodayMobile",
  components: { Alert },
  data() {
    return {
      isLoading: false,
      allSessions: [],
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    allSessionsSorted() {
      return [...this.allSessions]
          .map(s => ({
            ...s,
            durationHours: this.diffHours(s.startTime, s.endTime),
          }))
          .sort((a, b) => {
            const da = this.parseLocalDateTime(a.startTime)?.getTime() ?? 0;
            const db = this.parseLocalDateTime(b.startTime)?.getTime() ?? 0;
            return da - db;
          });
    },
  },
  methods: {
    async refresh() {
      await this.fetchSessions();
    },

    async fetchSessions() {
      this.isLoading = true;
      this.messageAlert = null;
      try {
        const res = await this.axios.get(this.$routing.generate("api_sessions_today"));
        this.allSessions = res?.data?.sessions || [];
      } catch (e) {
        console.error(e);
        this.messageAlert = "Erreur lors du chargement des sessions.";
        this.typeAlert = "danger";
      } finally {
        this.isLoading = false;
      }
    },

    parseLocalDateTime(isoString) {
      if (!isoString) return null;
      // Parse comme heure locale sans conversion timezone
      const s = String(isoString).replace(/[zZ]$/, '');
      const d = new Date(s);
      return isNaN(d.getTime()) ? null : d;
    },

    formatTimeLocal(datetime) {
      const d = this.parseLocalDateTime(datetime);
      if (!d) return "—";
      // Affiche l'heure exacte sans conversion
      const hours = String(d.getHours()).padStart(2, '0');
      const minutes = String(d.getMinutes()).padStart(2, '0');
      return `${hours}:${minutes}`;
    },

    formatDateLocal(datetime) {
      const d = this.parseLocalDateTime(datetime);
      if (!d) return "—";
      const options = {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
      };
      return d.toLocaleDateString("fr-FR", options);
    },

    diffHours(start, end) {
      const ds = this.parseLocalDateTime(start);
      const de = this.parseLocalDateTime(end);
      if (!ds || !de) return null;
      return (de - ds) / (1000 * 60 * 60);
    },

    formatDuration(hoursFloat) {
      if (hoursFloat == null) return "—";
      const hours = Math.floor(hoursFloat);
      const minutes = Math.round((hoursFloat - hours) * 60);
      const h = hours > 0 ? `${hours}h` : "";
      const m = minutes > 0 ? `${minutes}min` : "";
      return (h + (h && m ? " " : "") + m) || "0min";
    },

    getSessionStatus(session) {
      const now = new Date();
      const start = this.parseLocalDateTime(session.startTime);
      const end = this.parseLocalDateTime(session.endTime);
      if (!start) return "N/A";
      if (now < start) return "À venir";
      if (end && now > end) return "Terminée";
      return "En cours";
    },

    badgeClass(status) {
      switch (status) {
        case "À venir": return "badge-upcoming";
        case "En cours": return "badge-ongoing";
        case "Terminée": return "badge-done";
        default: return "badge-default";
      }
    },
  },
  mounted() {
    this.fetchSessions();
  },
};
</script>

<style scoped>
/* === MOBILE FIRST DESIGN === */
.mobile-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
  padding-bottom: 2rem;
}

/* En-tête fixe */
.header-sticky {
  position: sticky;
  top: 0;
  z-index: 100;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 1.25rem 1rem 1.5rem;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  max-width: 680px;
  margin: 0 auto;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.25rem 0;
  letter-spacing: -0.02em;
}

.session-count {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
  font-weight: 500;
}

.btn-refresh {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-refresh:active {
  transform: scale(0.95);
  background: rgba(255, 255, 255, 0.3);
}

.btn-refresh:disabled {
  opacity: 0.6;
}

/* Contenu */
.content-wrapper {
  max-width: 680px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* États loading et vide */
.loading-state, .empty-state {
  text-align: center;
  padding: 4rem 1.5rem;
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

.loading-state p, .empty-state p {
  color: #64748b;
  font-size: 1rem;
  margin: 0.5rem 0 0;
}

.empty-state i {
  font-size: 4rem;
  color: #cbd5e1;
  margin-bottom: 1rem;
}

.empty-state h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #475569;
  margin: 0 0 0.5rem;
}

/* Liste des sessions */
.sessions-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding-top: 1.5rem;
}

/* Carte de session - PLUS GRANDE */
.session-card {
  background: white;
  border-radius: 24px;
  padding: 1.75rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 3px solid transparent;
  position: relative;
  overflow: hidden;
}

.session-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 6px;
  height: 100%;
  background: #cbd5e1;
  transition: all 0.3s;
}

.session-card.is-current {
  border-color: #10b981;
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.25), 0 0 0 4px rgba(16, 185, 129, 0.12);
  transform: scale(1.02);
}

.session-card.is-current::before {
  background: linear-gradient(180deg, #10b981, #059669);
  width: 8px;
}

/* Badge statut - PLUS GRAND */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.65rem 1.25rem;
  border-radius: 100px;
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
  letter-spacing: 0.01em;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

.badge-upcoming {
  background: #dbeafe;
  color: #1e40af;
}

.badge-upcoming .status-dot {
  background: #3b82f6;
}

.badge-ongoing {
  background: #d1fae5;
  color: #065f46;
}

.badge-ongoing .status-dot {
  background: #10b981;
}

.badge-done {
  background: #f1f5f9;
  color: #475569;
}

.badge-done .status-dot {
  background: #94a3b8;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

/* Bloc horaire - PLUS GRAND */
.time-block {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 16px;
}

.time-display {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.time-display i {
  font-size: 1.5rem;
  color: #667eea;
}

.time-range {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1e293b;
  letter-spacing: -0.01em;
}

.duration {
  font-size: 1rem;
  font-weight: 600;
  color: #64748b;
  background: white;
  padding: 0.5rem 0.9rem;
  border-radius: 10px;
}

/* Date de la session */
.session-date {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-size: 1rem;
  font-weight: 500;
  color: #475569;
  margin-bottom: 1.25rem;
  padding: 0.75rem 1rem;
  background: #fef3c7;
  border-radius: 12px;
}

.session-date i {
  color: #f59e0b;
  font-size: 1.1rem;
}

/* Informations classe - PLUS GRANDE */
.class-info {
  margin-bottom: 1.25rem;
}

.class-name {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.3rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.class-name i {
  color: #667eea;
  font-size: 1.2rem;
}

.speciality {
  font-size: 1.05rem;
  color: #64748b;
  font-weight: 500;
  padding-left: 2rem;
}

/* Grille de détails - PLUS GRANDE */
.details-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.85rem;
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.05rem;
  color: #64748b;
  padding: 0.85rem 1rem;
  background: #f8fafc;
  border-radius: 12px;
}

.detail-item i {
  color: #94a3b8;
  font-size: 1.15rem;
  flex-shrink: 0;
}

.detail-item span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-weight: 500;
}

/* Bouton action - TRÈS GRAND */
.btn-action {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  width: 100%;
  height: 60px;
  border-radius: 16px;
  font-size: 1.1rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
  border: none;
  letter-spacing: 0.01em;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.35);
}

.btn-primary:active {
  transform: translateY(2px);
  box-shadow: 0 3px 8px rgba(102, 126, 234, 0.35);
}

/* Alerte flottante */
.alert-floating {
  position: fixed;
  bottom: 1rem;
  left: 1rem;
  right: 1rem;
  z-index: 200;
  max-width: 680px;
  margin: 0 auto;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

/* Responsive */
@media (min-width: 480px) {
  .details-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 640px) {
  .mobile-container {
    padding-bottom: 3rem;
  }

  .sessions-list {
    gap: 1.5rem;
    padding-top: 2rem;
  }

  .session-card {
    padding: 2rem;
  }
}

/* Petits écrans */
@media (max-width: 380px) {
  .page-title {
    font-size: 1.6rem;
  }

  .time-range {
    font-size: 1.25rem;
  }

  .class-name {
    font-size: 1.2rem;
  }

  .btn-action {
    font-size: 1rem;
    height: 56px;
  }
}
</style>