<template>
  <div class="container-fluid px-4 py-3">
    <!-- En-tête avec gradient -->
    <div class="header-section mb-5">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="display-6 fw-bold text-gradient mb-2">
            <i class="fas fa-graduation-cap me-3"></i>
            Liste des Étudiants
          </h1>
          <p class="text-muted mb-0">Gestion et suivi des étudiants</p>
        </div>
        <a :href="$routing.generate('app_student_new')" class="btn btn-primary btn-lg shadow-lg">
          <i class="fas fa-plus me-2"></i> Nouvel Étudiant
        </a>
      </div>
    </div>

    <!-- Filtres avancés -->
    <div class="filters-card mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
          <h5 class="mb-0">
            <i class="fas fa-filter me-2 text-primary"></i>
            Filtres de recherche
          </h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Recherche générale -->
            <div class="col-md-4">
              <label class="form-label fw-semibold">Recherche générale</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                  <i class="fas fa-search text-muted"></i>
                </span>
                <input
                    v-model.trim="searchTerm"
                    type="text"
                    class="form-control border-start-0"
                    placeholder="Nom, prénom..."
                    aria-label="Recherche nom/prénom"
                />
                <button
                    v-if="searchTerm"
                    @click="searchTerm = ''"
                    class="btn btn-outline-secondary"
                    type="button"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>

            <!-- Filtre par classe -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">Classe</label>
              <select v-model="selectedClass" class="form-select">
                <option value="">Toutes les classes</option>
                <option
                    v-for="opt in classOptions"
                    :key="opt"
                    :value="opt"
                >{{ opt }}</option>
              </select>
            </div>

            <!-- Filtre par niveau -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">Niveau</label>
              <select v-model="selectedLevel" class="form-select">
                <option value="">Tous les niveaux</option>
                <option
                    v-for="level in levelOptions"
                    :key="level"
                    :value="level"
                >{{ level }}</option>
              </select>
            </div>

            <!-- Filtre par âge -->
            <div class="col-md-2">
              <label class="form-label fw-semibold">Âge</label>
              <select v-model="selectedAgeRange" class="form-select">
                <option value="">Tous âges</option>
                <option value="0-15">0-15 ans</option>
                <option value="16-18">16-18 ans</option>
                <option value="19-25">19-25 ans</option>
                <option value="26+">26+ ans</option>
              </select>
            </div>
          </div>

          <!-- Boutons d'action rapide -->
          <div class="row mt-3">
            <div class="col-12">
              <div class="d-flex gap-2 flex-wrap">
                <button @click="clearAllFilters" class="btn btn-outline-secondary btn-sm">
                  <i class="fas fa-eraser me-1"></i> Effacer filtres
                </button>
