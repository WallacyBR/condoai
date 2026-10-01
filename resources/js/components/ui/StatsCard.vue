<template>
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700">
    <div class="flex items-center justify-between">
      <div class="flex-1 min-w-0 pr-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 truncate">
          {{ label || title }}
        </p>
        <p class="mt-1.5 text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100 truncate">
          {{ value }}
        </p>
        <p v-if="subtitle || description" class="mt-1 text-xs text-slate-500 dark:text-slate-400 truncate">
          {{ subtitle || description }}
        </p>
      </div>

      <div class="flex h-11 w-11 items-center justify-center rounded-xl flex-shrink-0 transition-colors" :class="iconContainerClasses">
        <slot name="icon">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="defaultIconPath" />
          </svg>
        </slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StatsCard',
  props: {
    label: {
      type: String,
      default: ''
    },
    title: {
      type: String,
      default: ''
    },
    value: {
      type: [String, Number],
      required: true
    },
    subtitle: {
      type: String,
      default: ''
    },
    description: {
      type: String,
      default: ''
    },
    variant: {
      type: String,
      default: 'primary'
    },
    defaultIconPath: {
      type: String,
      default: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
    }
  },
  computed: {
    iconContainerClasses() {
      switch (this.variant) {
        case 'green':
        case 'success':
          return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400';
        case 'orange':
        case 'warning':
          return 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400';
        case 'red':
        case 'danger':
          return 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400';
        case 'blue':
        case 'info':
          return 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400';
        case 'purple':
          return 'bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400';
        case 'primary':
        default:
          return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400';
      }
    }
  }
};
</script>