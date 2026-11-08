<template>
  <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 rounded-2xl p-6 border-2 border-gray-700/50 shadow-xl mb-6">
    <div class="flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <div v-if="icon || $slots.icon" class="h-10 w-10 rounded-full flex items-center justify-center" :class="iconBg">
          <component v-if="icon" :is="icon" class="h-5 w-5" />
          <slot v-else name="icon" />
        </div>
        <div>
          <h1 class="text-2xl font-semibold text-white">{{ title }}</h1>
          <p v-if="subtitle" class="text-gray-400 text-sm">{{ subtitle }}</p>
        </div>
      </div>
      <div>
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  color: { type: String, default: 'cyan' },
  icon: { type: [Object, Function], default: null }
});

const iconBg = computed(() => `bg-${props.color}-500/20 text-${props.color}-400`);
</script>
