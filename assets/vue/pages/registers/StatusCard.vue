<!-- src/components/StatusCard.vue -->
<template>
  <div class="col-md-2 col-sm-4 col-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body py-3 text-center">
        <h6 :class="['card-title', 'text-' + color, 'mb-1']">
          {{ title }}
        </h6>
        <p class="display-4 mb-0">{{ count }}</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatusCard',
  props: {
    /**
     * Le titre à afficher
     * ex. "Validation", "Terminé", etc.
     **/
    title: {
      type: String,
      required: true
    },
    /**
     * La couleur Bootstrap à appliquer
     * ex. "primary", "warning", "light", etc.
     **/
    color: {
      type: String,
      required: true
    },
    /**
     * Le nombre à afficher
     **/
    count: {
      type: Number,
      required: true
    }
  }
};
</script>

<style scoped>
.card-title {
  font-weight: 600;
}
/* .display-4 existe déjà sous Bootstrap 5, sinon on peut la redéfinir ici */
.display-4 {
  font-size: 2rem;
  font-weight: 600;
}
</style>
