<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'glass-morphism-btn group relative flex justify-center items-center font-medium text-white transition-all duration-300 ease-in-out overflow-hidden',
      sizeClass,
      variantClass,
      extraClass,
      { 'opacity-50 cursor-not-allowed': disabled || loading }
    ]"
    v-bind="$attrs"
  >
    <span v-if="loading" class="mr-2 flex items-center">
      <svg class="animate-spin h-5 w-5 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </span>
    <slot />
    <span class="glass-shine"></span>
    <span class="glass-reflection"></span>
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

const sizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'px-3 py-2 text-sm rounded-lg';
    case 'lg':
      return 'px-8 py-4 text-lg rounded-xl';
    case 'xl':
      return 'px-10 py-5 text-xl rounded-2xl';
    default:
      return 'px-4 py-3 text-base rounded-xl';
  }
});

const variantClass = computed(() => {
  switch (props.variant) {
    case 'secondary':
      return 'glass-secondary';
    case 'primary-action':
      return 'glass-primary-action';
    case 'success':
      return 'glass-success';
    case 'danger':
      return 'glass-danger';
    case 'info':
      return 'glass-info';
    case 'confirm':
      return 'glass-confirm';
    case 'cancel':
      return 'glass-cancel';
    case 'warning':
      return 'glass-warning';
    default:
      return 'glass-primary';
  }
});
</script>

<style scoped>
.glass-morphism-btn {
  position: relative;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  box-shadow:
    0 4px 16px rgba(0, 0, 0, 0.1),
    0 1px 4px rgba(255, 255, 255, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 0.75rem; /* Arredondamento padrão */
}

/* Arredondamentos específicos por tamanho */
.glass-morphism-btn.px-3.py-2 { /* sm */
  border-radius: 0.5rem;
}

.glass-morphism-btn.px-4.py-3 { /* md - padrão */
  border-radius: 0.75rem;
}

.glass-morphism-btn.px-8.py-4 { /* lg */
  border-radius: 0.75rem;
}

.glass-morphism-btn.px-10.py-5 { /* xl */
  border-radius: 1rem;
}

.glass-morphism-btn:focus {
  outline: 2px solid rgba(99, 102, 241, 0.5);
  outline-offset: 2px;
}

.glass-morphism-btn:active {
  transform: translateY(1px);
  box-shadow:
    0 2px 8px rgba(0, 0, 0, 0.15),
    inset 0 1px 0 rgba(255, 255, 255, 0.15);
}

.glass-morphism-btn:hover {
  border-color: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(20px) saturate(200%);
  -webkit-backdrop-filter: blur(20px) saturate(200%);
  box-shadow:
    0 6px 24px rgba(0, 0, 0, 0.15),
    0 2px 8px rgba(255, 255, 255, 0.1),
    inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

/* Efeito de brilho deslizante */
.glass-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, 
    transparent 0%, 
    rgba(255, 255, 255, 0.2) 45%, 
    rgba(255, 255, 255, 0.4) 50%, 
    rgba(255, 255, 255, 0.2) 55%, 
    transparent 100%);
  transition: left 0.6s ease;
  z-index: 2;
  opacity: 0;
}

.group:hover .glass-shine {
  left: 100%;
  opacity: 1;
}

/* Reflexão de vidro */
.glass-reflection {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 50%;
  background: linear-gradient(180deg,
    rgba(255, 255, 255, 0.12) 0%,
    rgba(255, 255, 255, 0.06) 50%,
    transparent 100%);
  border-radius: inherit;
  pointer-events: none;
  z-index: 1;
}

/* Variantes de cores */
.glass-primary {
  background: rgba(99, 102, 241, 0.15);
  border-color: rgba(99, 102, 241, 0.3);
}

.glass-primary:hover {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.4);
}

.glass-secondary {
  background: rgba(107, 114, 128, 0.15);
  border-color: rgba(107, 114, 128, 0.3);
}

.glass-secondary:hover {
  background: rgba(107, 114, 128, 0.2);
  border-color: rgba(107, 114, 128, 0.4);
}

.glass-success {
  background: rgba(16, 185, 129, 0.15);
  border-color: rgba(16, 185, 129, 0.3);
}

.glass-success:hover {
  background: rgba(16, 185, 129, 0.2);
  border-color: rgba(16, 185, 129, 0.4);
}

.glass-danger {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.3);
}

.glass-danger:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
}

.glass-info {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.3);
}

.glass-info:hover {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.4);
}

.glass-warning {
  background: rgba(245, 158, 11, 0.15);
  border-color: rgba(245, 158, 11, 0.3);
}

.glass-warning:hover {
  background: rgba(245, 158, 11, 0.2);
  border-color: rgba(245, 158, 11, 0.4);
}

.glass-confirm {
  background: rgba(34, 197, 94, 0.15);
  border-color: rgba(34, 197, 94, 0.3);
}

.glass-confirm:hover {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.4);
}

.glass-cancel {
  background: rgba(248, 113, 113, 0.15);
  border-color: rgba(248, 113, 113, 0.3);
}

.glass-cancel:hover {
  background: rgba(248, 113, 113, 0.2);
  border-color: rgba(248, 113, 113, 0.4);
}

.glass-primary-action {
  background: linear-gradient(135deg, 
    rgba(99, 102, 241, 0.2) 0%, 
    rgba(147, 51, 234, 0.2) 100%);
  border-color: rgba(99, 102, 241, 0.4);
}

.glass-primary-action:hover {
  background: linear-gradient(135deg, 
    rgba(99, 102, 241, 0.3) 0%, 
    rgba(147, 51, 234, 0.3) 100%);
  border-color: rgba(99, 102, 241, 0.5);
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style> 