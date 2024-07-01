// assets/vue/main.js
import { createApp } from 'vue';
import Hello from './components/Hello.vue';
import NewPage from './pages/NewPage.vue';
import FooterComponent from "./components/FooterComponent.vue";

document.addEventListener('DOMContentLoaded', () => {
    const app = createApp({});

    app.component('Hello', Hello);
    app.component('NewPage', NewPage);
    app.component('FooterComponent', FooterComponent);

    if (document.querySelector('#app')) {
        app.mount('#app');
    } else {
        console.error('Element with id #app not found in DOM.');
    }
});

