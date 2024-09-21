<!-- assets/vue/pages/NewPage.vue -->
<template>
  <div class="w-100">
    <nav-bar-guest></nav-bar-guest>
    <form-registration-academic-support></form-registration-academic-support>
  </div>

</template>
<script>
import NewComponent from '../components/NewComponent.vue';
import NavBarGuest from "../components/NavBarGuest.vue";
import FormRegistrationAcademicSupport from "./registrationPageAcademicSupport/FormRegistrationAcademicSupport.vue";
export default {
  name: 'Hello',
  components: {
    FormRegistrationAcademicSupport,
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
    console.log('Hello from Hello.vue')
  },
  methods: {
  }
}
</script>

<style scoped lang="css">
</style>
