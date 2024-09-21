<template>
  <div class="container registration-form">
    <h1 class="mb-4">Inscription au soutien scolaire</h1>

    <form v-if="!demandSent" @submit.prevent="submitForm">
      <p class="mb-4">
        Remplissez le formulaire ci-dessous pour inscrire votre enfant à notre
        programme de soutien scolaire.
      </p>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="studentFirstName" class="form-label">Prénom de l'élève :</label>
          <input type="text" class="form-control" v-model="form.studentFirstName" id="studentFirstName" required placeholder="Prénom de l'élève" />
        </div>

        <div class="col-md-6 mb-3">
          <label for="studentLastName" class="form-label">Nom de l'élève :</label>
          <input type="text" class="form-control" v-model="form.studentLastName" id="studentLastName" required placeholder="Nom de l'élève" />
        </div>
      </div>

      <div class="mb-3">
        <label for="level" class="form-label">Niveau d'étude :</label>
        <select v-model="form.level" class="form-select" id="level" required>
          <option value="" disabled selected>Choisir un niveau</option>
          <option value="CP">CP</option>
          <option value="CE1">CE1</option>
          <option value="CE2">CE2</option>
          <option value="CM1">CM1</option>
          <option value="CM2">CM2</option>
          <option value="6ème">6ème</option>
          <option value="5ème">5ème</option>
          <option value="4ème">4ème</option>
          <option value="3ème">3ème</option>
          <option value="2nde">2nde</option>
          <option value="1ère">1ère</option>
          <option value="Terminale">Terminale</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Matière(s) de soutien :</label>
        <div class="row">
          <div class="col-md-5 col-lg-4 mb-2" v-for="subject in subjectsList" :key="subject">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" :value="subject" v-model="form.subjects" :id="subject" />
              <label class="form-check-label" :for="subject">{{ subject }}</label>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="parentFirstName" class="form-label">Prénom du parent :</label>
          <input type="text" class="form-control" v-model="form.parentFirstName" id="parentFirstName" required placeholder="Prénom du parent" />
        </div>

        <div class="col-md-6 mb-3">
          <label for="parentLastName" class="form-label">Nom du parent :</label>
          <input type="text" class="form-control" v-model="form.parentLastName" id="parentLastName" required placeholder="Nom du parent" />
        </div>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email de contact :</label>
        <input type="email" class="form-control" v-model="form.email" id="email" required placeholder="exemple@gmail.com" />
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Téléphone :</label>
        <input type="tel" class="form-control" v-model="form.phone" id="phone" required placeholder="06 12 34 56 78" />
      </div>

      <button type="submit" class="btn btn-success">Envoyer</button>
    </form>

    <!-- Section de récapitulatif -->
    <div v-if="demandSent" class="recap">
      <h3 class="mb-4">Récapitulatif de la demande</h3>
      <p><strong>Prénom de l'élève :</strong> {{ form.studentFirstName }}</p>
      <p><strong>Nom de l'élève :</strong> {{ form.studentLastName }}</p>
      <p><strong>Niveau d'étude :</strong> {{ form.level }}</p>
      <p><strong>Matière(s) de soutien :</strong> {{ form.subjects.join(', ') }}</p>
      <p><strong>Prénom du parent :</strong> {{ form.parentFirstName }}</p>
      <p><strong>Nom du parent :</strong> {{ form.parentLastName }}</p>
      <p><strong>Email de contact :</strong> {{ form.email }}</p>
      <p><strong>Téléphone :</strong> {{ form.phone }}</p>
    </div>

    <!-- Modal de succès -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="successModalLabel">🎉 Inscription réussie !</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            ✨ Félicitations ! Votre inscription a bien été enregistrée. 🎉<br />
            L'équipe gestionnaire du Centre vous contactera très bientôt pour confirmer votre inscription et vous fournir tous les détails du planning. 📅<br /><br />
            Nous sommes ravis de vous compter parmi nous et avons hâte de vous accompagner dans cette belle aventure éducative. 😊
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fermer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'formRegistrationAcademicSupport',
  data() {
    return {
      form: {
        studentFirstName: '',
        studentLastName: '',
        parentFirstName: '',
        parentLastName: '',
        email: '',
        phone: '',
        level: '',
        subjects: [],
      },
      demandSent: false,
      success: false,
      subjectsList: ['Math', 'Français', 'Anglais', 'Physique/Chimie', 'SVT', 'Informatique', 'Arabe'],
    };
  },
  methods: {
    submitForm() {
      const payload = {
        student_first_name: this.form.studentFirstName,
        student_last_name: this.form.studentLastName,
        parent_first_name: this.form.parentFirstName,
        parent_last_name: this.form.parentLastName,
        email: this.form.email,
        phone: this.form.phone,
        level: this.form.level,
        subjects: this.form.subjects,
      };

      const url = this.$routing.generate('app_registration_academic_support_request');

      this.axios
          .post(url, payload)
          .then((response) => {
            this.success = true;
            // Utiliser this.$bootstrap pour accéder à Bootstrap Modal
            const successModal = new this.$bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            this.demandSent = true;
          })
          .catch((error) => {
            console.error('Erreur lors de l\'inscription :', error);
          });
    },
  },
};
</script>


<style scoped>
.registration-form {
  max-width: 700px;
  margin: 0 auto;
  padding: 20px;
}

.form-check-label {
  margin-left: 5px;
}
</style>