<template>
  <div v-if="show" class="fixed inset-0 z-40 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/70" @click="$emit('close')"></div>
    <div class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 border-2 border-cyan-500/20 rounded-3xl shadow-2xl p-8 w-full max-w-lg">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: { type: Boolean, default: false }
});
</script>
