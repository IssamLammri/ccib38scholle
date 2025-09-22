<template>
  <div class="container py-4" lang="fr">
    <!-- Barre d'actions améliorée -->
    <div class="action-bar">
      <a :href="$routing.generate('app_parent_entity_index')" class="btn btn-outline-secondary btn-modern">
        <i class="fas fa-arrow-left"></i>
        <span>Retour à la liste</span>
      </a>

      <div class="student-selector-wrapper">
        <div class="student-selector">
          <label for="studentForCharter" class="selector-label">
            <i class="fas fa-user-graduate"></i>
            Élève concerné
          </label>
          <select
              id="studentForCharter"
              class="form-select modern-select"
              v-model.number="selectedStudentId"
          >
            <option :value="0">-- Sélectionnez un élève --</option>
            <option v-for="s in normalizedStudents" :key="s.id" :value="s.id">
              {{ s.lastName }} {{ s.firstName }} — né(e) le {{ formatDate(s.birthDate) }}
            </option>
          </select>
        </div>
      </div>

      <div class="action-buttons">
        <button
            class="btn btn-print"
            @click="printCharter"
            :disabled="!selectedStudent"
            :class="{ 'disabled': !selectedStudent }"
        >
          <i class="fas fa-print"></i>
          <span>Imprimer la Charte</span>
        </button>
        <a :href="$routing.generate('app_parent_entity_edit', { id: parent.id })" class="btn btn-warning btn-modern">
          <i class="fas fa-edit"></i>
          <span>Modifier</span>
        </a>
      </div>
    </div>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="mb-4" />

    <!-- Alerte améliorée -->
    <div v-if="!selectedStudent" class="alert alert-info modern-alert">
      <div class="alert-content">
        <div class="alert-icon">
          <i class="fas fa-info-circle"></i>
        </div>
        <div class="alert-text">
          <strong>Information</strong><br>
          Sélectionnez un élève dans le menu déroulant ci-dessus pour générer et imprimer sa charte personnalisée.
        </div>
      </div>
    </div>

    <!-- FICHE PARENT améliorée -->
    <div id="parent-sheet" class="parent-card">
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
              <i class="fas fa-user-friends"></i>
              <span>{{ studentsCount }} enfant{{ studentsCount > 1 ? 's' : '' }}</span>
            </div>
            <div class="badge badge-modern" v-if="primaryEmail">
              <i class="fas fa-envelope"></i>
              <span>{{ primaryEmail }}</span>
            </div>
            <div class="badge badge-modern" v-if="primaryPhone">
              <i class="fas fa-phone"></i>
              <span>{{ primaryPhone }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="parent-body">
        <!-- Cartes d'information améliorées -->
        <div class="info-cards-grid">
          <div class="info-card father-card">
            <div class="card-icon">
              <i class="fas fa-male"></i>
            </div>
            <div class="card-content">
              <h3>Père</h3>
              <h4>{{ fatherFullName }}</h4>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <span>{{ displayOrNA(clean(parent.fatherEmail)) }}</span>
                </div>
                <div class="contact-item">
                  <i class="fas fa-phone"></i>
                  <span>{{ displayOrNA(clean(parent.fatherPhone)) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="info-card mother-card">
            <div class="card-icon">
              <i class="fas fa-female"></i>
            </div>
            <div class="card-content">
              <h3>Mère</h3>
              <h4>{{ motherFullName }}</h4>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <span>{{ displayOrNA(clean(parent.motherEmail)) }}</span>
                </div>
                <div class="contact-item">
                  <i class="fas fa-phone"></i>
                  <span>{{ displayOrNA(clean(parent.motherPhone)) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="info-card contact-card">
            <div class="card-icon">
              <i class="fas fa-id-card"></i>
            </div>
            <div class="card-content">
              <h3>Contact principal</h3>
              <h4>{{ displayOrNA(clean(parent.fullNameParent)) }}</h4>
              <div class="contact-info">
                <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <span>{{ displayOrNA(clean(parent.emailContact)) }}</span>
                </div>
                <div class="contact-item">
                  <i class="fas fa-phone"></i>
                  <span>{{ displayOrNA(clean(parent.phoneContact)) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tableau des étudiants amélioré -->
        <div class="students-section">
          <div class="section-header">
            <h2>
              <i class="fas fa-users"></i>
              Étudiants associés
            </h2>
          </div>

          <div class="students-table-wrapper">
            <table class="students-table">
              <thead>
              <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Date de naissance</th>
                <th>Niveau</th>
                <th>Actions</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="s in normalizedStudents" :key="s.id" class="student-row">
                <td class="id-cell">{{ s.id }}</td>
                <td class="name-cell">{{ s.lastName }}</td>
                <td class="name-cell">{{ s.firstName }}</td>
                <td class="date-cell">{{ formatDate(s.birthDate) }}</td>
                <td class="level-cell">
                  <span class="level-badge">{{ displayOrNA(s.level) }}</span>
                </td>
                <td class="actions-cell">
                  <a class="btn btn-view" :href="$routing.generate('app_student_show', { id: s.id })">
                    <i class="fas fa-eye"></i>
                    <span>Voir la fiche</span>
                  </a>
                </td>
              </tr>
              <tr v-if="normalizedStudents.length === 0">
                <td colspan="6" class="empty-state">
                  <div class="empty-content">
                    <i class="fas fa-users-slash"></i>
                    <p>Aucun étudiant associé</p>
                  </div>
                </td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- CHARTE AMÉLIORÉE (pour impression/PDF) -->
    <div id="charter-sheet" class="charter">
      <div class="charter-header">
        <div class="charter-branding">
          <img
              class="charter-logo"
              src="/static/icons/logoccib38.webp"
              alt="Logo CCIB"
              loading="lazy"
          />
          <div class="charter-title">
            <h1>Charte de l'Élève et de CCIB38</h1>
            <p class="charter-subtitle">Centre Culturel Ibn Badis Grenoble</p>
          </div>
        </div>
        <div class="charter-metadata">
          <div class="metadata-item">
            <span class="label">Date d'émission</span>
            <span class="value">{{ today }}</span>
          </div>
          <div class="metadata-item">
            <span class="label">N° Dossier Parent</span>
            <span class="value">{{ String(parent.id).padStart(6, '0') }}</span>
          </div>
        </div>
      </div>

      <div class="charter-content">
        <div class="charter-section">
          <h2><span class="section-number">1.</span> Coordonnées du Responsable Légal</h2>
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
                <span class="field-label">Adresse postale complète</span>
                <div class="address-field">
                  {{ selectedStudentPostalAddress || 'Adresse non renseignée' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="charter-section">
          <h2><span class="section-number">2.</span> Élève Concerné par cette Charte</h2>
          <div class="section-content">
            <div v-if="selectedStudent" class="student-info">
              <div class="student-header">
                <div class="student-avatar">
                  {{ selectedStudent.firstName.charAt(0) }}{{ selectedStudent.lastName.charAt(0) }}
                </div>
                <div class="student-details">
                  <h3>{{ selectedStudent.firstName }} {{ selectedStudent.lastName }}</h3>
                  <div class="student-meta">
                    <span class="meta-item">
                      <i class="fas fa-calendar"></i>
                      Né(e) le {{ formatDate(selectedStudent.birthDate) }}
                    </span>
                    <span class="meta-item">
                      <i class="fas fa-graduation-cap"></i>
                      Niveau : {{ displayOrNA(selectedStudent.level) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="no-student-selected">
              <i class="fas fa-user-slash"></i>
              <p>Aucun élève sélectionné pour cette charte</p>
            </div>
          </div>
        </div>

        <div class="charter-section rules-section">
          <h2><span class="section-number">3.</span> Règlement et Engagements Réciproques</h2>
          <div class="section-content">
            <p class="section-intro">
              Cette charte définit les engagements mutuels entre l'élève, ses parents et l'établissement
              scolaire afin de garantir un environnement d'apprentissage optimal et respectueux.
            </p>

            <div class="rules-grid">
              <div class="rule-category">
                <h3><i class="fas fa-clock"></i> Ponctualité et Assiduité</h3>
                <ul class="rule-list">
                  <li><strong>Présence obligatoire :</strong> La présence à tous les cours est impérative pour assurer une progression pédagogique continue.</li>
                  <li><strong>Gestion des retards :</strong> Un retard supérieur à 20 minutes ne permettra pas l'accès à la salle de classe, sauf justification exceptionnelle validée par la direction.</li>
                </ul>
              </div>

              <div class="rule-category">
                <h3><i class="fas fa-handshake"></i> Respect et Comportement</h3>
                <ul class="rule-list">
                  <li><strong>Respect mutuel :</strong> Le respect envers les enseignants, le personnel administratif et les autres étudiants constitue un pilier fondamental de notre établissement.</li>
                  <li><strong>Comportement en classe :</strong> Une attitude calme, attentive et participative est attendue. Les appareils électroniques personnels doivent être éteints et rangés.</li>
                  <li><strong>Préservation du matériel :</strong> Chaque élève s'engage à prendre soin du matériel pédagogique et des locaux mis à disposition.</li>
                </ul>
              </div>

              <div class="rule-category">
                <h3><i class="fas fa-book"></i> Engagement Scolaire</h3>
                <ul class="rule-list">
                  <li><strong>Matériel requis :</strong> L'étudiant doit impérativement se présenter avec l'intégralité de son matériel scolaire (manuels, cahiers, exercices, etc.). En cas d'oubli répété, l'enseignant se réserve le droit de refuser l'accès au cours.</li>
                  <li><strong>Devoirs et travaux :</strong> Les travaux assignés doivent être réalisés dans les délais impartis avec le sérieux requis.</li>
                </ul>
              </div>

              <div class="rule-category">
                <h3><i class="fas fa-comments"></i> Communication École-Famille</h3>
                <ul class="rule-list">
                  <li><strong>Suivi personnalisé :</strong> En cas de difficultés comportementales ou pédagogiques récurrentes, un entretien tripartite (élève-parents-direction) sera organisé pour définir ensemble des solutions constructives.</li>
                  <li><strong>Information régulière :</strong> Les parents seront informés régulièrement des progrès et difficultés de leur enfant.</li>
                </ul>
              </div>
            </div>
            <br><br><br><br><br><br>

            <div class="payment-terms">
              <h3><i class="fas fa-euro-sign"></i> Modalités Financières</h3>
              <div class="terms-content">
                <div class="term-item">
                  <strong>Paiement trimestriel :</strong> Les frais de scolarité sont exigibles au début de chaque trimestre.
                </div>
                <div class="term-item">
                  <strong>Droit de rétractation :</strong> Un remboursement intégral est possible après le premier cours uniquement, sur demande formulée avant le deuxième cours.
                </div>
                <div class="term-item important">
                  <strong>Aucun remboursement :</strong> Une fois le deuxième cours entamé, tout trimestre commencé est dû intégralement, sans possibilité de remboursement en cas d'abandon.
                </div>
              </div>
            </div>

            <div class="sanctions-notice">
              <h3><i class="fas fa-exclamation-triangle"></i> Mesures Disciplinaires</h3>
              <p>Le non-respect répété des présentes dispositions peut entraîner des sanctions graduelles : avertissement oral, avertissement écrit, convocation des parents, et en dernier recours, l'exclusion définitive de l'établissement.</p>
            </div>
          </div>
        </div>

        <div class="charter-signature">
          <div class="signature-location">
            <span class="location-label">Établi à</span>
            <span class="location-value">Grenoble</span>
            <span class="date-label">Le</span>
            <span class="date-value">{{ today }}</span>
          </div>

          <div class="signature-blocks">
            <div class="signature-block">
              <div class="signature-title">Signature du Responsable Légal</div>
              <div class="signature-area"></div>
              <div class="signature-name">{{ headerTitle }}</div>
              <div class="signature-note">(Précédée de la mention « Lu et approuvé »)</div>
            </div>

            <div class="signature-block">
              <div class="signature-title">Cachet et Signature de centre</div>
              <div class="signature-area"></div>
              <div class="signature-name">Centre Culturel Ibn Badis Grenoble</div>
              <div class="signature-note">Direction Pédagogique</div>
            </div>
          </div>

          <div class="charter-footer">
            <p class="footer-text">
              Cette charte, signée par les deux parties, engage moralement et pédagogiquement
              l'élève et sa famille dans le respect du règlement intérieur de l'établissement.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import html2pdf from "html2pdf.js";
import Alert from "../../ui/Alert.vue";

export default {
  name: "ShowParent",
  components: { Alert },
  props: {
    parent: { type: Object, required: true },
    students: { type: Array, required: true },
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
      selectedStudentId: 0,
    };
  },
  computed: {
    today() {
      return new Date().toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric"
      });
    },
    fatherFullName() {
      const ln = this.clean(this.parent.fatherLastName);
      const fn = this.clean(this.parent.fatherFirstName);
      return ln || fn ? `${ln} ${fn}`.trim() : "Non disponible";
    },
    motherFullName() {
      const ln = this.clean(this.parent.motherLastName);
      const fn = this.clean(this.parent.motherFirstName);
      return ln || fn ? `${ln} ${fn}`.trim() : "Non disponible";
    },
    primaryEmail() {
      return (
          this.clean(this.parent.emailContact) ||
          this.clean(this.parent.fatherEmail) ||
          this.clean(this.parent.motherEmail) ||
          ""
      );
    },
    primaryPhone() {
      return (
          this.clean(this.parent.phoneContact) ||
          this.clean(this.parent.fatherPhone) ||
          this.clean(this.parent.motherPhone) ||
          ""
      );
    },
    headerTitle() {
      return this.clean(this.parent.fullNameParent) || this.fatherFullName || "Fiche Parent";
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
      return initials || "P";
    },
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
        address: this.clean(s.address) || "",
        postalCode: this.clean(s.postalCode) || "",
        city: this.clean(s.city) || "",
      }));
    },
    selectedStudent() {
      if (!this.selectedStudentId) return null;
      return this.normalizedStudents.find((s) => s.id === this.selectedStudentId) || null;
    },
    selectedStudentPostalAddress() {
      if (this.selectedStudent) {
        const lines = [];
        if (this.selectedStudent.address) lines.push(this.selectedStudent.address);
        const cpVille = [this.selectedStudent.postalCode, this.selectedStudent.city].filter(Boolean).join(' ');
        if (cpVille) lines.push(cpVille);
        if (lines.length) return lines.join('\n');
      }
      const parentAddr =
          this.clean(this.parent.address) ||
          this.clean(this.parent.postalAddress) ||
          this.clean(this.parent.addressLine1) || "";
      return parentAddr || "";
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
    printCharter() {
      if (!this.selectedStudent) return;

      const el = document.getElementById("charter-sheet");
      if (!el) return;

      const win = window.open("", "_blank");
      const html = `
        <html>
        <head>
          <meta charset="utf-8" />
          <title>Charte - ${this.selectedStudent.lastName} ${this.selectedStudent.firstName}</title>
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

        .charter-branding {
          display: flex;
          align-items: center;
          gap: 12px;
        }

        .charter-logo { height: 45px; width: auto; }

        .charter-title h1 {
          font-size: 20px;
          font-weight: 700;
          color: #2c3e50;
          margin: 0;
          line-height: 1.2;
        }

        .charter-subtitle {
          font-size: 12px;
          color: #7f8c8d;
          margin: 2px 0 0 0;
        }

        .charter-metadata {
          text-align: right;
          font-size: 11px;
        }

        .metadata-item {
          display: block;
          margin-bottom: 4px;
        }

        .metadata-item .label {
          font-weight: 600;
          color: #7f8c8d;
        }

        .metadata-item .value {
          font-weight: 400;
          color: #2c3e50;
          margin-left: 6px;
        }

        .charter-section {
          margin-bottom: 18px;
        }

        .charter-section h2 {
          font-size: 16px;
          font-weight: 700;
          color: #34495e;
          margin: 0 0 10px 0;
          display: flex;
          align-items: center;
        }

        .section-number {
          background: #3498db;
          color: white;
          width: 24px;
          height: 24px;
          border-radius: 50%;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          font-size: 12px;
          margin-right: 8px;
        }

        .info-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 15px;
          margin-top: 8px;
        }

        .field {
          margin-bottom: 8px;
        }

        .field-label {
          font-weight: 600;
          color: #7f8c8d;
          font-size: 11px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          display: block;
          margin-bottom: 2px;
        }

        .field-value {
          color: #2c3e50;
          font-size: 13px;
          display: block;
        }

        .address-field {
          border: 1px solid #bdc3c7;
          border-radius: 4px;
          padding: 8px;
          min-height: 45px;
          white-space: pre-line;
          font-size: 13px;
          background: #f8f9fa;
          margin-top: 4px;
        }

        .student-info {
          border: 1px solid #3498db;
          border-radius: 6px;
          padding: 12px;
          background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
        }

        .student-header {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 10px;
        }

        .student-avatar {
          width: 40px;
          height: 40px;
          border-radius: 50%;
          background: #3498db;
          color: white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: 700;
          font-size: 14px;
        }

        .student-details h3 {
          margin: 0;
          font-size: 16px;
          color: #2c3e50;
        }

        .student-meta {
          display: flex;
          gap: 15px;
          margin-top: 4px;
        }

        .meta-item {
          font-size: 12px;
          color: #7f8c8d;
        }

        .meta-item i {
          margin-right: 4px;
        }

        .rules-section .section-intro {
          font-style: italic;
          color: #7f8c8d;
          margin-bottom: 15px;
          font-size: 13px;
          text-align: justify;
        }

        .rule-category {
          margin-bottom: 12px;
          page-break-inside: avoid;
        }

        .rule-category h3 {
          font-size: 14px;
          font-weight: 600;
          color: #34495e;
          margin: 0 0 6px 0;
          display: flex;
          align-items: center;
        }

        .rule-category h3 i {
          margin-right: 6px;
          color: #3498db;
        }

        .rule-list {
          margin: 0;
          padding-left: 18px;
          list-style-type: none;
        }

        .rule-list li {
          margin-bottom: 4px;
          font-size: 12px;
          line-height: 1.4;
          position: relative;
        }

        .rule-list li::before {
          content: "•";
          color: #3498db;
          font-weight: bold;
          position: absolute;
          left: -12px;
        }

        .payment-terms {
          background: #fff3cd;
          border: 1px solid #ffeaa7;
          border-radius: 6px;
          padding: 12px;
          margin: 15px 30px;
        }

        .payment-terms h3 {
          font-size: 14px;
          color: #856404;
          margin: 0 0 8px 0;
          display: flex;
          align-items: center;
        }

        .payment-terms h3 i {
          margin-right: 6px;
        }

        .term-item {
          font-size: 12px;
          margin-bottom: 6px;
          line-height: 1.4;
        }

        .term-item.important {
          font-weight: 600;
          color: #721c24;
        }

        .sanctions-notice {
          background: #f8d7da;
          border: 1px solid #f5c6cb;
          border-radius: 6px;
          padding: 12px;
          margin: 15px 0;
        }

        .sanctions-notice h3 {
          font-size: 14px;
          color: #721c24;
          margin: 0 0 6px 0;
          display: flex;
          align-items: center;
        }

        .sanctions-notice h3 i {
          margin-right: 6px;
        }

        .sanctions-notice p {
          font-size: 12px;
          margin: 0;
          line-height: 1.4;
          color: #721c24;
        }

        .charter-signature {
          margin-top: 25px;
          page-break-inside: avoid;
        }

        .signature-location {
          text-align: right;
          margin-bottom: 20px;
          font-size: 12px;
        }

        .location-label, .date-label {
          font-weight: 600;
          color: #7f8c8d;
        }

        .location-value, .date-value {
          color: #2c3e50;
          margin-left: 4px;
          margin-right: 15px;
        }

        .signature-blocks {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 20px;
        }

        .signature-block {
          text-align: center;
        }

        .signature-title {
          font-weight: 600;
          font-size: 12px;
          color: #34495e;
          margin-bottom: 15px;
        }

        .signature-area {
          height: 50px;
          border-bottom: 1px solid #2c3e50;
          margin: 15px 0 8px 0;
        }

        .signature-name {
          font-weight: 600;
          font-size: 11px;
          color: #2c3e50;
        }

        .signature-note {
          font-size: 10px;
          color: #7f8c8d;
          font-style: italic;
          margin-top: 4px;
        }

        .charter-footer {
          margin-top: 20px;
          padding-top: 15px;
          border-top: 1px solid #ecf0f1;
          text-align: center;
        }

        .footer-text {
          font-size: 11px;
          color: #7f8c8d;
          font-style: italic;
          margin: 0;
          line-height: 1.3;
        }
      `;
    },
  },
};
</script>

<style scoped>
/* Suppression des variables CSS - utilisation directe des couleurs */

/* Layout général */
.container {
  max-width: 1400px;
  margin: 0 auto;
  background: #f8fafc;
  min-height: 100vh;
  color: #2d3748;
}

/* Barre d'actions améliorée */
.action-bar {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  border: 1px solid #e2e8f0;
  color: #2d3748;
}

.btn-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
  text-decoration: none;
  border: none;
  cursor: pointer;
  color: #2d3748;
}

.btn-modern:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  text-decoration: none;
  color: #2d3748;
}

.btn-modern i {
  font-size: 0.9rem;
}

.btn-print {
  background: linear-gradient(135deg, #00d4aa, #00b894);
  color: white;
  border: none;
}

.btn-print:hover:not(.disabled) {
  background: linear-gradient(135deg, #00b894, #00d4aa);
  color: white;
}

.btn-print.disabled {
  background: #cbd5e0;
  color: #a0aec0;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Sélecteur d'élève */
.student-selector-wrapper {
  flex: 1;
  max-width: 400px;
}

.student-selector {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.selector-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #718096;
  margin: 0;
}

.modern-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  transition: all 0.2s ease;
  font-size: 0.875rem;
  color: #2d3748;
}

.modern-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  outline: none;
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
}

/* Alerte moderne */
.modern-alert {
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
  border-left: 4px solid #4facfe;
  color: #2d3748;
}

.alert-content {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.alert-icon {
  color: #4facfe;
  font-size: 1.25rem;
  margin-top: 0.125rem;
}

.alert-text {
  flex: 1;
  font-size: 0.9rem;
  line-height: 1.5;
  color: #2d3748;
}

/* Carte parent améliorée */
.parent-card {
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  overflow: hidden;
  border: 1px solid #e2e8f0;
  margin-bottom: 2rem;
}

.parent-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.avatar-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.avatar-circle {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.5rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  color: white;
}

.header-info h1 {
  margin: 0;
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
}

.header-subtitle {
  margin: 0.5rem 0 0 0;
  opacity: 0.9;
  font-size: 0.95rem;
  color: white;
}

.header-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: flex-start;
}

.badge-modern {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 20px;
  font-size: 0.85rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}

.parent-body {
  padding: 2rem;
}

/* Cartes d'information */
.info-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.info-card {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-radius: 12px;
  transition: all 0.2s ease;
  border: 1px solid #e2e8f0;
  background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.info-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.card-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
  flex-shrink: 0;
}

.father-card .card-icon {
  background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.mother-card .card-icon {
  background: linear-gradient(135deg, #f093fb, #f5576c);
}

.contact-card .card-icon {
  background: linear-gradient(135deg, #43e97b, #38f9d7);
}

.card-content {
  flex: 1;
}

.card-content h3 {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #718096;
  margin: 0 0 0.25rem 0;
  font-weight: 600;
}

.card-content h4 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0 0 0.75rem 0;
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #718096;
}

.contact-item i {
  width: 16px;
  color: #667eea;
}

/* Section étudiants */
.students-section {
  background: white;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.section-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #2d3748;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.section-header i {
  color: #667eea;
}

.students-table-wrapper {
  overflow-x: auto;
}

.students-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.students-table th {
  background: #f8f9fa;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #2d3748;
  border-bottom: 2px solid #e2e8f0;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.students-table td {
  padding: 1rem;
  border-bottom: 1px solid #f1f5f9;
  color: #2d3748;
}

.student-row:hover {
  background: #f8f9fa;
}

.id-cell {
  font-weight: 700;
  color: #667eea;
}

.name-cell {
  font-weight: 500;
  color: #2d3748;
}

.date-cell {
  color: #718096;
}

.level-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
  border: 1px solid #bbdefb;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 500;
  color: #2d3748;
}

.btn-view {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #4facfe, #00f2fe);
  color: white;
  text-decoration: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-view:hover {
  transform: translateY(-1px);
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  color: white;
  text-decoration: none;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-content {
  color: #718096;
}

.empty-content i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  display: block;
}

.empty-content p {
  margin: 0;
  font-size: 0.9rem;
}

/* Charte - Masquée par défaut */
.charter {
  display: none;
}

/* Responsive */
@media (max-width: 768px) {
  .action-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .student-selector-wrapper {
    max-width: none;
  }

  .action-buttons {
    justify-content: center;
  }

  .header-content {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .info-cards-grid {
    grid-template-columns: 1fr;
  }

  .students-table {
    font-size: 0.8rem;
  }

  .students-table th,
  .students-table td {
    padding: 0.75rem 0.5rem;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 1rem;
  }

  .parent-header,
  .parent-body {
    padding: 1rem;
  }

  .info-card {
    padding: 1rem;
  }

  .avatar-circle {
    width: 48px;
    height: 48px;
    font-size: 1.2rem;
  }

  .header-info h1 {
    font-size: 1.4rem;
  }
}

/* Styles pour l'impression */
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
/* Dans getOptimizedPrintCSS() */
@media print {
  .pagebreak-before {
    break-before: page;          /* moderne: Chrome/Edge/Firefox récents */
    page-break-before: always;   /* compat anciens navigateurs */
  }
  /* (optionnel) éviter que le bloc soit re-coupé après le titre */
  .avoid-break-inside {
    break-inside: avoid;
    page-break-inside: avoid;
  }
}

</style>