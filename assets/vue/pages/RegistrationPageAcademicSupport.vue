<template>
  <div class="container px-2 px-md-3">
    <nav-bar-guest />
    <form-registration-academic-support />
  </div>
</template>

<script>
import NavBarGuest from "../components/NavBarGuest.vue";
import FormRegistrationAcademicSupport from "./registrationPageAcademicSupport/FormRegistrationAcademicSupport.vue";

export default {
  name: 'RegistrationPageAcademicSupport',
  components: { NavBarGuest, FormRegistrationAcademicSupport }
};
</script>
