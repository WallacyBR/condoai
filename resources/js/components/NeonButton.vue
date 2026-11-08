<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'liquid-glass-btn group relative flex justify-center items-center font-medium text-white transition-all duration-300 ease-in-out overflow-hidden',
      sizeClass,
      extraClass,
      { 'opacity-50 cursor-not-allowed': disabled || loading }
    ]"
    v-bind="$attrs"
  >
    <span v-if="loading" class="mr-2 flex items-center">
      <svg class="animate-spin h-5 w-5 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
    <span class="liquid-glass-highlight"></span>
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
      return 'px-3 py-2 text-sm rounded-xl';
    case 'lg':
      return 'px-8 py-4 text-lg rounded-3xl';
    case 'xl':
      return 'px-10 py-5 text-xl rounded-full';
    default:
      return 'px-4 py-3 text-base rounded-2xl';
  }
});
</script>

<style scoped>
.liquid-glass-btn {
  position: relative;
  background: rgba(255, 255, 255, 0.18);
  border-radius: 2rem;
  border: 1.5px solid rgba(255,255,255,0.35);
  box-shadow:
    0 8px 32px rgba(31, 38, 135, 0.18),
    0 1.5px 4px rgba(255,255,255,0.12),
    inset 0 4px 24px rgba(255,255,255,0.22);
  backdrop-filter: blur(18px) saturate(200%);
  -webkit-backdrop-filter: blur(18px) saturate(200%);
  overflow: hidden;
  transition: 
    box-shadow 0.3s,
    border-color 0.3s,
    backdrop-filter 0.3s,
    -webkit-backdrop-filter 0.3s,
    background 0.3s;
}
.liquid-glass-btn:focus {
  outline: 2px solid #22d3ee;
  outline-offset: 2px;
}
.liquid-glass-btn:active {
  box-shadow: 0 2px 8px rgba(31, 38, 135, 0.25);
}
.liquid-glass-btn:hover {
  background: rgba(255,255,255,0.22);
  box-shadow:
    0 12px 48px rgba(31, 38, 135, 0.28),
    0 0 0 2px rgba(34, 211, 238, 0.18),
    0 2px 8px rgba(255,255,255,0.18),
    inset 0 10px 36px rgba(255,255,255,0.32);
  border-color: rgba(255,255,255,0.55);
  backdrop-filter: blur(24px) saturate(240%);
  -webkit-backdrop-filter: blur(24px) saturate(240%);
}
.liquid-glass-highlight {
  content: '';
  position: absolute;
  top: 0; left: 0; width: 100%; height: 60%;
  border-radius: inherit;
  background: linear-gradient(180deg, rgba(255,255,255,0.38) 0%, rgba(255,255,255,0.08) 80%, rgba(255,255,255,0) 100%);
  opacity: 0.7;
  pointer-events: none;
  box-shadow:
    0 1.5px 8px rgba(255,255,255,0.18),
    inset 0 2px 12px rgba(255,255,255,0.22);
  filter: blur(0.5px) brightness(1.08);
  z-index: 1;
  transition: opacity 0.3s, filter 0.3s;
}
.group:hover .liquid-glass-highlight {
  opacity: 0.95;
  filter: blur(1.5px) brightness(1.18);
}
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style> 