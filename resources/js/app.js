import './bootstrap';
import { createApp } from 'vue';

const app = createApp({});

app.component('example-component', {
    template: '<div>Example Vue Component</div>',
});

app.mount('#app');
