<template>
  <div class="class-details">

    <!-- Top bar -->
    <header class="topbar">
      <a :href="$routing.generate('app_study_class_index')" class="btn btn-ghost">
        <i class="fas fa-arrow-left"></i>
        <span>Retour</span>
      </a>

      <div class="topbar-actions">
        <button @click="printBooksReport" class="btn btn-soft" type="button">
          <i class="fas fa-print"></i>
          <span>Imprimer Livres</span>
        </button>

        <button @click="printClassReport" class="btn btn-soft" type="button">
          <i class="fas fa-print"></i>
          <span>Imprimer</span>
        </button>

        <a
            :href="$routing.generate('app_study_class_edit', { id: studyClass.id })"
            class="btn btn-primary"
        >
          <i class="fas fa-edit"></i>
          <span>Modifier</span>
        </a>
      </div>
    </header>

    <!-- Hero -->
    <section class="hero">
      <div class="hero-main">
        <div class="pill">
          <i class="fas fa-graduation-cap"></i>
          <span>{{ studyClass.levelClass }}</span>
        </div>

        <h1 class="hero-title">{{ studyClass.name }}</h1>
        <p class="hero-subtitle">{{ studyClass.speciality }}</p>

        <div class="hero-metas">
          <div class="meta">
            <i class="fas fa-users"></i>
            <span>{{ filteredStudents.length }} étudiants {{ filterActive ? 'actifs' : 'inactifs' }}</span>
          </div>
          <div class="meta">
            <i class="fas fa-calendar"></i>
            <span>{{ studyClass.day }}</span>
          </div>
          <div class="meta">
            <i class="fas fa-clock"></i>
            <span>{{ formatTime(studyClass.startHour) }} - {{ formatTime(studyClass.endHour) }}</span>
          </div>
          <div class="meta" v-if="studyClass.schoolYear">
            <i class="fas fa-school"></i>
            <span>{{ studyClass.schoolYear }}</span>
          </div>
          <div class="meta" v-if="studyClass.principalRoom">
            <i class="fas fa-door-open"></i>
            <span>{{ formatRoom(studyClass.principalRoom) }}</span>
          </div>
        </div>
      </div>

      <div class="hero-bg" aria-hidden="true">
        <span class="blob b1"></span>
        <span class="blob b2"></span>
        <span class="blob b3"></span>
      </div>
    </section>

    <alert v-if="messageAlert" :text="messageAlert" :type="typeAlert" class="page-alert" />

    <!-- Content grid -->
    <div class="grid">
      <!-- Left: infos -->
      <section class="card">
        <div class="card-header">
          <h2><i class="fas fa-info-circle"></i> Informations</h2>
        </div>

        <div class="info-list">
          <div class="info-item">
            <div class="info-label">Niveau</div>
            <div class="info-value">{{ studyClass.levelClass }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Spécialité</div>
            <div class="info-value">{{ studyClass.speciality }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Type</div>
            <div class="info-value">{{ studyClass.classType }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Jour</div>
            <div class="info-value">{{ studyClass.day }}</div>
          </div>

          <div class="info-item">
            <div class="info-label">Horaires</div>
            <div class="info-value">
              {{ formatTime(studyClass.startHour) }} - {{ formatTime(studyClass.endHour) }}
            </div>
          </div>

          <div class="info-item" v-if="studyClass.principalTeacher">
            <div class="info-label">Professeur</div>
            <div class="info-value">
              {{ studyClass.principalTeacher.firstName }} {{ studyClass.principalTeacher.lastName }}
            </div>
          </div>

          <div class="info-item" v-if="studyClass.schoolYear">
            <div class="info-label">Année scolaire</div>
            <div class="info-value">{{ studyClass.schoolYear }}</div>
          </div>

          <div class="info-item" v-if="studyClass.principalRoom">
            <div class="info-label">Salle principale</div>
            <div class="info-value">{{ formatRoom(studyClass.principalRoom) }}</div>
          </div>
        </div>

        <!-- WhatsApp -->
        <div class="divider" v-if="studyClass.whatsappUrl"></div>

        <div class="whatsapp" v-if="studyClass.whatsappUrl">
          <div class="whatsapp-head">
            <div class="whatsapp-icon"><i class="fab fa-whatsapp"></i></div>
            <div>
              <div class="whatsapp-title">Groupe WhatsApp</div>
              <div class="whatsapp-sub">Accès rapide + copie du lien</div>
            </div>
          </div>

          <div class="whatsapp-actions">
            <a
                :href="normalizedWhatsappUrl"
                target="_blank"
                rel="noopener"
                class="btn btn-whatsapp"
            >
              <i class="fab fa-whatsapp"></i>
              Ouvrir
            </a>

            <button type="button" class="btn btn-ghost" @click="copyWhatsappUrl">
              <i class="fas fa-copy"></i>
              Copier le lien
            </button>
          </div>

          <small class="whatsapp-link">{{ studyClass.whatsappUrl }}</small>
        </div>
      </section>

      <!-- Right: students -->
      <section class="card">
        <div class="card-header card-header-row">
          <h2><i class="fas fa-users"></i> Étudiants</h2>

          <div class="students-controls">
            <label class="segmented">
              <input type="checkbox" v-model="filterActive" />
              <span class="segmented-track">
                <span class="segmented-option">{{ filterActive ? 'Actifs' : 'Inactifs' }}</span>
              </span>
            </label>

            <button
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#newStudentModal"
                type="button"
            >
              <i class="fas fa-plus"></i>
              <span>Ajouter</span>
            </button>
          </div>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
            <tr>
              <th class="col-id">ID</th>
              <th>Nom</th>
              <th>Prénom</th>
              <th class="col-date">Naissance</th>
              <th class="col-level">Niveau</th>
              <th class="col-books">Livres</th>
              <th v-if="isCompetition" class="col-bookname">Livre (Compétition)</th>
              <th class="col-status">Statut</th>
              <th class="col-actions">Actions</th>
            </tr>
            </thead>

            <tbody>
            <tr
                v-for="studentClassRegistered in filteredStudents"
                :key="studentClassRegistered.id"
                :class="['col', { inactive: !studentClassRegistered.active }]"
            >
              <td class="muted">{{ studentClassRegistered.student.id }}</td>

              <td>
                <div class="name">
                  <div class="avatar">
                    {{ studentClassRegistered.student.lastName?.charAt(0) }}
                  </div>
                  <div class="name-lines">
                    <div class="name-main">{{ studentClassRegistered.student.lastName }}</div>
                    <div class="name-sub">#{{ studentClassRegistered.id }}</div>
                  </div>
                </div>
              </td>

              <td>{{ studentClassRegistered.student.firstName }}</td>
              <td class="muted">
                {{ new Date(studentClassRegistered.student.birthDate).toLocaleDateString('fr-FR') }}
              </td>

              <td>
                <span class="badge">{{ studentClassRegistered.student.levelClass }}</span>
              </td>

              <!-- Livres -->
              <td>
                  <span class="books tooltip">
                    {{ booksIndex[studentClassRegistered.id]?.count ?? 0 }}
                    <span
                        class="tooltip-panel"
                        v-if="booksIndex[studentClassRegistered.id]?.count"
                    >
                      <strong>Livres</strong>
                      <span class="tooltip-line"
                            v-for="(t, i) in booksIndex[studentClassRegistered.id].list"
                            :key="i"
                      >
                        • {{ t }}
                      </span>
                    </span>
                  </span>
              </td>

              <!-- Competition bookName -->
              <td v-if="isCompetition">
                <div class="bookname-edit">
                  <input
                      class="input"
                      type="text"
                      :value="bookNameDraft[studentClassRegistered.id]"
                      @input="bookNameDraft[studentClassRegistered.id] = $event.target.value"
                      placeholder="Nom du livre..."
                  />

                  <button
                      type="button"
                      class="btn btn-ok"
                      title="Valider"
                      :disabled="
                        savingBookName[studentClassRegistered.id] ||
                        (bookNameDraft[studentClassRegistered.id] || '') === (studentClassRegistered.bookName || '')
                      "
                      @click="updateBookName(studentClassRegistered)"
                  >
                    <i v-if="savingBookName[studentClassRegistered.id]" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-check"></i>
                  </button>
                </div>
              </td>

              <!-- Statut -->
              <td>
                <label class="switch">
                  <input
                      type="checkbox"
                      :checked="studentClassRegistered.active"
                      @change="confirmDeactivation(studentClassRegistered)"
                  />
                  <span class="switch-ui"></span>
                </label>
              </td>

              <!-- Actions -->
              <td>
                <button
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteConfirmationModal"
                    @click="studentToDelete = studentClassRegistered"
                    type="button"
                >
                  <i class="fas fa-trash-alt"></i>
                  <span class="hide-sm">Supprimer</span>
                </button>
              </td>
            </tr>

            <tr v-if="!filteredStudents.length">
              <td :colspan="isCompetition ? 9 : 8" class="empty">
                <div class="empty-box">
                  <i class="fas fa-users-slash"></i>
                  <div class="empty-title">
                    Aucun étudiant {{ filterActive ? 'actif' : 'inactif' }}
                  </div>
                  <div class="empty-sub">
                    Cette classe ne contient aucun étudiant {{ filterActive ? 'actif' : 'inactif' }} pour le moment.
                  </div>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="deactivationConfirmationModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header">
            <div class="modal-badge warning"><i class="fas fa-exclamation-triangle"></i></div>
            <h5 class="modal-title">Désactiver l'étudiant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="callout">
              <p><strong>Attention :</strong> vérifiez que l'élève a réglé tous ses frais avant de procéder.</p>
              <ul class="callout-list">
                <li><i class="fas fa-check-circle"></i> Paiements à jour → Désactiver</li>
                <li><i class="fas fa-times-circle"></i> Paiements en attente → Contacter l'administration</li>
              </ul>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Annuler
            </button>
            <button type="button" class="btn btn-warn" @click="deactivateStudent" data-bs-dismiss="modal">
              <i class="fas fa-user-slash"></i> Désactiver
            </button>
          </div>
        </div>
      </div>
    </div>

    <new-student-to-class-modal :students="studentsNotInStudyClass" :studyClass="studyClass" />

    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-modern">
          <div class="modal-header">
            <div class="modal-badge danger"><i class="fas fa-trash-alt"></i></div>
            <h5 class="modal-title">Supprimer l'étudiant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <p>Cette action supprimera définitivement cet étudiant de la classe.</p>
            <p><strong>Cette action est irréversible.</strong></p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">
              <i class="fas fa-times"></i> Annuler
            </button>
            <button type="button" class="btn btn-danger" @click="deleteStudent" data-bs-dismiss="modal">
              <i class="fas fa-trash-alt"></i> Confirmer
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";
import NewStudentToClassModal from "./NewStudentToClassModal.vue";

export default {
  name: "StudyClassDetails",
  components: { Alert, NewStudentToClassModal },
  props: {
    studyClass: { type: Object, required: true },
    studentsInStudyClass: { type: Array, required: true },
    studentsNotInStudyClass: { type: Array, required: true }
  },
  data() {
    return {
      messageAlert: null,
      typeAlert: null,
      studentToDelete: null,
      studentToDeactivate: null,
      savingBookName: {},
      bookNameDraft: {},
      filterActive: true,
      localStudentsInStudyClass: [...this.studentsInStudyClass],
    };
  },
  computed: {
    isCompetition() {
      return this.studyClass?.classType === 'Competition';
    },
    normalizedWhatsappUrl() {
      const url = this.studyClass?.whatsappUrl || '';
      if (!url) return '#';
      if (url.startsWith('http://') || url.startsWith('https://')) return url;
      return 'https://' + url;
    },
    filteredStudents() {
      return this.localStudentsInStudyClass.filter(s => s.active === this.filterActive);
    },
    booksIndex() {
      const idx = {};
      for (const r of this.localStudentsInStudyClass) {
        const payments = r?.student?.payments || [];
        const names = payments
            .filter(p => p && p.serviceType === 'livres')
            .flatMap(p => Array.isArray(p.bookItems) ? p.bookItems : [])
            .map(bi => bi?.book?.name)
            .filter(Boolean);
        idx[r.id] = { count: names.length, list: names };
      }
      return idx;
    }
  },
  mounted() {
    for (const r of this.localStudentsInStudyClass) {
      this.$set
          ? this.$set(this.bookNameDraft, r.id, r.bookName || '')
          : (this.bookNameDraft[r.id] = r.bookName || '');
    }
  },
  watch: {
    localStudentsInStudyClass: {
      deep: true,
      handler(list) {
        for (const r of list) {
          if (this.bookNameDraft[r.id] === undefined) {
            this.bookNameDraft[r.id] = r.bookName || '';
          }
        }
      }
    }
  },
  methods: {
    async updateBookName(registration) {
      const id = registration.id;
      const newName = (this.bookNameDraft[id] || '').trim();
      this.savingBookName = { ...this.savingBookName, [id]: true };

      try {
        const url = this.$routing.generate('_update_book_name_registered', { id });
        await this.$axios.post(url, { bookName: newName }, { headers: { Accept: 'application/json' } });

        this.localStudentsInStudyClass = this.localStudentsInStudyClass.map(r =>
            r.id === id ? { ...r, bookName: newName } : r
        );

        this.messageAlert = 'Livre mis à jour.';
        this.typeAlert = 'success';
      } catch (e) {
        console.error(e);
        this.messageAlert = "Erreur lors de la mise à jour du livre.";
        this.typeAlert = "danger";
        this.bookNameDraft[id] = registration.bookName || '';
      } finally {
        this.savingBookName = { ...this.savingBookName, [id]: false };
      }
    },

    async copyWhatsappUrl() {
      const url = this.studyClass?.whatsappUrl;
      if (!url) return;

      try {
        await navigator.clipboard.writeText(url);
        this.messageAlert = "Lien WhatsApp copié dans le presse-papiers.";
        this.typeAlert = "success";
      } catch (e) {
        console.error("Erreur lors de la copie du lien WhatsApp", e);
        this.messageAlert = "Impossible de copier le lien WhatsApp.";
        this.typeAlert = "danger";
      }
    },

    printBooksReport() {
      const html = this.generateBooksPrintContent();
      const w = window.open('', '_blank');
      w.document.write(html);
      w.document.close();
      w.onload = function () {
        w.print();
        w.close();
      };
    },

    generateBooksPrintContent() {
      const currentDate = new Date().toLocaleDateString('fr-FR');
      const registrations = this.localStudentsInStudyClass;

      const rows = registrations.map(r => {
        const names = (r?.student?.payments || [])
            .filter(p => p && p.serviceType === 'livres')
            .flatMap(p => Array.isArray(p.bookItems) ? p.bookItems : [])
            .map(bi => bi?.book?.name)
            .filter(Boolean);

        return `
          <tr>
            <td>${r.student.lastName?.toUpperCase() || ''}</td>
            <td>${r.student.firstName || ''}</td>
            <td>${names.length}</td>
            <td>${names.length ? names.join(' | ') : '—'}</td>
          </tr>
        `;
      }).join('');

      return `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Livres à attribuer - ${this.studyClass?.name || ''}</title>
          <style>
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 20px; color:#111; }
            h1 { margin: 0 0 6px; }
            .sub { color:#555; margin-bottom: 16px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; border-bottom: 1px solid #eee; text-align:left; vertical-align: top; }
            th { background: #f6f7ff; }
            tr:nth-child(even){ background:#fafbff; }
            .footer { margin-top: 18px; font-size:12px; color:#777; text-align:center; }
            @media print {
              body { margin: 8mm; }
              th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            }
          </style>
        </head>
        <body>
          <h1>Livres à attribuer</h1>
          <div class="sub">
            Classe: <strong>${this.studyClass?.name || ''}</strong>
            &nbsp;•&nbsp; Spécialité: ${this.studyClass?.speciality || ''}
            &nbsp;•&nbsp; Généré le ${currentDate}
          </div>

          <table>
            <thead>
              <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Nb</th>
                <th>Titres</th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
          </table>

          <div class="footer">Document livres — ${currentDate}</div>
        </body>
        </html>
      `;
    },

    extractBookNames(payments = []) {
      return payments
          .filter(p => p && p.serviceType === 'livres')
          .flatMap(p => Array.isArray(p.bookItems) ? p.bookItems : [])
          .map(bi => bi?.book?.name)
          .filter(Boolean);
    },

    countBooks(studentClassRegistered) {
      const payments = studentClassRegistered?.student?.payments || [];
      return this.extractBookNames(payments).length;
    },

    listBooks(studentClassRegistered) {
      const payments = studentClassRegistered?.student?.payments || [];
      const names = this.extractBookNames(payments);
      return names.length ? names.join(', ') : '—';
    },

    formatTime(timeString) {
      if (!timeString) return '';
      const date = new Date(timeString);
      return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit', minute: '2-digit', timeZone: 'UTC'
      });
    },

    formatRoom(room) {
      if (!room) return '';
      return room.name || `Salle #${room.id ?? ''}`.trim();
    },

    printClassReport() {
      const printContent = this.generatePrintContent();
      const printWindow = window.open('', '_blank');
      printWindow.document.write(printContent);
      printWindow.document.close();
      printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
      };
    },

    generatePrintContent() {
      const currentDate = new Date().toLocaleDateString('fr-FR');
      const activeStudents = this.localStudentsInStudyClass.filter(s => s.active);

      return `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Rapport de classe - ${this.studyClass.name}</title>
          <style>
            body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 20px; color: #111; line-height: 1.6; }
            .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #e9e9ff; padding-bottom: 16px; }
            .header h1 { margin:0 0 6px; color:#2c2cf0; }
            .class-info { background: #f6f7ff; border:1px solid #e9e9ff; padding: 16px; border-radius: 12px; margin-bottom: 20px; }
            .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; margin-top: 10px; }
            .info-item { background: #fff; border:1px solid #eee; padding: 10px; border-radius: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
            th { background: #2c2cf0; color: white; }
            tr:nth-child(even) { background-color: #fafbff; }
            .footer { margin-top: 28px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 14px; }
            @media print { body { margin: 8mm; } }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>${this.studyClass.name} - ${this.studyClass.speciality}</h1>
            <p>Rapport généré le ${currentDate}</p>
          </div>

          <div class="class-info">
            <h2>Informations sur la Classe</h2>
            <div class="info-grid">
              <div class="info-item"><strong>Niveau :</strong> ${this.studyClass.levelClass ?? ''}</div>
              <div class="info-item"><strong>Spécialité :</strong> ${this.studyClass.speciality ?? ''}</div>
              <div class="info-item"><strong>Type :</strong> ${this.studyClass.classType ?? ''}</div>
              <div class="info-item"><strong>Jour :</strong> ${this.studyClass.day ?? ''}</div>
              <div class="info-item"><strong>Horaires :</strong> ${this.formatTime(this.studyClass.startHour)} - ${this.formatTime(this.studyClass.endHour)}</div>
              ${this.studyClass.schoolYear ? `<div class="info-item"><strong>Année scolaire :</strong> ${this.studyClass.schoolYear}</div>` : ''}
              ${this.studyClass.principalRoom ? `<div class="info-item"><strong>Salle :</strong> ${this.formatRoom(this.studyClass.principalRoom)}</div>` : ''}
              ${this.studyClass.principalTeacher ? `<div class="info-item"><strong>Professeur :</strong> ${this.studyClass.principalTeacher.firstName} ${this.studyClass.principalTeacher.lastName}</div>` : ''}
            </div>
          </div>

          <div class="students-section">
            <h2>Étudiants Actifs (${activeStudents.length})</h2>
            <table>
              <thead>
                <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Date de naissance</th><th>Niveau</th></tr>
              </thead>
              <tbody>
                ${activeStudents.map(s => `
                  <tr>
                    <td>${s.student.id}</td>
                    <td>${s.student.lastName}</td>
                    <td>${s.student.firstName}</td>
                    <td>${new Date(s.student.birthDate).toLocaleDateString('fr-FR')}</td>
                    <td>${s.student.levelClass}</td>
                  </tr>`).join('')}
                ${activeStudents.length === 0 ? '<tr><td colspan="5" style="text-align:center;color:#666;padding:30px;">Aucun étudiant actif dans cette classe</td></tr>' : ''}
              </tbody>
            </table>
          </div>

          <div class="footer">Document généré automatiquement le ${currentDate}</div>
        </body>
        </html>
      `;
    },

    confirmDeactivation(student) {
      if (!student.active) return;
      this.studentToDeactivate = student;
      const deactivationModal = new this.$bootstrap.Modal(document.getElementById('deactivationConfirmationModal'));
      deactivationModal.show();
    },

    deactivateStudent() {
      const url = this.$routing.generate('deactivate_student_from_class', { id: this.studentToDeactivate.id });
      this.$axios.post(url)
          .then(() => {
            this.localStudentsInStudyClass = this.localStudentsInStudyClass.map(s =>
                s.id === this.studentToDeactivate.id ? { ...s, active: false } : s
            );
          })
          .catch(err => console.error("Erreur lors de la désactivation", err));
    },

    deleteStudent() {
      const url = this.$routing.generate('delete_student_from_class', { id: this.studentToDelete.id });
      this.$axios.post(url)
          .then(() => {
            this.messageAlert = "L'étudiant a été supprimé avec succès!";
            this.typeAlert = "success";
            this.localStudentsInStudyClass = this.localStudentsInStudyClass.filter(s => s.id !== this.studentToDelete.id);
          })
          .catch(err => {
            console.error("Erreur lors de la suppression de l'étudiant", err);
            this.messageAlert = "Erreur lors de la suppression de l'étudiant.";
            this.typeAlert = "danger";
          });
    }
  }
};
</script>

