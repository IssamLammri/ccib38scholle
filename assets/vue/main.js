// assets/vue/main.js
import { createApp } from 'vue';
import axios from 'axios'; // Importer Axios
import VueAxios from 'vue-axios'; // Importer VueAxios pour l'intégration
import Hello from './components/Hello.vue';
import NewPage from './pages/NewPage.vue';
import RegistrationPageAcademicSupport from './pages/RegistrationPageAcademicSupport.vue';
import FooterComponent from "./components/FooterComponent.vue";
import * as bootstrap from 'bootstrap'

// Importer le router.min.js et les données de routing
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
import routes from '../../public/js/fos_js_routes.json';

// Initialiser le routing avec les routes générées
Routing.setRoutingData(routes);

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp({});

    // Utilisation globale d'Axios
    app.use(VueAxios, axios);
    app.config.globalProperties.$axios = axios;
    app.config.globalProperties.$bootstrap = bootstrap;

    // Utilisation globale du routing via un plugin
    app.config.globalProperties.$routing = Routing;

    // Enregistrement des composants
    app.component('Hello', Hello);
    app.component('NewPage', NewPage);
    app.component('RegistrationPageAcademicSupport', RegistrationPageAcademicSupport);
    app.component('FooterComponent', FooterComponent);

    if (document.querySelector('#app')) {
        app.mount('#app');
    } else {
        console.error('Element with id #app not found in DOM.');
    }
});
