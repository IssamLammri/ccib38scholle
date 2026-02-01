<template>
  <div class="edit-session-container">
    <!-- Header -->
    <div class="page-header">
      <div class="header-left">
        <button class="btn-back" type="button" @click="goBack">←</button>
        <div>
          <h1 class="title">✏️ Modifier la session</h1>
          <p class="subtitle" v-if="session">
            #{{ session.id }} — {{ session?.studyClass?.name || 'Cours' }}
          </p>
        </div>
      </div>

      <div class="header-right">
        <span class="pill" v-if="session">
          {{ formatDate(session.startTime) }}
        </span>
      </div>
    </div>

    <Alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mt-3" />

    <!-- Loading -->
    <div v-if="loading" class="loading-card">
      <div class="spinner"></div>
      <div>Chargement…</div>
    </div>

    <div v-else>
      <!-- Résumé -->
      <div class="card">
        <div class="card-title">📌 Résumé</div>

        <div class="summary-grid">
          <div class="summary-item">
            <div class="lbl">Cours</div>
            <div class="val">{{ session?.studyClass?.name || '-' }}</div>
          </div>

          <div class="summary-item">
            <div class="lbl">Prof actuel</div>
            <div class="val">
              {{ fullTeacherName(session?.teacher) }}
            </div>
          </div>

          <div class="summary-item">
            <div class="lbl">Salle</div>
            <div class="val">
              {{ session?.room?.name || '—' }}
            </div>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form class="card" @submit.prevent="save">
        <div class="card-title">🧾 Informations</div>

        <!-- Date -->
        <div class="field">
          <label class="label">📅 Date</label>
          <input v-model="form.date" type="date" class="input" />
        </div>

        <!-- Heures -->
        <div class="two-cols">
          <div class="field">
            <label class="label">⏱️ Début</label>
            <input v-model="form.start" type="time" class="input" />
          </div>
          <div class="field">
            <label class="label">⏱️ Fin</label>
            <input v-model="form.end" type="time" class="input" />
          </div>
        </div>

        <div v-if="timeError" class="inline-error">
          ⚠️ L’heure de fin doit être après l’heure de début.
        </div>

        <!-- Prof -->
        <div class="field">
          <label class="label">👨‍🏫 Professeur</label>
          <select v-model.number="form.teacherId" class="input">
            <option :value="null">— Choisir —</option>
            <option v-for="t in teachers" :key="t.id" :value="t.id">
              {{ t.firstName }} {{ t.lastName }}
            </option>
          </select>
        </div>

        <!-- Salle -->
        <div class="field">
          <label class="label">🏫 Salle</label>
          <select v-model.number="form.roomId" class="input">
            <option :value="null">Aucune</option>
            <option v-for="r in rooms" :key="r.id" :value="r.id">
              {{ r.name }}
            </option>
          </select>
          <div class="hint">Tu peux laisser “Aucune” si la session n’a pas de salle.</div>
        </div>

        <!-- Actions -->
        <div class="actions">
          <button type="button" class="btn-secondary" :disabled="saving" @click="resetToInitial">
            Réinitialiser
          </button>

          <button type="submit" class="btn-primary" :disabled="saving || timeError">
            <span v-if="saving">Enregistrement…</span>
            <span v-else>✅ Enregistrer</span>
          </button>
        </div>
      </form>

      <!-- Danger zone (optionnel) -->
      <div class="card danger" v-if="canDelete">
        <div class="card-title">🗑️ Zone sensible</div>
        <p class="danger-text">
          Supprimer une session est irréversible.
        </p>
        <button type="button" class="btn-danger" @click="askDelete" :disabled="saving">
          Supprimer la session
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "SessionEdit",
  components: { Alert },
  props: {
    session: { type: Object, required: false },
    currentUser: { type: Object, required: false },

    // Optionnel : si tu veux passer les listes depuis le parent
    teachersProp: { type: Array, required: false, default: () => [] },
    roomsProp: { type: Array, required: false, default: () => [] },
  },
  data() {
    return {
      loading: false,
      saving: false,

      messageAlert: null,
      typeAlert: null,

      teachers: [],
      rooms: [],

      initialForm: null,
      form: {
        date: "",
        start: "",
        end: "",
        teacherId: null,
        roomId: null,
      },
    };
  },

  computed: {
    roles() {
      return Array.isArray(this.currentUser?.roles) ? this.currentUser.roles : [];
    },
    canDelete() {
      return this.roles.includes("ROLE_ADMIN") || this.roles.includes("ROLE_MANAGER");
    },
    timeError() {
      if (!this.form.date || !this.form.start || !this.form.end) return false;
      const start = new Date(`${this.form.date}T${this.form.start}:00`);
      const end   = new Date(`${this.form.date}T${this.form.end}:00`);
      return end <= start;
    },
  },

  created() {
    this.initFromSession();
  },

  mounted() {
    this.loadOptionsIfNeeded();
  },

  watch: {
    session: {
      deep: true,
      handler() {
        this.initFromSession();
      }
    }
  },

  methods: {
    goBack() {
      // si tu as vue-router
      if (this.$router) return this.$router.back();
      // sinon fallback
      window.history.back();
    },

    fullTeacherName(t) {
      if (!t) return "—";
      const fn = t.firstName || "";
      const ln = t.lastName || "";
      return `${fn} ${ln}`.trim() || "—";
    },

    initFromSession() {
      if (!this.session) return;

      const date = this.toDateInput(this.session.startTime);
      const start = this.toTimeInput(this.session.startTime);
      const end = this.toTimeInput(this.session.endTime);

      this.form = {
        date,
        start,
        end,
        teacherId: this.session?.teacher?.id ?? null,
        roomId: this.session?.room?.id ?? null,
      };

      this.initialForm = JSON.parse(JSON.stringify(this.form));

      // options props si fournies
      if (this.teachersProp?.length) this.teachers = this.teachersProp;
      if (this.roomsProp?.length) this.rooms = this.roomsProp;
    },

    resetToInitial() {
      if (!this.initialForm) return;
      this.form = JSON.parse(JSON.stringify(this.initialForm));
    },

    async loadOptionsIfNeeded() {
      // si déjà passés via props -> nothing
      if (this.teachers.length || this.rooms.length) return;

      // sinon on appelle une API (route à adapter)
      this.loading = true;
      try {
        const url = this.$routing.generate("api_session_edit_options");
        const { data } = await this.$axios.get(url);

        // Attendu:
        // data.teachers: [{id, firstName, lastName}]
        // data.rooms: [{id, name}]
        this.teachers = data.teachers || [];
        this.rooms = data.rooms || [];
      } catch (e) {
        this.messageAlert = "Impossible de charger les professeurs / salles.";
        this.typeAlert = "danger";
      } finally {
        this.loading = false;
      }
    },

    async save() {
      if (!this.session?.id) return;
      if (this.timeError) return;

      this.saving = true;
      this.messageAlert = null;
      this.typeAlert = null;

      try {
        const payload = {
          startTime: this.toIsoLike(this.form.date, this.form.start),
          endTime: this.toIsoLike(this.form.date, this.form.end),
          teacherId: this.form.teacherId,
          roomId: this.form.roomId, // null autorisé
        };

        const url = this.$routing.generate("api_session_update", { id: this.session.id });

        // PUT ou POST selon ton backend
        await this.$axios.put(url, payload);

        this.messageAlert = "Session mise à jour ✅";
        this.typeAlert = "success";
        this.initialForm = JSON.parse(JSON.stringify(this.form));
        setTimeout(() => (this.messageAlert = null), 2500);

        // Optionnel : avertir le parent
        this.$emit("updated", payload);
      } catch (e) {
        this.messageAlert = "Erreur lors de la mise à jour de la session.";
        this.typeAlert = "danger";
      } finally {
        this.saving = false;
      }
    },

    async askDelete() {
      if (!this.session?.id) return;
      if (!confirm("Supprimer définitivement cette session ?")) return;

      this.saving = true;
      this.messageAlert = null;
      this.typeAlert = null;

      try {
        const url = this.$routing.generate("session_delete_api", { id: this.session.id });
        await this.$axios.delete(url);

        this.messageAlert = "Session supprimée ✅";
        this.typeAlert = "success";
        this.$emit("deleted", this.session.id);

        setTimeout(() => this.goBack(), 600);
      } catch (e) {
        this.messageAlert = "Erreur lors de la suppression.";
        this.typeAlert = "danger";
      } finally {
        this.saving = false;
      }
    },

    // --- Helpers date/time ---
    toDateInput(iso) {
      // iso: "2026-02-01T10:00:00+01:00" ou "...Z"
      if (!iso) return "";
      const s = String(iso);
      // garde la partie YYYY-MM-DD
      const m = s.match(/^(\d{4}-\d{2}-\d{2})/);
      return m ? m[1] : "";
    },

    toTimeInput(iso) {
      if (!iso) return "";
      const s = String(iso);
      const m = s.match(/T(\d{2}):(\d{2})/);
      return m ? `${m[1]}:${m[2]}` : "";
    },

    toIsoLike(date, time) {
      // IMPORTANT : ton backend attend du datetime immutable.
      // Ici on envoie un ISO sans timezone explicite.
      // Si tu veux forcer +01:00 : `${date}T${time}:00+01:00`
      if (!date || !time) return null;
      return `${date}T${time}:00`;
    },

    formatDate(iso) {
      if (!iso) return "";
      const d = new Date(iso);
      const dd = String(d.getDate()).padStart(2, "0");
      const mm = String(d.getMonth() + 1).padStart(2, "0");
      const yyyy = d.getFullYear();
      return `${dd}/${mm}/${yyyy}`;
    },
  },
};
</script>

