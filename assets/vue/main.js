// assets/vue/main.js
import { createApp } from 'vue';
import Hello from './components/Hello.vue';
import NewComponent from './components/NewComponent.vue';
import NewPage from './pages/NewPage.vue';

console.log('issam ');


const app = createApp({});

app.component('Hello', Hello);
app.component('NewComponent', NewComponent);
app.component('NewPage', NewPage);

app.mount('#app');