<!--                <button @click="exportStudents" class="btn btn-outline-success btn-sm">-->
<!--                  <i class="fas fa-download me-1"></i> Exporter-->
<!--                </button>-->
                <div class="ms-auto">
                  <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="viewType" id="tableView" v-model="viewType" value="table" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="tableView">
                      <i class="fas fa-table"></i>
                    </label>
                    <input type="radio" class="btn-check" name="viewType" id="cardView" v-model="viewType" value="card" autocomplete="off">
                    <label class="btn btn-outline-primary btn-sm" for="cardView">
                      <i class="fas fa-th-large"></i>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="stat-card bg-primary">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ filteredStudents.length }}</h3>
            <p class="stat-label">Étudiants affichés</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-success">
          <div class="stat-icon">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ classOptions.length }}</h3>
            <p class="stat-label">Classes actives</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-info">
          <div class="stat-icon">
            <i class="fas fa-calendar-alt"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ averageAge }}</h3>
            <p class="stat-label">Âge moyen</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-warning">
          <div class="stat-icon">
            <i class="fas fa-user-graduate"></i>
          </div>
          <div class="stat-content">
            <h3 class="stat-number">{{ levelOptions.length }}</h3>
            <p class="stat-label">Niveaux</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading spinner -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <p class="text-muted mt-2">Chargement des étudiants...</p>
    </div>

    <!-- Vue tableau -->
    <div v-else-if="viewType === 'table'" class="table-container">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">
              {{ filteredStudents.length }} étudiant(s) trouvé(s)
            </h6>
            <div class="d-flex gap-2">
              <button @click="selectAll" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-check-square me-1"></i> Tout sélectionner
              </button>
            </div>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
            <tr>
              <th style="width: 3%;">
                <input type="checkbox" class="form-check-input" v-model="selectAllChecked" @change="toggleSelectAll">
              </th>
              <th style="width: 15%;" @click="sortBy('lastName')" class="sortable">
                Nom
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th style="width: 15%;" @click="sortBy('firstName')" class="sortable">
                Prénom
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th style="width: 12%;" @click="sortBy('birthDate')" class="sortable">
                Date de Naissance
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th style="width: 8%;">Âge</th>
              <th style="width: 10%;" @click="sortBy('level')" class="sortable">
                Niveau
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th style="width: 25%;">Classes inscrites</th>
              <th style="width: 12%;" class="text-center">Actions</th>
            </tr>
            </thead>

            <tbody>
            <tr v-for="s in paginatedStudents" :key="s.id" class="student-row">
              <td>
                <input type="checkbox" class="form-check-input" v-model="selectedStudents" :value="s.id">
              </td>
              <td class="fw-semibold">{{ s.lastName }}</td>
              <td>{{ s.firstName }}</td>
              <td>{{ formatDate(s.birthDate) }}</td>
              <td>
                <span class="badge bg-light text-dark">{{ calculateAge(s.birthDate) }} ans</span>
              </td>
              <td>
                <span class="badge" :class="getLevelBadgeClass(s.level)">{{ s.level }}</span>
              </td>
              <td>
                <div class="d-flex flex-wrap gap-1">
                  <span
                      v-for="className in classNames(s)"
                      :key="className"
                      class="badge bg-primary rounded-pill"
                  >
                    <i class="fas fa-chalkboard me-1"></i>{{ className }}
                  </span>
                  <span v-if="!classNames(s).length" class="text-muted fst-italic">
                    Aucune classe
                  </span>
                </div>
              </td>
              <td class="text-center">
                <div class="btn-group" role="group">
                  <barcode-modal :studentName="s.lastName + ' ' + s.firstName" :id="s.id" specifier="1" />
                  <a :href="$routing.generate('app_student_show', { id: s.id })"
                     class="btn btn-outline-info btn-sm"
                     data-bs-toggle="tooltip"
                     title="Voir détails">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a :href="$routing.generate('app_student_edit', { id: s.id })"
                     class="btn btn-outline-warning btn-sm"
                     data-bs-toggle="tooltip"
                     title="Modifier">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button @click="confirmDelete(s)"
                          class="btn btn-outline-danger btn-sm"
                          data-bs-toggle="tooltip"
                          title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredStudents.length === 0">
              <td colspan="8" class="text-center py-5">
                <div class="text-muted">
                  <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
                  <h5>Aucun étudiant trouvé</h5>
                  <p>Essayez de modifier vos critères de recherche</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="card-footer bg-white border-0">
          <nav aria-label="Navigation des pages">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="currentPage = 1" :disabled="currentPage === 1">
                  <i class="fas fa-angle-double-left"></i>
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="currentPage--" :disabled="currentPage === 1">
                  <i class="fas fa-angle-left"></i>
                </button>
              </li>
              <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                <button class="page-link" @click="currentPage = page">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="currentPage++" :disabled="currentPage === totalPages">
                  <i class="fas fa-angle-right"></i>
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="currentPage = totalPages" :disabled="currentPage === totalPages">
                  <i class="fas fa-angle-double-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- Vue cartes -->
    <div v-else class="cards-container">
      <div class="row g-4">
        <div v-for="s in paginatedStudents" :key="s.id" class="col-md-6 col-xl-4">
          <div class="card student-card h-100 border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                  <i class="fas fa-user-circle me-2"></i>
                  {{ s.firstName }} {{ s.lastName }}
                </h6>
                <input type="checkbox" class="form-check-input bg-white" v-model="selectedStudents" :value="s.id">
              </div>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-6">
                  <small class="text-muted">Date de naissance</small>
                  <p class="mb-0 fw-semibold">{{ formatDate(s.birthDate) }}</p>
                </div>
                <div class="col-6">
                  <small class="text-muted">Âge</small>
                  <p class="mb-0">
                    <span class="badge bg-light text-dark">{{ calculateAge(s.birthDate) }} ans</span>
                  </p>
                </div>
                <div class="col-12">
                  <small class="text-muted">Niveau</small>
                  <p class="mb-0">
                    <span class="badge" :class="getLevelBadgeClass(s.level)">{{ s.level }}</span>
                  </p>
                </div>
                <div class="col-12">
                  <small class="text-muted">Classes inscrites</small>
                  <div class="mt-1">
                    <span
                        v-for="className in classNames(s)"
                        :key="className"
                        class="badge bg-primary rounded-pill me-1 mb-1"
                    >
                      <i class="fas fa-chalkboard me-1"></i>{{ className }}
                    </span>
                    <span v-if="!classNames(s).length" class="text-muted fst-italic">
                      Aucune classe
                    </span>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light border-0">
              <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a :href="$routing.generate('app_student_show', { id: s.id })" class="btn btn-outline-info btn-sm">
                  <i class="fas fa-eye me-1"></i> Voir
                </a>
                <a :href="$routing.generate('app_student_edit', { id: s.id })" class="btn btn-outline-warning btn-sm">
                  <i class="fas fa-edit me-1"></i> Modifier
                </a>
                <button @click="confirmDelete(s)" class="btn btn-outline-danger btn-sm">
                  <i class="fas fa-trash me-1"></i> Supprimer
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- >>> Pagination pour le mode cartes <<< -->
      <div v-if="totalPages > 1" class="card-footer bg-white border-0 mt-3">
        <nav aria-label="Navigation des pages">
          <ul class="pagination justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="setPage(1)" :disabled="currentPage === 1">
                <i class="fas fa-angle-double-left"></i>
              </button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button class="page-link" @click="setPage(currentPage - 1)" :disabled="currentPage === 1">
                <i class="fas fa-angle-left"></i>
              </button>
            </li>
            <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <button class="page-link" @click="setPage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="setPage(currentPage + 1)" :disabled="currentPage === totalPages">
                <i class="fas fa-angle-right"></i>
              </button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button class="page-link" @click="setPage(totalPages)" :disabled="currentPage === totalPages">
                <i class="fas fa-angle-double-right"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Alerte -->
    <alert
        v-if="messageAlert"
        :text="messageAlert"
        :type="typeAlert"
        class="mt-4"
    />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "StudyClassDetails",
  components: { Alert },
  data() {
    return {
      students: [],
      isLoading: false,

      // Filtres
      searchTerm: "",
      selectedClass: "",
      selectedLevel: "",
      selectedAgeRange: "",

      // Vue et pagination
      viewType: "table",
      currentPage: 1,
      itemsPerPage: 10,

      // Sélection multiple
      selectedStudents: [],
      selectAllChecked: false,

      // Tri
      sortField: '',
      sortOrder: 'asc',

      // Alertes
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    // Options de classe
    classOptions() {
      const set = new Set();
      this.students.forEach(s => {
        (s.registrations || []).forEach(r => {
          if (r && r.studyClass && r.studyClass.name) set.add(r.studyClass.name);
        });
      });
      return Array.from(set).sort((a, b) => a.localeCompare(b, "fr"));
    },

    // Options de niveau
    levelOptions() {
      const set = new Set();
      this.students.forEach(s => {
        if (s.level) set.add(s.level);
      });
      return Array.from(set).sort();
    },

    // Âge moyen
    averageAge() {
      if (this.students.length === 0) return 0;
      const totalAge = this.students.reduce((sum, s) => sum + this.calculateAge(s.birthDate), 0);
      return Math.round(totalAge / this.students.length);
    },


    // Liste filtrée et triée
    filteredStudents() {
      const norm = v =>
          (v || "")
              .toString()
              .normalize("NFD")
              .replace(/[\u0300-\u036f]/g, "")
              .toLowerCase();

      const q = norm(this.searchTerm);

      let filtered = this.students.filter(s => {
        const fullNP = `${norm(s.lastName)} ${norm(s.firstName)}`.trim();
        const fullPN = `${norm(s.firstName)} ${norm(s.lastName)}`.trim();

        const matchName = !q ||
            norm(s.lastName).includes(q) ||
            norm(s.firstName).includes(q) ||
            fullNP.includes(q) ||
            fullPN.includes(q);

        const classes = this.classNames(s);
        const matchClass = !this.selectedClass || classes.includes(this.selectedClass);

        const matchLevel = !this.selectedLevel || s.level === this.selectedLevel;

        let matchAge = true;
        if (this.selectedAgeRange) {
          const age = this.calculateAge(s.birthDate);
          const [min, max] = this.selectedAgeRange.includes('+')
              ? [parseInt(this.selectedAgeRange), 999]
              : this.selectedAgeRange.split('-').map(Number);
          matchAge = age >= min && age <= max;
        }

        return matchName && matchClass && matchLevel && matchAge;
      });

      // Tri
      if (this.sortField) {
        filtered.sort((a, b) => {
          let aVal = a[this.sortField];
          let bVal = b[this.sortField];

          if (this.sortField === 'birthDate') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
          }

          if (aVal < bVal) return this.sortOrder === 'asc' ? -1 : 1;
          if (aVal > bVal) return this.sortOrder === 'asc' ? 1 : -1;
          return 0;
        });
      }

      return filtered;
    },

    // Pagination
    totalPages() {
      return Math.ceil(this.filteredStudents.length / this.itemsPerPage);
    },

    paginatedStudents() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredStudents.slice(start, end);
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
    filteredStudents() {
      this.currentPage = 1;
    },
    totalPages(newVal) {
      if (this.currentPage > newVal) this.currentPage = newVal || 1;
    }
  },
  methods: {
    setPage(n) {
      // borne entre 1 et totalPages (et gère le cas 0)
      const target = Math.min(Math.max(1, n), this.totalPages || 1);
      if (target !== this.currentPage) {
        this.currentPage = target;
        // optionnel : remonter en haut
        this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
      }
    },
    async fetchStudents() {
      this.isLoading = true;
      try {
        const res = await this.$axios.get(this.$routing.generate("all_students"));
        this.students = res.data.allStudents || [];
      } catch (e) {
        console.error("Erreur lors de la récupération des étudiants :", e);
        this.typeAlert = "danger";
        this.messageAlert = "Erreur lors du chargement des étudiants.";
      } finally {
        this.isLoading = false;
      }
    },

    classNames(student) {
      if (!student?.registrations) return [];

      return student.registrations
          .filter(r => r?.active === true)
          .map(r => r?.studyClass?.name)
          .filter(Boolean);
    },

    formatDate(iso) {
      if (!iso) return "—";
      try {
        const d = new Date(iso);
        const dd = String(d.getDate()).padStart(2, "0");
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const yyyy = d.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
      } catch {
        return "—";
      }
    },

    calculateAge(birthDate) {
      if (!birthDate) return 0;
      const today = new Date();
      const birth = new Date(birthDate);
      let age = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
      }
      return age;
    },

    getLevelBadgeClass(level) {
      const levelColors = {
        'Débutant': 'bg-success',
        'Intermédiaire': 'bg-warning',
        'Avancé': 'bg-danger',
        'Expert': 'bg-dark'
      };
      return levelColors[level] || 'bg-secondary';
    },

    // Filtres
    clearAllFilters() {
      this.searchTerm = "";
      this.selectedClass = "";
      this.selectedLevel = "";
      this.selectedAgeRange = "";
    },

    // Tri
    sortBy(field) {
      if (this.sortField === field) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortField = field;
        this.sortOrder = 'asc';
      }
    },

    // Sélection
    toggleSelectAll() {
      if (this.selectAllChecked) {
        this.selectedStudents = this.filteredStudents.map(s => s.id);
      } else {
        this.selectedStudents = [];
      }
    },

    selectAll() {
      this.selectedStudents = this.filteredStudents.map(s => s.id);
      this.selectAllChecked = true;
    },

    // Actions
    confirmDelete(student) {
      if (confirm(`Êtes-vous sûr de vouloir supprimer l'étudiant ${student.firstName} ${student.lastName} ?`)) {
        // Logique de suppression
        console.log('Suppression de', student);
      }
    },

    exportStudents() {
      // Logique d'export
      console.log('Export des étudiants sélectionnés:', this.selectedStudents);
    },

    csrfToken(key) {
      return "";
    },
  },
  mounted() {
    this.fetchStudents();
  },
};
</script>

