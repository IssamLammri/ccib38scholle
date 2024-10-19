<template>
  <pulse-loader
    :color="color"
    size="0.5rem"
  />
</template>

<script>
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

export default {
  name: 'Loader',
  components: { PulseLoader },
  props: {
    color: {
      type: String,
      default: '#80BC00'
    }
  }
}
</script>

<style scoped>
  .v-spinner{
    height: 1rem;
  }
</style>
