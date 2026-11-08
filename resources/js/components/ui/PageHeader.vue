<template>
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex items-center space-x-3">
      <!-- Botão Voltar (aparece se showBack for true ou backRoute estiver definido) -->
      <button
        v-if="showBack || backRoute"
        @click="handleBack"
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
        title="Voltar"
        aria-label="Voltar para a página anterior"
      >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
      </button>

      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            {{ title }}
          </h1>
          <span
            v-if="badge"
            :class="[
              'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
              badgeColor || 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
            ]"
          >
            {{ badge }}
          </span>
        </div>
        <p v-if="subtitle" class="mt-0.5 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
          {{ subtitle }}
        </p>
      </div>
    </div>

    <!-- Ações no canto direito -->
    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2.5 sm:justify-end">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  showBack: { type: Boolean, default: false },
  backRoute: { type: [String, Object], default: null },
  badge: { type: String, default: '' },
  badgeColor: { type: String, default: '' }
});

const emit = defineEmits(['back']);
const router = useRouter();

const handleBack = () => {
  emit('back');
  if (props.backRoute && router) {
    router.push(props.backRoute);
  } else if (router && window.history.length > 1) {
    router.back();
  }
};
</script>
