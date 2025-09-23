<template>
  <div class="container my-5" lang="fr">
    <!-- En-tête -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <a :href="$routing.generate('app_book_list')" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Retour à la liste
      </a>
      <h1 class="text-primary mb-0 fw-semibold">Modifier le Livre</h1>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="messageType" class="mb-3" />

    <div class="row g-4">
      <!-- Colonne formulaire -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <form @submit.prevent="save" novalidate>
              <div class="row">
                <!-- Nom -->
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Nom du Livre</label>
                  <input
                      id="name"
                      type="text"
                      class="form-control"
                      :class="{'is-invalid': touched.name && !validators.name}"
                      v-model.trim="form.name"
                      required
                      maxlength="150"
                      autocomplete="off"
                  />
                  <div class="invalid-feedback">Le nom est requis (max 150 caractères).</div>
                </div>

                <!-- Niveau -->
                <div class="col-md-6 mb-3">
                  <label for="level" class="form-label">Niveau scolaire</label>
                  <select
                      id="level"
                      class="form-select"
                      :class="{'is-invalid': touched.level && !validators.level}"
                      v-model="form.level"
                      required
                      aria-describedby="levelHelp"
                  >
                    <option value="">-- Choisir un niveau --</option>
                    <option v-for="opt in levelOptionsArabic" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                  <div id="levelHelp" class="form-text">Sélectionnez le niveau pour le classement.</div>
                  <div class="invalid-feedback">Le niveau est requis.</div>
                </div>
              </div>

              <!-- Description -->
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea
                    id="description"
                    rows="3"
                    class="form-control"
                    v-model="form.description"
                    placeholder="Résumé, auteur(s), édition, remarques…"
                    maxlength="1000"
                ></textarea>
                <div class="form-text">{{ form.description?.length || 0 }}/1000 caractères</div>
              </div>

              <div class="row">
                <!-- Prix d'achat -->
                <div class="col-md-6 mb-3">
                  <label for="purchasePrice" class="form-label">Prix d’achat (€)</label>
                  <div class="input-group has-validation">
                    <span class="input-group-text"><i class="fas fa-euro-sign"></i></span>
                    <input
                        id="purchasePrice"
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        :class="{'is-invalid': touched.purchasePrice && !validators.purchasePrice}"
                        v-model.number="form.purchasePrice"
                        required
                        inputmode="decimal"
                    />
                    <div class="invalid-feedback">Prix d’achat invalide.</div>
                  </div>
                </div>

                <!-- Prix de vente -->
                <div class="col-md-6 mb-3">
                  <label for="salePrice" class="form-label">Prix de vente (€)</label>
                  <div class="input-group has-validation">
                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                    <input
                        id="salePrice"
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        :class="{'is-invalid': touched.salePrice && !validators.salePrice}"
                        v-model.number="form.salePrice"
                        required
                        inputmode="decimal"
                    />
                    <div class="invalid-feedback">
                      Prix de vente invalide (doit être ≥ prix d’achat).
                    </div>
                  </div>
                  <!-- Indicateur marge -->
                  <div class="small mt-2" :class="marginValue >= 0 ? 'text-success' : 'text-danger'">
                    <i :class="marginValue >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    Marge : <strong>{{ marginCurrency }}</strong>
                    <span v-if="isFinite(marginPercent)"> ({{ marginPercent.toFixed(0) }}%)</span>
                  </div>
                </div>
              </div>

              <!-- Upload: Dropzone -->
              <div class="mb-3">
                <label class="form-label d-flex align-items-center gap-2">
                  <i class="fas fa-images"></i> Image (drag & drop ou clic)
                </label>

                <div
                    class="dropzone border rounded-3 bg-light d-flex flex-column align-items-center justify-content-center text-center p-4 position-relative"
                    :class="{'dropzone--drag': isDragging}"
                    tabindex="0"
                    role="button"
                    aria-label="Déposer une image ici ou cliquer pour sélectionner un fichier"
                    @click="triggerFileDialog"
                    @keydown.enter.prevent="triggerFileDialog"
                    @dragover.prevent="onDragOver"
                    @dragleave.prevent="onDragLeave"
                    @drop.prevent="onDrop"
                >
                  <input
                      ref="fileInput"
                      id="image"
                      type="file"
                      accept="image/*"
                      class="visually-hidden"
                      @change="onFileChange"
                  />
                  <div class="mb-2">
                    <i class="fas fa-cloud-upload-alt fa-2x"></i>
                  </div>
                  <div class="fw-semibold">Glissez-déposez une image ici</div>
                  <div class="text-muted small">ou cliquez pour parcourir</div>
                  <div class="text-muted small mt-2">JPG/PNG recommandés, &lt; 4 Mo</div>
                </div>

                <!-- Infos fichier -->
                <div v-if="fileMeta" class="mt-2 small text-muted">
                  <i class="fas fa-file-image me-1"></i>
                  {{ fileMeta.name }} — {{ fileMeta.size }} — {{ fileMeta.type }}
                </div>

                <div v-if="uploadError" class="alert alert-danger mt-3 py-2 px-3">
                  <i class="fas fa-exclamation-triangle me-2"></i>{{ uploadError }}
                </div>
              </div>

              <!-- Boutons -->
              <div class="d-flex justify-content-between pt-2">
                <a :href="$routing.generate('app_book_list')" class="btn btn-outline-secondary">
                  Annuler
                </a>
                <button type="submit" class="btn btn-primary px-4" :disabled="isSaving || !formValid">
                  <i v-if="isSaving" class="fas fa-spinner fa-spin me-2"></i>
                  <i v-else class="fa fa-save me-2"></i>
                  {{ isSaving ? 'Sauvegarde...' : 'Enregistrer' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Colonne aperçu (sticky) -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm position-sticky" style="top: 1.5rem;">
          <div class="card-body p-3">
            <h5 class="mb-3 d-flex align-items-center gap-2">
              <i class="fas fa-image"></i> Aperçu de l’image
            </h5>

            <div class="ratio ratio-4x3 border rounded overflow-hidden bg-light">
              <img
                  v-if="previewUrl || currentImageUrl"
                  :src="previewUrl || currentImageUrl"
                  alt="Couverture"
                  class="w-100 h-100 object-fit-cover"
              />
              <div v-else class="d-flex align-items-center justify-content-center text-muted">
                <i class="fas fa-image me-2"></i> Aucune image
              </div>
            </div>

            <div class="mt-3 d-flex flex-wrap gap-2">
              <button
                  v-if="currentImageUrl || previewUrl"
                  type="button"
                  class="btn btn-outline-danger btn-sm"
                  @click="clearImage"
              >
                <i class="fas fa-times me-1"></i> Retirer l’image
              </button>
              <a
                  v-if="currentImageUrl"
                  :href="currentImageUrl"
                  target="_blank"
                  class="btn btn-outline-secondary btn-sm"
              >
                <i class="fas fa-external-link-alt me-1"></i> Ouvrir
              </a>
            </div>

            <hr />
            <div class="small text-muted">
              <div><strong>ID :</strong> {{ book.id }}</div>
              <div><strong>Nom actuel :</strong> {{ book.name }}</div>
              <div><strong>Niveau :</strong> {{ book.level }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

const MAX_IMAGE_MB = 4;
const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp"];

export default {
  name: "BookEdit",
  components: { Alert },
  props: {
    book: { type: Object, required: true },
  },
  data() {
    return {
      messageAlert: null,
      messageType: null,
      isSaving: false,
      isDragging: false,
      uploadError: null,
      fileMeta: null,
      levelOptionsArabic: [
        "CP",
        "N0",
        "N1_1",
        "N1_2",
        "N2_1",
        "N2_2",
        "N3_1",
        "N3_2",
        "N4_1",
        "N4_2",
        "N5_1",
        "N5_2",
        "N6_1",
        "N6_2",
        "Adolescent",
        "Adult",
      ],
      form: {
        name: "",
        description: "",
        level: "",
        purchasePrice: null,
        salePrice: null,
        image: null, // nouveau fichier si choisi
        removeImage: false, // retire l'image existante si true
      },
      previewUrl: null, // URL.createObjectURL du nouveau fichier
      touched: {
        name: false,
        level: false,
        purchasePrice: false,
        salePrice: false,
      },
    };
  },
  created() {
    // initialise form avec props
    this.form.name = this.book.name ?? "";
    this.form.description = this.book.description ?? "";
    this.form.level = this.book.level ?? "";
    this.form.purchasePrice =
        this.book.purchasePrice !== undefined && this.book.purchasePrice !== null
            ? Number(this.book.purchasePrice)
            : null;
    this.form.salePrice =
        this.book.salePrice !== undefined && this.book.salePrice !== null
            ? Number(this.book.salePrice)
            : null;
  },
  computed: {
    currentImageUrl() {
      const imageName = this.book?.imageName;
      return imageName ? `/uploads/books/${imageName}` : null;
    },
    validators() {
      const name = !!this.form.name && this.form.name.length <= 150;
      const level = !!this.form.level;
      const pp =
          this.form.purchasePrice !== null &&
          !isNaN(this.form.purchasePrice) &&
          this.form.purchasePrice >= 0;
      const sp =
          this.form.salePrice !== null &&
          !isNaN(this.form.salePrice) &&
          this.form.salePrice >= 0 &&
          (this.form.purchasePrice === null ||
              this.form.salePrice >= this.form.purchasePrice);
      return { name, level, purchasePrice: pp, salePrice: sp };
    },
    formValid() {
      const v = this.validators;
      return v.name && v.level && v.purchasePrice && v.salePrice && !this.uploadError;
    },
    marginValue() {
      const p = Number(this.form.purchasePrice ?? 0);
      const s = Number(this.form.salePrice ?? 0);
      return isFinite(p) && isFinite(s) ? s - p : 0;
    },
    marginPercent() {
      const p = Number(this.form.purchasePrice ?? 0);
      if (!p) return NaN;
      return (this.marginValue / p) * 100;
    },
    marginCurrency() {
      return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "EUR",
        maximumFractionDigits: 2,
      }).format(this.marginValue || 0);
    },
  },
  methods: {
    triggerFileDialog() {
      this.$refs.fileInput?.click();
    },
    onDragOver() {
      this.isDragging = true;
    },
    onDragLeave() {
      this.isDragging = false;
    },
    onDrop(e) {
      this.isDragging = false;
      const file = e.dataTransfer?.files?.[0];
      if (file) this.handleNewFile(file);
    },
    onFileChange(e) {
      const file = e.target.files?.[0] ?? null;
      if (file) this.handleNewFile(file);
    },
    handleNewFile(file) {
      this.uploadError = null;
      // validation: type & taille
      if (!ACCEPTED_TYPES.includes(file.type)) {
        this.uploadError = "Format non pris en charge. Utilisez JPG, PNG ou WebP.";
        return;
      }
      const sizeMb = file.size / (1024 * 1024);
      if (sizeMb > MAX_IMAGE_MB) {
        this.uploadError = `Fichier trop volumineux (${sizeMb.toFixed(
            1
        )} Mo). Limite : ${MAX_IMAGE_MB} Mo.`;
        return;
      }

      this.form.image = file;
      this.form.removeImage = false;

      // metadata d’affichage
      this.fileMeta = {
        name: file.name,
        size: `${sizeMb.toFixed(2)} Mo`,
        type: file.type.replace("image/", "").toUpperCase(),
      };

      // preview
      if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
      this.previewUrl = URL.createObjectURL(file);
    },
    clearImage() {
      // supprime l'image côté UI ; le backend supprimera si removeImage=true
      this.form.image = null;
      this.fileMeta = null;
      if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
      this.previewUrl = null;
      this.form.removeImage = true;
      this.uploadError = null;
    },
    blur(field) {
      this.touched[field] = true;
    },
    async save() {
      // marque tous les champs comme touchés pour afficher la validation
      Object.keys(this.touched).forEach((k) => (this.touched[k] = true));
      if (!this.formValid) {
        this.messageAlert = "Veuillez corriger les champs en surbrillance.";
        this.messageType = "danger";
        return;
      }

      this.isSaving = true;
      this.messageAlert = null;

      try {
        const formData = new FormData();
        formData.append("name", this.form.name);
        formData.append("description", this.form.description ?? "");
        formData.append("level", this.form.level ?? "");
        formData.append("purchasePrice", String(this.form.purchasePrice ?? ""));
        formData.append("salePrice", String(this.form.salePrice ?? ""));
        formData.append("removeImage", String(!!this.form.removeImage));
        if (this.form.image) formData.append("image", this.form.image);

        const url = this.$routing.generate("book_update", { id: this.book.id });
        const res = await this.$axios.post(url, formData, {
          headers: { "Content-Type": "multipart/form-data", Accept: "application/json" },
        });

        // succès
        this.messageAlert = "Livre enregistré avec succès.";
        this.messageType = "success";

        // mettre à jour l’aperçu si l’image a changé
        const newImage = res?.data?.imageName ?? null;
        if (typeof newImage !== "undefined") {
          this.book.imageName = newImage;
          if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
          }
          // on garde fileMeta pour l’info locale, mais on efface le fichier
          this.form.image = null;
        }
      } catch (err) {
        // erreurs backend lisibles
        console.error("Update error:", err?.response?.data ?? err);
        const msg =
            err?.response?.data?.message ||
            err?.response?.data?.detail ||
            "Erreur lors de la sauvegarde.";
        this.messageAlert = msg;
        this.messageType = "danger";
      } finally {
        this.isSaving = false;
      }
    },
  },
  watch: {
    "form.name"() {
      this.touched.name = true;
    },
    "form.level"() {
      this.touched.level = true;
    },
    "form.purchasePrice"() {
      this.touched.purchasePrice = true;
    },
    "form.salePrice"() {
      this.touched.salePrice = true;
    },
  },
  unmounted() {
    if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
  },
};
</script>

