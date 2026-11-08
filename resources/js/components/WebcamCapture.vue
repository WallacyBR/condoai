<template>
  <div class="space-y-2">
    <div v-if="image">
      <img :src="image" class="h-32 object-cover" />
    </div>
    <video v-if="stream && !image" ref="video" class="h-32"></video>
    <div class="space-x-2">
      <button v-if="!stream" @click="start" class="px-2 py-1 bg-gray-500 text-white rounded">Abrir C\u00e2mera</button>
      <button v-if="stream && !image" @click="capture" class="px-2 py-1 bg-green-500 text-white rounded">Capturar</button>
      <button v-if="image" @click="clear" class="px-2 py-1 bg-red-500 text-white rounded">Remover</button>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'WebcamCapture',
  props: { modelValue: String },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const video = ref(null);
    const stream = ref(null);
    const image = ref(props.modelValue);

    const start = async () => {
      stream.value = await navigator.mediaDevices.getUserMedia({ video: true });
      video.value.srcObject = stream.value;
      video.value.play();
    };

    const capture = () => {
      const canvas = document.createElement('canvas');
      canvas.width = video.value.videoWidth;
      canvas.height = video.value.videoHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(video.value, 0, 0);
      image.value = canvas.toDataURL('image/png');
      emit('update:modelValue', image.value);
      stream.value.getTracks().forEach(t => t.stop());
    };

    const clear = () => {
      image.value = '';
      emit('update:modelValue', '');
    };

    return { video, start, capture, clear, stream, image };
  }
};
</script>
