<template>
  <div class="container py-4" lang="fr">
    <!-- Barre d'actions -->
    <div class="action-bar">
      <a :href="$routing.generate('app_teacher_index')" class="btn btn-outline-secondary btn-modern">
        <i class="fas fa-arrow-left"></i>
        <span>Retour à la liste</span>
      </a>

      <div class="action-buttons">
        <button class="btn btn-print" @click="printCharter">
          <i class="fas fa-print"></i>
          <span>Imprimer la Charte</span>
        </button>
        <a :href="$routing.generate('app_teacher_edit', { id: teacher.id })" class="btn btn-warning btn-modern">
          <i class="fas fa-edit"></i>
          <span>Modifier</span>
        </a>
      </div>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-4" />

    <!-- FICHE ENSEIGNANT -->
    <div id="teacher-sheet" class="parent-card">
      <div class="parent-header">
        <div class="header-content">
          <div class="avatar-section">
            <div class="avatar-circle">
              <span>{{ headerInitials }}</span>
            </div>
            <div class="header-info">
              <h1>{{ headerTitle }}</h1>
              <p class="header-subtitle">{{ headerSubtitle }}</p>
            </div>
          </div>
          <div class="header-badges">
            <div class="badge badge-modern">
              <i class="fas fa-user-tie"></i>
              <span>Niveau d'études : {{ displayOrNA(clean(teacher.educationLevel)) }}</span>
            </div>
            <div class="badge badge-modern">
              <i class="fas fa-star"></i>
              <span>{{ specialitiesCount }} spécialité{{ specialitiesCount > 1 ? 's' : '' }}</span>
            </div>
            <div class="badge badge-modern">
              <i class="fas fa-chalkboard-teacher"></i>
              <span>{{ classesCount }} classe{{ classesCount > 1 ? 's' : '' }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="parent-body">
        <!-- Cartes d'information -->
        <div class="info-cards-grid">
          <div class="info-card contact-card">
            <div class="card-icon">
              <i class="fas fa-id-card"></i>
            </div>
            <div class="card-content">
              <h3>Contact</h3>
              <h4>{{ headerTitle }}</h4>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <span>{{ displayOrNA(primaryEmail) }}</span>
                </div>
                <div class="contact-item">
                  <i class="fas fa-phone"></i>
                  <span>{{ displayOrNA(primaryPhone) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="info-card mother-card">
            <div class="card-icon">
              <i class="fas fa-user-graduate"></i>
            </div>
            <div class="card-content">
              <h3>Spécialités</h3>
              <h4>{{ specialitiesCount ? teacher.specialities.join(', ') : 'Aucune spécialité renseignée' }}</h4>
            </div>
          </div>

          <div class="info-card father-card">
            <div class="card-icon">
              <i class="fas fa-certificate"></i>
            </div>
            <div class="card-content">
              <h3>Informations</h3>
              <h4>ID #{{ String(teacher.id).padStart(6, '0') }}</h4>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="fas fa-briefcase"></i>
                  <span>Niveau d'études : {{ displayOrNA(clean(teacher.educationLevel)) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tableau des classes (dans la fiche seulement, pas dans la charte imprimée) -->
        <div class="students-section">
          <div class="section-header">
            <h2>
              <i class="fas fa-chalkboard"></i>
              Classes assignées
            </h2>
          </div>

          <div class="students-table-wrapper">
            <table class="students-table">
              <thead>
              <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Actions</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="c in normalizedClasses" :key="c.id" class="student-row">
                <td class="id-cell">{{ c.id }}</td>
                <td class="name-cell">{{ c.name }}</td>
                <td class="actions-cell">
                  <a class="btn btn-view" :href="$routing.generate('app_study_class_show', { id: c.id })">
                    <i class="fas fa-eye"></i>
                    <span>Voir la classe</span>
                  </a>
                </td>
              </tr>
              <tr v-if="normalizedClasses.length === 0">
                <td colspan="3" class="empty-state">
                  <div class="empty-content">
                    <i class="fas fa-folder-open"></i>
                    <p>Aucune classe assignée</p>
                  </div>
                </td>
              </tr>
              </tbody>
            </table>
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
            <div class="info-grid">
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
                <li>Les bénévoles doivent adhérer aux valeurs de respect, d’égalité et de bienveillance prônées par le centre culturel.</li>
                <li>Tout comportement discriminatoire, irrespectueux ou contraire à l’éthique du centre est interdit.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-people-carry"></i> 3. Collaboration et esprit d'équipe</h3>
              <ul class="rule-list">
                <li>Travailler en collaboration avec les autres membres (bénévoles, salariés, enseignants, etc.) pour le bon fonctionnement du centre.</li>
                <li>Les différends ou malentendus doivent être abordés de manière constructive et, si nécessaire, portés à l’attention de la direction.</li>
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
                <li>La présence et l’assiduité sont essentielles pour garantir le bon déroulement des activités.</li>
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
                <li>Le centre s’engage à accompagner ses bénévoles par des formations ou des échanges visant à développer leurs compétences et à faciliter leur intégration.</li>
              </ul>
            </div>

            <div class="rule-category">
              <h3><i class="fas fa-sync-alt"></i> 9. Évolution de la charte</h3>
              <ul class="rule-list">
                <li>Cette charte est susceptible d’évoluer pour s’adapter aux besoins du centre et aux réglementations en vigueur.</li>
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

        <!-- Signatures (gauche/droite, sans cadres ni lignes) -->
        <div class="charter-signature">
          <div class="signature-location">
            <span class="location-label">Établi à</span>
            <span class="location-value">Grenoble</span>
            <span class="date-label">Le</span>
            <span class="date-value">{{ today }}</span>
          </div>

          <div class="signature-row">
            <!-- Colonne gauche : bénévole -->
            <div class="sig-left">
              <div class="sig-title">Signature du bénévole</div>
              <div class="sig-meta">Nom : <strong>{{ headerTitle }}</strong></div>
              <div class="sig-pad"></div>
              <div class="sig-note">(Précédée de la mention « Lu et approuvé »)</div>
            </div>

            <!-- Colonne droite : centre -->
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
    <!-- /CHARTE -->
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";

export default {
  name: "ShowTeacher",
  components: { Alert },
  props: {
    teacher: { type: Object, required: true },
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
      return (ln || fn) ? `${ln} ${fn}`.trim() : "Enseignant";
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
      return this.teacher.classes.map((c) => ({ id: c.id, name: this.clean(c.name) || "—" }));
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
          @bottom-center {
            content: "Page " counter(page) " / " counter(pages);
            font-size: 10px;
            color: #666;
          }
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

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 8px; }
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

        /* === Signatures imprimées : 2 colonnes sans lignes/cadres === */
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
          height: 70px;        /* hauteur de signature/tampon */
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
/* Copie des styles globaux du composant parent, adaptés au contexte enseignant */
.container { max-width: 1400px; margin: 0 auto; background: #f8fafc; min-height: 100vh; color: #2d3748; }
.action-bar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 2rem; padding: 1.5rem; background: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; color: #2d3748; }
.btn-modern { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500; transition: all 0.2s ease; text-decoration: none; border: none; cursor: pointer; color: #2d3748; }
.btn-modern:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-decoration: none; color: #2d3748; }
.btn-modern i { font-size: 0.9rem; }
.btn-print { background: linear-gradient(135deg, #00d4aa, #00b894); color: white; border: none; }
.btn-print:hover { background: linear-gradient(135deg, #00b894, #00d4aa); color: white; }

.parent-card { background: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; border: 1px solid #e2e8f0; margin-bottom: 2rem; }
.parent-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; color: white; }
.header-content { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem; }
.avatar-section { display: flex; align-items: center; gap: 1rem; }
.avatar-circle { width: 64px; height: 64px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.5rem; border: 2px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); color: white; }
.header-info h1 { margin: 0; font-size: 1.75rem; font-weight: 700; color: white; }
.header-subtitle { margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.95rem; color: white; }
.header-badges { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; }
.badge-modern { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(255, 255, 255, 0.15); border-radius: 20px; font-size: 0.85rem; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); color: white; }
.parent-body { padding: 2rem; }

.info-cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.info-card { display: flex; gap: 1rem; padding: 1.5rem; border-radius: 12px; transition: all 0.2s ease; border: 1px solid #e2e8f0; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); }
.info-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.card-icon { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; flex-shrink: 0; }
.father-card .card-icon { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.mother-card .card-icon { background: linear-gradient(135deg, #f093fb, #f5576c); }
.contact-card .card-icon { background: linear-gradient(135deg, #43e97b, #38f9d7); }
.card-content { flex: 1; }
.card-content h3 { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #718096; margin: 0 0 0.25rem 0; font-weight: 600; }
.card-content h4 { font-size: 1.1rem; font-weight: 700; color: #2d3748; margin: 0 0 0.75rem 0; }
.contact-info { display: flex; flex-direction: column; gap: 0.25rem; }
.contact-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #718096; }
.contact-item i { width: 16px; color: #667eea; }

.students-section { background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
.section-header { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 1.5rem; border-bottom: 1px solid #e2e8f0; }
.section-header h2 { margin: 0; font-size: 1.25rem; font-weight: 600; color: #2d3748; display: flex; align-items: center; gap: 0.5rem; }
.section-header i { color: #667eea; }
.students-table-wrapper { overflow-x: auto; }
.students-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.students-table th { background: #f8f9fa; padding: 1rem; text-align: left; font-weight: 600; color: #2d3748; border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
.students-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #2d3748; }
.student-row:hover { background: #f8f9fa; }
.id-cell { font-weight: 700; color: #667eea; }
.name-cell { font-weight: 500; color: #2d3748; }
.btn-view { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; text-decoration: none; border-radius: 6px; font-size: 0.8rem; font-weight: 500; transition: all 0.2s ease; }
.btn-view:hover { transform: translateY(-1px); box-shadow: 0 1px 3px rgba(0,0,0,0.1); color: white; text-decoration: none; }
.empty-state { text-align: center; padding: 3rem 1rem; }
.empty-content { color: #718096; }
.empty-content i { font-size: 2rem; margin-bottom: 0.5rem; display: block; }

/* Charte - Masquée par défaut */
.charter { display: none; }

/* Responsive */
@media (max-width: 768px) {
  .action-bar { flex-direction: column; align-items: stretch; gap: 1rem; }
  .header-content { flex-direction: column; align-items: center; text-align: center; }
  .info-cards-grid { grid-template-columns: 1fr; }
  .students-table { font-size: 0.8rem; }
  .students-table th, .students-table td { padding: 0.75rem 0.5rem; }
}

@media (max-width: 480px) {
  .container { padding: 1rem; }
  .parent-header, .parent-body { padding: 1rem; }
  .info-card { padding: 1rem; }
  .avatar-circle { width: 48px; height: 48px; font-size: 1.2rem; }
  .header-info h1 { font-size: 1.4rem; }
}

/* Impression */
@media print {
  body * { visibility: hidden !important; }
  #charter-sheet, #charter-sheet * { visibility: visible !important; }
  #charter-sheet { position: absolute; left: 0; top: 0; width: 100%; }
  .charter { display: block !important; }
}

@media print {
  .pagebreak-before { break-before: page; page-break-before: always; }
  .avoid-break-inside { break-inside: avoid; page-break-inside: avoid; }
}

/* === Signatures écran : 2 colonnes sans lignes/cadres === */
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
  height: 70px;          /* hauteur de signature/tampon */
  margin: 4px 0 6px 0;
  background: transparent;
}
.sig-note {
  font-size: 10px;
  color: #7f8c8d;
  font-style: italic;
}
</style>
