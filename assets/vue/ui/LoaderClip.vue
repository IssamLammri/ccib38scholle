<template>
  <clip-loader
    :color="color"
    size="1rem"
  />
</template>

<script>
import ClipLoader from 'vue-spinner/src/ClipLoader.vue'

export default {
  name: 'LoaderClip',
  components: { ClipLoader },
  props: {
    color: {
      type: String,
      default: '#80BC00'
    }
  }
}
</script>

<style scoped>
.v-spinner {
  height: 1rem;
}
</style>
