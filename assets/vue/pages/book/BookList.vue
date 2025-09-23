<template>
  <div class="container py-4" lang="fr">
    <!-- Header & actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h1 class="h3 mb-0">Livres</h1>
      <div class="d-flex gap-2">
        <a :href="$routing.generate('book_new')" class="btn btn-primary">
          <i class="fas fa-plus"></i> Nouveau livre
        </a>
      </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow-sm p-3 mb-3">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Recherche</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input
                type="text"
                class="form-control"
                v-model.trim="filters.q"
                placeholder="Nom ou description…"
            />
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Niveau</label>
          <select class="form-select" v-model="filters.level">
            <option value="">Tous</option>
            <option v-for="opt in levelOptionsArabic" :key="opt" :value="opt">{{ opt }}</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Prix min (€)</label>
          <input type="number" step="0.01" min="0" class="form-control" v-model.number="filters.minPrice">
        </div>

        <div class="col-md-2">
          <label class="form-label">Prix max (€)</label>
          <input type="number" step="0.01" min="0" class="form-control" v-model.number="filters.maxPrice">
        </div>

        <div class="col-md-1 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="withImage" v-model="filters.withImage">
            <label class="form-check-label" for="withImage">Image</label>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-3">
          <label class="form-label">Trier par</label>
          <select class="form-select" v-model="sort.by">
            <option value="name">Nom</option>
            <option value="salePrice">Prix de vente</option>
            <option value="purchasePrice">Prix d'achat</option>
            <option value="level">Niveau</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Ordre</label>
          <select class="form-select" v-model="sort.dir">
            <option value="asc">Croissant</option>
            <option value="desc">Décroissant</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-outline-secondary w-100" @click="resetFilters">
            Réinitialiser
          </button>
        </div>
      </div>
    </div>

    <!-- Résumé -->
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="text-muted small">
        {{ filteredBooks.length }} résultat{{ filteredBooks.length > 1 ? 's' : '' }}
        <span v-if="filters.q"> • recherche « {{ filters.q }} »</span>
        <span v-if="filters.level"> • niveau « {{ filters.level }} »</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <label class="small text-muted">Par page</label>
        <select class="form-select form-select-sm" style="width:auto" v-model.number="page.size">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
        </select>
      </div>
    </div>

    <!-- Tableau -->
    <div class="table-responsive card shadow-sm">
      <table class="table align-middle mb-0">
        <thead class="table-light">
        <tr>
          <th style="width: 72px;">Image</th>
          <th>Nom</th>
          <th class="d-none d-md-table-cell">Description</th>
          <th>Niveau</th>
          <th class="text-end">Prix achat (€)</th>
          <th class="text-end">Prix vente (€)</th>
          <th style="width: 160px;" class="text-end">Actions</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="b in paginated" :key="b.id" class="align-middle">
          <td>
            <div class="thumb">
              <img
                  v-if="b.imageName"
                  :src="imageUrl(b.imageName)"
                  :alt="b.name"
                  loading="lazy"
              />
              <div v-else class="thumb-placeholder">
                <i class="fas fa-image"></i>
              </div>
            </div>
          </td>
          <td>
            <div class="fw-semibold">{{ b.name || '—' }}</div>
            <div class="text-muted small d-md-none">{{ truncate(b.description, 64) }}</div>
          </td>
          <td class="d-none d-md-table-cell text-truncate" style="max-width: 380px;">
            <span class="text-muted">{{ b.description || '—' }}</span>
          </td>
          <td>
            <span class="badge rounded-pill bg-gradient level-pill">{{ b.level || '—' }}</span>
          </td>
          <td class="text-end">{{ asMoney(b.purchasePrice) }}</td>
          <td class="text-end fw-semibold">{{ asMoney(b.salePrice) }}</td>
          <td class="text-end">
            <div class="btn-group">
              <a
                  class="btn btn-sm btn-outline-primary"
                  :href="$routing.generate('book_edit', { id: b.id })"
                  title="Modifier"
              >
                <i class="fas fa-edit"></i>
              </a>
              <a
                  v-if="b.imageName"
                  class="btn btn-sm btn-outline-secondary"
                  :href="imageUrl(b.imageName)"
                  target="_blank"
                  title="Voir l'image"
              >
                <i class="fas fa-external-link-alt"></i>
              </a>
            </div>
          </td>
        </tr>

        <tr v-if="paginated.length === 0">
          <td colspan="7" class="text-center py-5 text-muted">
            <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
            Aucun livre ne correspond à vos filtres.
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <nav v-if="pagesCount > 1" class="mt-3">
      <ul class="pagination justify-content-center mb-0">
        <li :class="['page-item', { disabled: page.index === 1 }]" @click="goTo(1)">
          <span class="page-link">&laquo;</span>
        </li>
        <li :class="['page-item', { disabled: page.index === 1 }]" @click="goTo(page.index-1)">
          <span class="page-link">Préc.</span>
        </li>

        <li v-for="n in pageWindow" :key="n" :class="['page-item', { active: n === page.index }]" @click="goTo(n)">
          <span class="page-link">{{ n }}</span>
        </li>

        <li :class="['page-item', { disabled: page.index === pagesCount }]" @click="goTo(page.index+1)">
          <span class="page-link">Suiv.</span>
        </li>
        <li :class="['page-item', { disabled: page.index === pagesCount }]" @click="goTo(pagesCount)">
          <span class="page-link">&raquo;</span>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script>
