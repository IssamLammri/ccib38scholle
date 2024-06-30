<!-- assets/vue/pages/NewPage.vue -->
<template>
  <div>
    <h1>Welcome to the new page!</h1>
    <NewComponent v-if="success" name="Issam"></NewComponent>
  </div>
</template>
<script>
import NewComponent from '../components/NewComponent.vue';
export default {

  name: 'Hello',
  components: {
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
