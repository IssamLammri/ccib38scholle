<template>
  <div class="container-fluid px-4 py-3">
    <!-- En-tête moderne -->
    <div class="header-section">
      <div class="header-content">
        <div class="header-left">
          <div class="icon-wrapper">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
          <div class="header-text">
            <h1 class="page-title">Liste des Enseignants</h1>
            <p class="page-subtitle">Gestion et suivi de {{ teachers.length }} enseignant{{ teachers.length > 1 ? 's' : '' }}</p>
          </div>
        </div>
        <a :href="$routing.generate('app_teacher_new')" class="btn btn-modern btn-primary">
          <i class="fas fa-plus"></i>
          <span>Nouvel Enseignant</span>
        </a>
      </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="stats-grid">
      <div class="stat-card primary">
        <div class="stat-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ filteredTeachers.length }}</h3>
          <p class="stat-label">Enseignants affichés</p>
        </div>
        <div class="stat-trend">
          <i class="fas fa-arrow-up"></i>
        </div>
      </div>

      <div class="stat-card success">
        <div class="stat-icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="stat-content">
          <h3 class="stat-number">{{ specialityOptions.length }}</h3>
          <p class="stat-label">Spécialités</p>
        </div>
        <div class="stat-trend">
          <i class="fas fa-bookmark"></i>
        </div>
      </div>
    </div>

    <!-- Filtres avancés -->
    <div class="filters-section">
      <div class="filters-header">
        <div class="filters-title">
          <i class="fas fa-filter"></i>
          <span>Filtres de recherche</span>
        </div>
        <button @click="clearAllFilters" class="btn btn-modern btn-outline btn-sm">
          <i class="fas fa-eraser"></i>
          <span>Effacer</span>
        </button>
      </div>

      <div class="filters-body">
        <div class="filters-grid">
          <!-- Recherche générale -->
          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-search"></i>
              Recherche générale
            </label>
            <div class="search-box">
              <input
                  v-model.trim="searchTerm"
                  type="text"
                  class="search-input"
                  placeholder="Nom, prénom, email..."
              />
              <button
                  v-if="searchTerm"
                  @click="searchTerm = ''"
                  class="search-clear"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Filtre par classe -->
          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-chalkboard"></i>
              Classe
            </label>
            <select v-model="selectedClass" class="filter-select">
              <option value="">Toutes les classes</option>
              <option v-for="opt in classOptions" :key="opt" :value="opt">
                {{ opt }}
              </option>
            </select>
          </div>

          <!-- Filtre par spécialité -->
          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-book"></i>
              Spécialité
            </label>
            <select v-model="selectedSpeciality" class="filter-select">
              <option value="">Toutes les spécialités</option>
              <option v-for="spec in specialityOptions" :key="spec" :value="spec">
                {{ spec }}
              </option>
            </select>
          </div>

          <!-- Filtre par niveau d'études -->
          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-graduation-cap"></i>
              Niveau d'études
            </label>
            <select v-model="selectedEducationLevel" class="filter-select">
              <option value="">Tous les niveaux</option>
              <option v-for="level in educationLevelOptions" :key="level" :value="level">
                {{ level }}
              </option>
            </select>
          </div>
        </div>

        <!-- Actions et vue -->
        <div class="filters-actions">
          <div class="results-count">
            <i class="fas fa-info-circle"></i>
            <span>{{ filteredTeachers.length }} résultat{{ filteredTeachers.length > 1 ? 's' : '' }}</span>
          </div>
          <div class="view-toggle">
            <button
                @click="viewType = 'table'"
                class="view-btn"
                :class="{ active: viewType === 'table' }"
            >
              <i class="fas fa-table"></i>
            </button>
            <button
                @click="viewType = 'card'"
                class="view-btn"
                :class="{ active: viewType === 'card' }"
            >
              <i class="fas fa-th-large"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des enseignants...</p>
    </div>

    <!-- Vue tableau -->
    <div v-else-if="viewType === 'table'" class="table-section">
      <div class="table-header">
        <div class="table-title">
          <i class="fas fa-list"></i>
          <span>Liste complète</span>
        </div>
        <div class="table-actions">
          <button @click="selectAll" class="btn btn-modern btn-outline btn-sm">
            <i class="fas fa-check-square"></i>
            <span>Tout sélectionner</span>
          </button>
        </div>
      </div>

      <div class="table-wrapper">
        <table class="data-table">
          <thead>
          <tr>
            <th class="col-checkbox">
              <input
                  type="checkbox"
                  class="checkbox-input"
                  v-model="selectAllChecked"
                  @change="toggleSelectAll"
              />
            </th>
            <th @click="sortBy('lastName')" class="sortable">
              Nom
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('firstName')" class="sortable">
              Prénom
              <i class="fas fa-sort"></i>
            </th>
            <th @click="sortBy('email')" class="sortable">
              Email
              <i class="fas fa-sort"></i>
            </th>
            <th>Téléphone</th>
            <th>Niveau d'études</th>
            <th>Spécialités</th>
            <th class="col-actions">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="t in paginatedTeachers" :key="t.id" class="table-row">
            <td class="col-checkbox">
              <input
                  type="checkbox"
                  class="checkbox-input"
                  v-model="selectedTeachers"
                  :value="t.id"
              />
            </td>
            <td class="col-name">{{ t.lastName }}</td>
            <td>{{ t.firstName }}</td>
            <td>
              <a :href="'mailto:' + t.email" class="link-email">
                <i class="fas fa-envelope"></i>
                {{ t.email }}
              </a>
            </td>
            <td>
              <a :href="'tel:' + t.phoneNumber" class="link-phone">
                <i class="fas fa-phone"></i>
                {{ t.phoneNumber }}
              </a>
            </td>
            <td>
              <span class="badge badge-level">{{ t.educationLevel }}</span>
            </td>
            <td>
              <div class="specialities-list">
                  <span
                      v-for="spec in (t.specialities || [])"
                      :key="spec"
                      class="badge badge-speciality"
                  >
                    <i class="fas fa-book"></i>
                    {{ spec }}
                  </span>
                <span v-if="!t.specialities || t.specialities.length === 0" class="text-muted">
                    Aucune
                  </span>
              </div>
            </td>
            <td class="col-actions">
              <div class="action-buttons">
                <a
                    :href="$routing.generate('app_teacher_show', { id: t.id })"
                    class="action-btn view"
                    title="Voir"
                >
                  <i class="fas fa-eye"></i>
                </a>
                <a
                    :href="$routing.generate('app_teacher_edit', { id: t.id })"
                    class="action-btn edit"
                    title="Modifier"
                >
                  <i class="fas fa-edit"></i>
                </a>
                <button
                    @click="confirmDelete(t)"
                    class="action-btn delete"
                    title="Supprimer"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredTeachers.length === 0">
            <td colspan="8" class="empty-cell">
              <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>Aucun enseignant trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination-wrapper">
        <div class="pagination-info">
          Page {{ currentPage }} sur {{ totalPages }}
        </div>
        <div class="pagination-controls">
          <button
              @click="currentPage = 1"
              :disabled="currentPage === 1"
              class="pagination-btn"
          >
            <i class="fas fa-angle-double-left"></i>
          </button>
          <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="pagination-btn"
          >
            <i class="fas fa-angle-left"></i>
          </button>
          <button
              v-for="page in visiblePages"
              :key="page"
              @click="currentPage = page"
              class="pagination-btn"
              :class="{ active: page === currentPage }"
          >
            {{ page }}
          </button>
          <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="pagination-btn"
          >
            <i class="fas fa-angle-right"></i>
          </button>
          <button
              @click="currentPage = totalPages"
              :disabled="currentPage === totalPages"
              class="pagination-btn"
          >
            <i class="fas fa-angle-double-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Vue cartes -->
    <div v-else class="cards-section">
      <div class="cards-grid">
        <div v-for="t in paginatedTeachers" :key="t.id" class="teacher-card">
          <div class="card-header">
            <div class="card-avatar">
              <span>{{ getInitials(t) }}</span>
            </div>
            <div class="card-header-info">
              <h3 class="card-name">{{ t.firstName }} {{ t.lastName }}</h3>
              <p class="card-email">{{ t.email }}</p>
            </div>
            <input
                type="checkbox"
                class="checkbox-input"
                v-model="selectedTeachers"
                :value="t.id"
            />
          </div>

          <div class="card-body">
            <div class="card-info-item">
              <div class="info-label">
                <i class="fas fa-phone"></i>
                <span>Téléphone</span>
              </div>
              <div class="info-value">
                <a :href="'tel:' + t.phoneNumber" class="link-phone">
                  {{ t.phoneNumber }}
                </a>
              </div>
            </div>

            <div class="card-info-item">
              <div class="info-label">
                <i class="fas fa-graduation-cap"></i>
                <span>Niveau d'études</span>
              </div>
              <div class="info-value">
                <span class="badge badge-level">{{ t.educationLevel }}</span>
              </div>
            </div>

            <div class="card-info-item">
              <div class="info-label">
                <i class="fas fa-book"></i>
                <span>Spécialités</span>
              </div>
              <div class="info-value">
                <div class="specialities-list">
                  <span
                      v-for="spec in (t.specialities || [])"
                      :key="spec"
                      class="badge badge-speciality"
                  >
                    {{ spec }}
                  </span>
                  <span v-if="!t.specialities || t.specialities.length === 0" class="text-muted">
                    Aucune
                  </span>
                </div>
              </div>
            </div>

            <div class="card-info-item">
              <div class="info-label">
                <i class="fas fa-chalkboard"></i>
                <span>Classes</span>
              </div>
              <div class="info-value">
                <div class="classes-list">
                  <span
                      v-for="cls in (t.classes || [])"
                      :key="cls.id"
                      class="badge badge-class"
                  >
                    {{ cls.name }}
                  </span>
                  <span v-if="!t.classes || t.classes.length === 0" class="text-muted">
                    Aucune
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <a
                :href="$routing.generate('app_teacher_show', { id: t.id })"
                class="btn btn-modern btn-outline btn-sm"
            >
              <i class="fas fa-eye"></i>
              <span>Voir</span>
            </a>
            <a
                :href="$routing.generate('app_teacher_edit', { id: t.id })"
                class="btn btn-modern btn-warning btn-sm"
            >
              <i class="fas fa-edit"></i>
              <span>Modifier</span>
            </a>
            <button
                @click="confirmDelete(t)"
                class="btn btn-modern btn-danger btn-sm"
            >
              <i class="fas fa-trash"></i>
              <span>Supprimer</span>
            </button>
          </div>
        </div>

        <div v-if="filteredTeachers.length === 0" class="empty-state-card">
          <i class="fas fa-search"></i>
          <h3>Aucun enseignant trouvé</h3>
          <p>Essayez de modifier vos critères de recherche</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination-wrapper">
        <div class="pagination-info">
          Page {{ currentPage }} sur {{ totalPages }}
        </div>
        <div class="pagination-controls">
          <button
              @click="setPage(1)"
              :disabled="currentPage === 1"
              class="pagination-btn"
          >
            <i class="fas fa-angle-double-left"></i>
          </button>
          <button
              @click="setPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="pagination-btn"
          >
            <i class="fas fa-angle-left"></i>
          </button>
          <button
              v-for="page in visiblePages"
              :key="page"
              @click="setPage(page)"
              class="pagination-btn"
              :class="{ active: page === currentPage }"
          >
            {{ page }}
          </button>
          <button
              @click="setPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="pagination-btn"
          >
            <i class="fas fa-angle-right"></i>
          </button>
          <button
              @click="setPage(totalPages)"
              :disabled="currentPage === totalPages"
              class="pagination-btn"
          >
            <i class="fas fa-angle-double-right"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Alerte -->
    <transition name="slide-fade">
      <alert
          v-if="messageAlert"
          :text="messageAlert"
          :type="typeAlert"
          class="alert-notification"
      />
    </transition>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "TeachersList",
  components: { Alert },
  props: {
    csrfDeleteToken: { type: String, required: true }
  },
  data() {
    return {
      teachers: [],
      isLoading: false,

      searchTerm: "",
      selectedClass: "",
      selectedSpeciality: "",
      selectedEducationLevel: "",

      viewType: "table",
      currentPage: 1,
      itemsPerPage: 10,

      selectedTeachers: [],
      selectAllChecked: false,

      sortField: '',
      sortOrder: 'asc',

      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    classOptions() {
      const set = new Set();
      this.teachers.forEach(t => {
        (t.classes || []).forEach(c => {
          if (c && c.name) set.add(c.name);
        });
      });
      return Array.from(set).sort((a, b) => a.localeCompare(b, "fr"));
    },

    specialityOptions() {
      const set = new Set();
      this.teachers.forEach(t => {
        (t.specialities || []).forEach(s => {
          if (s) set.add(s);
        });
      });
      return Array.from(set).sort((a, b) => a.localeCompare(b, "fr"));
    },

    educationLevelOptions() {
      const set = new Set();
      this.teachers.forEach(t => {
        if (t.educationLevel) set.add(t.educationLevel);
      });
      return Array.from(set).sort((a, b) => a.localeCompare(b, "fr"));
    },

    filteredTeachers() {
      const norm = v =>
          (v || "")
              .toString()
              .normalize("NFD")
              .replace(/[\u0300-\u036f]/g, "")
              .toLowerCase();

      const q = norm(this.searchTerm);

      let filtered = this.teachers.filter(t => {
        const matchName = !q ||
            norm(t.lastName).includes(q) ||
            norm(t.firstName).includes(q) ||
            norm(t.email).includes(q) ||
            norm(`${t.lastName} ${t.firstName}`).includes(q) ||
            norm(`${t.firstName} ${t.lastName}`).includes(q);

        const classes = (t.classes || []).map(c => c.name);
        const matchClass = !this.selectedClass || classes.includes(this.selectedClass);

        const matchSpeciality = !this.selectedSpeciality ||
            (t.specialities || []).includes(this.selectedSpeciality);

        const matchEducationLevel = !this.selectedEducationLevel ||
            t.educationLevel === this.selectedEducationLevel;

        return matchName && matchClass && matchSpeciality && matchEducationLevel;
      });

      if (this.sortField) {
        filtered.sort((a, b) => {
          let aVal = a[this.sortField];
          let bVal = b[this.sortField];

          if (typeof aVal === 'string') aVal = aVal.toLowerCase();
          if (typeof bVal === 'string') bVal = bVal.toLowerCase();

          if (aVal < bVal) return this.sortOrder === 'asc' ? -1 : 1;
          if (aVal > bVal) return this.sortOrder === 'asc' ? 1 : -1;
          return 0;
        });
      }

      return filtered;
    },

    totalPages() {
      return Math.ceil(this.filteredTeachers.length / this.itemsPerPage);
    },

    paginatedTeachers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredTeachers.slice(start, end);
    },

    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  watch: {
    filteredTeachers() {
      this.currentPage = 1;
    },
    totalPages(newVal) {
      if (this.currentPage > newVal) this.currentPage = newVal || 1;
    }
  },
  methods: {
    setPage(n) {
      const target = Math.min(Math.max(1, n), this.totalPages || 1);
      if (target !== this.currentPage) {
        this.currentPage = target;
        this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
      }
    },

    async fetchTeachers() {
      this.isLoading = true;
      try {
        const res = await this.$axios.get(this.$routing.generate("all_teachers"));
        this.teachers = res.data.allTeachers || [];
      } catch (e) {
        console.error("Erreur lors de la récupération des enseignants :", e);
        this.showAlert("danger", "Erreur lors du chargement des enseignants.");
      } finally {
        this.isLoading = false;
      }
    },

    clearAllFilters() {
      this.searchTerm = "";
      this.selectedClass = "";
      this.selectedSpeciality = "";
      this.selectedEducationLevel = "";
    },

    sortBy(field) {
      if (this.sortField === field) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortField = field;
        this.sortOrder = 'asc';
      }
    },

    toggleSelectAll() {
      if (this.selectAllChecked) {
        this.selectedTeachers = this.filteredTeachers.map(t => t.id);
      } else {
        this.selectedTeachers = [];
      }
    },

    selectAll() {
      this.selectedTeachers = this.filteredTeachers.map(t => t.id);
      this.selectAllChecked = true;
    },

    async confirmDelete(teacher) {
      if (!confirm(`Êtes-vous sûr de vouloir supprimer l'enseignant ${teacher.firstName} ${teacher.lastName} ?`)) {
        return;
      }

      try {
        const res = await this.$axios.post(
            this.$routing.generate('app_teacher_delete', { id: teacher.id }),
            { _token: this.csrfDeleteToken }
        );

        if (res.data?.success) {
          this.showAlert("success", "Enseignant supprimé avec succès.");
          await this.fetchTeachers();
        } else {
          this.showAlert("danger", res.data?.message || "Erreur lors de la suppression.");
        }
      } catch (e) {
        console.error("Erreur lors de la suppression :", e);
        this.showAlert("danger", "Erreur lors de la suppression de l'enseignant.");
      }
    },

    getInitials(teacher) {
      const first = teacher.firstName?.[0]?.toUpperCase() || '';
      const last = teacher.lastName?.[0]?.toUpperCase() || '';
      return first + last || 'E';
    },

    showAlert(type, message) {
      this.typeAlert = type;
      this.messageAlert = message;

      setTimeout(() => {
        this.messageAlert = null;
      }, 5000);
    }
  },
  mounted() {
    this.fetchTeachers();
  },
};
</script>

