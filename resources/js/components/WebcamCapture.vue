<template>
  <div class="space-y-4">
    <!-- Preview da imagem capturada -->
    <div v-if="image" class="relative">
      <img 
        :src="image" 
        class="w-full h-64 object-cover rounded-lg border-2 border-gray-600/50 shadow-lg" 
        alt="Foto capturada"
      />
      <div class="absolute top-2 right-2">
        <button 
          @click="clear" 
          class="bg-red-500/80 hover:bg-red-500 text-white p-2 rounded-full shadow-lg transition-all duration-200 backdrop-blur-sm"
          title="Remover foto"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H9a1 1 0 00-1 1v1M4 7h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Stream de vídeo -->
    <div v-if="stream && !image" class="relative">
      <video 
        ref="video" 
        class="w-full h-64 object-cover rounded-lg border-2 border-gray-600/50 shadow-lg bg-gray-800"
        autoplay
        playsinline
      ></video>
      <div class="absolute inset-0 flex items-center justify-center" v-if="!videoReady">
        <div class="bg-gray-900/80 rounded-lg p-4 text-white">
          <div class="animate-spin h-6 w-6 border-2 border-white border-t-transparent rounded-full mx-auto mb-2"></div>
          <p class="text-sm">Carregando câmera...</p>
        </div>
      </div>
    </div>

    <!-- Placeholder quando não há stream nem imagem -->
    <div v-if="!stream && !image" class="w-full h-64 bg-gray-800/50 border-2 border-dashed border-gray-600/50 rounded-lg flex items-center justify-center">
      <div class="text-center text-gray-400">
        <svg class="h-12 w-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="text-sm">Nenhuma foto capturada</p>
        <p class="text-xs text-gray-500 mt-1">Clique em "Abrir Câmera" para tirar uma foto</p>
      </div>
    </div>

    <!-- Botões de controle -->
    <div class="flex justify-center space-x-3">
      <!-- Botão para abrir câmera -->
      <button 
        v-if="!stream && !image" 
        @click="start" 
        type="button"
        class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-blue-600/60 to-blue-700/60 hover:from-blue-600/80 hover:to-blue-700/80 text-white rounded-lg shadow-lg transition-all duration-200 font-medium"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>Abrir Câmera</span>
      </button>

      <!-- Botão para capturar foto -->
      <button 
        v-if="stream && !image && videoReady" 
        @click="capture" 
        type="button"
        class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-green-600/60 to-green-700/60 hover:from-green-600/80 hover:to-green-700/80 text-white rounded-lg shadow-lg transition-all duration-200 font-medium"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>Capturar Foto</span>
      </button>

      <!-- Botão para cancelar/fechar câmera -->
      <button 
        v-if="stream && !image" 
        @click="stop" 
        type="button"
        class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-gray-600/60 to-gray-700/60 hover:from-gray-600/80 hover:to-gray-700/80 text-white rounded-lg shadow-lg transition-all duration-200 font-medium"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span>Cancelar</span>
      </button>

      <!-- Botão para tirar nova foto -->
      <button 
        v-if="image" 
        @click="retake" 
        type="button"
        class="flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-blue-600/60 to-blue-700/60 hover:from-blue-600/80 hover:to-blue-700/80 text-white rounded-lg shadow-lg transition-all duration-200 font-medium"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Tirar Nova Foto</span>
      </button>
    </div>

    <!-- Mensagem de erro -->
    <div v-if="error" class="bg-red-500/20 border border-red-500/50 rounded-lg p-3 text-red-300 text-sm">
      <div class="flex items-center space-x-2">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ error }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onUnmounted } from 'vue';

export default {
  name: 'WebcamCapture',
  props: { 
    modelValue: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const video = ref(null);
    const stream = ref(null);
    const image = ref(props.modelValue || '');
    const videoReady = ref(false);
    const error = ref('');

    // Watch para sincronizar com o modelValue
    watch(() => props.modelValue, (newValue) => {
      image.value = newValue || '';
    });

    // Watch para emitir mudanças na imagem
    watch(image, (newImage) => {
      emit('update:modelValue', newImage);
    });

    const start = async () => {
      try {
        error.value = '';
        videoReady.value = false;
        
        // Verificar se o navegador suporta getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          throw new Error('Seu navegador não suporta acesso à câmera');
        }

        // Solicitar acesso à câmera
        stream.value = await navigator.mediaDevices.getUserMedia({ 
          video: { 
            facingMode: 'environment', // Preferir câmera traseira em dispositivos móveis
            width: { ideal: 1280 },
            height: { ideal: 720 }
          } 
        });
        
        // Aguardar o próximo tick para garantir que o elemento video existe
        await new Promise(resolve => setTimeout(resolve, 100));
        
        if (video.value) {
          video.value.srcObject = stream.value;
          
          // Aguardar o vídeo estar pronto
          video.value.addEventListener('loadedmetadata', () => {
            videoReady.value = true;
          });
          
          await video.value.play();
        }
      } catch (e) {
        console.error('Erro ao acessar câmera:', e);
        if (e.name === 'NotAllowedError') {
          error.value = 'Acesso à câmera foi negado. Permita o acesso e tente novamente.';
        } else if (e.name === 'NotFoundError') {
          error.value = 'Nenhuma câmera foi encontrada no dispositivo.';
        } else if (e.name === 'NotReadableError') {
          error.value = 'A câmera está sendo usada por outro aplicativo.';
        } else {
          error.value = e.message || 'Erro ao acessar a câmera. Tente novamente.';
        }
        stop();
      }
    };

    const capture = () => {
      if (!video.value || !stream.value) return;
      
      try {
        const canvas = document.createElement('canvas');
        canvas.width = video.value.videoWidth;
        canvas.height = video.value.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video.value, 0, 0);
        image.value = canvas.toDataURL('image/jpeg', 0.8); // Usar JPEG com qualidade 80%
        stop();
      } catch (e) {
        console.error('Erro ao capturar foto:', e);
        error.value = 'Erro ao capturar a foto. Tente novamente.';
      }
    };

    const stop = () => {
      if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
      }
      videoReady.value = false;
    };

    const clear = () => {
      image.value = '';
      error.value = '';
    };

    const retake = () => {
      clear();
      start();
    };

    // Cleanup ao desmontar componente
    onUnmounted(() => {
      stop();
    });

    return { 
      video, 
      stream, 
      image, 
      videoReady,
      error,
      start, 
      capture, 
      stop,
      clear, 
      retake 
    };
  }
};
</script>
