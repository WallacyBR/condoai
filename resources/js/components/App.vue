<template>
  <div>
    <router-view />
    <PWAInstallBanner />
    <PWAStatusBanner />
  </div>
</template>

<script>
import PWAInstallBanner from './PWAInstallBanner.vue';
import PWAStatusBanner from './PWAStatusBanner.vue';
import { usePWA } from '../composables/usePWA.js';

export default {
  name: 'App',
  components: {
    PWAInstallBanner,
    PWAStatusBanner
  },
  setup() {
    // Inicializar PWA
    const pwa = usePWA();
    
    return {
      ...pwa
    };
  }
};
</script>
