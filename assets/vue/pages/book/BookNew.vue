<template>
  <div class="container mt-5" lang="fr">
    <!-- Titre & retour -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <a :href="$routing.generate('app_book_list')" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
      <h1 class="text-primary mb-0">Créer un Livre</h1>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="messageType" />

    <div class="card shadow-sm p-4">
      <form @submit.prevent="save" enctype="multipart/form-data">
        <div class="row">
          <!-- Nom -->
          <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Nom du Livre</label>
            <input
                id="name"
                type="text"
                class="form-control"
                v-model="form.name"
                required
            />
          </div>

          <!-- Niveau -->
          <div class="col-md-6 mb-3">
            <label for="level" class="form-label">Niveau scolaire</label>
            <select
                id="level"
                class="form-control"
                v-model="form.level"
                required
            >
              <option value="">-- Choisir un niveau --</option>
              <option v-for="opt in levelOptionsArabic" :key="opt" :value="opt">
                {{ opt }}
              </option>
            </select>
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
          ></textarea>
        </div>

        <div class="row">
          <!-- Prix d'achat -->
          <div class="col-md-6 mb-3">
            <label for="purchasePrice" class="form-label">Prix d’achat (€)</label>
            <input
                id="purchasePrice"
                type="number"
                step="0.01"
                min="0"
                class="form-control"
                v-model="form.purchasePrice"
                required
            />
          </div>

          <!-- Prix de vente -->
          <div class="col-md-6 mb-3">
            <label for="salePrice" class="form-label">Prix de vente (€)</label>
            <input
                id="salePrice"
                type="number"
                step="0.01"
                min="0"
                class="form-control"
                v-model="form.salePrice"
                required
            />
          </div>
        </div>

        <!-- Image -->
        <div class="mb-3">
          <label for="image" class="form-label">Image</label>
          <input
              id="image"
              type="file"
              accept="image/*"
              class="form-control"
              @change="onFileChange"
          />
        </div>

        <!-- Boutons -->
        <div class="d-flex justify-content-between mt-4">
          <a :href="$routing.generate('app_book_list')" class="btn btn-outline-secondary">
            Annuler
          </a>
          <button type="submit" class="btn btn-success" :disabled="isSaving">
            <i v-if="isSaving" class="fas fa-spinner fa-spin"></i>
            <i v-else class="fa fa-save"></i>
            {{ isSaving ? 'Création...' : 'Créer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "BookNew",
  components: { Alert },
  data() {
    return {
      messageAlert: null,
      messageType: null,
      isSaving: false,
      levelOptionsArabic: [
        'CP','N0','N1_1','N1_2','N2_1','N2_2','N3_1','N3_2',
        'N4_1','N4_2','N5_1','N5_2','N6_1','N6_2','Adolescent','Adult'
      ],
      form: {
        name: "",
        description: "",
        level: "",
        purchasePrice: "",
        salePrice: "",
        image: null,
      },
    };
  },
  methods: {
    onFileChange(e) {
      this.form.image = e.target.files[0];
    },
    async save() {
      this.isSaving = true;
      this.messageAlert = null;

      try {
        const formData = new FormData();
        Object.keys(this.form).forEach((key) => {
          if (this.form[key] !== null && this.form[key] !== "")
            formData.append(key, this.form[key]);
        });

        const url = this.$routing.generate("book_save");
        const res = await this.$axios.post(url, formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        const createdId = res?.data?.id;
        if (createdId) {
          window.location.href = this.$routing.generate("book_edit", { id: createdId });
        } else {
          this.messageAlert = "Livre enregistré mais ID introuvable.";
          this.messageType = "warning";
        }
      } catch (err) {
        console.error(err);
        this.messageAlert = "Erreur lors de l’enregistrement.";
        this.messageType = "danger";
      } finally {
        this.isSaving = false;
      }
    },
  },
};
</script>

<style scoped>
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.fa-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
