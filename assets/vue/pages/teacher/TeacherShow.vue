<template>
  <div class="container-fluid px-4 py-3">
    <!-- Barre d'actions -->
    <div class="action-bar">
      <a :href="$routing.generate('app_teacher_index')" class="btn btn-modern btn-secondary">
        <i class="fas fa-arrow-left"></i>
        <span>Retour à la liste</span>
      </a>

      <div class="action-buttons">
        <button class="btn btn-modern btn-print" @click="printCharter">
          <i class="fas fa-print"></i>
          <span>Imprimer la Charte</span>
        </button>
        <a :href="$routing.generate('app_teacher_edit', { id: teacher.id })" class="btn btn-modern btn-warning">
          <i class="fas fa-edit"></i>
          <span>Modifier</span>
        </a>
        <button @click="confirmDelete" class="btn btn-modern btn-danger">
          <i class="fas fa-trash"></i>
          <span>Supprimer</span>
        </button>
      </div>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="alert-notification" />

    <!-- FICHE ENSEIGNANT -->
    <div id="teacher-sheet" class="teacher-profile">
      <!-- En-tête du profil -->
      <div class="profile-header">
        <div class="profile-banner">
          <div class="banner-gradient"></div>
        </div>

        <div class="profile-info">
          <div class="avatar-container">
            <div class="avatar-circle">
              <span>{{ headerInitials }}</span>
            </div>
            <div class="status-indicator"></div>
          </div>

          <div class="profile-details">
            <h1 class="profile-name">{{ headerTitle }}</h1>
            <p class="profile-subtitle">{{ headerSubtitle }}</p>

            <div class="profile-badges">
              <div class="badge badge-modern badge-primary">
                <i class="fas fa-graduation-cap"></i>
                <span>{{ displayOrNA(clean(teacher.educationLevel)) }}</span>
              </div>
              <div class="badge badge-modern badge-success">
                <i class="fas fa-star"></i>
                <span>{{ specialitiesCount }} spécialité{{ specialitiesCount > 1 ? 's' : '' }}</span>
              </div>
              <div class="badge badge-modern badge-info">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>{{ classesCount }} classe{{ classesCount > 1 ? 's' : '' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Grille d'informations -->
      <div class="info-grid">
        <!-- Carte Contact -->
        <div class="info-card contact-card">
          <div class="card-header">
            <div class="card-icon contact">
              <i class="fas fa-id-card"></i>
            </div>
            <h3 class="card-title">Informations de contact</h3>
          </div>
          <div class="card-content">
            <div class="info-item">
              <div class="info-label">
                <i class="fas fa-user"></i>
                <span>Nom complet</span>
              </div>
              <div class="info-value">{{ headerTitle }}</div>
            </div>
            <div class="info-item">
              <div class="info-label">
                <i class="fas fa-envelope"></i>
                <span>Email</span>
              </div>
              <div class="info-value">
                <a :href="'mailto:' + primaryEmail" class="link-primary">
                  {{ displayOrNA(primaryEmail) }}
                </a>
              </div>
            </div>
            <div class="info-item">
              <div class="info-label">
                <i class="fas fa-phone"></i>
                <span>Téléphone</span>
              </div>
              <div class="info-value">
                <a :href="'tel:' + primaryPhone" class="link-primary">
                  {{ displayOrNA(primaryPhone) }}
                </a>
              </div>
            </div>
            <div class="info-item">
              <div class="info-label">
                <i class="fas fa-hashtag"></i>
                <span>ID Enseignant</span>
              </div>
              <div class="info-value">
                <span class="id-badge">#{{ String(teacher.id).padStart(6, '0') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Carte Académique -->
        <div class="info-card academic-card">
          <div class="card-header">
            <div class="card-icon academic">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="card-title">Informations académiques</h3>
          </div>
          <div class="card-content">
            <div class="info-item">
              <div class="info-label">
                <i class="fas fa-award"></i>
                <span>Niveau d'études</span>
              </div>
              <div class="info-value">
                <span class="badge badge-level">{{ displayOrNA(clean(teacher.educationLevel)) }}</span>
              </div>
            </div>
            <div class="info-item">
              <div class="info-label">
                <i class="fas fa-book-open"></i>
                <span>Spécialités enseignées</span>
              </div>
              <div class="info-value">
                <div v-if="specialitiesCount > 0" class="specialities-list">
                  <span
                      v-for="spec in teacher.specialities"
                      :key="spec"
                      class="badge badge-speciality"
                  >
                    <i class="fas fa-book"></i>
                    {{ spec }}
                  </span>
                </div>
                <span v-else class="text-muted">Aucune spécialité renseignée</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section Classes -->
      <div class="classes-section">
        <div class="section-header">
          <div class="section-title-wrapper">
            <div class="section-icon">
              <i class="fas fa-chalkboard"></i>
            </div>
            <div>
              <h2 class="section-title">Classes assignées</h2>
              <p class="section-subtitle">
                {{ classesCount }} classe{{ classesCount > 1 ? 's' : '' }} où cet enseignant est professeur principal
              </p>
            </div>
          </div>
          <div class="section-stats">
            <div class="stat-badge">
              <i class="fas fa-users"></i>
              <span>{{ classesCount }} classe{{ classesCount > 1 ? 's' : '' }}</span>
            </div>
          </div>
        </div>

        <div class="classes-grid">
          <div
              v-for="cls in normalizedClasses"
              :key="cls.id"
              class="class-card"
          >
            <div class="class-card-header">
              <div class="class-icon">
                <i class="fas fa-chalkboard-teacher"></i>
              </div>

              <div class="class-head">
                <h4 class="class-name">{{ cls.name }}</h4>
                <div class="class-sub">
        <span class="pill pill-year">
          <i class="fas fa-calendar-alt"></i>
          {{ cls.schoolYear }}
        </span>

                  <span class="pill pill-status" :class="cls.active ? 'is-active' : 'is-inactive'">
          <i class="fas" :class="cls.active ? 'fa-check-circle' : 'fa-times-circle'"></i>
          {{ cls.active ? 'Active' : 'Inactive' }}
        </span>
                </div>
              </div>
            </div>

            <div class="class-card-body">
              <div class="class-metrics">
                <div class="metric">
                  <i class="fas fa-clock"></i>
                  <div class="metric-text">
                    <div class="metric-label">Créneau</div>
                    <div class="metric-value">{{ cls.day }} · {{ cls.timeRange }}</div>
                  </div>
                </div>

                <div class="metric">
                  <i class="fas fa-layer-group"></i>
                  <div class="metric-text">
                    <div class="metric-label">Niveau</div>
                    <div class="metric-value">{{ cls.level }}</div>
                  </div>
                </div>

                <div class="metric">
                  <i class="fas fa-book"></i>
                  <div class="metric-text">
                    <div class="metric-label">Spécialité</div>
                    <div class="metric-value">{{ cls.speciality }}</div>
                  </div>
                </div>

                <div class="metric">
                  <i class="fas fa-tag"></i>
                  <div class="metric-text">
                    <div class="metric-label">Type</div>
                    <div class="metric-value">{{ cls.classType }}</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="class-card-footer">
              <a
                  :href="$routing.generate('app_study_class_show', { id: cls.id })"
                  class="btn btn-view"
              >
                <i class="fas fa-eye"></i>
                <span>Voir la classe</span>
              </a>
            </div>
          </div>

          <div v-if="classesCount === 0" class="empty-state">
            <div class="empty-icon">
              <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="empty-title">Aucune classe assignée</h3>
            <p class="empty-description">Cet enseignant n'a pas encore de classes assignées</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CHARTE À IMPRIMER -->
    <div id="charter-sheet" class="charter">
      <div class="charter-header">
        <div class="charter-branding">
          <img class="charter-logo" src="/static/icons/logoccib38.webp" alt="Logo CCIB" loading="lazy" />
          <div class="charter-title">
            <h1>Charte des bénévoles du Centre Culturel Ibn Badis de Grenoble (CCIB38)</h1>
            <p class="charter-subtitle">Centre Culturel Ibn Badis Grenoble</p>
          </div>
        </div>
        <div class="charter-metadata">
          <div class="metadata-item">
            <span class="label">Date d'émission</span>
            <span class="value">{{ today }}</span>
          </div>
          <div class="metadata-item">
            <span class="label">N° Dossier</span>
            <span class="value">{{ String(teacher.id).padStart(6, '0') }}</span>
          </div>
        </div>
      </div>

      <div class="charter-content">
        <!-- Coordonnées -->
        <div class="charter-section">
          <h2><span class="section-number">1.</span> Coordonnées du bénévole</h2>
          <div class="section-content">
            <div class="info-grid-charter">
              <div class="info-group">
                <div class="field">
                  <span class="field-label">Nom complet</span>
                  <span class="field-value">{{ headerTitle }}</span>
                </div>
                <div class="field">
                  <span class="field-label">Adresse e-mail</span>
                  <span class="field-value">{{ primaryEmail || 'Non renseigné' }}</span>
                </div>
                <div class="field">
                  <span class="field-label">Téléphone</span>
                  <span class="field-value">{{ primaryPhone || 'Non renseigné' }}</span>
                </div>
              </div>
              <div class="address-group">
                <span class="field-label">Spécialités</span>
                <div class="address-field">
                  {{ specialitiesCount ? teacher.specialities.join(', ') : 'Non renseignées' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Règlement de la charte -->
        <div class="charter-section rules-section">
          <h2><span class="section-number">2.</span> Principes & Règles de la Charte</h2>
          <div class="section-content">
            <p class="section-intro">
              Le CCIB38 valorise l'engagement et le dévouement de ses bénévoles. Afin de garantir un environnement
              harmonieux et une collaboration efficace, la présente charte fixe les principes et règles encadrant les activités des bénévoles.
            </p>

            <div class="rule-category">
              <h3><i class="fas fa-tasks"></i> 1. Engagement et responsabilités</h3>
              <ul class="rule-list">
                <li>Les bénévoles s'engagent à respecter les missions qui leur sont confiées avec sérieux et implication.</li>
                <li>Toute absence ou indisponibilité doit être signalée à la coordination bénévole dans les meilleurs délais.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-heart"></i> 2. Respect des valeurs du centre</h3>
              <ul class="rule-list">
                <li>Les bénévoles doivent adhérer aux valeurs de respect, d'égalité et de bienveillance prônées par le centre culturel.</li>
                <li>Tout comportement discriminatoire, irrespectueux ou contraire à l'éthique du centre est interdit.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-people-carry"></i> 3. Collaboration et esprit d'équipe</h3>
              <ul class="rule-list">
                <li>Travailler en collaboration avec les autres membres (bénévoles, salariés, enseignants, etc.) pour le bon fonctionnement du centre.</li>
                <li>Les différends ou malentendus doivent être abordés de manière constructive et, si nécessaire, portés à l'attention de la direction.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-user-shield"></i> 4. Confidentialité</h3>
              <ul class="rule-list">
                <li>Respecter la confidentialité des informations sensibles concernant le centre, les élèves, les participants ou les autres bénévoles.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-clock"></i> 5. Participation et assiduité</h3>
              <ul class="rule-list">
                <li>La présence et l'assiduité sont essentielles pour garantir le bon déroulement des activités.</li>
                <li>Respect des horaires et des engagements pris.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-boxes"></i> 6. Utilisation des ressources du centre</h3>
              <ul class="rule-list">
                <li>Les ressources et infrastructures du centre sont destinées exclusivement aux activités liées au centre culturel.</li>
                <li>Toute utilisation personnelle ou abusive est interdite.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-first-aid"></i> 7. Sécurité et prévention</h3>
              <ul class="rule-list">
                <li>Respect des consignes de sécurité et signalement immédiat de tout incident ou anomalie à la direction.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-chalkboard-teacher"></i> 8. Formation et accompagnement</h3>
              <ul class="rule-list">
                <li>Le centre s'engage à accompagner ses bénévoles par des formations ou des échanges visant à développer leurs compétences et à faciliter leur intégration.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-sync-alt"></i> 9. Évolution de la charte</h3>
              <ul class="rule-list">
                <li>Cette charte est susceptible d'évoluer pour s'adapter aux besoins du centre et aux réglementations en vigueur.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-check-circle"></i> 10. Acceptation de la charte</h3>
              <ul class="rule-list">
                <li>Chaque bénévole doit lire, comprendre et accepter cette charte avant de commencer ses activités.</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Signatures -->
        <div class="charter-signature">
          <div class="signature-location">
            <span class="location-label">Établi à</span>
            <span class="location-value">Grenoble</span>
            <span class="date-label">Le</span>
            <span class="date-value">{{ today }}</span>
          </div>

          <div class="signature-row">
            <div class="sig-left">
              <div class="sig-title">Signature du bénévole</div>
              <div class="sig-meta">Nom : <strong>{{ headerTitle }}</strong></div>
              <div class="sig-pad"></div>
              <div class="sig-note">(Précédée de la mention « Lu et approuvé »)</div>
            </div>

            <div class="sig-right">
              <div class="sig-title">Cachet et signature du centre</div>
              <div class="sig-pad"></div>
              <div class="sig-name">Centre Culturel Ibn Badis Grenoble</div>
              <div class="sig-note">Direction</div>
            </div>
          </div>

          <div class="charter-footer">
            <p class="footer-text">
              En signant cette charte, chaque bénévole s'engage à respecter ses dispositions
              et à contribuer activement au succès des activités du centre culturel.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "ShowTeacher",
  components: { Alert },
  props: {
    teacher: { type: Object, required: true },
    csrfDeleteToken: { type: String, default: '' }
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
    };
  },
  computed: {
    today() {
      return new Date().toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
      });
    },
    headerTitle() {
      const ln = this.clean(this.teacher.lastName);
      const fn = this.clean(this.teacher.firstName);
      return (ln || fn) ? `${fn} ${ln}`.trim() : "Enseignant";
    },
    headerSubtitle() {
      const email = this.primaryEmail || "Email non renseigné";
      const phone = this.primaryPhone || "Téléphone non renseigné";
      return `${email} • ${phone}`;
    },
    headerInitials() {
      const name = this.headerTitle;
      const parts = name.split(" ").filter(Boolean);
      const initials = parts.slice(0, 2).map((p) => p[0]?.toUpperCase() || "").join("");
      return initials || "E";
    },
    primaryEmail() {
      return this.clean(this.teacher.email) || "";
    },
    primaryPhone() {
      return this.clean(this.teacher.phoneNumber) || "";
    },
    specialitiesCount() {
      return Array.isArray(this.teacher.specialities) ? this.teacher.specialities.length : 0;
    },
    classesCount() {
      return Array.isArray(this.teacher.classes) ? this.teacher.classes.length : 0;
    },
    normalizedClasses() {
      if (!Array.isArray(this.teacher.classes)) return [];

      const fmtTime = (iso) => {
        if (!iso) return "—";
        const d = new Date(iso);
        // si ton backend renvoie +00:00, ça peut décaler selon le navigateur,
        // mais on garde simple ici.
        return d.toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" });
      };

      return this.teacher.classes.map((c) => ({
        id: c.id,
        name: this.clean(c.name) || "—",
        speciality: this.clean(c.speciality) || "—",
        level: this.clean(c.level) || "—",
        day: this.clean(c.day) || "—",
        classType: this.clean(c.classType) || "—",
        schoolYear: this.clean(c.schoolYear) || "—",
        active: !!c.active,
        startHour: fmtTime(c.startHour),
        endHour: fmtTime(c.endHour),
        timeRange: `${fmtTime(c.startHour)} – ${fmtTime(c.endHour)}`,
      }));
    },
  },
  methods: {
    clean(v) {
      if (v === null || v === undefined) return "";
      const s = String(v).trim();
      return s.toLowerCase() === "vide" ? "" : s;
    },
    displayOrNA(v) {
      return v && String(v).trim() !== "" ? v : "Non renseigné(e)";
    },
    async confirmDelete() {
      if (!confirm(`Êtes-vous sûr de vouloir supprimer l'enseignant ${this.headerTitle} ?`)) {
        return;
      }

      try {
        const res = await this.$axios.post(
            this.$routing.generate('app_teacher_delete', { id: this.teacher.id }),
            { _token: this.csrfDeleteToken }
        );

        if (res.data?.success) {
          this.showAlert("success", "Enseignant supprimé avec succès.");
          setTimeout(() => {
            window.location.href = this.$routing.generate('app_teacher_index');
          }, 1500);
        } else {
          this.showAlert("danger", res.data?.message || "Erreur lors de la suppression.");
        }
      } catch (e) {
        console.error("Erreur lors de la suppression :", e);
        this.showAlert("danger", "Erreur lors de la suppression de l'enseignant.");
      }
    },
    showAlert(type, message) {
      this.typeAlert = type;
      this.messageAlert = message;

      setTimeout(() => {
        this.messageAlert = null;
      }, 5000);
    },
    printCharter() {
      const el = document.getElementById("charter-sheet");
      if (!el) return;

      const win = window.open("", "_blank");
      const html = `
        <html>
        <head>
          <meta charset="utf-8" />
          <title>Charte - ${this.headerTitle}</title>
          <style>${this.getOptimizedPrintCSS()}</style>
        </head>
        <body>${el.outerHTML}</body>
        </html>
      `;

      win.document.write(html);
      win.document.close();
      win.focus();

      setTimeout(() => {
        win.print();
        win.close();
      }, 250);
    },
    getOptimizedPrintCSS() {
      return `
        @page {
          size: A4;
          margin: 15mm 12mm 12mm 12mm;
        }

        * { box-sizing: border-box; }

        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          color: #2c3e50;
          line-height: 1.4;
          margin: 0;
          padding: 0;
        }

        .charter { display: block !important; }

        .charter-header {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          padding-bottom: 15px;
          border-bottom: 2px solid #34495e;
          margin-bottom: 20px;
        }

        .charter-branding { display: flex; align-items: center; gap: 12px; }
        .charter-logo { height: 45px; width: auto; }
        .charter-title h1 { font-size: 20px; font-weight: 700; color: #2c3e50; margin: 0; line-height: 1.2; }
        .charter-subtitle { font-size: 12px; color: #7f8c8d; margin: 2px 0 0 0; }
        .charter-metadata { text-align: right; font-size: 11px; }
        .metadata-item { display: block; margin-bottom: 4px; }
        .metadata-item .label { font-weight: 600; color: #7f8c8d; }
        .metadata-item .value { font-weight: 400; color: #2c3e50; margin-left: 6px; }

        .charter-section { margin-bottom: 18px; }
        .charter-section h2 { font-size: 16px; font-weight: 700; color: #34495e; margin: 0 0 10px 0; display: flex; align-items: center; }
        .section-number { background: #3498db; color: white; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; margin-right: 8px; }

        .info-grid-charter { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 8px; }
        .field { margin-bottom: 8px; }
        .field-label { font-weight: 600; color: #7f8c8d; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px; }
        .field-value { color: #2c3e50; font-size: 13px; display: block; }
        .address-field { border: 1px solid #bdc3c7; border-radius: 4px; padding: 8px; min-height: 45px; white-space: pre-line; font-size: 13px; background: #f8f9fa; margin-top: 4px; }

        .rules-section .section-intro { font-style: italic; color: #7f8c8d; margin-bottom: 15px; font-size: 13px; text-align: justify; }
        .rule-category { margin-bottom: 12px; page-break-inside: avoid; }
        .rule-category h3 { font-size: 14px; font-weight: 600; color: #34495e; margin: 0 0 6px 0; display: flex; align-items: center; }
        .rule-category h3 i { margin-right: 6px; color: #3498db; }
        .rule-list { margin: 0; padding-left: 18px; list-style-type: none; }
        .rule-list li { margin-bottom: 4px; font-size: 12px; line-height: 1.4; position: relative; }
        .rule-list li::before { content: "•"; color: #3498db; font-weight: bold; position: absolute; left: -12px; }

        .charter-signature { margin-top: 25px; page-break-inside: avoid; }
        .signature-location { text-align: right; margin-bottom: 20px; font-size: 12px; }
        .location-label, .date-label { font-weight: 600; color: #7f8c8d; }
        .location-value, .date-value { color: #2c3e50; margin-left: 4px; margin-right: 15px; }

        .signature-row {
          display: flex;
          gap: 24px;
          align-items: flex-start;
          justify-content: space-between;
          margin-top: 10px;
        }
        .sig-left, .sig-right { flex: 1; }
        .sig-left { flex: 1.1; }
        .sig-title {
          font-weight: 600;
          font-size: 12px;
          color: #34495e;
          margin-bottom: 6px;
        }
        .sig-meta, .sig-name {
          font-size: 11px;
          color: #2c3e50;
          margin-bottom: 6px;
        }
        .sig-pad {
          height: 70px;
          margin: 4px 0 6px 0;
          background: transparent;
        }
        .sig-note {
          font-size: 10px;
          color: #7f8c8d;
          font-style: italic;
        }

        .charter-footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ecf0f1; text-align: center; }
        .footer-text { font-size: 11px; color: #7f8c8d; font-style: italic; margin: 0; line-height: 1.3; }
      `;
    },
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
  --gradient-contact: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);

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
  max-width: 1400px;
  margin: 0 auto;
  background: var(--bg-secondary);
  min-height: 100vh;
}

