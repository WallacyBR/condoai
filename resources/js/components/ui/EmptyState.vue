<template>
  <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white/50 p-8 text-center dark:border-slate-800 dark:bg-slate-900/40 sm:p-12">
    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 shadow-sm dark:bg-slate-800 dark:text-slate-400 mb-4">
      <slot name="icon">
        <svg v-if="!icon" class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <component v-else :is="icon" class="h-7 w-7" />
      </slot>
    </div>

    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
      {{ title }}
    </h3>
    <p v-if="description" class="mt-1.5 max-w-sm text-xs sm:text-sm text-slate-500 dark:text-slate-400">
      {{ description }}
    </p>

    <div v-if="$slots.action || actionText" class="mt-5">
      <slot name="action">
        <AppButton v-if="actionText" @click="$emit('action')" variant="primary" size="md">
          {{ actionText }}
        </AppButton>
      </slot>
    </div>
  </div>
</template>

<script setup>
import AppButton from './AppButton.vue';

defineProps({
  title: { type: String, required: true },
  description: { type: String, default: '' },
  icon: { type: [Object, Function], default: null },
  actionText: { type: String, default: '' }
});

defineEmits(['action']);
</script>