<style scoped>
/* Variables */
:global(:root) {
  --primary: #667eea;
  --primary-dark: #5568d3;
  --secondary: #718096;
  --success: #48bb78;
  --danger: #f56565;
  --warning: #ed8936;
  --info: #4299e1;

  --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --gradient-success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  --gradient-info: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  --gradient-warning: linear-gradient(135deg, #fa709a 0%, #fee140 100%);

  --bg-primary: #ffffff;
  --bg-secondary: #f8fafc;
  --bg-tertiary: #edf2f7;

  --text-primary: #2d3748;
  --text-secondary: #718096;
  --text-tertiary: #a0aec0;

  --border-color: #e2e8f0;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);

  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;

  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Layout */
.container-fluid {
  max-width: 1800px;
  margin: 0 auto;
  background: var(--bg-secondary);
  min-height: 100vh;
}

/* Header */
.header-section {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.icon-wrapper {
  width: 64px;
  height: 64px;
  background: var(--gradient-primary);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.75rem;
  flex-shrink: 0;
  box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.page-subtitle {
  font-size: 1rem;
  color: var(--text-secondary);
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  position: relative;
  padding: 1.5rem;
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: var(--transition);
  border: 1px solid var(--border-color);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.stat-card.primary {
  background: var(--gradient-primary);
  color: white;
}

.stat-card.success {
  background: var(--gradient-success);
  color: white;
}

.stat-card.info {
  background: var(--gradient-info);
  color: white;
}

.stat-card.warning {
  background: var(--gradient-warning);
  color: white;
}

.stat-icon {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 2.5rem;
  opacity: 0.2;
}

.stat-content {
  position: relative;
  z-index: 2;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
  animation: countUp 1s ease-out;
}

@keyframes countUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-label {
  font-size: 0.9375rem;
  margin: 0;
  opacity: 0.95;
}

.stat-trend {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  font-size: 1.25rem;
  opacity: 0.3;
}

/* Filters Section */
.filters-section {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--border-color);
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.filters-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.filters-title i {
  color: var(--primary);
}

.filters-body {
  padding: 2rem;
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-label i {
  color: var(--primary);
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem;
  padding-left: 2.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  color: var(--text-primary);
  background: var(--bg-primary);
  transition: var(--transition);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-box::before {
  content: '\f002';
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  position: absolute;
  left: 1rem;
  color: var(--text-tertiary);
  pointer-events: none;
}

.search-clear {
  position: absolute;
  right: 0.5rem;
  padding: 0.5rem;
  border: none;
  background: transparent;
  color: var(--text-tertiary);
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: var(--transition);
}

.search-clear:hover {
  color: var(--danger);
  background: rgba(245, 101, 101, 0.1);
}

.filter-select {
  padding: 0.875rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  color: var(--text-primary);
  background: var(--bg-primary);
  transition: var(--transition);
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filters-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.results-count {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9375rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.results-count i {
  color: var(--primary);
}

.view-toggle {
  display: flex;
  gap: 0.5rem;
}

.view-btn {
  width: 40px;
  height: 40px;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-primary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.view-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.view-btn.active {
  background: var(--gradient-primary);
  border-color: var(--primary);
  color: white;
}

/* Buttons */
.btn-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.875rem 1.5rem;
  border: 2px solid transparent;
  border-radius: var(--radius-sm);
  font-size: 0.9375rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
}

.btn-modern:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  text-decoration: none;
}

.btn-primary {
  background: var(--gradient-primary);
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #5568d3 0%, #663b8e 100%);
  color: white;
}

.btn-outline {
  background: transparent;
  border-color: var(--border-color);
  color: var(--text-primary);
}

.btn-outline:hover {
  border-color: var(--text-secondary);
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.btn-warning {
  background: linear-gradient(135deg, #fa709a, #fee140);
  color: white;
}

.btn-danger {
  background: linear-gradient(135deg, #f56565, #fc8181);
  color: white;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Loading */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Table Section */
.table-section {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  overflow: hidden;
  margin-bottom: 2rem;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--border-color);
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.table-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.table-title i {
  color: var(--primary);
}

.table-wrapper {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table thead {
  background: var(--bg-secondary);
}

.data-table th {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 2px solid var(--border-color);
}

.data-table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: var(--transition);
}

.data-table th.sortable:hover {
  color: var(--primary);
  background: var(--bg-tertiary);
}

.data-table td {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-primary);
}

.table-row {
  transition: var(--transition);
}

.table-row:hover {
  background: var(--bg-secondary);
}

.col-checkbox {
  width: 40px;
}

.col-name {
  font-weight: 600;
}

.col-actions {
  width: 140px;
  text-align: center;
}

.checkbox-input {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: var(--primary);
}

.link-email,
.link-phone {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--primary);
  text-decoration: none;
  transition: var(--transition);
}

.link-email:hover,
.link-phone:hover {
  color: var(--primary-dark);
  text-decoration: underline;
}

.badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-level {
  background: var(--gradient-success);
  color: white;
}

.badge-speciality {
  background: var(--gradient-primary);
  color: white;
}

.badge-class {
  background: var(--gradient-info);
  color: white;
}

.specialities-list,
.classes-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.text-muted {
  color: var(--text-tertiary);
  font-style: italic;
  font-size: 0.875rem;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  text-decoration: none;
}

.action-btn.view {
  background: rgba(66, 153, 225, 0.1);
  color: var(--info);
}

.action-btn.view:hover {
  background: var(--info);
  color: white;
}

.action-btn.edit {
  background: rgba(237, 137, 54, 0.1);
  color: var(--warning);
}

.action-btn.edit:hover {
  background: var(--warning);
  color: white;
}

.action-btn.delete {
  background: rgba(245, 101, 101, 0.1);
  color: var(--danger);
}

.action-btn.delete:hover {
  background: var(--danger);
  color: white;
}

/* Empty State */
.empty-cell {
  padding: 4rem 2rem !important;
}

.empty-state {
  text-align: center;
  color: var(--text-secondary);
}

.empty-state i {
  font-size: 3rem;
  opacity: 0.3;
  margin-bottom: 1rem;
  display: block;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  margin: 0;
}

/* Cards Section */
.cards-section {
  margin-bottom: 2rem;
}

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.teacher-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: var(--transition);
  box-shadow: var(--shadow-sm);
}

.teacher-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.teacher-card .card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid var(--border-color);
}

.card-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--gradient-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.card-header-info {
  flex: 1;
}

.card-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.25rem 0;
}

.card-email {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.card-info-item {
  margin-bottom: 1.25rem;
}

.card-info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-label i {
  color: var(--primary);
}

.info-value {
  font-size: 0.9375rem;
  color: var(--text-primary);
}

.card-footer {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
}

.empty-state-card {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
  background: var(--bg-primary);
  border: 1px dashed var(--border-color);
  border-radius: var(--radius-lg);
}

.empty-state-card i {
  font-size: 3rem;
  opacity: 0.3;
  margin-bottom: 1rem;
  display: block;
}

.empty-state-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.empty-state-card p {
  margin: 0;
}

/* Pagination */
.pagination-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--border-color);
  background: var(--bg-primary);
  border-radius: 0 0 var(--radius-lg) var(--radius-lg);
}

.pagination-info {
  font-size: 0.9375rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.pagination-controls {
  display: flex;
  gap: 0.5rem;
}

.pagination-btn {
  min-width: 40px;
  height: 40px;
  padding: 0 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--bg-primary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  font-weight: 500;
}

.pagination-btn:hover:not(:disabled) {
  border-color: var(--primary);
  color: var(--primary);
}

.pagination-btn.active {
  background: var(--gradient-primary);
  border-color: var(--primary);
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Alert */
.alert-notification {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 9999;
  max-width: 500px;
  box-shadow: var(--shadow-lg);
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Responsive */
@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .filters-grid {
    grid-template-columns: 1fr;
  }

  .cards-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    align-items: stretch;
  }

  .header-left {
    width: 100%;
  }

  .btn-modern {
    width: 100%;
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .filters-actions {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .view-toggle {
    justify-content: center;
  }

  .table-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .pagination-wrapper {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-controls {
    flex-wrap: wrap;
    justify-content: center;
  }

  .cards-grid {
    grid-template-columns: 1fr;
  }

  .card-footer {
    flex-direction: column;
  }

  .alert-notification {
    top: 1rem;
    right: 1rem;
    left: 1rem;
    max-width: none;
  }
}

@media (max-width: 480px) {
  .container-fluid {
    padding: 1rem;
  }

  .header-section {
    padding: 1.5rem;
  }

  .icon-wrapper {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .page-subtitle {
    font-size: 0.875rem;
  }

  .filters-body {
    padding: 1.5rem;
  }

  .stat-number {
    font-size: 2rem;
  }
}
</style>