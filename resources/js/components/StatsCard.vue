<template>
  <div class="bg-card rounded-xl p-6 shadow-sm border border-border hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
        <p class="text-3xl font-bold text-foreground">{{ value }}</p>
        <p v-if="change" class="text-xs flex items-center mt-1" :class="changeClasses">
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="changeDirection === 'up'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
          </svg>
          {{ change }}
        </p>
      </div>
      <div :class="iconContainerClasses">
        <component :is="icon" class="w-6 h-6" :class="iconClasses" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  change: {
    type: String,
    default: ''
  },
  changeDirection: {
    type: String,
    default: 'up',
    validator: (value) => ['up', 'down'].includes(value)
  },
  icon: {
    type: [String, Object],
    required: true
  },
  color: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'accent', 'success', 'warning', 'danger'].includes(value)
  }
})

const changeClasses = computed(() => {
  const classes = ['flex', 'items-center']
  
  if (props.changeDirection === 'up') {
    classes.push('text-primary')
  } else {
    classes.push('text-destructive')
  }
  
  return classes.join(' ')
})

const iconContainerClasses = computed(() => {
  const baseClasses = ['w-12', 'h-12', 'rounded-lg', 'flex', 'items-center', 'justify-center']
  
  const colorClasses = {
    primary: ['bg-primary/10'],
    secondary: ['bg-secondary/10'],
    accent: ['bg-accent/10'],
    success: ['bg-green-100'],
    warning: ['bg-yellow-100'],
    danger: ['bg-red-100']
  }
  
  return [...baseClasses, ...colorClasses[props.color]].join(' ')
})

const iconClasses = computed(() => {
  const colorClasses = {
    primary: ['text-primary'],
    secondary: ['text-secondary'],
    accent: ['text-accent'],
    success: ['text-green-600'],
    warning: ['text-yellow-600'],
    danger: ['text-red-600']
  }
  
  return colorClasses[props.color].join(' ')
})
</script>