/* Action Bar */
.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem 2rem;
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  margin-bottom: 2rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
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

.btn-secondary {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background: var(--text-tertiary);
  color: var(--text-primary);
}

.btn-print {
  background: var(--gradient-success);
  color: white;
}

.btn-print:hover {
  background: linear-gradient(135deg, #00b894, #00d4aa);
  color: white;
}

.btn-warning {
  background: linear-gradient(135deg, #fa709a, #fee140);
  color: white;
}

.btn-warning:hover {
  background: linear-gradient(135deg, #f85a8a, #fdd130);
  color: white;
}

.btn-danger {
  background: linear-gradient(135deg, #f56565, #fc8181);
  color: white;
}

.btn-danger:hover {
  background: linear-gradient(135deg, #e53e3e, #f56565);
  color: white;
}

/* Profile Section */
.teacher-profile {
  background: var(--bg-primary);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  margin-bottom: 2rem;
  animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.profile-header {
  position: relative;
}

.profile-banner {
  height: 180px;
  position: relative;
  overflow: hidden;
}

.banner-gradient {
  width: 100%;
  height: 100%;
  background: var(--gradient-primary);
  position: relative;
}

.banner-gradient::before {
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
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.profile-info {
  display: flex;
  align-items: flex-start;
  gap: 2rem;
  padding: 0 2rem 2rem 2rem;
  margin-top: -4rem;
  position: relative;
  z-index: 10;
}

.avatar-container {
  position: relative;
  flex-shrink: 0;
}

.avatar-circle {
  width: 128px;
  height: 128px;
  border-radius: 50%;
  background: var(--bg-primary);
  border: 6px solid var(--bg-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: 700;
  color: var(--primary);
  box-shadow: var(--shadow-lg);
  position: relative;
}

.status-indicator {
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 24px;
  height: 24px;
  background: var(--success);
  border: 4px solid var(--bg-primary);
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.profile-details {
  flex: 1;
  padding-top: 1rem;
}

.profile-name {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
  line-height: 1.2;
}

.profile-subtitle {
  font-size: 1rem;
  color: var(--text-secondary);
  margin: 0 0 1.5rem 0;
}

.profile-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.badge-modern {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
}

.badge-primary {
  background: var(--gradient-primary);
}

.badge-success {
  background: var(--gradient-success);
}

.badge-info {
  background: linear-gradient(135deg, #f093fb, #f5576c);
}

/* Info Grid */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 2rem;
  padding: 2rem;
}

.info-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: var(--transition);
}

.info-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.info-card .card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.card-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  flex-shrink: 0;
}

.card-icon.contact {
  background: var(--gradient-contact);
}

.card-icon.academic {
  background: var(--gradient-primary);
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.card-content {
  padding: 1.5rem;
}

.info-item {
  margin-bottom: 1.5rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-label i {
  color: var(--primary);
  font-size: 0.875rem;
}

.info-value {
  font-size: 1rem;
  color: var(--text-primary);
  font-weight: 500;
}

.link-primary {
  color: var(--primary);
  text-decoration: none;
  transition: var(--transition);
}

.link-primary:hover {
  color: var(--primary-dark);
  text-decoration: underline;
}

.id-badge {
  display: inline-block;
  padding: 0.375rem 0.875rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  color: var(--primary);
  border-radius: 999px;
  font-weight: 700;
  font-family: monospace;
  font-size: 0.875rem;
}

.badge-level {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: var(--gradient-success);
  color: white;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.875rem;
}

.specialities-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge-speciality {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  background: var(--gradient-primary);
  color: white;
  border-radius: 999px;
  font-size: 0.875rem;
  font-weight: 500;
}

.text-muted {
  color: var(--text-secondary);
  font-style: italic;
}

/* Classes Section */
.classes-section {
  border-top: 1px solid var(--border-color);
  padding: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.section-title-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.section-icon {
  width: 56px;
  height: 56px;
  background: var(--gradient-primary);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.25rem 0;
}

.section-subtitle {
  font-size: 0.9375rem;
  color: var(--text-secondary);
  margin: 0;
}

.section-stats {
  display: flex;
  gap: 1rem;
}

.stat-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
  color: var(--primary);
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.9375rem;
}

.classes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.5rem;
}

.class-card {
  background: var(--bg-primary);
  border: 2px solid var(--border-color);
  border-radius: var(--radius-md);
  overflow: hidden;
  transition: var(--transition);
}

.class-card:hover {
  border-color: var(--primary);
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
}

.class-card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid var(--border-color);
}

.class-icon {
  width: 40px;
  height: 40px;
  background: var(--gradient-primary);
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.125rem;
}

.class-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.class-card-body {
  padding: 1.25rem;
}

.class-info {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.class-info-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 0.9375rem;
  color: var(--text-secondary);
}

.class-info-item i {
  width: 18px;
  color: var(--primary);
  font-size: 0.875rem;
}

.class-card-footer {
  padding: 1rem 1.25rem;
  background: var(--bg-secondary);
  border-top: 1px solid var(--border-color);
}

.btn-view {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: var(--gradient-success);
  color: white;
  text-decoration: none;
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 600;
  transition: var(--transition);
  width: 100%;
  justify-content: center;
}

.btn-view:hover {
  background: linear-gradient(135deg, #00b894, #00d4aa);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
  color: white;
  text-decoration: none;
}

/* Empty State */
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
}

.empty-icon {
  font-size: 4rem;
  opacity: 0.3;
  margin-bottom: 1.5rem;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 0.5rem 0;
}

.empty-description {
  font-size: 1rem;
  margin: 0;
}

/* Alert Notification */
.alert-notification {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 9999;
  max-width: 500px;
  box-shadow: var(--shadow-lg);
  animation: slideInRight 0.4s ease;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Charter - Hidden by default */
.charter {
  display: none;
}

/* Print Styles */
@media print {
  body * {
    visibility: hidden !important;
  }
  #charter-sheet,
  #charter-sheet * {
    visibility: visible !important;
  }
  #charter-sheet {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
  .charter {
    display: block !important;
  }
}

/* Responsive */
@media (max-width: 1024px) {
  .info-grid {
    grid-template-columns: 1fr;
  }

  .classes-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

@media (max-width: 768px) {
  .action-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .action-buttons {
    flex-direction: column;
  }

  .btn-modern {
    width: 100%;
    justify-content: center;
  }

  .profile-info {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 0 1rem 2rem 1rem;
  }

  .profile-name {
    font-size: 1.5rem;
  }

  .profile-badges {
    justify-content: center;
  }

  .info-grid {
    padding: 1rem;
  }

  .classes-section {
    padding: 1rem;
  }

  .section-header {
    flex-direction: column;
  }

  .section-title-wrapper {
    width: 100%;
  }

  .classes-grid {
    grid-template-columns: 1fr;
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

  .action-bar {
    padding: 1rem;
  }

  .profile-banner {
    height: 120px;
  }

  .avatar-circle {
    width: 96px;
    height: 96px;
    font-size: 2rem;
  }

  .profile-name {
    font-size: 1.25rem;
  }

  .profile-subtitle {
    font-size: 0.875rem;
  }

  .info-card .card-header,
  .card-content {
    padding: 1rem;
  }

  .section-title {
    font-size: 1.25rem;
  }
}
.class-head {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.class-sub {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  border: 1px solid var(--border-color);
  background: var(--bg-secondary);
  color: var(--text-secondary);
}

.pill-year i,
.pill-status i {
  font-size: 0.8rem;
}

.pill-status.is-active {
  background: rgba(72, 187, 120, 0.12);
  color: #1f7a46;
  border-color: rgba(72, 187, 120, 0.35);
}

.pill-status.is-inactive {
  background: rgba(245, 101, 101, 0.12);
  color: #b42323;
  border-color: rgba(245, 101, 101, 0.35);
}

.class-metrics {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.9rem;
}

.metric {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.metric i {
  width: 20px;
  margin-top: 2px;
  color: var(--primary);
}

.metric-text {
  flex: 1;
}

.metric-label {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  color: var(--text-tertiary);
  margin-bottom: 0.15rem;
}

.metric-value {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text-primary);
}

</style>