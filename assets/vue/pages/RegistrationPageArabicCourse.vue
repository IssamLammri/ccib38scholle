<!-- assets/vue/pages/NewPage.vue -->
<template>
  <div class="w-100">
    <nav-bar-guest></nav-bar-guest>
    <form-registration-arabic-course></form-registration-arabic-course>
  </div>

</template>
<script>
import NewComponent from '../components/NewComponent.vue';
import NavBarGuest from "../components/NavBarGuest.vue";
import FormRegistrationArabicCourse from "./registrationPageArabicCourse/FormRegistrationArabicCourse.vue";
export default {
  name: 'RegistrationPageArabicCourse',
  components: {
    FormRegistrationArabicCourse,
    NavBarGuest,
    NewComponent
  },
  data () {
    return {
      success: true
    }
  },
  props: {
  },
  mounted () {
  },
  methods: {
  }
}
</script>

<style scoped lang="css">
</style>
