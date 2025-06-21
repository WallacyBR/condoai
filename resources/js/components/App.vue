<template>
  <div>
    <router-view />
    <PWAInstallBanner />
  </div>
</template>

<script>
import PWAInstallBanner from './PWAInstallBanner.vue';

export default {
  name: 'App',
  components: {
    PWAInstallBanner
  }
};
</script>