export default {
  name: "BookList",
  props: {
    bookList: { type: Array, required: true }
  },
  data() {
    return {
      // mêmes options que création/édition
      levelOptionsArabic: [
        'CP','N0','N1_1','N1_2','N2_1','N2_2','N3_1','N3_2',
        'N4_1','N4_2','N5_1','N5_2','N6_1','N6_2','Adolescent','Adult'
      ],
      filters: {
        q: "",
        level: "",
        minPrice: null,
        maxPrice: null,
        withImage: false,
      },
      sort: { by: "name", dir: "asc" },
      page: { index: 1, size: 10 },
    };
  },
  computed: {
    normalized() {
      // sécurise types prix pour les tris/filtres
      return (Array.isArray(this.bookList) ? this.bookList : []).map(b => ({
        ...b,
        _sale: this.toNumber(b.salePrice),
        _purchase: this.toNumber(b.purchasePrice),
        _name: (b.name || '').toString().toLowerCase(),
        _desc: (b.description || '').toString().toLowerCase(),
      }));
    },
    filteredBooks() {
      const q = this.filters.q.trim().toLowerCase();
      return this.normalized.filter(b => {
        if (q && !(b._name.includes(q) || b._desc.includes(q))) return false;
        if (this.filters.level && b.level !== this.filters.level) return false;
        if (this.filters.withImage && !b.imageName) return false;
        if (this.filters.minPrice != null && b._sale != null && b._sale < this.filters.minPrice) return false;
        if (this.filters.maxPrice != null && b._sale != null && b._sale > this.filters.maxPrice) return false;
        return true;
      });
    },
    sorted() {
      const arr = [...this.filteredBooks];
      const by = this.sort.by;
      const dir = this.sort.dir === 'desc' ? -1 : 1;
      arr.sort((a, b) => {
        let va, vb;
        switch (by) {
          case 'salePrice':     va = a._sale ?? 0;     vb = b._sale ?? 0; break;
          case 'purchasePrice': va = a._purchase ?? 0; vb = b._purchase ?? 0; break;
          case 'level':         va = (a.level || '');  vb = (b.level || ''); break;
          default:              va = (a._name || '');  vb = (b._name || '');
        }
        if (va < vb) return -1 * dir;
        if (va > vb) return  1 * dir;
        return 0;
      });
      return arr;
    },
    pagesCount() {
      return Math.max(1, Math.ceil(this.sorted.length / this.page.size));
    },
    paginated() {
      const start = (this.page.index - 1) * this.page.size;
      return this.sorted.slice(start, start + this.page.size);
    },
    pageWindow() {
      // petite fenêtre de pagination (±2 autour)
      const total = this.pagesCount;
      const cur = this.page.index;
      const from = Math.max(1, cur - 2);
      const to = Math.min(total, cur + 2);
      return Array.from({ length: to - from + 1 }, (_, i) => from + i);
    }
  },
  watch: {
    // reset page sur changement filtrage/tri
    filters: {
      deep: true,
      handler() { this.page.index = 1; }
    },
    'sort.by'() { this.page.index = 1; },
    'sort.dir'() { this.page.index = 1; },
    'page.size'() { this.page.index = 1; },
  },
  methods: {
    imageUrl(imageName) {
      return `/uploads/books/${imageName}`;
    },
    truncate(s, n = 80) {
      if (!s) return '';
      const str = String(s);
      return str.length > n ? str.slice(0, n - 1) + '…' : str;
    },
    asMoney(v) {
      const n = this.toNumber(v);
      return n == null ? '—' : n.toFixed(2).replace('.', ',');
    },
    toNumber(v) {
      if (v == null) return null;
      const n = Number(v);
      return Number.isFinite(n) ? n : null;
    },
    resetFilters() {
      this.filters = { q: "", level: "", minPrice: null, maxPrice: null, withImage: false };
      this.sort = { by: "name", dir: "asc" };
    },
    goTo(n) {
      if (n < 1 || n > this.pagesCount) return;
      this.page.index = n;
      // scroll top table (optionnel)
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    exportCsv() {
      const headers = ['id','name','description','imageName','salePrice','purchasePrice','level'];
      const rows = this.sorted.map(b => ([
        b.id, this.csvSafe(b.name), this.csvSafe(b.description),
        b.imageName || '', b.salePrice || '', b.purchasePrice || '', b.level || ''
      ]));
      const csv = [headers.join(';')]
          .concat(rows.map(r => r.join(';')))
          .join('\n');

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `books_export_${new Date().toISOString().slice(0,10)}.csv`;
      a.click();
      URL.revokeObjectURL(url);
    },
    csvSafe(val) {
      if (val == null) return '';
      const s = String(val).replace(/"/g, '""');
      // si contient ; ou retour ligne, encapsule
      return /[;\n"]/.test(s) ? `"${s}"` : s;
    },
  }
};
</script>

<style scoped>
/* vignettes */
.thumb {
  width: 56px;
  height: 56px;
  border-radius: 8px;
  overflow: hidden;
  background: #f1f5f9;
  display: grid;
  place-items: center;
  border: 1px solid #e2e8f0;
}
.thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb-placeholder {
  color: #94a3b8;
  font-size: 1.2rem;
}

/* pastille niveau */
.level-pill {
  background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
  color: #334155;
  border: 1px solid #bbdefb;
  font-weight: 600;
}

/* table */
.table > :not(caption) > * > * { vertical-align: middle; }
.table td, .table th { white-space: nowrap; }
.table td.text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* pagination */
.page-item { cursor: pointer; }
.page-item.disabled .page-link { cursor: not-allowed; }
</style>
