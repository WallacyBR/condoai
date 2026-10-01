<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    @click="emit('click', $event)"
    :class="[
      'inline-flex justify-center items-center font-medium transition-all duration-150 select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900',
      sizeClass,
      variantClass,
      extraClass,
      { 'opacity-50 cursor-not-allowed pointer-events-none': disabled || loading }
    ]"
    v-bind="$attrs"
  >
    <span v-if="loading" class="mr-2 flex items-center">
      <svg class="animate-spin h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: { type: String, default: 'button' },
  size: { type: String, default: 'md' }, // 'sm', 'md', 'lg', 'xl'
  variant: { type: String, default: 'primary' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  extraClass: { type: String, default: '' }
});

const emit = defineEmits(['click']);

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-3 py-1.5 text-xs rounded-lg gap-1.5';
    case 'lg':
      return 'px-6 py-3 text-base rounded-xl gap-2.5';
    case 'xl':
      return 'px-8 py-4 text-lg rounded-xl gap-3';
    default:
      return 'px-4 py-2 text-sm rounded-lg gap-2';
  }
});

const variantClass = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-100 border border-slate-200 dark:border-slate-700 focus-visible:ring-slate-500';
    case 'outline':
      return 'bg-transparent hover:bg-slate-100 text-slate-700 border border-slate-300 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800 focus-visible:ring-slate-500';
    case 'ghost':
      return 'bg-transparent hover:bg-slate-100 text-slate-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 focus-visible:ring-slate-500';
    case 'success':
    case 'confirm':
      return 'bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white shadow-sm focus-visible:ring-emerald-500';
    case 'danger':
    case 'cancel':
      return 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-sm focus-visible:ring-red-500';
    case 'warning':
      return 'bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white shadow-sm focus-visible:ring-amber-500';
    case 'info':
      return 'bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white shadow-sm focus-visible:ring-sky-500';
    case 'primary-action':
    case 'primary':
    default:
      return 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white shadow-sm focus-visible:ring-indigo-500';
  }
});
</script>
