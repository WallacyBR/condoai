<template>
  <div class="qr-code-container text-center p-4 bg-gray-800/70 rounded-lg shadow-md border border-gray-700/50">
    <div v-if="!value" class="text-gray-400">
      QR Code indisponível.
    </div>
    <div v-else>
      <qrcode-vue
        :value="fullUrl"
        :size="size"
        level="H"
        render-as="svg"
        class="inline-block p-2 bg-white rounded-md shadow"
        ref="qrCodeComponent"
      />
      <div class="mt-4 space-x-2">
        <button
          @click="downloadQrCode('png')"
          class="px-3 py-1.5 text-xs bg-cyan-600 hover:bg-cyan-700 text-white rounded-md shadow transition-colors"
        >
          Download PNG
        </button>
        <button
          @click="downloadQrCode('svg')"
          class="px-3 py-1.5 text-xs bg-gray-600 hover:bg-gray-700 text-white rounded-md shadow transition-colors"
        >
          Download SVG
        </button>
      </div>
      <p class="text-xs text-gray-400 mt-2 break-all">URL: {{ fullUrl }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
  value: { // Este será o qr_code_hash
    type: String,
    required: true,
  },
  size: {
    type: Number,
    default: 150,
  },
  filenamePrefix: {
    type: String,
    default: 'qrcode_equipamento'
  }
});

const qrCodeComponent = ref(null);

const fullUrl = computed(() => {
  if (!props.value) return '';
  // Constrói a URL completa. Adapte o base_url conforme necessário.
  // Em um app real, isso viria de uma config ou env var.
  const baseUrl = window.location.origin;
  return `${baseUrl}/qr/${props.value}`;
});

const downloadQrCode = async (format) => {
  if (!qrCodeComponent.value || !qrCodeComponent.value.$el) {
    console.error("Componente QR Code não renderizado.");
    return;
  }

  const qrEl = qrCodeComponent.value.$el;
  let dataUrl;

  if (format === 'svg') {
    const serializer = new XMLSerializer();
    const svgString = serializer.serializeToString(qrEl);
    dataUrl = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svgString)}`;
  } else { // png
    // Para converter SVG para PNG no cliente, precisamos de uma tela (canvas)
    const svgString = new XMLSerializer().serializeToString(qrEl);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();

    const devicePixelRatio = window.devicePixelRatio || 1;
    canvas.width = props.size * devicePixelRatio;
    canvas.height = props.size * devicePixelRatio;
    ctx.scale(devicePixelRatio, devicePixelRatio);

    img.onload = () => {
      ctx.drawImage(img, 0, 0, props.size, props.size);
      dataUrl = canvas.toDataURL('image/png');
      triggerDownload(dataUrl, format);
    };
    img.src = `data:image/svg+xml;base64,${btoa(svgString)}`;
    return; // Download será acionado no onload da imagem
  }

  triggerDownload(dataUrl, format);
};

const triggerDownload = (dataUrl, format) => {
  const link = document.createElement('a');
  link.href = dataUrl;
  link.download = `${props.filenamePrefix}_${props.value.substring(0,8)}.${format}`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

</script>

<style scoped>
.qr-code-container {
  display: inline-block; /* Para que o container não ocupe a largura total se não necessário */
}
</style>
