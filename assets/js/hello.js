import { createApp } from 'vue';
import '../bootstrap.js';
import '../styles/app.css';
import Hello from '../vue/controllers/Hello.vue';

const app = createApp({
    components: {
        Hello
    }
});

app.mount('#hello');
