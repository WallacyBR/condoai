<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'neon-btn group relative flex justify-center items-center font-medium text-white bg-gradient-to-r from-cyan-400 via-blue-400 to-pink-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500/50 transition-all duration-300 ease-in-out overflow-hidden',
      sizeClass,
      extraClass,
      { 'opacity-50 cursor-not-allowed': disabled || loading }
    ]"
    v-bind="$attrs"
  >
    <span v-if="loading" class="mr-2 flex items-center">
      <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
    <span class="btn-shimmer"></span>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: { type: String, default: 'button' },
  size: { type: String, default: 'md' }, // 'sm', 'md', 'lg', 'xl'
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  extraClass: { type: String, default: '' }
});

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-3 py-2 text-sm rounded-lg';
    case 'lg':
      return 'px-8 py-4 text-lg rounded-2xl';
    case 'xl':
      return 'px-10 py-5 text-xl rounded-3xl';
    default:
      return 'px-4 py-3 text-base rounded-2xl';
  }
});
</script>

<style scoped>
@keyframes shimmer {
  0% {
    left: -60%;
  }
  100% {
    left: 120%;
  }
}

.neon-btn {
  position: relative;
}

.group:hover .btn-shimmer {
  opacity: 1;
  animation: shimmer 1.2s linear;
}

.btn-shimmer {
  content: '';
  position: absolute;
  top: 0;
  left: -60%;
  width: 60%;
  height: 100%;
  background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
  opacity: 0;
  pointer-events: none;
  border-radius: inherit;
  transition: opacity 0.2s;
  z-index: 2;
}

.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style> 