<style scoped>
* { box-sizing: border-box; }

.edit-session-container {
  min-height: 100vh;
  background: #f5f7fa;
  padding: 1rem;
  padding-bottom: 2rem;
  font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

/* Header */
.page-header {
  background: #ffffff;
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
  gap: 1rem;
}

.header-left {
  display: flex;
  gap: .75rem;
  align-items: flex-start;
}

.btn-back {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: none;
  background: #f1f5f9;
  cursor: pointer;
  font-size: 1.2rem;
}

.title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 800;
  color: #0f172a;
}
.subtitle {
  margin: .2rem 0 0 0;
  color: #64748b;
  font-weight: 600;
  font-size: .95rem;
}

.pill {
  background: #eef2ff;
  color: #3730a3;
  padding: .45rem .65rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: .85rem;
  white-space: nowrap;
}

/* Cards */
.card {
  margin-top: 1rem;
  background: #fff;
  border-radius: 16px;
  padding: 1rem;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
}

.card-title {
  font-weight: 800;
  color: #0f172a;
  margin-bottom: .75rem;
}

.summary-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: .75rem;
}
.summary-item .lbl {
  color: #64748b;
  font-size: .8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.summary-item .val {
  margin-top: .15rem;
  font-size: 1.05rem;
  font-weight: 800;
  color: #0f172a;
}

/* Form */
.field { margin-bottom: .9rem; }

.label {
  display: block;
  margin-bottom: .35rem;
  color: #334155;
  font-weight: 800;
  font-size: .9rem;
}

.input {
  width: 100%;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: .75rem .9rem;
  font-size: 1rem;
  background: white;
}
.input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99,102,241,.12);
}

