<template>
  <div
    class="rounded-2xl p-6 border-2 shadow-xl"
    :class="containerClasses"
  >
    <div class="flex items-center space-x-4">
      <div
        class="h-16 w-16 rounded-full flex items-center justify-center border-2 flex-shrink-0"
        :class="iconContainerClasses"
      >
        <svg
          class="h-8 w-8"
          :class="iconClasses"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
          v-if="iconPath"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath"></path>
        </svg>
        <slot name="icon" v-else></slot>
      </div>
      <div>
        <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
        <p :class="descriptionClasses">{{ description }}</p>
      </div>
    </div>
    <div v-if="$slots.actions" class="mt-4 flex justify-end space-x-3">
        <slot name="actions"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  iconPath: { // Caminho SVG para o ícone
    type: String,
    default: '',
  },
  iconColor: { // 'indigo', 'green', 'orange', 'blue', etc.
    type: String,
    default: 'indigo', // Cor padrão do DESIGN_MANUAL para headers
  }
});

const baseBgClasses = 'bg-gradient-to-r';
const baseBorderClasses = 'border-opacity-30';
const baseTextClasses = '';
const baseIconBgClasses = '';
const baseIconTextClasses = '';


const colorMap = {
  indigo: {
    from: 'from-indigo-600/20',
    to: 'to-purple-600/20',
    border: 'border-indigo-500',
    text: 'text-indigo-300',
    iconBg: 'bg-indigo-100',
    iconText: 'text-indigo-600',
  },
  green: {
    from: 'from-green-600/20',
    to: 'to-emerald-600/20',
    border: 'border-green-500',
    text: 'text-green-300',
    iconBg: 'bg-green-100',
    iconText: 'text-green-600',
  },
  orange: {
    from: 'from-orange-600/20',
    to: 'to-amber-600/20',
    border: 'border-orange-500',
    text: 'text-orange-300',
    iconBg: 'bg-orange-100',
    iconText: 'text-orange-600',
  },
  blue: {
    from: 'from-blue-600/20',
    to: 'to-cyan-600/20',
    border: 'border-blue-500',
    text: 'text-blue-300',
    iconBg: 'bg-blue-100',
    iconText: 'text-blue-600',
  },
  // Adicionar mais cores conforme necessário
};

const selectedColor = computed(() => colorMap[props.iconColor] || colorMap.indigo);

const containerClasses = computed(() => [
  baseBgClasses,
  selectedColor.value.from,
  selectedColor.value.to,
  selectedColor.value.border,
  baseBorderClasses,
]);

const descriptionClasses = computed(() => [
  baseTextClasses,
  selectedColor.value.text,
]);

const iconContainerClasses = computed(() => [
  selectedColor.value.iconBg,
  selectedColor.value.border, // Usa a mesma cor de borda principal, mas mais opaca
  'border-opacity-30'
]);

const iconClasses = computed(() => [
  selectedColor.value.iconText,
]);

</script>
