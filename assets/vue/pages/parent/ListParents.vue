<template>
  <div class="parents-page" lang="fr">
    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="typeAlert"
        class="modern-alert"
    />

    <!-- Header -->
    <header class="page-header">
      <div class="title-wrap">
        <h1 class="title">
          <span class="icon">👨‍👩‍👧‍👦</span>
          Parents
        </h1>
        <p class="subtitle">Gestion des contacts et des élèves rattachés</p>
      </div>

      <div class="stats">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-content">
            <span class="stat-value">{{ filteredParents.length }}</span>
            <span class="stat-label">Parents</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🎓</div>
          <div class="stat-content">
            <span class="stat-value">{{ totalStudents(filteredParents) }}</span>
            <span class="stat-label">Élèves</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="toolbar-left">
        <!-- Recherche globale -->
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input
              v-model="q"
              type="text"
              class="search-input"
              placeholder="Rechercher un parent, email, téléphone, élève..."
          />
          <span v-if="q" class="clear-btn" @click="q = ''">✕</span>
        </div>

        <!-- Page size -->
        <select v-model.number="pageSize" class="select-input">
          <option :value="10">10 par page</option>
          <option :value="20">20 par page</option>
          <option :value="50">50 par page</option>
          <option :value="100">100 par page</option>
        </select>
      </div>

      <div class="toolbar-right">
        <button class="btn-icon" @click="resetFilters" :disabled="loading" title="Réinitialiser">
          <span class="btn-icon-emoji">↻</span>
        </button>
        <button class="btn-action" @click="copyEmails" :disabled="!filteredParents.length">
          <span class="btn-emoji">📧</span>
          Emails
        </button>
        <button class="btn-action" @click="exportCsv" :disabled="!filteredParents.length">
          <span class="btn-emoji">📄</span>
          CSV
        </button>
        <a v-if="createUrl" :href="createUrl" class="btn-primary">
          <span class="btn-emoji">➕</span>
          Nouveau parent
        </a>
      </div>
    </div>

    <!-- Filtres avancés -->
    <div class="toolbar" style="margin-top:-1rem">
      <div class="toolbar-left" style="row-gap:.5rem">
        <!-- Classe/Niveau -->
        <select v-model="fLevel" class="select-input">
          <option value="">Tous niveaux / classes</option>
          <option v-for="lv in availableLevels" :key="lv" :value="lv">{{ lv }}</option>
        </select>

        <!-- Type de service (dérivé des niveaux) -->
        <select v-model="fServiceType" class="select-input">
          <option value="">Tous types de service</option>
          <option v-for="t in availableServiceTypes" :key="t" :value="t">{{ t }}</option>
        </select>

        <!-- Ville (des élèves) -->
        <select v-model="fCity" class="select-input">
          <option value="">Toutes les villes</option>
          <option v-for="c in availableCities" :key="c" :value="c">{{ c }}</option>
        </select>

        <!-- Nb élèves min -->
        <div class="number-field">
          <label for="minStudents">Élèves ≥</label>
          <input id="minStudents" v-model.number="fMinStudents" type="number" min="0" class="number-input" />
        </div>
      </div>

      <div class="toolbar-right" style="row-gap:.5rem">
        <!-- Contacts dispo -->
        <label class="check">
          <input type="checkbox" v-model="fHasEmail" />
          <span>Avec email</span>
        </label>
        <label class="check">
          <input type="checkbox" v-model="fHasPhone" />
          <span>Avec téléphone</span>
        </label>

        <!-- Tri -->
        <select v-model="sortBy" class="select-input">
          <option value="name">Tri: Nom parent</option>
          <option value="students">Tri: Nb élèves</option>
          <option value="city">Tri: Ville (1er élève)</option>
        </select>
        <select v-model="sortDir" class="select-input">
          <option value="asc">Asc</option>
          <option value="desc">Desc</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p class="loading-text">Chargement des parents...</p>
    </div>

    <!-- Empty -->
    <div v-else-if="!filteredParents.length" class="empty-state">
      <div class="empty-icon">📭</div>
      <h3 class="empty-title">Aucun parent trouvé</h3>
      <p class="empty-text">Ajustez vos filtres ou votre recherche</p>
    </div>

    <!-- Table -->
    <div v-else class="table-container" ref="printArea">
      <table class="modern-table">
        <thead>
        <tr>
          <th class="th-parent">Parent</th>
          <th class="th-contact">Contact</th>
          <th class="th-students">Élèves rattachés</th>
          <th class="th-actions">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="(p, index) in paginatedParents"
            :key="p.id"
            class="table-row"
            :style="{'--row-index': index}"
        >
          <!-- Parent -->
          <td class="td-parent">
            <div class="parent-cell">
              <div class="parent-avatar">{{ initials(parentDisplayName(p)) }}</div>
              <div class="parent-info">
                <div class="parent-name">{{ parentDisplayName(p) }}</div>
                <div class="parent-id">ID: {{ p.id }}</div>
              </div>
            </div>
          </td>

          <!-- Contact -->
          <td class="td-contact">
            <div class="contact-info">
              <a v-if="p.emailContact" class="contact-link email" :href="`mailto:${p.emailContact}`">
                <span class="contact-icon">📧</span>
                {{ p.emailContact }}
              </a>
              <a v-if="p.phoneContact" class="contact-link phone" :href="`tel:${cleanPhone(p.phoneContact)}`">
                <span class="contact-icon">📱</span>
                {{ p.phoneContact }}
              </a>
              <span v-if="!p.emailContact && !p.phoneContact" class="no-contact">Aucun contact</span>
            </div>
          </td>

          <!-- Students -->
          <td class="td-students">
            <div class="students-list">
                <span
                    v-for="s in p.students"
                    :key="s.id"
                    class="student-badge"
                    :title="studentTooltip(s)"
                >
                  <span class="student-name">{{ s.firstName }} {{ s.lastName }}</span>
                  <span v-if="s.level" class="student-level">{{ s.level }}</span>
                </span>
              <span v-if="!p.students || p.students.length === 0" class="no-students">
                  Aucun élève
                </span>
            </div>
          </td>

          <!-- Actions -->
          <td class="td-actions">
            <div class="action-buttons">
              <a v-if="showUrl(p.id)" class="action-btn view" :href="showUrl(p.id)" title="Voir les détails">
                <span class="action-icon">👁️</span>
              </a>
              <a v-if="editUrl(p.id)" class="action-btn edit" :href="editUrl(p.id)" title="Modifier">
                <span class="action-icon">✏️</span>
              </a>
              <button class="action-btn delete" @click="askDelete(p)" title="Supprimer">
                <span class="action-icon">🗑️</span>
              </button>
            </div>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav v-if="filteredParents.length" class="pagination">
      <button class="pagination-btn" :disabled="page === 1" @click="page--">
        <span class="pagination-icon">◀</span>
      </button>
      <div class="pagination-info">
        <span class="current-page">Page {{ page }}</span>
        <span class="separator">/</span>
        <span class="total-pages">{{ totalPages }}</span>
      </div>
      <button class="pagination-btn" :disabled="page === totalPages" @click="page++">
        <span class="pagination-icon">▶</span>
      </button>
    </nav>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "ParentsList",
  components: { Alert },

  data() {
    return {
      loading: false,
      parents: [],
      // recherche / pagination
      q: "",
      page: 1,
      pageSize: 20,
      // filtres avancés
      fLevel: "",
      fServiceType: "",
      fCity: "",
      fHasEmail: false,
      fHasPhone: false,
      fMinStudents: 0,
      // tri
      sortBy: "name",
      sortDir: "asc",

      messageAlert: null,
      typeAlert: null,
    };
  },

  computed: {
    createUrl() {
      try {
        return this.$routing?.generate("app_parent_entity_new") || null;
      } catch {
        return null;
      }
    },

    // listes pour les selects (dérivées des données)
    availableLevels() {
      const set = new Set();
      this.parents.forEach(p => (p.students || []).forEach(s => s.level && set.add(String(s.level))));
      return Array.from(set).sort();
    },
    availableCities() {
      const set = new Set();
      this.parents.forEach(p => (p.students || []).forEach(s => s.city && set.add(String(s.city).trim())));
      return Array.from(set).sort((a,b)=>a.localeCompare(b));
    },
    availableServiceTypes() {
      const set = new Set();
      this.parents.forEach(p => (p.students || []).forEach(s => set.add(this.guessServiceType(s))));
      set.delete("Autre"); // on le garde implicite
      return Array.from(set).sort();
    },

    // filtre + tri
    filteredParents() {
      const term = this.q.trim().toLowerCase();

      const matchSearch = (p) => {
        if (!term) return true;
        const parentFields = [
          p.fatherLastName, p.fatherFirstName, p.fatherEmail, p.fatherPhone,
          p.motherLastName, p.motherFirstName, p.motherEmail, p.motherPhone,
          p.fullNameParent, p.emailContact, p.phoneContact,
        ].filter(Boolean).map(v => String(v).toLowerCase());

        const hitParent = parentFields.some(v => v.includes(term));
        const hitStudent = (p.students || []).some(s =>
            [s.firstName, s.lastName, s.level, s.city, s.address]
                .filter(Boolean)
                .some(v => String(v).toLowerCase().includes(term))
        );
        return hitParent || hitStudent;
      };

      const matchLevel = (p) => !this.fLevel || (p.students || []).some(s => String(s.level) === this.fLevel);

      const matchService = (p) => !this.fServiceType || (p.students || []).some(s => this.guessServiceType(s) === this.fServiceType);

      const matchCity = (p) => !this.fCity || (p.students || []).some(s => String(s.city).trim() === this.fCity);

      const matchHasEmail = (p) => !this.fHasEmail || !!p.emailContact;

      const matchHasPhone = (p) => !this.fHasPhone || !!p.phoneContact;

      const matchMinStudents = (p) => (p.students?.length || 0) >= (this.fMinStudents || 0);

      // appliquer les filtres
      let res = this.parents
          .filter(matchSearch)
          .filter(matchLevel)
          .filter(matchService)
          .filter(matchCity)
          .filter(matchHasEmail)
          .filter(matchHasPhone)
          .filter(matchMinStudents);

      // tri
      const dir = this.sortDir === "desc" ? -1 : 1;
      res = res.sort((a,b) => {
        if (this.sortBy === "students") {
          const da = (a.students?.length || 0);
          const db = (b.students?.length || 0);
          return (da - db) * dir;
        }
        if (this.sortBy === "city") {
          const ca = (a.students?.[0]?.city || "").toString().toLowerCase();
          const cb = (b.students?.[0]?.city || "").toString().toLowerCase();
          return ca.localeCompare(cb) * dir;
        }
        // name
        const na = this.parentDisplayName(a).toLowerCase();
        const nb = this.parentDisplayName(b).toLowerCase();
        return na.localeCompare(nb) * dir;
      });

      return res;
    },

    totalPages() {
      return Math.max(1, Math.ceil(this.filteredParents.length / this.pageSize));
    },

    paginatedParents() {
      const start = (this.page - 1) * this.pageSize;
      return this.filteredParents.slice(start, start + this.pageSize);
    },
  },

  watch: {
    q() { this.page = 1; },
    pageSize() { this.page = 1; },
    // si un filtre change -> revenir 1ère page
    fLevel() { this.page = 1; },
    fServiceType() { this.page = 1; },
    fCity() { this.page = 1; },
    fHasEmail() { this.page = 1; },
    fHasPhone() { this.page = 1; },
    fMinStudents() { this.page = 1; },
    sortBy() { this.page = 1; },
    sortDir() { this.page = 1; },
  },

  mounted() {
    this.fetchParents();
  },

  methods: {
    async fetchParents() {
      this.loading = true;
      try {
        const url = this.$routing ? this.$routing.generate("api_parent_list") : "/parent/list";
        const { data } = await this.axios.get(url);
        this.parents = data?.message?.parents ?? [];
      } catch (e) {
        this.typeAlert = "danger";
        this.messageAlert = "Impossible de récupérer la liste des parents.";
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.q = "";
      this.page = 1;
      this.pageSize = 20;

      this.fLevel = "";
      this.fServiceType = "";
      this.fCity = "";
      this.fHasEmail = false;
      this.fHasPhone = false;
      this.fMinStudents = 0;

      this.sortBy = "name";
      this.sortDir = "asc";
    },

    // Déduction du type de service à partir du niveau élève
    guessServiceType(s) {
      const lv = String(s.level || "").trim().toUpperCase();
      if (!lv) return "Autre";
      if (["ADOLESCENT", "ADOLESCENTS"].includes(lv)) return "Adolescent";
      if (["ADULT", "ADULTE", "ADULTES", "A1","A2","B1","B2","C1","C2"].includes(lv)) return "Adulte";
      if (["CP","N0","N1","N1_1","N2","N2_1","N2_2","N3","N3_1","N4","N4_1","N4_2"].includes(lv)) return "Enfant";
      if (/^\d+$/.test(lv)) return "Scolaire"; // ex: 2..12
      return "Autre";
    },

    parentDisplayName(p) {
      if (p.fullNameParent) return p.fullNameParent;
      const father = [p.fatherLastName, p.fatherFirstName].filter(Boolean).join(" ");
      const mother = [p.motherLastName, p.motherFirstName].filter(Boolean).join(" ");
      return [father, mother].filter(Boolean).join(" — ");
    },

    initials(name) {
      return (name || "")
          .split(" ")
          .filter(Boolean)
          .slice(0, 2)
          .map((n) => n[0]?.toUpperCase())
          .join("");
    },

    cleanPhone(tel) {
      return String(tel).replace(/[^\d+]/g, "");
    },

    studentTooltip(s) {
      const parts = [];
      if (s.level) parts.push(`Niveau: ${s.level}`);
      if (s.city) parts.push(s.city);
      return parts.join(" • ");
    },

    totalStudents(list) {
      return list.reduce((acc, p) => acc + (Array.isArray(p.students) ? p.students.length : 0), 0);
    },

    showUrl(id) {
      try {
        return this.$routing?.generate("app_parent_entity_show", { id }) || null;
      } catch {
        return `/parents/${id}`;
      }
    },

    editUrl(id) {
      try {
        return this.$routing?.generate("app_parent_entity_edit", { id }) || null;
      } catch {
        return `/parents/${id}/edit`;
      }
    },

    askDelete(p) {
      if (confirm(`Supprimer le parent "${this.parentDisplayName(p)}" ?`)) {
        this.typeAlert = "warning";
        this.messageAlert = "Exemple : branche l'action DELETE Symfony ici.";
      }
    },

    copyEmails() {
      const set = new Set(
          this.filteredParents.map((p) => p.emailContact).filter(Boolean)
      );
      const txt = Array.from(set).join(", ");
      navigator.clipboard.writeText(txt).then(() => {
        this.typeAlert = "success";
        this.messageAlert = `${set.size} email(s) copié(s) dans le presse-papier.`;
      });
    },

    exportCsv() {
      const headers = ["ID", "Parent", "Email", "Téléphone", "Élèves", "Niveaux", "Ville(s)", "Type(s) de service"];
      const rows = this.filteredParents.map((p) => {
        const parent = this.parentDisplayName(p);
        const emails = p.emailContact || "";
        const phone = p.phoneContact || "";
        const studentNames = (p.students || []).map((s) => `${s.firstName} ${s.lastName}`).join(" | ");
        const studentLevels = (p.students || []).map((s) => s.level || "").join(" | ");
        const cities = Array.from(new Set((p.students || []).map(s => s.city).filter(Boolean))).join(" | ");
        const services = Array.from(new Set((p.students || []).map(s => this.guessServiceType(s)))).join(" | ");
        return [p.id, parent, emails, phone, `"${studentNames}"`, `"${studentLevels}"`, `"${cities}"`, `"${services}"`];
      });

      const csv = headers.join(";") + "\n" + rows.map((r) => r.join(";")).join("\n");
      const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.href = url;
      a.download = "parents.csv";
      a.click();
      URL.revokeObjectURL(url);
    },
  },
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.parents-page {
  min-height: 100vh;
  padding: 2rem;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Header */
.page-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 2rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.title-wrap {
  flex: 1;
}

.title {
  font-size: 2.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 0 0 0.5rem 0;
}

.icon {
  font-size: 2.5rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.subtitle {
  margin: 0;
  color: #5f6368;
  font-size: 1.1rem;
}

.stats {
  display: flex;
  gap: 1rem;
}

.stat-card {
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  padding: 1rem 1.5rem;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 10px 30px rgba(46, 125, 50, 0.3);
  min-width: 140px;
}

.stat-icon {
  font-size: 2rem;
}

.stat-content {
  display: flex;
  flex-direction: column;
  color: white;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: 800;
  line-height: 1;
}

.stat-label {
  font-size: 0.85rem;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Toolbar */
.toolbar {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.toolbar-left,
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  min-width: 320px;
  transition: all 0.3s ease;
}

.search-box:focus-within {
  border-color: #66bb6a;
  box-shadow: 0 0 0 3px rgba(102, 187, 106, 0.1);
}

.search-icon {
  font-size: 1.2rem;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
  background: transparent;
}

.search-input::placeholder {
  color: #9e9e9e;
}

.clear-btn {
  cursor: pointer;
  color: #9e9e9e;
  font-size: 1.1rem;
  padding: 0.25rem;
  transition: color 0.2s;
}

.clear-btn:hover {
  color: #ef5350;
}

.select-input {
  padding: 0.75rem 1rem;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  background: white;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: inherit;
}

.select-input:hover {
  border-color: #bdbdbd;
}

.select-input:focus {
  outline: none;
  border-color: #66bb6a;
  box-shadow: 0 0 0 3px rgba(102, 187, 106, 0.1);
}

.btn-icon {
  width: 44px;
  height: 44px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-family: inherit;
  font-size: inherit;
}

.btn-icon:hover:not(:disabled) {
  border-color: #66bb6a;
  background: #f1f8e9;
  transform: rotate(180deg);
}

.btn-icon:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon-emoji {
  font-size: 1.3rem;
}

.btn-action {
  padding: 0.75rem 1.25rem;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  transition: all 0.3s ease;
  font-family: inherit;
  font-size: inherit;
  color: inherit;
}

.btn-action:hover:not(:disabled) {
  border-color: #66bb6a;
  background: #f1f8e9;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 5px 15px rgba(46, 125, 50, 0.3);
  font-family: inherit;
  font-size: inherit;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(46, 125, 50, 0.4);
}

.btn-primary:active {
  transform: translateY(0);
}

.btn-emoji {
  font-size: 1.1rem;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
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

.loading-text {
  color: #5f6368;
  font-size: 1.1rem;
  margin: 0;
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

.empty-title {
  color: #212121;
  font-size: 1.5rem;
  margin: 0 0 0.5rem 0;
  font-weight: 700;
}

.empty-text {
  color: #757575;
  margin: 0;
  font-size: 1rem;
}

/* Table */
.table-container {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
}

.modern-table thead {
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  color: white;
}

.modern-table th {
  padding: 1.25rem 1.5rem;
  text-align: left;
  font-size: 0.9rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.th-parent {
  width: 25%;
}

.th-contact {
  width: 25%;
}

.th-students {
  width: 35%;
}

.th-actions {
  width: 15%;
  text-align: center;
}

.table-row {
  border-bottom: 1px solid #e0e0e0;
  transition: all 0.3s ease;
  animation: slideIn 0.5s ease backwards;
  animation-delay: calc(var(--row-index) * 0.03s);
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

.table-row:last-child {
  border-bottom: none;
}

.table-row:hover {
  background: #f1f8e9;
}

.modern-table td {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
}

/* Parent Cell */
.parent-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.parent-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2e7d32 0%, #66bb6a 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1rem;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
}

.parent-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 0;
}

.parent-name {
  font-weight: 700;
  color: #212121;
  font-size: 1rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.parent-id {
  font-size: 0.8rem;
  color: #9e9e9e;
}

/* Contact Cell */
.contact-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.contact-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #1976d2;
  text-decoration: none;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.contact-link:hover {
  color: #2e7d32;
  transform: translateX(3px);
}

.contact-icon {
  font-size: 1rem;
  flex-shrink: 0;
}

.no-contact {
  color: #9e9e9e;
  font-style: italic;
  font-size: 0.9rem;
}

/* Students Cell */
.students-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.student-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
  padding: 0.5rem 0.75rem;
  border-radius: 50px;
  font-size: 0.85rem;
  border: 2px solid #a5d6a7;
  transition: all 0.3s ease;
  cursor: default;
}

.student-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
}

.student-name {
  font-weight: 600;
  color: #2e7d32;
}

.student-level {
  font-weight: 700;
  color: #1b5e20;
  background: #a5d6a7;
  padding: 0.15rem 0.5rem;
  border-radius: 50px;
  font-size: 0.75rem;
}

.no-students {
  color: #9e9e9e;
  font-style: italic;
  font-size: 0.9rem;
}

/* Actions Cell */
.td-actions {
  text-align: center;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.action-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 2px solid transparent;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  text-decoration: none;
  font-family: inherit;
  font-size: inherit;
}

.action-btn.view {
  border-color: #1976d2;
}

.action-btn.view:hover {
  background: #e3f2fd;
  transform: scale(1.1);
}

.action-btn.edit {
  border-color: #ffa726;
}

.action-btn.edit:hover {
  background: #fff3e0;
  transform: scale(1.1);
}

.action-btn.delete {
  border-color: #ef5350;
}

.action-btn.delete:hover {
  background: #ffebee;
  transform: scale(1.1);
}

.action-icon {
  font-size: 1.1rem;
}

/* Pagination */
.pagination {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 1.25rem;
  margin-top: 1.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.5rem;
}

.pagination-btn {
  width: 44px;
  height: 44px;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  font-family: inherit;
  font-size: inherit;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #66bb6a;
  background: #f1f8e9;
  transform: scale(1.05);
}

.pagination-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.pagination-icon {
  font-size: 1.2rem;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #212121;
  font-size: 1rem;
}

.current-page {
  color: #2e7d32;
  font-weight: 700;
}

.separator {
  color: #9e9e9e;
}

.total-pages {
  color: #757575;
}

/* Alert */
.alert-message {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 1000;
  animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Responsive Design */
@media (max-width: 1200px) {
  .th-parent,
  .th-contact {
    width: 22%;
  }
  .th-students {
    width: 40%;
  }
  .th-actions {
    width: 16%;
  }
}

@media (max-width: 992px) {
  .parents-page {
    padding: 1.5rem;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .stats {
    width: 100%;
    flex-wrap: wrap;
  }

  .stat-card {
    flex: 1;
    min-width: 120px;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .toolbar-left,
  .toolbar-right {
    width: 100%;
    flex-direction: column;
  }

  .search-box {
    width: 100%;
    min-width: auto;
  }

  .select-input,
  .btn-action,
  .btn-primary {
    width: 100%;
    justify-content: center;
  }

  .modern-table {
    font-size: 0.9rem;
  }

  .modern-table th,
  .modern-table td {
    padding: 1rem;
  }
}

@media (max-width: 768px) {
  .title {
    font-size: 2rem;
  }

  .icon {
    font-size: 2rem;
  }

  .subtitle {
    font-size: 1rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }

  .table-container {
    overflow-x: auto;
  }

  .modern-table {
    min-width: 800px;
  }

  .alert-message {
    left: 1rem;
    right: 1rem;
    top: 1rem;
  }
}

@media (max-width: 480px) {
  .parents-page {
    padding: 1rem;
  }

  .page-header {
    padding: 1.5rem;
  }

  .title {
    font-size: 1.75rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .toolbar {
    padding: 1rem;
  }

  .stat-card {
    min-width: 100%;
  }

  .pagination {
    gap: 1rem;
  }

  .pagination-btn {
    width: 40px;
    height: 40px;
  }
}

/* Print Styles */
@media print {
  .parents-page {
    background: white;
    padding: 0;
  }

  .toolbar,
  .pagination,
  .alert-message,
  .action-buttons {
    display: none !important;
  }

  .page-header {
    box-shadow: none;
    border-bottom: 2px solid #2e7d32;
  }

  .table-container {
    box-shadow: none;
  }

  .table-row {
    break-inside: avoid;
  }
}
/* Ajouts mineurs pour les nouveaux contrôles */
.number-field { display: flex; align-items: center; gap: .5rem; }
.number-field label { font-size: .9rem; color: #555; }
.number-input {
  width: 90px; padding: .6rem .7rem; border: 2px solid #e0e0e0; border-radius: 12px; background: #fff; font-size: .95rem;
}
.number-input:focus { outline: none; border-color: #66bb6a; box-shadow: 0 0 0 3px rgba(102,187,106,.1); }

.check { display: inline-flex; align-items: center; gap: .45rem; padding: .5rem .7rem; border: 2px solid #e0e0e0; border-radius: 12px; background: #fff; }
.check input { width: 16px; height: 16px; }
.check:hover { border-color: #66bb6a; background: #f1f8e9; }

</style>
