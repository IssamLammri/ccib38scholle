<template>
  <div>
    <button @click="openModal" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="Code-barres">
      <i class="fa-solid fa-barcode"></i>
    </button>
    <teleport to="body">
      <div class="modal fade" id="evaluationModal" tabindex="-1" aria-hidden="true" ref="evaluationModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Code-barres de {{ studentName }}</h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body text-center">
              <VueBarcode ref="barcode" :value="formattedBarcode"
                :options="{ format: 'CODE128', width: 2, height: 80 }" />
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-success" @click="downloadBarcode">
                Télécharger
              </button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<script>
import { Modal } from "bootstrap";
import VueBarcode from "vue3-barcode";

export default {
  name: "BarcodeModal",
  components: { VueBarcode },
  props: {
    id: {
      type: [String, Number],
      required: true,
    },
    specifier: {
      type: String,
      required: true,
      validator: v => ["1", "2"].includes(v),
    },
    studentName: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      modal: null,
    };
  },
  computed: {
    formattedBarcode() {
      const paddedId = String(this.id).padStart(8, "0");
      return `${this.specifier}${paddedId}`;
    },
  },
  methods: {
    openModal() {
      if (!this.modal) this.modal = new Modal(this.$refs.evaluationModal);
      this.modal.show();
    },
    closeModal() {
      if (this.modal) this.modal.hide();
    },
    downloadBarcode() {
      const svgEl = this.$refs.barcode.$el.querySelector("svg");
      if (!svgEl) return;

      // Convert SVG to canvas
      const xml = new XMLSerializer().serializeToString(svgEl);
      const svg64 = btoa(unescape(encodeURIComponent(xml)));
      const imgSrc = "data:image/svg+xml;base64," + svg64;

      const img = new Image();
      img.onload = () => {
        const canvas = document.createElement("canvas");
        canvas.width = img.width * 2; // optional: increase resolution
        canvas.height = img.height * 2;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

        // Trigger download
        const a = document.createElement("a");
        a.href = canvas.toDataURL("image/png");
        a.download = `barcode_${this.formattedBarcode}.png`;
        a.click();
      };
      img.src = imgSrc;
    },
  },
};
</script>

<style scoped>
.modal-body {
  padding: 2rem;
}
</style>