<style>
:root {
  --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}
</style>

<style scoped>
/* En-tête */
.header-section {
  margin: -1rem -1.5rem 2rem -1.5rem;
  padding: 2rem 1.5rem;
  border-radius: 0 0 1rem 1rem;
  color: white;
}

.text-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Cartes de statistiques */
.stat-card {
  background: linear-gradient(135deg, var(--bs-primary) 0%, color-mix(in srgb, var(--bs-primary) 80%, black) 100%);
  color: white;
  padding: 1.5rem;
  border-radius: 1rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-card.bg-success {
  background: var(--success-gradient);
}

.stat-card.bg-info {
  background: var(--info-gradient);
}

.stat-card.bg-warning {
  background: var(--warning-gradient);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: rgba(255,255,255,0.1);
  border-radius: 50%;
  transition: transform 0.6s ease;
}

.stat-card:hover::before {
  transform: scale(1.5);
}

.stat-icon {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 2rem;
  opacity: 0.3;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  position: relative;
  z-index: 2;
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
  margin: 0;
  position: relative;
  z-index: 2;
}

/* Filtres */
.filters-card {
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.filters-card .card {
  border-radius: 1rem;
}

.filters-card .card-header {
  border-radius: 1rem 1rem 0 0;
}

/* Tableau */
.table-container .card {
  border-radius: 1rem;
  overflow: hidden;
}

.table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.table th.sortable:hover {
  background-color: rgba(255,255,255,0.1) !important;
}

.student-row {
  transition: all 0.2s ease;
}

.student-row:hover {
  background-color: rgba(102, 126, 234, 0.05);
  transform: translateX(2px);
}

/* Cartes étudiants */
.student-card {
  transition: all 0.3s ease;
  border-radius: 1rem;
  overflow: hidden;
}

.student-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.student-card .card-header {
  border-radius: 1rem 1rem 0 0;
}

.bg-gradient-primary {
  background: var(--primary-gradient);
}

/* Badges personnalisés */
.badge.bg-primary {
  background: linear-gradient(45deg, #667eea, #764ba2) !important;
  border: none;
  font-size: 0.75rem;
  padding: 0.4rem 0.8rem;
  font-weight: 500;
}

.badge.rounded-pill {
  border-radius: 50rem !important;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.student-row, .student-card {
  animation: fadeInUp 0.3s ease forwards;
}

/* Boutons améliorés */
.btn {
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-primary {
  background: var(--primary-gradient);
  border: none;
}

.btn-group .btn {
  border-radius: 0.375rem;
}

/* Pagination */
.pagination .page-link {
  border-radius: 0.5rem;
  margin: 0 0.1rem;
  border: 1px solid #e9ecef;
  color: #667eea;
}

.pagination .page-link:hover {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
}

.pagination .page-item.active .page-link {
  background: var(--primary-gradient);
  border-color: transparent;
}

/* Responsive */
@media (max-width: 768px) {
  .header-section {
    margin: -1rem -1rem 2rem -1rem;
    padding: 1.5rem 1rem;
  }

  .stat-number {
    font-size: 2rem;
  }

  .filters-card .row {
    gap: 0.5rem;
  }

  .btn-group {
    background-color: transparent;
    flex-direction: column;
  }

  .btn-group .btn {
    border-radius: 0.375rem !important;
    margin-bottom: 0.25rem;
  }
}

/* Chargement */
.spinner-border {
  width: 3rem;
  height: 3rem;
}

/* États vides */
.text-muted i.fa-3x {
  opacity: 0.3;
}

/* Input groups améliorés */
.input-group-text {
  border-radius: 0.5rem 0 0 0.5rem;
}

.input-group .form-control {
  border-radius: 0 0.5rem 0.5rem 0;
}

.input-group .btn {
  border-radius: 0 0.5rem 0.5rem 0;
}

/* Tooltips */
[data-bs-toggle="tooltip"] {
  cursor: pointer;
}

/* Sélection multiple */
.form-check-input:checked {
  background-color: #667eea;
  border-color: #667eea;
}

/* Cartes avec gradient */
.card {
  border: none;
}

.card-header.bg-light {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

/* Animation pour les statistiques */
.stat-card .stat-number {
  animation: countUp 1s ease-out;
}

@keyframes countUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Améliorations des filtres */
.filters-card .form-label {
  color: #495057;
  font-size: 0.875rem;
}

.form-control:focus, .form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* États des badges de niveau */
.badge.bg-success {
  background: linear-gradient(45deg, #28a745, #20c997) !important;
}

.badge.bg-warning {
  background: linear-gradient(45deg, #ffc107, #fd7e14) !important;
  color: #212529 !important;
}

.badge.bg-danger {
  background: linear-gradient(45deg, #dc3545, #e83e8c) !important;
}

.badge.bg-dark {
  background: linear-gradient(45deg, #343a40, #6c757d) !important;
}

.badge.bg-secondary {
  background: linear-gradient(45deg, #6c757d, #adb5bd) !important;
}

/* Amélioration des cartes de vue */
.cards-container .card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cards-container .card:hover {
  box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
}

/* Scrollbar personnalisée */
.table-responsive::-webkit-scrollbar {
  height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
