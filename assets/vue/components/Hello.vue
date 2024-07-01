<template>
  <div>Hello {{ test }}!</div>
</template>

<script>

export default {
  name: 'Hello',
  data () {
    return {
      success: false
    }
  },
  props: {
    test: {
      type: String,
      required: true
    },
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
