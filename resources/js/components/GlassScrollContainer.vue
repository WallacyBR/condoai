<template>
  <div 
    ref="scrollContainer"
    :class="containerClasses"
    :style="containerStyles"
    @scroll="handleScroll"
  >
    <slot />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';

const props = defineProps({
  // Variantes de cor
  color: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'cyan', 'green', 'blue', 'purple', 'orange', 'pink'].includes(value)
  },
  // Tamanhos
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  // Altura máxima
  maxHeight: {
    type: String,
    default: '60'
  },
  // Mostrar efeito de fade
  showFade: {
    type: Boolean,
    default: false
  },
  // Animar itens da lista
  animateItems: {
    type: Boolean,
    default: false
  },
  // Padding direito para a barra
  paddingRight: {
    type: String,
    default: '2'
  },
  // Classes adicionais
  extraClass: {
    type: String,
    default: ''
  }
});

const scrollContainer = ref(null);
const hasOverflow = ref(false);

const containerClasses = computed(() => {
  const classes = [
    'glass-scrollbar',
    'overflow-y-auto',
    `pr-${props.paddingRight}`,
    'relative'
  ];

  // Adicionar variante de cor
  if (props.color !== 'default') {
    classes.push(`glass-scrollbar-${props.color}`);
  }

  // Adicionar tamanho
  if (props.size !== 'md') {
    classes.push(`glass-scrollbar-${props.size}`);
  }

  // Adicionar efeito de fade
  if (props.showFade) {
    classes.push('glass-scrollbar-fade');
    if (hasOverflow.value) {
      classes.push('has-overflow');
    }
  }

  // Adicionar animação de itens
  if (props.animateItems) {
    classes.push('glass-scrollbar-items');
  }

  // Adicionar classes extras
  if (props.extraClass) {
    classes.push(props.extraClass);
  }

  return classes.join(' ');
});

const containerStyles = computed(() => {
  return {
    maxHeight: props.maxHeight.includes('px') ? props.maxHeight : `${props.maxHeight}rem`
  };
});

const checkOverflow = () => {
  if (!scrollContainer.value) return;
  
  const element = scrollContainer.value;
  hasOverflow.value = element.scrollHeight > element.clientHeight;
};

const handleScroll = () => {
  // Emitir evento de scroll se necessário
  // this.$emit('scroll', event);
};

onMounted(async () => {
  await nextTick();
  checkOverflow();
  
  // Verificar overflow quando o conteúdo mudar
  const resizeObserver = new ResizeObserver(() => {
    checkOverflow();
  });
  
  if (scrollContainer.value) {
    resizeObserver.observe(scrollContainer.value);
  }
});

// Expor métodos para uso externo
defineExpose({
  checkOverflow,
  hasOverflow: computed(() => hasOverflow.value)
});
</script>

<style scoped>
/* Estilos específicos do componente */
.glass-scrollbar {
  scrollbar-gutter: stable;
}

/* Garantir que o container tenha posicionamento relativo para o fade */
.glass-scrollbar-fade {
  position: relative;
}

/* Glass Scrollbar Styles - Aplicados diretamente no componente */
.glass-scrollbar::-webkit-scrollbar {
  width: 10px;
}

.glass-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  margin: 6px 0;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 
    inset 0 2px 8px rgba(0, 0, 0, 0.2),
    0 1px 4px rgba(255, 255, 255, 0.05);
}

.glass-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(15px);
  -webkit-backdrop-filter: blur(15px);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.3),
    inset 0 2px 8px rgba(255, 255, 255, 0.1),
    0 1px 4px rgba(255, 255, 255, 0.1);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.glass-scrollbar::-webkit-scrollbar-thumb::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.25) 0%,
    rgba(255, 255, 255, 0.05) 50%,
    rgba(255, 255, 255, 0.1) 100%
  );
  border-radius: inherit;
  pointer-events: none;
}

