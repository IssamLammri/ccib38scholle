<template>
  <div class="container py-4" lang="fr">
    <!-- Action bar (même vibe que ShowParent) -->
    <div class="action-bar">
      <a :href="$routing.generate('app_parent_entity_index')" class="btn btn-outline-secondary btn-modern">
        <i class="fas fa-arrow-left"></i><span>Retour à la liste</span>
      </a>

      <div class="action-buttons">
        <a :href="$routing.generate('app_parent_entity_show', { id: parent.id })" class="btn btn-outline-primary btn-modern">
          <i class="fas fa-eye"></i><span>Voir</span>
        </a>

        <button class="btn btn-warning btn-modern" @click="resetForm" :disabled="saving">
          <i class="fas fa-undo"></i><span>Réinitialiser</span>
        </button>

        <button class="btn btn-primary btn-modern" @click="submit" :disabled="saving">
          <i class="fas fa-save"></i><span>{{ saving ? 'Sauvegarde...' : 'Enregistrer' }}</span>
        </button>
      </div>
    </div>

    <Alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-4" />

    <!-- Form -->
    <div class="parent-card">
      <div class="parent-header">
        <div class="header-content">
          <div class="avatar-section">
            <div class="avatar-circle">
              <span>{{ headerInitials }}</span>
            </div>
            <div class="header-info">
              <h1>Modifier Parent</h1>
              <p class="header-subtitle">{{ headerSubtitle }}</p>
            </div>
          </div>
          <div class="header-badges">
            <div class="badge badge-modern">
              <i class="fas fa-user-friends"></i>
              <span>{{ studentsCount }} enfant{{ studentsCount > 1 ? 's' : '' }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="parent-body">
        <div class="info-cards-grid">
          <!-- Père -->
          <div class="info-card">
            <div class="card-icon father">
              <i class="fas fa-male"></i>
            </div>
            <div class="card-content">
              <h3>Père</h3>

              <div class="form-grid">
                <div class="form-group">
                  <label>Nom</label>
                  <input v-model.trim="form.fatherLastName" class="form-control" />
                </div>
                <div class="form-group">
                  <label>Prénom</label>
                  <input v-model.trim="form.fatherFirstName" class="form-control" />
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input v-model.trim="form.fatherEmail" type="email" class="form-control" />
                </div>
                <div class="form-group">
                  <label>Téléphone</label>
                  <input v-model.trim="form.fatherPhone" class="form-control" />
                </div>
              </div>
            </div>
          </div>

          <!-- Mère -->
          <div class="info-card">
            <div class="card-icon mother">
              <i class="fas fa-female"></i>
            </div>
            <div class="card-content">
              <h3>Mère</h3>

              <div class="form-grid">
                <div class="form-group">
                  <label>Nom</label>
                  <input v-model.trim="form.motherLastName" class="form-control" />
                </div>
                <div class="form-group">
                  <label>Prénom</label>
                  <input v-model.trim="form.motherFirstName" class="form-control" />
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input v-model.trim="form.motherEmail" type="email" class="form-control" />
                </div>
                <div class="form-group">
                  <label>Téléphone</label>
                  <input v-model.trim="form.motherPhone" class="form-control" />
                </div>
              </div>
            </div>
          </div>

          <!-- Famille -->
          <div class="info-card">
            <div class="card-icon family">
              <i class="fas fa-home"></i>
            </div>
            <div class="card-content">
              <h3>Famille</h3>

              <div class="form-grid">
                <div class="form-group">
                  <label>Statut familial</label>
                  <select v-model="form.familyStatus" class="form-select">
                    <option value="">-- Choisir --</option>
                    <option value="Married">Mariés</option>
                    <option value="Divorced">Divorcés</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Montant dû (Arabe)</label>
                  <input v-model.number="form.amountDueArabic" type="number" min="0" class="form-control" />
                </div>

                <div class="form-group">
                  <label>Montant dû (Soutien)</label>
                  <input v-model.number="form.amountDueSoutien" type="number" min="0" class="form-control" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Étudiants (comme dans twig) -->
        <div class="students-section">
          <div class="section-header">
            <h2><i class="fas fa-users"></i> Étudiants associés</h2>
          </div>

          <div class="students-table-wrapper">
            <table class="students-table">
              <thead>
              <tr>
                <th>Nom complet</th>
                <th>Date de naissance</th>
                <th>Niveau</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="s in normalizedStudents" :key="s.id">
                <td>{{ s.lastName }} {{ s.firstName }}</td>
                <td>{{ formatDate(s.birthDate) }}</td>
                <td>{{ s.level || 'Non disponible' }}</td>
              </tr>
              <tr v-if="normalizedStudents.length === 0">
                <td colspan="3" class="empty-state">Aucun étudiant associé</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "EditParent",
  components: { Alert },
  props: {
    parent: { type: Object, required: true },
    students: { type: Array, required: true },
    csrfToken: { type: String, required: true },
    updateUrl: { type: String, required: true }, // route API
  },
  data() {
    return {
      saving: false,
      messageAlert: null,
      typeAlert: null,
      form: this.makeFormFromParent(this.parent),
      initialSnapshot: null,
    };
  },
  created() {
    this.initialSnapshot = JSON.stringify(this.form);
  },
  computed: {
    studentsCount() {
      return Array.isArray(this.students) ? this.students.length : 0;
    },
    normalizedStudents() {
      if (!Array.isArray(this.students)) return [];
      return this.students.map((s) => ({
        id: s.id,
        lastName: this.clean(s.lastName) || "—",
        firstName: this.clean(s.firstName) || "—",
        birthDate: s.birthDate || null,
        level: this.clean(s.level) || null,
      }));
    },
    headerSubtitle() {
      const email =
          this.clean(this.form.fatherEmail) ||
          this.clean(this.form.motherEmail) ||
          "Email non renseigné";
      const phone =
          this.clean(this.form.fatherPhone) ||
          this.clean(this.form.motherPhone) ||
          "Téléphone non renseigné";
      return `${email} • ${phone}`;
    },
    headerInitials() {
      const t = (this.clean(this.form.fatherLastName) || "P").charAt(0).toUpperCase();
      const m = (this.clean(this.form.motherLastName) || "").charAt(0).toUpperCase();
      return (t + m).trim() || "P";
    },
  },
  methods: {
    makeFormFromParent(p) {
      return {
        fatherLastName: this.clean(p.fatherLastName),
        fatherFirstName: this.clean(p.fatherFirstName),
        fatherEmail: this.clean(p.fatherEmail),
        fatherPhone: this.clean(p.fatherPhone),
        motherLastName: this.clean(p.motherLastName),
        motherFirstName: this.clean(p.motherFirstName),
        motherEmail: this.clean(p.motherEmail),
        motherPhone: this.clean(p.motherPhone),
        familyStatus: this.clean(p.familyStatus),
        amountDueArabic: Number(p.amountDueArabic ?? 0),
        amountDueSoutien: Number(p.amountDueSoutien ?? 0),
      };
    },
    resetForm() {
      this.form = JSON.parse(this.initialSnapshot);
      this.messageAlert = "Formulaire réinitialisé.";
      this.typeAlert = "info";
    },
    clean(v) {
      if (v === null || v === undefined) return "";
      const s = String(v).trim();
      return s.toLowerCase() === "vide" ? "" : s;
    },
    formatDate(date) {
      if (!date) return "Non disponible";
      try {
        return new Date(date).toLocaleDateString("fr-FR", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
      } catch {
        return "Non disponible";
      }
    },
    async submit() {
      this.saving = true;
      this.messageAlert = null;

      try {
        const res = await fetch(this.updateUrl, {
          method: "PATCH",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": this.csrfToken,
          },
          body: JSON.stringify(this.form),
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
          throw new Error(data?.message || "Erreur lors de la sauvegarde.");
        }

        this.typeAlert = "success";
        this.messageAlert = data?.text || "Parent mis à jour avec succès.";
        this.initialSnapshot = JSON.stringify(this.form);
      } catch (e) {
        this.typeAlert = "danger";
        this.messageAlert = e.message || "Erreur inconnue.";
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
/* Reprend ton style ShowParent en simplifié */
.container{max-width:1400px;margin:0 auto;background:#f8fafc;min-height:100vh;color:#2d3748;}
.action-bar{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:2rem;padding:1.5rem;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);border:1px solid #e2e8f0;}
.btn-modern{display:flex;align-items:center;gap:.5rem;padding:.75rem 1.5rem;border-radius:8px;font-weight:500;transition:all .2s ease;text-decoration:none;border:none;cursor:pointer;}
.btn-modern:hover{transform:translateY(-1px);box-shadow:0 4px 6px rgba(0,0,0,.1);text-decoration:none;}
.action-buttons{display:flex;gap:.75rem;}
.parent-card{background:#fff;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.1);overflow:hidden;border:1px solid #e2e8f0;}
.parent-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:2rem;color:#fff;}
.header-content{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1.5rem;}
.avatar-section{display:flex;align-items:center;gap:1rem;}
.avatar-circle{width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.5rem;border:2px solid rgba(255,255,255,.3);}
.header-info h1{margin:0;font-size:1.75rem;font-weight:700;}
.header-subtitle{margin:.5rem 0 0 0;opacity:.9;}
.badge-modern{display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem;background:rgba(255,255,255,.15);border-radius:20px;border:1px solid rgba(255,255,255,.2);}
.parent-body{padding:2rem;}
.info-cards-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.5rem;margin-bottom:2rem;}
.info-card{display:flex;gap:1rem;padding:1.5rem;border-radius:12px;border:1px solid #e2e8f0;background:linear-gradient(135deg,#fff 0%,#f8f9fa 100%);}
.card-icon{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#fff;flex-shrink:0;}
.card-icon.father{background:linear-gradient(135deg,#4facfe,#00f2fe);}
.card-icon.mother{background:linear-gradient(135deg,#f093fb,#f5576c);}
.card-icon.family{background:linear-gradient(135deg,#43e97b,#38f9d7);}
.card-content{flex:1;}
.card-content h3{font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;color:#718096;margin:0 0 .75rem 0;font-weight:600;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.form-group label{font-size:.85rem;color:#718096;margin-bottom:4px;display:block;}
.form-control,.form-select{border:2px solid #e2e8f0;border-radius:8px;padding:.65rem .9rem;}
.form-control:focus,.form-select:focus{border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.1);outline:none;}
.students-section{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;}
.section-header{background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%);padding:1.5rem;border-bottom:1px solid #e2e8f0;}
.section-header h2{margin:0;font-size:1.25rem;font-weight:600;display:flex;align-items:center;gap:.5rem;}
.students-table{width:100%;border-collapse:collapse;font-size:.9rem;}
.students-table th{background:#f8f9fa;padding:1rem;text-align:left;font-weight:600;border-bottom:2px solid #e2e8f0;}
.students-table td{padding:1rem;border-bottom:1px solid #f1f5f9;}
.empty-state{text-align:center;padding:2rem;color:#718096;}
@media (max-width:768px){.form-grid{grid-template-columns:1fr;}.action-bar{flex-direction:column;align-items:stretch;}}
</style>