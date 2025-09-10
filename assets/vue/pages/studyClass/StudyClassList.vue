<template>
  <div class="container-fluid px-4 py-3">
    <!-- En-tête -->
    <div class="header-section mb-5">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h1 class="display-6 fw-bold text-gradient mb-2">
            <i class="fas fa-chalkboard me-3"></i>
            Liste des Classes
          </h1>
          <p class="text-muted mb-0">Gestion et planification des classes</p>
        </div>
        <a :href="$routing.generate('app_study_class_new')" class="btn btn-primary btn-lg shadow-lg">
          <i class="fas fa-plus me-2"></i> Nouvelle Classe
        </a>
      </div>
    </div>

    <!-- Filtres -->
    <!-- Filtres -->
    <div class="filters-card mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
          <h5 class="mb-0 d-flex align-items-center gap-2">
            <i class="fas fa-filter text-primary"></i>
            Filtres de recherche
          </h5>
        </div>

        <div class="card-body">
          <!-- ligne 1 : recherche globale seule -->
          <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-6">
              <label class="form-label fw-semibold">Recherche générale</label>
              <div class="input-group">
            <span class="input-group-text bg-light border-end-0">
              <i class="fas fa-search text-muted"></i>
            </span>
                <input
                    v-model.trim="searchTerm"
                    type="text"
                    class="form-control border-start-0"
                    placeholder="Nom, spécialité, niveau, professeur, type…"
                    @input="debounceSearch"
                />
                <button
                    v-if="searchTerm"
                    class="btn btn-outline-secondary"
                    @click="clearSearch"
                    type="button"
                    title="Effacer la recherche"
                >
                  <i class="fas fa-times"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- ligne 2 : 5 filtres en grille -->
          <div class="row g-3 mt-0">
            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label fw-semibold">Jour</label>
              <select v-model="selectedDay" class="form-select">
                <option value="">Tous</option>
                <option v-for="d in dayOptions" :key="d || 'empty'" :value="d">
                  {{ d || 'Non défini' }}
                </option>
              </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label fw-semibold">Spécialité</label>
              <select v-model="selectedSpeciality" class="form-select">
                <option value="">Toutes</option>
                <option v-for="sp in specialityOptions" :key="sp || 'empty'" :value="sp">
                  {{ sp || 'Non définie' }}
                </option>
              </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label fw-semibold">Niveau</label>
              <select v-model="selectedLevel" class="form-select">
                <option value="">Tous</option>
                <option v-for="lv in levelOptions" :key="lv || 'empty'" :value="lv">
                  {{ lv || 'Non défini' }}
                </option>
              </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label fw-semibold">Type</label>
              <select v-model="selectedType" class="form-select">
                <option value="">Tous</option>
                <option v-for="t in typeOptions" :key="t || 'empty'" :value="t">
                  {{ t || 'Non défini' }}
                </option>
              </select>
            </div>
          </div>

          <!-- ligne 3 : profs + créneau + actions alignées -->
          <div class="row g-3 mt-1 align-items-end">
            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label fw-semibold">Professeur (état)</label>
              <select v-model="selectedTeacher" class="form-select" :disabled="!!selectedTeacherId">
                <option value="">Tous</option>
                <option value="with">Avec professeur</option>
                <option value="without">Sans professeur</option>
              </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-4">
              <label class="form-label fw-semibold">Professeur (précis)</label>
              <small class="text-muted d-block">
                Si un professeur est sélectionné, le filtre “avec/sans” est ignoré.
              </small>
              <select v-model.number="selectedTeacherId" class="form-select">
                <option :value="''">Tous</option>
                <option v-for="opt in teacherOptions" :key="opt.id" :value="opt.id">
                  {{ opt.label }}
                </option>
              </select>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label fw-semibold">Créneau horaire</label>
              <select v-model="selectedSlot" class="form-select">
                <option value="">Tous</option>
                <option value="morning">Matin (06:00–12:00)</option>
                <option value="afternoon">Après-midi (12:00–18:00)</option>
                <option value="evening">Soir (18:00–23:00)</option>
                <option value="none">Non défini</option>
              </select>
            </div>

            <!-- Actions à droite, bien alignées au bas -->
            <div class="col-12 col-lg-2">
              <div class="filters-actions d-flex gap-2 justify-content-lg-end">
                <select v-model.number="itemsPerPage" class="form-select w-auto">
                  <option :value="10">10 / page</option>
                  <option :value="20">20 / page</option>
                  <option :value="50">50 / page</option>
                  <option :value="filteredClasses.length || 1">Tout</option>
                </select>

                <div class="btn-group" role="group">
                  <input type="radio" class="btn-check" name="viewType" id="tableView" v-model="viewType" value="table">
                  <label class="btn btn-outline-primary" for="tableView" title="Tableau">
                    <i class="fas fa-table"></i>
                  </label>
                  <input type="radio" class="btn-check" name="viewType" id="cardView" v-model="viewType" value="card">
                  <label class="btn btn-outline-primary" for="cardView" title="Cartes">
                    <i class="fas fa-th-large"></i>
                  </label>
                </div>

                <button class="btn btn-outline-secondary" @click="clearAllFilters">
                  <i class="fas fa-eraser me-1"></i>
                </button>

                <button class="btn btn-outline-info" @click="exportToCSV">
                  <i class="fas fa-download"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="stat-card bg-primary">
          <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
          <div class="stat-content">
            <h3 class="stat-number">{{ filteredClasses.length }}</h3>
            <p class="stat-label">Classes affichées</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-success">
          <div class="stat-icon"><i class="fas fa-book"></i></div>
          <div class="stat-content">
            <h3 class="stat-number">{{ specialityOptions.length }}</h3>
            <p class="stat-label">Spécialités</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-info">
          <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
          <div class="stat-content">
            <h3 class="stat-number">{{ dayOptions.length }}</h3>
            <p class="stat-label">Jours distincts</p>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-warning">
          <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
          <div class="stat-content">
            <h3 class="stat-number">{{ teacherCount }}</h3>
            <p class="stat-label">Avec professeur</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Chargement -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Chargement…</span>
      </div>
      <p class="text-muted mt-2">Chargement des classes…</p>
    </div>

    <!-- Vue tableau -->
    <div v-else-if="viewType === 'table'" class="table-container">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-muted">
              {{ filteredClasses.length }} classe(s) trouvée(s)
              <span v-if="classes.length !== filteredClasses.length">
                sur {{ classes.length }}
              </span>
            </h6>
            <div class="d-flex gap-2">
              <button
                  class="btn btn-outline-primary btn-sm"
                  @click="sortBy('name')"
                  :class="{ active: sortField === 'name' }"
              >
                <i class="fas fa-sort me-1"></i>
                Nom
                <i v-if="sortField === 'name'"
                   :class="sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'"
                   class="ms-1"></i>
              </button>
              <button
                  class="btn btn-outline-primary btn-sm"
                  @click="sortBy('startHour')"
                  :class="{ active: sortField === 'startHour' }"
              >
                <i class="fas fa-sort me-1"></i>
                Heure
                <i v-if="sortField === 'startHour'"
                   :class="sortOrder === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'"
                   class="ms-1"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
            <tr>
              <th @click="sortBy('name')" class="sortable">
                Nom
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th @click="sortBy('speciality')" class="sortable">
                Spécialité
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th @click="sortBy('day')" class="sortable">
                Jour
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th @click="sortBy('startHour')" class="sortable">
                Heures
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th @click="sortBy('levelClass')" class="sortable">
                Niveau
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th @click="sortBy('classType')" class="sortable">
                Type
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th @click="sortBy('students')" class="sortable">
                Élèves
                <i class="fas fa-sort ms-1"></i>
              </th>
              <th>Professeur</th>
              <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="c in paginatedClasses" :key="c.id">
              <td class="fw-semibold">{{ c.name }}</td>
              <td>
                  <span class="badge bg-primary rounded-pill">
                    {{ c.speciality || 'Non définie' }}
                  </span>
              </td>
              <td>{{ c.day || 'Non défini' }}</td>
              <td>{{ formatTimeRange(c.startHour, c.endHour) }}</td>
              <td>
                  <span class="badge bg-secondary rounded-pill">
                    {{ c.levelClass || c.level || 'Non défini' }}
                  </span>
              </td>
              <td>
                  <span class="badge bg-warning rounded-pill text-dark">
                    {{ c.classType || 'Non défini' }}
                  </span>
              </td>
              <td>
                  <span class="badge bg-info rounded-pill">
                    {{ studentCount(c.id) }} élève{{ studentCount(c.id) > 1 ? 's' : '' }}
                  </span>
              </td>
              <td>
                  <span v-if="c.principalTeacher">
                    <i class="fas fa-user-tie me-1 text-primary"></i>
                    {{ formatTeacherName(c.principalTeacher) }}
                  </span>
                <span v-else class="text-muted">
                    <i class="fas fa-user-slash me-1"></i>
                    Aucun
                  </span>
              </td>
              <td class="text-center">
                <div class="btn-group">
                  <a
                      :href="$routing.generate('app_study_class_show', {id: c.id})"
                      class="btn btn-outline-info btn-sm"
                      title="Voir les détails"
                  >
                    <i class="fas fa-eye"></i>
                  </a>
                  <a
                      :href="$routing.generate('app_study_class_edit', {id: c.id})"
                      class="btn btn-outline-warning btn-sm"
                      title="Modifier"
                  >
                    <i class="fas fa-edit"></i>
                  </a>
                  <button
                      @click="confirmDelete(c)"
                      class="btn btn-outline-danger btn-sm"
                      title="Supprimer"
                      :disabled="isDeleting === c.id"
                  >
                    <i v-if="isDeleting === c.id" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="filteredClasses.length === 0 && !isLoading">
              <td colspan="8" class="text-center py-5 text-muted">
                <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
                <div>
                  <strong>Aucune classe trouvée</strong>
                </div>
                <div class="mt-2">
                  <small>Essayez de modifier vos critères de recherche</small>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="card-footer bg-white border-0">
          <nav aria-label="Pagination des classes">
            <ul class="pagination justify-content-center mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button
                    class="page-link"
                    @click="setPage(1)"
                    :disabled="currentPage === 1"
                    title="Première page"
                >
                  <i class="fas fa-angle-double-left"></i>
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button
                    class="page-link"
                    @click="setPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    title="Page précédente"
                >
                  <i class="fas fa-angle-left"></i>
                </button>
              </li>
              <li
                  v-for="p in visiblePages"
                  :key="p"
                  class="page-item"
                  :class="{ active: p === currentPage }"
              >
                <button class="page-link" @click="setPage(p)">{{ p }}</button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button
                    class="page-link"
                    @click="setPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    title="Page suivante"
                >
                  <i class="fas fa-angle-right"></i>
                </button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button
                    class="page-link"
                    @click="setPage(totalPages)"
                    :disabled="currentPage === totalPages"
                    title="Dernière page"
                >
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
        <div v-for="c in paginatedClasses" :key="c.id" class="col-md-6 col-xl-4">
          <div class="card class-card h-100 border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
              <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                  <i class="fas fa-chalkboard me-2"></i>
                  {{ c.name }}
                </h6>

                <div class="d-flex align-items-center gap-2">
                  <!-- Élèves -->
                  <span class="badge bg-info rounded-pill" :title="`${studentCount(c.id)} inscrit(s)`">
                    <i class="fas fa-user-graduate me-1"></i>
                    {{ studentCount(c.id) }}
                  </span>
                  <!-- Niveau -->
                  <span class="badge bg-secondary rounded-pill">
                    {{ c.levelClass || c.level || 'Non défini' }}
                  </span>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-6">
                  <small class="text-muted">Jour</small>
                  <p class="mb-0 fw-semibold">{{ c.day || 'Non défini' }}</p>
                </div>
                <div class="col-6">
                  <small class="text-muted">Heures</small>
                  <p class="mb-0 fw-semibold">{{ formatTimeRange(c.startHour, c.endHour) }}</p>
                </div>
                <div class="col-6">
                  <small class="text-muted">Spécialité</small>
                  <p class="mb-0">
                    <span class="badge bg-primary rounded-pill">
                      {{ c.speciality || 'Non définie' }}
                    </span>
                  </p>
                </div>
                <div class="col-6">
                  <small class="text-muted">Élèves</small>
                  <p class="mb-0 fw-semibold">
                    {{ studentCount(c.id) }} élève{{ studentCount(c.id) > 1 ? 's' : '' }}
                  </p>
                </div>
                <div class="col-6">
                  <small class="text-muted">Type</small>
                  <p class="mb-0">
                    <span class="badge bg-warning rounded-pill text-dark">
                      {{ c.classType || 'Non défini' }}
                    </span>
                  </p>
                </div>
                <div class="col-12">
                  <small class="text-muted">Professeur</small>
                  <p class="mb-0">
                    <span v-if="c.principalTeacher">
                      <i class="fas fa-user-tie me-1 text-primary"></i>
                      {{ formatTeacherName(c.principalTeacher) }}
                    </span>
                    <span v-else class="text-muted">
                      <i class="fas fa-user-slash me-1"></i>
                      Aucun professeur assigné
                    </span>
                  </p>
                </div>
              </div>
            </div>
            <div class="card-footer bg-light border-0">
              <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <a
                    :href="$routing.generate('app_study_class_show', {id: c.id})"
                    class="btn btn-outline-info btn-sm"
                >
                  <i class="fas fa-eye me-1"></i> Voir
                </a>
                <a
                    :href="$routing.generate('app_study_class_edit', {id: c.id})"
                    class="btn btn-outline-warning btn-sm"
                >
                  <i class="fas fa-edit me-1"></i> Modifier
                </a>
                <button
                    @click="confirmDelete(c)"
                    class="btn btn-outline-danger btn-sm"
                    :disabled="isDeleting === c.id"
                >
                  <i v-if="isDeleting === c.id" class="fas fa-spinner fa-spin me-1"></i>
                  <i v-else class="fas fa-trash me-1"></i>
                  Supprimer
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Message si aucune classe -->
        <div v-if="filteredClasses.length === 0 && !isLoading" class="col-12">
          <div class="text-center py-5">
            <i class="fas fa-search fa-3x mb-3 text-secondary"></i>
            <h5>Aucune classe trouvée</h5>
            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
          </div>
        </div>
      </div>

      <!-- Pagination pour les cartes -->
      <div v-if="totalPages > 1" class="mt-4">
        <nav aria-label="Pagination des classes">
          <ul class="pagination justify-content-center mb-0">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button
                  class="page-link"
                  @click="setPage(1)"
                  :disabled="currentPage === 1"
              >
                <i class="fas fa-angle-double-left"></i>
              </button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <button
                  class="page-link"
                  @click="setPage(currentPage - 1)"
                  :disabled="currentPage === 1"
              >
                <i class="fas fa-angle-left"></i>
              </button>
            </li>
            <li
                v-for="p in visiblePages"
                :key="p"
                class="page-item"
                :class="{ active: p === currentPage }"
            >
              <button class="page-link" @click="setPage(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button
                  class="page-link"
                  @click="setPage(currentPage + 1)"
                  :disabled="currentPage === totalPages"
              >
                <i class="fas fa-angle-right"></i>
              </button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <button
                  class="page-link"
                  @click="setPage(totalPages)"
                  :disabled="currentPage === totalPages"
              >
                <i class="fas fa-angle-double-right"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <!-- Modal de confirmation de suppression -->
    <div v-if="classToDelete" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmer la suppression</h5>
            <button type="button" class="btn-close" @click="cancelDelete"></button>
          </div>

          <div class="modal-body">
            <!-- Erreur locale visible DANS le modal -->
            <div v-if="deleteError" class="alert alert-danger py-2 px-3 mb-3">
              <i class="fas fa-exclamation-triangle me-1"></i>
              {{ deleteError }}
            </div>

            <p>Êtes-vous sûr de vouloir supprimer la classe :</p>
            <p class="fw-bold">{{ classToDelete.name }}</p>
            <p class="text-muted">Cette action est irréversible.</p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelDelete">Annuler</button>
            <button type="button" class="btn btn-danger" @click="deleteClass" :disabled="isDeleting">
              <i v-if="isDeleting" class="fas fa-spinner fa-spin me-1"></i>
              Supprimer
            </button>
          </div>
        </div>
      </div>
    </div>


    <!-- Alerte -->
    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mt-4" />
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "StudyClassList",
  components: { Alert },
  data() {
    return {
      classes: [],
      studentCountMap: {},
      isLoading: false,
      isDeleting: null,
      classToDelete: null,
      searchTimeout: null,
      deleteError: null,

      // filtres
      searchTerm: "",
      selectedDay: "",
      selectedSpeciality: "",
      selectedLevel: "",
      selectedType: "",
      selectedTeacher: "", // "", "with", "without"
      selectedSlot: "",    // "", "morning", "afternoon", "evening", "none"
      selectedTeacherId: "",

      // vue & pagination
      viewType: "table",
      currentPage: 1,
      itemsPerPage: 20,

      // tri
      sortField: "name",
      sortOrder: "asc",

      // alertes
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    teacherOptions() {
      const map = new Map(); // id -> name
      this.classes.forEach(c => {
        const t = c.principalTeacher;
        if (t && (t.id || t.firstName || t.lastName)) {
          const name = `${t.firstName || ''} ${t.lastName || ''}`.trim() || `Prof #${t.id}`;
          if (t.id != null && !map.has(t.id)) map.set(t.id, name);
        }
      });
      return [...map.entries()]
          .map(([id, label]) => ({ id, label }))
          .sort((a,b) => a.label.localeCompare(b.label, 'fr'));
    },
    // options pour filtres avec gestion des valeurs vides
    dayOptions() {
      const days = this.classes.map(c => c.day).filter(d => d);
      const uniqueDays = [...new Set(days)];
      return uniqueDays.sort((a, b) => {
        const dayOrder = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        return dayOrder.indexOf(a) - dayOrder.indexOf(b);
      });
    },
    specialityOptions() {
      const specialities = this.classes.map(c => c.speciality).filter(s => s);
      return [...new Set(specialities)].sort((a, b) => a.localeCompare(b, "fr"));
    },
    levelOptions() {
      const levels = this.classes.map(c => c.levelClass || c.level).filter(l => l);
      return [...new Set(levels)].sort((a, b) => a.localeCompare(b, "fr"));
    },
    typeOptions() {
      const types = this.classes.map(c => c.classType).filter(t => t);
      return [...new Set(types)].sort((a, b) => a.localeCompare(b, "fr"));
    },
    teacherCount() {
      return this.classes.filter(c => c.principalTeacher &&
          (c.principalTeacher.firstName || c.principalTeacher.lastName)).length;
    },

    // filtrage + tri amélioré
    filteredClasses() {
      const normalize = v => (v || "").toString().normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "").toLowerCase();
      const query = normalize(this.searchTerm);

      let filtered = this.classes.filter(c => {
        // recherche texte globale
        if (query) {
          const searchFields = [
            c.name,
            c.speciality,
            c.classType,
            c.levelClass || c.level,
            c.day,
            c.principalTeacher?.firstName,
            c.principalTeacher?.lastName
          ].filter(Boolean);

          const matchText = searchFields.some(field =>
              normalize(field).includes(query)
          );

          if (!matchText) return false;
        }

        // filtres spécifiques
        if (this.selectedDay && c.day !== this.selectedDay) return false;
        if (this.selectedSpeciality && c.speciality !== this.selectedSpeciality) return false;
        if (this.selectedLevel && (c.levelClass || c.level) !== this.selectedLevel) return false;
        if (this.selectedType && c.classType !== this.selectedType) return false;

        // filtre professeur
        // filtre professeur précis (prioritaire)
        if (this.selectedTeacherId !== "" && this.selectedTeacherId != null) {
          const teacherId = c.principalTeacher?.id;
          if (teacherId !== this.selectedTeacherId) return false;
        } else {
          // filtre "avec/sans professeur"
          const hasTeacher = !!(c.principalTeacher?.firstName || c.principalTeacher?.lastName || c.principalTeacher?.id);
          if (this.selectedTeacher === "with" && !hasTeacher) return false;
          if (this.selectedTeacher === "without" && hasTeacher) return false;
        }


        // filtre créneau
        if (this.selectedSlot) {
          const slot = this.timeSlot(c.startHour);
          if (this.selectedSlot !== slot) return false;
        }

        return true;
      });

      // tri
      if (this.sortField) {
        filtered.sort((a, b) => {
          let valueA = this.getSortValue(a, this.sortField);
          let valueB = this.getSortValue(b, this.sortField);

          if (valueA === null && valueB === null) return 0;
          if (valueA === null) return 1;
          if (valueB === null) return -1;

          let result;
          if (typeof valueA === 'string') {
            result = valueA.localeCompare(valueB, 'fr');
          } else {
            result = valueA < valueB ? -1 : valueA > valueB ? 1 : 0;
          }

          return this.sortOrder === 'asc' ? result : -result;
        });
      }

      return filtered;
    },

    // pagination
    totalPages() {
      return Math.ceil(this.filteredClasses.length / this.itemsPerPage) || 1;
    },
    paginatedClasses() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      return this.filteredClasses.slice(start, start + this.itemsPerPage);
    },
    visiblePages() {
      const pages = [];
      const total = this.totalPages;
      const current = this.currentPage;

      // Logique pour afficher les pages visibles
      let start = Math.max(1, current - 2);
      let end = Math.min(total, current + 2);

      // Ajuster si on est près du début ou de la fin
      if (end - start < 4) {
        if (start === 1) {
          end = Math.min(total, start + 4);
        } else if (end === total) {
          start = Math.max(1, end - 4);
        }
      }

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    },
  },

  watch: {
    filteredClasses() {
      this.currentPage = 1;
    },
    totalPages(newVal) {
      if (this.currentPage > newVal) {
        this.currentPage = newVal || 1;
      }
    },
    searchTerm() {
      this.debounceSearch();
    }
  },

  methods: {
    async fetchClasses() {
      this.isLoading = true;
      try {
        const response = await this.$axios.get(this.$routing.generate("study_class_list"));
        this.classes = response.data.studyClass || [];
        this.studentCountMap = response.data.studentCountMap || {};
        this.showAlert("Classes chargées avec succès", "success");
      } catch (error) {
        console.error('Erreur lors du chargement des classes:', error);
        this.showAlert("Erreur lors du chargement des classes", "danger");
      } finally {
        this.isLoading = false;
      }
    },

    // studentCountMap peut être null
    studentCount(id) {
      // les clés du map peuvent être des strings -> on gère les deux
      if (!this.studentCountMap) return 0;
      return this.studentCountMap[id] ??
          this.studentCountMap[String(id)] ??
          0;
    },

    // Fonction utilitaire pour obtenir la valeur de tri
    getSortValue(item, field) {
      switch (field) {
        case 'name':
          return item.name || '';
        case 'speciality':
          return item.speciality || '';
        case 'day':
          return item.day || '';
        case 'levelClass':
          return item.levelClass || item.level || '';
        case 'classType':
          return item.classType || '';
        case 'startHour':
          return this.parseTime(item.startHour);
        case 'students':
          return this.studentCount(item.id);
        default:
          return item[field] || '';
      }
    },

    // Tri générique amélioré
    sortBy(field) {
      if (this.sortField === field) {
        this.sortOrder = this.sortOrder === "asc" ? "desc" : "asc";
      } else {
        this.sortField = field;
        this.sortOrder = "asc";
      }
    },

    // Pagination améliorée
    setPage(page) {
      const targetPage = Math.min(Math.max(1, page), this.totalPages || 1);
      if (targetPage !== this.currentPage) {
        this.currentPage = targetPage;
        this.$nextTick(() => {
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
      }
    },

    // Helpers pour l'heure
    formatTimeRange(start, end) {
      const startTime = this.formatTime(start);
      const endTime = this.formatTime(end);

      if (!startTime && !endTime) return "Non défini";
      if (!startTime) return `– ${endTime}`;
      if (!endTime) return `${startTime} –`;
      return `${startTime} – ${endTime}`;
    },

    formatTime(isoString) {
      if (!isoString) return "";
      try {
        const date = new Date(isoString);
        if (isNaN(date.getTime())) return "";

        const hours = String(date.getHours()).padStart(2, "0");
        const minutes = String(date.getMinutes()).padStart(2, "0");
        return `${hours}:${minutes}`;
      } catch (error) {
        return "";
      }
    },

    parseTime(isoString) {
      if (!isoString) return null;
      try {
        const date = new Date(isoString);
        if (isNaN(date.getTime())) return null;
        return date.getHours() * 60 + date.getMinutes();
      } catch (error) {
        return null;
      }
    },

    timeSlot(isoString) {
      const minutes = this.parseTime(isoString);
      if (minutes === null) return "none";

      if (minutes < 12 * 60) return "morning";    // 00:00 - 11:59
      if (minutes < 18 * 60) return "afternoon";  // 12:00 - 17:59
      return "evening";                           // 18:00 - 23:59
    },

    // Formatage du nom du professeur
    formatTeacherName(teacher) {
      if (!teacher) return "";
      const firstName = teacher.firstName || "";
      const lastName = teacher.lastName || "";
      return `${firstName} ${lastName}`.trim() || "Nom non défini";
    },

    // Gestion des filtres
    clearAllFilters() {
      this.searchTerm = "";
      this.selectedDay = "";
      this.selectedSpeciality = "";
      this.selectedLevel = "";
      this.selectedType = "";
      this.selectedTeacher = "";
      this.selectedTeacherId = "";
      this.selectedSlot = "";
      this.showAlert("Filtres effacés", "info");
    },

    clearSearch() {
      this.searchTerm = "";
    },

    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        // La recherche se fait automatiquement via le computed
      }, 300);
    },

    // Actions sur les classes
    confirmDelete(classItem) {
      this.classToDelete = classItem;
      this.deleteError = null
    },

    cancelDelete() {
      this.classToDelete = null;
      this.isDeleting = null;
      this.deleteError = null
    },

    async deleteClass() {
      if (!this.classToDelete) return;
      this.isDeleting = this.classToDelete.id;
      this.deleteError = null;

      try {
        const res = await this.$axios.delete(
            this.$routing.generate("app_study_class_delete", { id: this.classToDelete.id }),
            { headers: { Accept: "application/json" } }
        );

        // maj locale
        this.classes = this.classes.filter(c => c.id !== this.classToDelete.id);

        // succès (message backend prioritaire)
        const successMsg = res?.data?.message || `Classe "${this.classToDelete.name}" supprimée avec succès`;
        this.showAlert(successMsg, "success");

        // ferme le modal
        this.cancelDelete();

        // réajuste pagination
        if (this.paginatedClasses.length === 0 && this.currentPage > 1) {
          this.setPage(this.currentPage - 1);
        }
      } catch (error) {
        console.log(error)
        // récupère le message backend
        const msg = this.extractBackendMessage(
            error,
            "Impossible de supprimer la classe. Réessayez plus tard."
        );

        // 1) montre-le dans le modal (visible immédiatement)
        this.deleteError = msg;

        // 2) et/ou alerte globale (facultatif)
        this.showAlert(msg, "danger");

        // Option : si tu préfères FERMER le modal même en erreur et scroller vers l’alerte :
        // this.cancelDelete();
        // this.$nextTick(() => window.scrollTo({ top: 0, behavior: "smooth" }));
      } finally {
        this.isDeleting = null;
      }
    },

    extractBackendMessage(error, fallback = "Erreur lors de la suppression de la classe") {
      const data = error?.response?.data;
      if (typeof data?.message === "string" && data.message.trim()) return data.message;
      if (typeof data?.error === "string" && data.error.trim()) return data.error;
      if (typeof data?.detail === "string" && data.detail.trim()) return data.detail;
      if (Array.isArray(data?.errors) && data.errors.length) {
        const first = data.errors[0];
        if (typeof first === "string") return first;
        if (typeof first?.message === "string") return first.message;
      }
      if (typeof data?.title === "string" && data.title.trim()) return data.title;
      return fallback;
    },
    // Export CSV
    exportToCSV() {
      try {
        const headers = [
          'Nom', 'Spécialité', 'Jour', 'Heure début', 'Heure fin',
          'Niveau', 'Type', 'Professeur'
        ];

        const csvContent = [
          headers.join(','),
          ...this.filteredClasses.map(c => [
            `"${(c.name || '').replace(/"/g, '""')}"`,
            `"${(c.speciality || '').replace(/"/g, '""')}"`,
            `"${(c.day || '').replace(/"/g, '""')}"`,
            `"${this.formatTime(c.startHour)}"`,
            `"${this.formatTime(c.endHour)}"`,
            `"${(c.levelClass || c.level || '').replace(/"/g, '""')}"`,
            `"${(c.classType || '').replace(/"/g, '""')}"`,
            `"${this.formatTeacherName(c.principalTeacher).replace(/"/g, '""')}"`,
            this.studentCount(c.id)
          ].join(','))
        ].join('\n');

        const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `classes_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();

        this.showAlert("Export CSV réussi", "success");
      } catch (error) {
        console.error('Erreur lors de l\'export:', error);
        this.showAlert("Erreur lors de l'export", "danger");
      }
    },

    // Gestion des alertes
    showAlert(message, type) {
      this.messageAlert = message;
      this.typeAlert = type;

      // Auto-masquer après 5 secondes
      setTimeout(() => {
        this.messageAlert = null;
        this.typeAlert = null;
      }, 5000);
    },
  },

  mounted() {
    this.fetchClasses();

    // Gestion du redimensionnement pour la vue responsive
    window.addEventListener('resize', this.handleResize);
  },

  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize);
    clearTimeout(this.searchTimeout);
  },
};
</script>

<style>
/* Variables globales */
:root {
  --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
  --shadow-soft: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
  --shadow-medium: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12);
}
</style>

<style scoped>
/* En-tête */
.header-section {
  margin: -1rem -1.5rem 2rem;
  padding: 2rem 1.5rem;
  border-radius: 0 0 1rem 1rem;
  color: #fff;
}

.text-gradient {
  background: var(--primary-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.bg-gradient-primary {
  background: var(--primary-gradient);
}

/* Cartes de statistiques */
.stat-card {
  color: #fff;
  padding: 1.5rem;
  border-radius: 1rem;
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-medium);
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-card .stat-icon {
  position: absolute;
  top: 1rem;
  right: 1rem;
  opacity: 0.25;
  font-size: 1.75rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.stat-label {
  margin: 0;
  opacity: 0.9;
  font-size: 0.875rem;
}

.stat-card.bg-success { background: var(--success-gradient); }
.stat-card.bg-info { background: var(--info-gradient); }
.stat-card.bg-warning { background: var(--warning-gradient); }

/* Cartes */
.filters-card .card,
.table-container .card,
.cards-container .card {
  border-radius: 1rem;
  box-shadow: var(--shadow-soft);
}

.class-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.class-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-medium);
}

.class-card .card-header {
  background: var(--primary-gradient);
  color: #fff;
}

/* Table */
.table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: background-color 0.2s ease;
}

.table th.sortable:hover {
  background-color: rgba(255, 255, 255, 0.1) !important;
}

.table th.sortable.active {
  background-color: rgba(255, 255, 255, 0.15) !important;
}

.table-responsive {
  border-radius: 0.5rem;
}

/* Badges */
.badge.rounded-pill {
  border-radius: 50rem !important;
  font-weight: 500;
}

.badge.bg-primary {
  background: linear-gradient(45deg, #667eea, #764ba2) !important;
}

.badge.bg-warning {
  background: linear-gradient(45deg, #fa709a, #fee140) !important;
  color: #212529 !important;
}

.badge.bg-secondary {
  background: linear-gradient(45deg, #6c757d, #adb5bd) !important;
}

/* Pagination */
.pagination .page-link {
  border-radius: 0.5rem;
  margin: 0 0.1rem;
  color: #667eea;
  border: 1px solid #dee2e6;
  transition: all 0.2s ease;
}

.pagination .page-link:hover {
  background-color: #667eea;
  border-color: #667eea;
  color: white;
}

.pagination .page-item.active .page-link {
  background: var(--primary-gradient);
  border-color: transparent;
  color: white;
}

.pagination .page-item.disabled .page-link {
  opacity: 0.5;
}

/* Spinner de chargement */
.spinner-border {
  width: 3rem;
  height: 3rem;
}

/* Boutons */
.btn {
  transition: all 0.2s ease;
}

.btn:hover {
  transform: translateY(-1px);
}

.btn-group .btn-check:checked + .btn {
  background: var(--primary-gradient);
  border-color: transparent;
}

/* Formulaires */
.form-control:focus,
.form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group .form-control:focus {
  z-index: 3;
}

/* Messages d'état vide */
.text-center .fa-3x {
  opacity: 0.3;
}

/* Modal */
.modal.show {
  animation: fadeIn 0.15s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  border: none;
  border-radius: 1rem;
  box-shadow: var(--shadow-medium);
}

/* Responsive */
@media (max-width: 768px) {
  .header-section {
    text-align: center;
  }

  .header-section .d-flex {
    flex-direction: column;
    gap: 1rem;
  }

  .filters-card .row > * {
    margin-bottom: 0.5rem;
  }

  .stat-card {
    margin-bottom: 1rem;
  }

  .table-responsive {
    font-size: 0.875rem;
  }

  .btn-group {
    display: flex;
    flex-wrap: wrap;
  }
}

/* Animations */
@media (prefers-reduced-motion: no-preference) {
  .class-card,
  .stat-card,
  .btn {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
}

/* Accessibilité */
@media (prefers-reduced-motion: reduce) {
  * {
    transition: none !important;
    animation: none !important;
  }
}

/* Focus visible pour l'accessibilité */
.btn:focus-visible,
.form-control:focus-visible,
.form-select:focus-visible,
.page-link:focus-visible {
  outline: 2px solid #667eea;
  outline-offset: 2px;
}

/* même hauteur & bons espacements */
.filters-card .form-select,
.filters-card .form-control,
.filters-card .btn,
.filters-card .input-group-text {
  height: 42px;
}

.filters-card .btn i { line-height: 1; }

/* conteneur actions : colle le bas des contrôles, même en responsive */
.filters-actions {
  align-items: end;             /* aligne en bas */
}

@media (max-width: 991.98px) {
  .filters-actions {
    justify-content: flex-start; /* à gauche sur mobile */
    flex-wrap: wrap;
    gap: .5rem;
  }
}

/* petits polish */
.filters-card .card-header {
  border-radius: 1rem 1rem 0 0;
}
.filters-card .card {
  border-radius: 1rem;
}

</style>