.glass-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  box-shadow: 
    0 8px 24px rgba(0, 0, 0, 0.4),
    inset 0 4px 12px rgba(255, 255, 255, 0.15),
    0 2px 8px rgba(255, 255, 255, 0.15),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  transform: scale(1.05);
}

.glass-scrollbar::-webkit-scrollbar-thumb:active {
  background: rgba(255, 255, 255, 0.35);
  border-color: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(25px);
  -webkit-backdrop-filter: blur(25px);
  box-shadow: 
    0 4px 16px rgba(0, 0, 0, 0.5),
    inset 0 6px 16px rgba(255, 255, 255, 0.2),
    0 1px 4px rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

/* Efeito de brilho sutil na barra */
.glass-scrollbar::-webkit-scrollbar-thumb::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  right: 2px;
  height: 30%;
  background: linear-gradient(
    180deg,
    rgba(255, 255, 255, 0.3) 0%,
    transparent 100%
  );
  border-radius: 8px;
  pointer-events: none;
  opacity: 0.6;
}

/* Variantes de cores */
.glass-scrollbar-cyan::-webkit-scrollbar-thumb {
  background: rgba(34, 211, 238, 0.15);
  border-color: rgba(34, 211, 238, 0.25);
}

.glass-scrollbar-cyan::-webkit-scrollbar-thumb:hover {
  background: rgba(34, 211, 238, 0.25);
  border-color: rgba(34, 211, 238, 0.4);
  box-shadow: 
    0 8px 24px rgba(34, 211, 238, 0.3),
    inset 0 4px 12px rgba(34, 211, 238, 0.1),
    0 2px 8px rgba(34, 211, 238, 0.15);
}

.glass-scrollbar-green::-webkit-scrollbar-thumb {
  background: rgba(34, 197, 94, 0.15);
  border-color: rgba(34, 197, 94, 0.25);
}

.glass-scrollbar-green::-webkit-scrollbar-thumb:hover {
  background: rgba(34, 197, 94, 0.25);
  border-color: rgba(34, 197, 94, 0.4);
  box-shadow: 
    0 8px 24px rgba(34, 197, 94, 0.3),
    inset 0 4px 12px rgba(34, 197, 94, 0.1),
    0 2px 8px rgba(34, 197, 94, 0.15);
}

.glass-scrollbar-blue::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.25);
}

.glass-scrollbar-blue::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.25);
  border-color: rgba(59, 130, 246, 0.4);
  box-shadow: 
    0 8px 24px rgba(59, 130, 246, 0.3),
    inset 0 4px 12px rgba(59, 130, 246, 0.1),
    0 2px 8px rgba(59, 130, 246, 0.15);
}

/* Estilos para Firefox */
.glass-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.15) rgba(255, 255, 255, 0.05);
}

.glass-scrollbar-cyan {
  scrollbar-color: rgba(34, 211, 238, 0.15) rgba(255, 255, 255, 0.05);
}

.glass-scrollbar-green {
  scrollbar-color: rgba(34, 197, 94, 0.15) rgba(255, 255, 255, 0.05);
}

.glass-scrollbar-blue {
  scrollbar-color: rgba(59, 130, 246, 0.15) rgba(255, 255, 255, 0.05);
}

/* Efeito de fade glass para indicar rolagem */
.glass-scrollbar-fade::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 10px;
  height: 30px;
  background: linear-gradient(
    transparent,
    rgba(17, 24, 39, 0.6)
  );
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 0 0 12px 12px;
}

.glass-scrollbar-fade.has-overflow::after {
  opacity: 1;
}

/* Animações para itens da lista */
.glass-scrollbar-items li {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.glass-scrollbar-items li:hover {
  transform: translateX(6px) scale(1.02);
  box-shadow: 
    0 8px 24px rgba(0, 0, 0, 0.3),
    0 2px 8px rgba(255, 255, 255, 0.1),
    inset 0 1px 2px rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}
</style> 