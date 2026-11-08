<template>
  <div class="signature-pad-container" ref="containerRef">
    <canvas
      ref="canvasRef"
      class="signature-canvas"
      :width="canvasWidth"
      :height="canvasHeight"
    ></canvas>
    <div class="signature-controls mt-3 flex gap-2 justify-center">
      <NeonButton
        type="button"
        @click="clear"
        size="sm"
        variant="danger"
        class="flex items-center"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Limpar
      </NeonButton>
      <NeonButton
        type="button"
        @click="undo"
        size="sm"
        variant="secondary"
        :disabled="!canUndo"
        class="flex items-center"
      >
        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>
        Desfazer
      </NeonButton>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import SignaturePad from 'signature_pad';
import NeonButton from './NeonButton.vue';

const props = defineProps({
  penColor: {
    type: String,
    default: '#fff'
  },
  backgroundColor: {
    type: String,
    default: 'rgba(0, 0, 0, 0)'
  },
  minWidth: {
    type: Number,
    default: 0.5
  },
  maxWidth: {
    type: Number,
    default: 2.5
  },
  throttle: {
    type: Number,
    default: 16
  },
  aspectRatio: {
    type: Number,
    default: 2
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const canvasRef = ref(null);
const containerRef = ref(null);
const signaturePad = ref(null);
const canUndo = ref(false);
const canvasWidth = ref(400);
const canvasHeight = ref(200);
let resizeObserver = null;

const updateCanvasSize = () => {
  if (!containerRef.value) return;
  const width = containerRef.value.offsetWidth;
  canvasWidth.value = width;
  canvasHeight.value = Math.round(width / props.aspectRatio);
  nextTick(() => {
    reinitSignaturePad();
  });
};

const reinitSignaturePad = () => {
  if (!canvasRef.value) return;
  const canvas = canvasRef.value;
  const ctx = canvas.getContext('2d');
  const dpr = window.devicePixelRatio || 1;
  canvas.width = canvasWidth.value * dpr;
  canvas.height = canvasHeight.value * dpr;
  ctx.setTransform(1, 0, 0, 1, 0, 0); // reset transform
  ctx.scale(dpr, dpr);
  ctx.clearRect(0, 0, canvasWidth.value, canvasHeight.value);
  drawDashedBaseLine();
  if (signaturePad.value) {
    const data = signaturePad.value.toData();
    signaturePad.value.off();
    signaturePad.value = new SignaturePad(canvas, getPadOptions());
    signaturePad.value.fromData(data);
    patchClear();
    addListeners();
  } else {
    signaturePad.value = new SignaturePad(canvas, getPadOptions());
    patchClear();
    addListeners();
  }
};

const getPadOptions = () => ({
  penColor: props.penColor,
  backgroundColor: props.backgroundColor,
  minWidth: props.minWidth,
  maxWidth: props.maxWidth,
  throttle: props.throttle
});

const patchClear = () => {
  const originalClear = signaturePad.value.clear.bind(signaturePad.value);
  signaturePad.value.clear = () => {
    originalClear();
    drawDashedBaseLine();
  };
};

const addListeners = () => {
  signaturePad.value.addEventListener('beginStroke', () => {
    canUndo.value = true;
  });
  signaturePad.value.addEventListener('endStroke', () => {
    emitChange();
  });
};

const drawDashedBaseLine = () => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  ctx.save();
  ctx.setLineDash([8, 6]);
  ctx.strokeStyle = '#94a3b8';
  ctx.lineWidth = 1.5;
  ctx.beginPath();
  ctx.moveTo(16, canvasHeight.value - 24);
  ctx.lineTo(canvasWidth.value - 16, canvasHeight.value - 24);
  ctx.stroke();
  ctx.setLineDash([]);
  ctx.restore();
};

const clear = () => {
  if (signaturePad.value) {
    signaturePad.value.clear();
    canUndo.value = false;
    emitChange();
  }
};

const undo = () => {
  if (signaturePad.value && canUndo.value) {
    const data = signaturePad.value.toData();
    if (data.length > 0) {
      data.pop();
      signaturePad.value.fromData(data);
      canUndo.value = data.length > 0;
      emitChange();
    }
  }
};

const isEmpty = () => {
  return signaturePad.value ? signaturePad.value.isEmpty() : true;
};

const getSignatureDataUrl = (type = 'image/png', encoderOptions = 0.92) => {
  if (signaturePad.value && !signaturePad.value.isEmpty()) {
    return signaturePad.value.toDataURL('image/png', encoderOptions);
  }
  return null;
};

const getSignatureData = () => {
  return signaturePad.value ? signaturePad.value.toData() : [];
};

const fromData = (data) => {
  if (signaturePad.value) {
    signaturePad.value.fromData(data);
    canUndo.value = data.length > 0;
  }
};

const emitChange = () => {
  const isEmpty = signaturePad.value ? signaturePad.value.isEmpty() : true;
  const dataUrl = isEmpty ? null : getSignatureDataUrl();
  emit('update:modelValue', dataUrl);
  emit('change', { isEmpty, dataUrl });
};

onMounted(() => {
  updateCanvasSize();
  resizeObserver = new ResizeObserver(updateCanvasSize);
  if (containerRef.value) {
    resizeObserver.observe(containerRef.value);
  }
});

onUnmounted(() => {
  if (resizeObserver && containerRef.value) {
    resizeObserver.unobserve(containerRef.value);
  }
  if (signaturePad.value) {
    signaturePad.value.off();
  }
});

watch(() => props.penColor, (newColor) => {
  if (signaturePad.value) {
    signaturePad.value.penColor = newColor;
  }
});

watch(() => props.backgroundColor, (newBgColor) => {
  if (signaturePad.value) {
    signaturePad.value.backgroundColor = newBgColor;
  }
});

// Expor métodos para o componente pai
defineExpose({
  clear,
  undo,
  isEmpty,
  getSignatureDataUrl,
  getSignatureData,
  fromData
});
</script>

<style scoped>
.signature-pad-container {
  @apply relative w-full;
  max-width: 100%;
}
.signature-canvas {
  @apply border border-gray-600 rounded-lg bg-gray-700/20 cursor-crosshair w-full block;
  touch-action: none;
  max-width: 100%;
  height: auto;
}
.signature-controls button:disabled {
  @apply opacity-50 cursor-not-allowed;
}
</style>
