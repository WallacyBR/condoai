<template>
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-center space-x-3">
      <div v-if="icon || $slots.icon" class="h-9 w-9 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400 flex-shrink-0">
        <component v-if="icon" :is="icon" class="h-5 w-5" />
        <slot v-else name="icon" />
      </div>
      <div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ title }}</h1>
        <p v-if="subtitle" class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-0.5">{{ subtitle }}</p>
      </div>
    </div>
    <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  color: { type: String, default: 'indigo' },
  icon: { type: [Object, Function], default: null }
});
</script>
