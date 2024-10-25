<template>
  <div
      class="alert alert-dismissible d-flex align-items-center justify-content-between p-3 show"
      :class="`alert-${type}`"
      role="alert"
  >
    <span class="flex-grow-1" v-html="text"></span>
    <button
        type="button"
        class="btn-close ms-3"
        data-bs-dismiss="alert"
        aria-label="Close"
    ></button>
  </div>
</template>

<script>
export default {
  name: 'Alert',
  props: {
    type: {
      type: String,
      required: true
    },
    text: {
      type: String,
      required: true
    }
  }
}
</script>

<style scoped>
.alert {
  display: flex;
  align-items: center;
}
.btn-close {
  margin-left: auto;
}
</style>
