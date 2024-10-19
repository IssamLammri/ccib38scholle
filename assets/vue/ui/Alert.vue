<template>
  <div
    class="alert alert-dismissible align-items-start d-flex fade justify-content-between p-3 show"
    :class="`alert-${type}`"
    role="alert"
  >
    <div v-html="text" />
    <button
      type="button"
      class="btn-close d-block position-relative ps-3"
      data-bs-dismiss="alert"
      aria-label="Close"
    />
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