<style scoped>
/* ✅ Astuce : :root ne fonctionne pas bien en scoped. On met les variables sur le conteneur. */
.class-details{
  --bg: #f6f7fb;
  --card: #ffffff;
  --text: #0f172a;
  --muted: #64748b;
  --border: rgba(15, 23, 42, 0.10);
  --shadow: 0 10px 30px rgba(2, 6, 23, 0.08);
  --radius: 16px;

  --primary: #4f46e5;
  --primary2:#6d28d9;

  --danger:#ef4444;
  --warn:#f59e0b;
  --ok:#10b981;

  min-height: 100vh;
  background: var(--bg);
  padding: 20px;
  font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color: var(--text);
}

/* Topbar */
.topbar{
  position: sticky;
  top: 14px;
  z-index: 30;
  display:flex;
  justify-content: space-between;
  align-items:center;
  gap: 12px;
  padding: 12px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: rgba(255,255,255,0.85);
  backdrop-filter: blur(12px);
  box-shadow: 0 8px 20px rgba(2, 6, 23, 0.06);
}

.topbar-actions{
  display:flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

/* Buttons */
.btn{
  display:inline-flex;
  align-items:center;
  gap: 10px;
  border: 1px solid transparent;
  border-radius: 12px;
  padding: 10px 14px;
  font-weight: 700;
  cursor:pointer;
  text-decoration:none;
  background: #fff;
  color: var(--text);
  transition: transform .15s ease, box-shadow .15s ease, background .15s ease, border-color .15s ease;
  white-space: nowrap;
}
.btn:hover{ transform: translateY(-1px); box-shadow: 0 10px 20px rgba(2,6,23,.08); }
.btn:active{ transform: translateY(0px); }

.btn-ghost{
  background: transparent;
  border-color: var(--border);
}
.btn-soft{
  background: #fff;
  border-color: var(--border);
}
.btn-primary{
  background: linear-gradient(135deg, var(--primary), var(--primary2));
  color: #fff;
  border-color: rgba(255,255,255,0.15);
}
.btn-danger{
  background: rgba(239, 68, 68, 0.12);
  border-color: rgba(239, 68, 68, 0.25);
  color: #b91c1c;
}
.btn-warn{
  background: rgba(245, 158, 11, 0.18);
  border-color: rgba(245, 158, 11, 0.35);
  color: #92400e;
}
.btn-ok{
  background: rgba(16, 185, 129, 0.16);
  border-color: rgba(16, 185, 129, 0.35);
  color: #065f46;
  padding: 10px 12px;
}
.btn-whatsapp{
  background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
  color:#fff;
  border-color: rgba(255,255,255,0.15);
}

/* Hero */
.hero{
  margin-top: 16px;
  position: relative;
  border-radius: var(--radius);
  overflow: hidden;
  background: linear-gradient(135deg, #111827 0%, #1f2a65 45%, #4f46e5 100%);
  box-shadow: var(--shadow);
}
.hero-main{
  position: relative;
  z-index: 2;
  padding: 26px 24px;
}
.pill{
  display:inline-flex;
  align-items:center;
  gap:10px;
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(255,255,255,0.14);
  border: 1px solid rgba(255,255,255,0.18);
  color: #fff;
  font-weight: 800;
}
.hero-title{
  margin: 12px 0 6px;
  color:#fff;
  font-size: 34px;
  line-height: 1.15;
  letter-spacing: -0.3px;
  font-weight: 900;
}
.hero-subtitle{
  margin: 0 0 16px;
  color: rgba(255,255,255,0.86);
  font-size: 15px;
}
.hero-metas{
  display:flex;
  flex-wrap: wrap;
  gap: 10px;
}
.meta{
  display:flex;
  align-items:center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 12px;
  background: rgba(255,255,255,0.10);
  border: 1px solid rgba(255,255,255,0.14);
  color: rgba(255,255,255,0.92);
  font-weight: 700;
  font-size: 13px;
}
.hero-bg .blob{
  position:absolute;
  border-radius: 999px;
  filter: blur(30px);
  opacity: 0.35;
}
.hero-bg .b1{ width: 220px; height: 220px; background:#22c55e; top: -60px; right: -70px; }
.hero-bg .b2{ width: 260px; height: 260px; background:#a78bfa; bottom: -100px; left: -80px; }
.hero-bg .b3{ width: 180px; height: 180px; background:#60a5fa; top: 40%; left: 55%; }

.page-alert{ margin: 16px 0; }

/* Grid */
.grid{
  margin-top: 16px;
  display:grid;
  grid-template-columns: 360px 1fr;
  gap: 16px;
}

/* Cards */
.card{
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.card-header{
  padding: 16px 16px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(180deg, rgba(79,70,229,0.06) 0%, rgba(255,255,255,1) 60%);
}
.card-header h2{
  margin:0;
  font-size: 16px;
  font-weight: 900;
  display:flex;
  align-items:center;
  gap: 10px;
}
.card-header-row{
  display:flex;
  align-items:center;
  justify-content: space-between;
  gap: 12px;
}

/* Info list */
.info-list{ padding: 14px 16px; display:flex; flex-direction: column; gap: 10px; }
.info-item{
  display:flex;
  align-items:flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 12px;
  border: 1px solid var(--border);
  border-radius: 14px;
  background: rgba(2,6,23,0.02);
}
.info-label{ color: var(--muted); font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
.info-value{ font-weight: 900; color: var(--text); text-align:right; }

.divider{ height: 1px; background: var(--border); margin: 0 16px; }

/* WhatsApp */
.whatsapp{ padding: 14px 16px; }
.whatsapp-head{
  display:flex;
  align-items:center;
  gap: 12px;
  margin-bottom: 10px;
}
.whatsapp-icon{
  width: 42px; height: 42px;
  border-radius: 12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(37,211,102,0.14);
  border: 1px solid rgba(18,140,126,0.25);
  color: #128c7e;
  font-size: 18px;
}
.whatsapp-title{ font-weight: 900; }
.whatsapp-sub{ color: var(--muted); font-size: 12px; font-weight: 700; }
.whatsapp-actions{ display:flex; gap: 10px; flex-wrap: wrap; margin: 10px 0; }
.whatsapp-link{ display:block; color: var(--muted); word-break: break-all; }

/* Students controls */
.students-controls{ display:flex; align-items:center; gap: 10px; flex-wrap: wrap; }
.segmented{ display:inline-flex; align-items:center; cursor:pointer; }
.segmented input{ display:none; }
.segmented-track{
  display:flex;
  align-items:center;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: rgba(2,6,23,0.02);
  min-width: 110px;
  justify-content:center;
}
.segmented-option{
  font-weight: 900;
  font-size: 12px;
  color: var(--text);
}

/* Table */
.table-wrap{
  padding: 12px 12px 16px;
  overflow-x: auto;   /* ✅ garde le scroll horizontal */
  overflow-y: visible; /* ✅ laisse sortir le tooltip */
}
.table{
  width:100%;
  border-collapse: collapse;
  overflow: visible;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: #fff;
}
.table thead th{
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .5px;
  color: #334155;
  background: rgba(79,70,229,0.06);
  border-bottom: 1px solid var(--border);
  padding: 12px 12px;
  text-align:left;
  white-space: nowrap;
}
.table tbody td{
  padding: 12px 12px;
  border-bottom: 1px solid rgba(15,23,42,0.06);
  vertical-align: middle;
}
.row:hover td{ background: rgba(79,70,229,0.04); }
.inactive td{ opacity: 0.65; }

.muted{ color: var(--muted); font-weight: 700; }
.badge{
  display:inline-flex;
  align-items:center;
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 900;
  font-size: 12px;
  background: rgba(109,40,217,0.10);
  border: 1px solid rgba(109,40,217,0.18);
  color: #5b21b6;
}

/* Name cell */
.name{ display:flex; align-items:center; gap: 12px; }
.avatar{
  width: 36px; height: 36px;
  border-radius: 12px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: linear-gradient(135deg, var(--primary), var(--primary2));
  color:#fff;
  font-weight: 900;
}
.name-lines{ display:flex; flex-direction: column; }
.name-main{ font-weight: 900; }
.name-sub{ font-size: 12px; color: var(--muted); font-weight: 800; }

/* Books tooltip */
.books{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width: 28px;
  height: 28px;
  padding: 0 10px;
  border-radius: 999px;
  font-weight: 900;
  color: #0f172a;
  background: rgba(59,130,246,0.14);
  border: 1px solid rgba(59,130,246,0.22);
  position: relative;
}
.tooltip{ cursor: default; }
.tooltip-panel{
  position:absolute;
  left: 50%;
  transform: translateX(-50%);
  bottom: calc(100% + 10px);
  width: 320px;
  max-width: 420px;
  background: #0b1220;
  color: #fff;
  border: 1px solid rgba(255,255,255,0.10);
  border-radius: 14px;
  padding: 12px 12px;
  box-shadow: 0 20px 45px rgba(0,0,0,0.35);
  opacity: 0;
  visibility: hidden;
  transition: opacity .15s ease, transform .15s ease, visibility .15s ease;
  z-index: 9999;
}
.tooltip-panel strong{ display:block; margin-bottom: 8px; }
.tooltip-line{ display:block; color: rgba(255,255,255,0.92); margin: 3px 0; font-weight: 600; }
.tooltip:hover .tooltip-panel{
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(-2px);
}

/* Book name edit */
.bookname-edit{ display:flex; gap: 10px; align-items:center; }
.input{
  width: 100%;
  min-width: 180px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid var(--border);
  outline: none;
  font-weight: 700;
}
.input:focus{
  border-color: rgba(79,70,229,0.35);
  box-shadow: 0 0 0 4px rgba(79,70,229,0.12);
}

/* Switch */
.switch{ position: relative; display:inline-flex; align-items:center; }
.switch input{ display:none; }
.switch-ui{
  width: 46px; height: 26px;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: rgba(2,6,23,0.06);
  position: relative;
  transition: background .15s ease;
}
.switch-ui::after{
  content:'';
  position:absolute;
  top: 3px; left: 3px;
  width: 20px; height: 20px;
  border-radius: 999px;
  background: #fff;
  box-shadow: 0 6px 14px rgba(2,6,23,0.16);
  transition: transform .15s ease;
}
.switch input:checked + .switch-ui{
  background: rgba(16,185,129,0.26);
  border-color: rgba(16,185,129,0.35);
}
.switch input:checked + .switch-ui::after{
  transform: translateX(20px);
}

/* Empty */
.empty{ padding: 22px !important; }
.empty-box{
  text-align:center;
  padding: 26px 10px;
  color: var(--muted);
}
.empty-box i{ font-size: 36px; opacity: .35; margin-bottom: 10px; }
.empty-title{ font-weight: 900; color: var(--text); margin-bottom: 6px; }
.empty-sub{ font-weight: 700; }

/* Modals */
.modal-modern{
  border-radius: var(--radius);
  border: 1px solid var(--border);
  box-shadow: var(--shadow);
}
.modal-badge{
  width: 44px; height: 44px;
  border-radius: 14px;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size: 18px;
}
.modal-badge.warning{ background: rgba(245,158,11,0.18); color:#92400e; border: 1px solid rgba(245,158,11,0.35); }
.modal-badge.danger{ background: rgba(239,68,68,0.14); color:#b91c1c; border: 1px solid rgba(239,68,68,0.25); }

.callout{
  border-radius: 14px;
  border: 1px solid rgba(245,158,11,0.25);
  background: rgba(245,158,11,0.10);
  padding: 12px 12px;
}
.callout-list{ margin: 10px 0 0; padding: 0; list-style: none; display:flex; flex-direction:column; gap: 8px; }
.callout-list li{ display:flex; align-items:center; gap: 10px; font-weight: 800; color:#111; }
.callout-list i{ opacity: .9; }

.hide-sm{ display:inline; }

/* Column hints */
.col-id{ width: 70px; }
.col-date{ width: 140px; }
.col-level{ width: 110px; }
.col-books{ width: 110px; }
.col-bookname{ width: 320px; }
.col-status{ width: 100px; }
.col-actions{ width: 120px; }

/* Responsive */
@media (max-width: 1100px){
  .grid{ grid-template-columns: 1fr; }
}
@media (max-width: 640px){
  .class-details{ padding: 12px; }
  .hero-title{ font-size: 26px; }
  .btn span{ display:none; }
  .hide-sm{ display:none; }
  .col-bookname{ width: auto; }
  .tooltip-panel{ width: 260px; }
}
</style>