<style scoped>
.object-fit-cover { object-fit: cover; }

/* Dropzone moderne */
.dropzone {
  border: 2px dashed rgba(0, 0, 0, 0.15);
  transition: border-color 0.2s ease, background-color 0.2s ease, transform 0.05s ease;
  cursor: pointer;
  border-radius: 1rem;
}
.dropzone:hover {
  border-color: rgba(0, 123, 255, 0.4);
  background-color: rgba(0, 123, 255, 0.04);
}
.dropzone--drag {
  border-color: rgba(25, 135, 84, 0.45) !important;
  background-color: rgba(25, 135, 84, 0.06) !important;
  transform: scale(0.998);
}

/* Inputs: micro-interactions */
.form-control, .form-select, .input-group-text {
  border-radius: 0.8rem;
}
.form-control:focus, .form-select:focus {
  box-shadow: 0 0 0 .2rem rgba(13,110,253,.15);
  border-color: rgba(13,110,253,.5);
}

/* Boutons */
.btn { transition: transform .04s ease, box-shadow .15s ease; }
.btn:active { transform: translateY(1px); }
.btn:disabled { opacity: .6; cursor: not-allowed; }

/* Spinner FA */
.fa-spin { animation: spin 1s linear infinite; }
@keyframes spin { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }

/* Card adoucie */
.card { border-radius: 1rem; }

/* Mode sombre (optionnel si vous avez un switch) */
@media (prefers-color-scheme: dark) {
  .dropzone { border-color: rgba(255,255,255,.15); }
  .dropzone:hover { border-color: rgba(86,156,214,.5); }
}
</style>
