<template>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary">
    <!-- Container wrapper -->
    <div class="container-fluid d-flex justify-content-between">
      <!-- Navbar brand -->
      <a class="navbar-brand me-2" target="_blank" href="https://www.ccib38.com/">
        <img
            src="/static/icons/logoccib38.webp"
            height="40"
            alt="Company Logo"
            loading="lazy"
            style="margin-top: -1px;"
        />
      </a>

      <!-- Collapsible wrapper -->
      <div class="collapse navbar-collapse" id="navbarButtonsExample">
        <!-- Left links -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 d-flex">
          <li class="nav-item">
            <a class="nav-link" target="_blank" href="https://www.ccib38.com/">Centre Culturel Ibn Badis Grenoble</a>
          </li>
        </ul>
        <!-- Left links -->

        <div class="d-flex align-items-center">
            <span data-mdb-ripple-init class="px-3 me-2">
              + 33 7 51 93 84 04
            </span>
          <span data-mdb-ripple-init class="px-3 me-2">
               soutien.scolaire.ccib38@gmail.com
            </span>
          <a
              data-mdb-ripple-init
              class="px-3"
              href="https://maps.app.goo.gl/bMoK4PY1YuLfqciy6"
              role="button"
          >
            <img
                src="/static/icons/location.webp"
                height="50"
                alt="Company Logo"
                loading="lazy"
                style="margin-top: -1px;"
            />
          </a>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
    defineProps({
        name: String
    });
</script>
