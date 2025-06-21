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

export default {
  name: 'App',
  components: {
    PWAInstallBanner,
    PWAStatusBanner
  }
};
</script>
