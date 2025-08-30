<template>
  <div class="container mt-5">
    <div class="container mt-5">
      <h1>Planning</h1>
      <ul v-if="studyClasses.length">
        <li v-for="sc in studyClasses" :key="sc.id">
          <pre>{{sc}}</pre>
        </li>
      </ul>
      <p v-else>Aucun cours trouvé.</p>
    </div>
  </div>
</template>

<script>
import Alert from "../../ui/Alert.vue";
import NewStudentToClassModal from "./NewStudentToClassModal.vue";

export default {
  name: "PlanningStudyClass",
  components: {
    Alert,
    NewStudentToClassModal
  },
  props: {

  },
  data() {
    return {
      studyClasses: [],
      messageAlert: null,
      typeAlert: null,
      search: '',
    };
  },
  computed: {

  },
  mounted() {
    this.fetchStatistics();
  },
  methods: {
    fetchStatistics() {
      this.axios
          .get(this.$routing.generate("list_study_class_filtered", { search: this.search }))
          .then(({ data }) => {
            this.studyClasses = data;
          })
          .catch(error => console.error("Erreur:", error));
    },
  }
};
</script>

<style scoped>

</style>