.hint {
  margin-top: .35rem;
  color: #64748b;
  font-size: .85rem;
  font-weight: 600;
}

.two-cols {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .75rem;
}

.inline-error {
  margin: -.25rem 0 .9rem 0;
  padding: .75rem;
  border-radius: 12px;
  background: #fff1f2;
  color: #9f1239;
  font-weight: 800;
}

/* Buttons */
.actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .75rem;
  margin-top: .5rem;
}

.btn-primary,
.btn-secondary,
.btn-danger {
  height: 52px;
  border: none;
  border-radius: 14px;
  font-weight: 900;
  cursor: pointer;
  font-size: 1rem;
}

.btn-primary {
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  color: white;
  box-shadow: 0 8px 18px rgba(99,102,241,.25);
}
.btn-secondary {
  background: #f1f5f9;
  color: #0f172a;
}
.btn-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
  width: 100%;
}

.btn-primary:disabled,
.btn-secondary:disabled,
.btn-danger:disabled {
  opacity: .6;
  cursor: not-allowed;
}

/* Loading */
.loading-card {
  margin-top: 1rem;
  background: #fff;
  border-radius: 16px;
  padding: 2rem 1rem;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0,0,0,.08);
  display: grid;
  gap: .75rem;
  justify-items: center;
}

.spinner {
  width: 44px;
  height: 44px;
  border: 4px solid rgba(99,102,241,.15);
  border-top-color: #4f46e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Danger */
.card.danger {
  border: 2px solid #fee2e2;
}
.danger-text {
  margin: 0 0 .75rem 0;
  color: #7f1d1d;
  font-weight: 700;
}

/* Responsive */
@media (max-width: 420px) {
  .two-cols { grid-template-columns: 1fr; }
  .actions { grid-template-columns: 1fr; }
}
</style>
