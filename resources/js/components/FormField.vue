<template>
  <div class="mb-4">
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-foreground mb-2">
      {{ label }}
      <span v-if="required" class="text-destructive">*</span>
    </label>
    
    <div class="relative">
      <!-- Icon -->
      <div v-if="icon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <component :is="icon" class="h-5 w-5 text-muted-foreground" />
      </div>
      
      <!-- Input Field -->
      <input
        v-if="type !== 'textarea' && type !== 'select'"
        :id="inputId"
        :type="type"
        :name="name"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      
      <!-- Textarea -->
      <textarea
        v-else-if="type === 'textarea'"
        :id="inputId"
        :name="name"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :readonly="readonly"
        :rows="rows || 3"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      ></textarea>
      
      <!-- Select -->
      <select
        v-else-if="type === 'select'"
        :id="inputId"
        :name="name"
        :value="modelValue"
        :required="required"
        :disabled="disabled"
        :class="inputClasses"
        @change="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      
      <!-- Show/Hide Password Button -->
      <button
        v-if="type === 'password' && showPasswordToggle"
        type="button"
        @click="togglePassword"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted-foreground hover:text-foreground transition-colors"
      >
        <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        </svg>
        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
        </svg>
      </button>
    </div>
    
    <!-- Error Message -->
    <p v-if="error" class="mt-2 text-sm text-destructive flex items-center">
      <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      {{ error }}
    </p>
    
    <!-- Help Text -->
    <p v-if="help" class="mt-1 text-sm text-muted-foreground">
      {{ help }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  name: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  help: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  icon: {
    type: [String, Object],
    default: null
  },
  options: {
    type: Array,
    default: () => []
  },
  rows: {
    type: Number,
    default: 3
  },
  showPasswordToggle: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

const showPassword = ref(false)

const inputId = computed(() => `field-${props.name}`)

const actualType = computed(() => {
  if (props.type === 'password' && showPassword.value) {
    return 'text'
  }
  return props.type
})

const inputClasses = computed(() => {
  const baseClasses = [
    'w-full',
    'py-3',
    'bg-background',
    'border',
    'border-border',
    'rounded-lg',
    'focus:ring-2',
    'focus:ring-ring',
    'focus:border-transparent',
    'transition-all',
    'duration-200',
    'placeholder-muted-foreground'
  ]
  
  if (props.icon) {
    baseClasses.push('pl-10', 'pr-4')
  } else {
    baseClasses.push('px-4')
  }
  
  if (props.type === 'password' && props.showPasswordToggle) {
    baseClasses.push('pr-12')
  }
  
  if (props.error) {
    baseClasses.push('border-destructive', 'ring-destructive')
  }
  
  if (props.disabled) {
    baseClasses.push('opacity-50', 'cursor-not-allowed')
  }
  
  return baseClasses.join(' ')
})

const togglePassword = () => {
  showPassword.value = !showPassword.value
}
</script>
