import './bootstrap';
import { createApp } from 'vue';
import CondominiumManager from './components/CondominiumManager.vue';
import UnitManager from './components/UnitManager.vue';
import ResidentManager from './components/ResidentManager.vue';
import UserManager from './components/UserManager.vue';
import ProfileManager from './components/ProfileManager.vue';

const app = createApp({});

app.component('condominium-manager', CondominiumManager);
app.component('unit-manager', UnitManager);
app.component('resident-manager', ResidentManager);
app.component('user-manager', UserManager);
app.component('profile-manager', ProfileManager);

app.mount('#app');
