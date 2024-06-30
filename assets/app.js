import { createApp } from 'vue';
import './bootstrap.js';
import './styles/app.css';
import Hello from './vue/controllers/Hello.vue';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

const app = createApp({
    components: {
        Hello
    }
});

app.mount('#app');
