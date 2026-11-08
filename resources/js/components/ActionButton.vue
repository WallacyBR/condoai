<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <span v-if="loading" class="mr-2">
      <svg class="animate-spin h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
      </svg>
    </span>
    
    <span v-if="icon && !loading" class="mr-2">
      <component :is="icon" class="h-4 w-4" />
    </span>
    
    <slot>{{ text }}</slot>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'outline', 'ghost'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  icon: {
    type: [String, Object],
    default: null
  },
  text: {
    type: String,
    default: ''
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex',
    'items-center',
    'justify-center',
    'font-medium',
    'rounded-lg',
    'transition-all',
    'duration-200',
    'focus:outline-none',
    'focus:ring-2',
    'focus:ring-offset-2',
    'focus:ring-ring'
  ]
  
  // Size classes
  const sizeClasses = {
    sm: ['px-3', 'py-2', 'text-sm'],
    md: ['px-4', 'py-3', 'text-sm'],
    lg: ['px-6', 'py-4', 'text-base']
  }
  
  // Variant classes
  const variantClasses = {
    primary: [
      'bg-primary',
      'text-primary-foreground',
      'hover:bg-primary/90',
      'shadow-lg',
      'hover:shadow-xl'
    ],
    secondary: [
      'bg-secondary',
      'text-secondary-foreground',
      'hover:bg-secondary/90'
    ],
    danger: [
      'bg-destructive',
      'text-destructive-foreground',
      'hover:bg-destructive/90',
      'shadow-lg',
      'hover:shadow-xl'
    ],
    success: [
      'bg-green-600',
      'text-white',
      'hover:bg-green-700',
      'shadow-lg',
      'hover:shadow-xl'
    ],
    outline: [
      'border',
      'border-border',
      'bg-background',
      'text-foreground',
      'hover:bg-accent',
      'hover:text-accent-foreground'
    ],
    ghost: [
      'text-foreground',
      'hover:bg-accent',
      'hover:text-accent-foreground'
    ]
  }
  
  const classes = [
    ...baseClasses,
    ...sizeClasses[props.size],
    ...variantClasses[props.variant]
  ]
  
  if (props.fullWidth) {
    classes.push('w-full')
  }
  
  if (props.disabled || props.loading) {
    classes.push('opacity-50', 'cursor-not-allowed')
  }
  
  return classes.join(' ')
})

const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>
