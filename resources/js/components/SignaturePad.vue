<template>
  <div>
    <canvas ref="canvas" class="border bg-white"
            @mousedown="start" @mousemove="draw" @mouseup="end" @mouseleave="end"></canvas>
    <button @click="clear" class="mt-2 px-2 py-1 bg-gray-500 text-white rounded">Limpar</button>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'SignaturePad',
  props: { modelValue: String },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const canvas = ref(null);
    let ctx;
    let drawing = false;

    onMounted(() => {
      canvas.value.width = 300;
      canvas.value.height = 150;
      ctx = canvas.value.getContext('2d');
      ctx.strokeStyle = '#000';
      ctx.lineWidth = 2;
    });

    const start = (e) => {
      drawing = true;
      ctx.beginPath();
      ctx.moveTo(e.offsetX, e.offsetY);
    };

    const draw = (e) => {
      if (!drawing) return;
      ctx.lineTo(e.offsetX, e.offsetY);
      ctx.stroke();
    };

    const end = () => {
      drawing = false;
      emit('update:modelValue', canvas.value.toDataURL('image/png'));
    };

    const clear = () => {
      ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
      emit('update:modelValue', '');
    };

    return { canvas, start, draw, end, clear };
  }
};
</script>
