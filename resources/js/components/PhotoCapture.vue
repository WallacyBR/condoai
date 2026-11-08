<template>
  <div class="space-y-4">
    <!-- Preview da foto atual -->
    <div v-if="modelValue" class="relative">
      <div class="flex items-center space-x-4">
        <div class="relative">
          <img 
            :src="modelValue" 
            alt="Foto do morador" 
            class="w-24 h-24 rounded-lg object-cover border-2 border-indigo-500/30"
          />
          <button
            @click="removePhoto"
            type="button"
            class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs transition-colors"
            title="Remover foto"
          >
            ×
          </button>
        </div>
        <div class="flex-1">
          <p class="text-sm text-gray-300">Foto atual</p>
          <p class="text-xs text-gray-500">Clique em "Remover" para tirar uma nova foto</p>
        </div>
      </div>
    </div>

    <!-- Botões de ação -->
    <div v-if="!modelValue" class="space-y-3">
      <button
        @click="showCameraModal = true"
        type="button"
        class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center justify-center space-x-2"
        :disabled="uploading"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <span>{{ uploading ? 'Fazendo upload...' : 'Tirar Foto' }}</span>
      </button>

      <div class="relative">
        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          @change="handleFileUpload"
          class="hidden"
        />
        <button
          @click="$refs.fileInput.click()"
          type="button"
          class="w-full px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors flex items-center justify-center space-x-2"
          :disabled="uploading"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <span>{{ uploading ? 'Fazendo upload...' : 'Fazer Upload' }}</span>
        </button>
      </div>
    </div>

    <!-- Modal da câmera -->
    <div v-if="showCameraModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-gray-900 rounded-2xl p-6 max-w-md w-full mx-4 border-2 border-indigo-500/20">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-white">Capturar Foto</h3>
          <button
            @click="closeCameraModal"
            type="button"
            class="text-gray-400 hover:text-white transition-colors"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Status da câmera -->
        <div v-if="cameraStatus" class="mb-4 p-3 rounded-lg text-sm" :class="cameraStatusClass">
          {{ cameraStatus }}
        </div>

        <!-- Preview da câmera -->
        <div class="relative bg-black rounded-lg overflow-hidden mb-4">
          <video
            ref="video"
            class="w-full h-64 object-cover camera-video"
            autoplay
            playsinline
            muted
          ></video>
          
          <!-- Overlay de carregamento -->
          <div v-if="cameraLoading" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="text-white text-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
              <p>Iniciando câmera...</p>
            </div>
          </div>

          <!-- Overlay de erro -->
          <div v-if="cameraError" class="absolute inset-0 bg-black bg-opacity-75 flex items-center justify-center">
            <div class="text-white text-center p-4">
              <svg class="h-12 w-12 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <p class="text-sm">{{ cameraError }}</p>
              <button
                @click="retryCamera"
                class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm transition-colors"
              >
                Tentar Novamente
              </button>
            </div>
          </div>
        </div>

        <!-- Botões da câmera -->
        <div class="flex space-x-3">
          <button
            @click="capturePhoto"
            type="button"
            class="flex-1 px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center justify-center space-x-2"
            :disabled="cameraLoading || cameraError || !isVideoReady"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ isVideoReady ? 'Capturar' : 'Aguardando...' }}</span>
          </button>
          
          <button
            @click="closeCameraModal"
            type="button"
            class="px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
          >
            Cancelar
          </button>
        </div>

        <!-- Debug info (apenas em desenvolvimento) -->
        <div v-if="debugInfo" class="mt-4 p-3 bg-gray-800 rounded text-xs text-gray-300">
          <p>Debug: {{ debugInfo }}</p>
        </div>
      </div>
    </div>

    <!-- Modal de preview da foto capturada -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
      <div class="bg-gray-900 rounded-2xl p-6 max-w-md w-full mx-4 border-2 border-indigo-500/20">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-white">Preview da Foto</h3>
          <button
            @click="showPreviewModal = false"
            type="button"
            class="text-gray-400 hover:text-white transition-colors"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Preview da foto -->
        <div class="mb-4">
          <img 
            :src="capturedPhoto" 
            alt="Foto capturada" 
            class="w-full h-64 object-cover rounded-lg"
          />
        </div>

        <!-- Botões -->
        <div class="flex space-x-3">
          <button
            @click="savePhoto"
            type="button"
            class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center justify-center space-x-2"
            :disabled="uploading"
          >
            <svg v-if="!uploading" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
            <span>{{ uploading ? 'Salvando...' : 'Salvar Foto' }}</span>
          </button>
          
          <button
            @click="retakePhoto"
            type="button"
            class="px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
          >
            Tirar Nova
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, nextTick, computed, watch } from 'vue';
import { imageUploadService } from '../services/imageUpload.js';

export default {
  name: 'PhotoCapture',
  props: {
    modelValue: {
      type: String,
      default: ''
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const showCameraModal = ref(false);
    const showPreviewModal = ref(false);
    const video = ref(null);
    const fileInput = ref(null);
    const capturedPhoto = ref('');
    const uploading = ref(false);
    const cameraLoading = ref(false);
    const cameraError = ref('');
    const cameraStatus = ref('');
    const stream = ref(null);
    const isVideoReady = ref(false);
    const debugInfo = ref('');

    const cameraStatusClass = computed(() => {
      if (cameraError.value) return 'bg-red-900/50 text-red-300 border border-red-500/30';
      if (cameraLoading.value) return 'bg-blue-900/50 text-blue-300 border border-blue-500/30';
      return 'bg-green-900/50 text-green-300 border border-green-500/30';
    });

    const updateDebugInfo = (info) => {
      debugInfo.value = info;
      console.log('Debug:', info);
    };

    const startCamera = async () => {
      try {
        updateDebugInfo('Iniciando câmera...');
        cameraLoading.value = true;
        cameraError.value = '';
        cameraStatus.value = 'Solicitando permissão da câmera...';
        isVideoReady.value = false;

        // Aguardar o DOM ser atualizado
        await nextTick();
        
        // Aguardar mais tempo para garantir que o modal está visível
        await new Promise(resolve => setTimeout(resolve, 500));

        // Tentar encontrar o elemento de vídeo
        let videoElement = video.value;
        updateDebugInfo(`video.value: ${videoElement ? 'encontrado' : 'não encontrado'}`);
        
        if (!videoElement) {
          updateDebugInfo('Tentando fallback com querySelector...');
          videoElement = document.querySelector('.camera-video');
          updateDebugInfo(`videoElement via .camera-video: ${videoElement ? 'encontrado' : 'não encontrado'}`);
        }
        
        if (!videoElement) {
          updateDebugInfo('Tentando fallback com querySelector video...');
          videoElement = document.querySelector('video');
          updateDebugInfo(`videoElement via video: ${videoElement ? 'encontrado' : 'não encontrado'}`);
        }

        if (!videoElement) {
          throw new Error('Elemento de vídeo não encontrado após múltiplas tentativas');
        }

        cameraStatus.value = 'Configurando câmera...';
        updateDebugInfo('Solicitando permissão da câmera...');

        // Verificar se a API está disponível
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          throw new Error('API MediaDevices não está disponível neste navegador');
        }

        // Solicitar acesso à câmera com configurações mais flexíveis
        stream.value = await navigator.mediaDevices.getUserMedia({
          video: {
            width: { ideal: 640, min: 320 },
            height: { ideal: 480, min: 240 },
            facingMode: 'user'
          }
        });

        updateDebugInfo(`Stream obtido: ${stream.value ? 'sucesso' : 'falha'}`);

        // Configurar o vídeo
        videoElement.srcObject = stream.value;
        
        videoElement.onloadedmetadata = () => {
          const width = videoElement.videoWidth;
          const height = videoElement.videoHeight;
          updateDebugInfo(`Vídeo carregado, dimensões: ${width}x${height}`);
          
          videoElement.play().then(() => {
            updateDebugInfo('Vídeo iniciado com sucesso');
            cameraLoading.value = false;
            cameraStatus.value = 'Câmera pronta!';
            isVideoReady.value = true;
          }).catch(error => {
            updateDebugInfo(`Erro ao iniciar vídeo: ${error.message}`);
            cameraError.value = 'Erro ao iniciar vídeo: ' + error.message;
            cameraLoading.value = false;
          });
        };

        videoElement.onerror = (error) => {
          updateDebugInfo(`Erro no vídeo: ${error}`);
          cameraError.value = 'Erro no elemento de vídeo';
          cameraLoading.value = false;
        };

        // Timeout de segurança
        setTimeout(() => {
          if (cameraLoading.value) {
            updateDebugInfo('Timeout da câmera - forçando parada');
            cameraLoading.value = false;
            cameraError.value = 'Timeout ao carregar câmera';
            cameraStatus.value = '';
          }
        }, 15000); // 15 segundos

      } catch (error) {
        updateDebugInfo(`Erro ao iniciar câmera: ${error.message}`);
        cameraLoading.value = false;
        cameraError.value = getCameraErrorMessage(error);
        cameraStatus.value = '';
      }
    };

    const getCameraErrorMessage = (error) => {
      updateDebugInfo(`Tipo de erro: ${error.name} - ${error.message}`);
      
      if (error.name === 'NotAllowedError') {
        return 'Permissão da câmera negada. Permita o acesso à câmera e tente novamente.';
      } else if (error.name === 'NotFoundError') {
        return 'Câmera não encontrada. Verifique se há uma câmera conectada.';
      } else if (error.name === 'NotSupportedError') {
        return 'Câmera não suportada. Tente usar um navegador mais recente.';
      } else if (error.name === 'NotReadableError') {
        return 'A câmera está sendo usada por outro aplicativo.';
      } else if (error.name === 'OverconstrainedError') {
        return 'As configurações da câmera não são suportadas.';
      } else {
        return `Erro ao acessar câmera: ${error.message}`;
      }
    };

    const stopCamera = () => {
      updateDebugInfo('Parando câmera...');
      if (stream.value) {
        stream.value.getTracks().forEach(track => {
          updateDebugInfo(`Parando track: ${track.kind}`);
          track.stop();
        });
        stream.value = null;
      }
      cameraLoading.value = false;
      cameraError.value = '';
      cameraStatus.value = '';
      isVideoReady.value = false;
    };

    const capturePhoto = () => {
      updateDebugInfo('Tentando capturar foto...');
      try {
        const videoElement = video.value || document.querySelector('.camera-video');
        updateDebugInfo(`Elemento de vídeo para captura: ${videoElement ? 'encontrado' : 'não encontrado'}`);
        
        if (!videoElement) {
          throw new Error('Elemento de vídeo não encontrado');
        }

        // Verificar se o vídeo está pronto
        if (videoElement.videoWidth === 0 || videoElement.videoHeight === 0) {
          throw new Error('Vídeo não está pronto para captura. Aguarde o carregamento.');
        }

        const width = videoElement.videoWidth;
        const height = videoElement.videoHeight;
        updateDebugInfo(`Dimensões do vídeo: ${width}x${height}`);

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Configurar canvas com as dimensões do vídeo
        canvas.width = width;
        canvas.height = height;

        updateDebugInfo(`Canvas configurado: ${canvas.width}x${canvas.height}`);

        // Desenhar o frame atual do vídeo no canvas
        ctx.drawImage(videoElement, 0, 0);

        updateDebugInfo('Frame desenhado no canvas');

        // Converter para base64
        capturedPhoto.value = canvas.toDataURL('image/jpeg', 0.8);
        updateDebugInfo(`Foto convertida para base64, tamanho: ${capturedPhoto.value.length} chars`);

        // Parar a câmera e fechar o modal
        stopCamera();
        showCameraModal.value = false;
        showPreviewModal.value = true;

        updateDebugInfo('Foto capturada com sucesso');

      } catch (error) {
        updateDebugInfo(`Erro ao capturar foto: ${error.message}`);
        cameraError.value = 'Erro ao capturar foto: ' + error.message;
      }
    };

    const savePhoto = async () => {
      try {
        updateDebugInfo('Iniciando salvamento da foto...');
        uploading.value = true;
        
        // Redimensionar imagem antes do upload
        updateDebugInfo('Redimensionando imagem...');
        const resizedPhoto = await imageUploadService.resizeImage(capturedPhoto.value, 800, 600, 0.8);
        updateDebugInfo('Imagem redimensionada');
        
        // Fazer upload da imagem
        updateDebugInfo('Fazendo upload da imagem...');
        const result = await imageUploadService.uploadImage(resizedPhoto, 'residents');
        updateDebugInfo(`Upload concluído: ${result.success ? 'sucesso' : 'falha'}`);
        
        if (result.success) {
          emit('update:modelValue', result.url);
          showPreviewModal.value = false;
          capturedPhoto.value = '';
          updateDebugInfo('Foto salva com sucesso');
        } else {
          throw new Error(result.error || 'Erro desconhecido no upload');
        }
      } catch (error) {
        updateDebugInfo(`Erro ao salvar foto: ${error.message}`);
        console.error('Erro ao salvar foto:', error);
        alert('Erro ao salvar foto: ' + error.message);
      } finally {
        uploading.value = false;
      }
    };

    const retakePhoto = () => {
      showPreviewModal.value = false;
      capturedPhoto.value = '';
      showCameraModal.value = true;
      startCamera();
    };

    const handleFileUpload = async (event) => {
      const file = event.target.files[0];
      if (!file) return;

      try {
        uploading.value = true;

        // Validar arquivo
        const validation = imageUploadService.validateImageFile(file, 5);
        if (!validation.valid) {
          alert(validation.error);
          return;
        }

        // Converter para base64
        const base64 = await imageUploadService.fileToBase64(file);
        
        // Redimensionar imagem
        const resizedImage = await imageUploadService.resizeImage(base64, 800, 600, 0.8);
        
        // Fazer upload
        const result = await imageUploadService.uploadImage(resizedImage, 'residents');
        
        if (result.success) {
          emit('update:modelValue', result.url);
        }
      } catch (error) {
        console.error('Erro no upload do arquivo:', error);
        alert('Erro ao fazer upload: ' + error.message);
      } finally {
        uploading.value = false;
        // Limpar input
        if (fileInput.value) {
          fileInput.value.value = '';
        }
      }
    };

    const removePhoto = async () => {
      if (props.modelValue) {
        try {
          // Tentar deletar a imagem do servidor
          await imageUploadService.deleteImage(props.modelValue);
        } catch (error) {
          console.warn('Erro ao deletar imagem do servidor:', error);
          // Continuar mesmo se falhar ao deletar
        }
      }
      
      emit('update:modelValue', '');
    };

    const closeCameraModal = () => {
      stopCamera();
      showCameraModal.value = false;
    };

    const retryCamera = () => {
      cameraError.value = '';
      startCamera();
    };

    // Watcher para abrir câmera quando modal for aberto
    watch(showCameraModal, (newValue) => {
      updateDebugInfo(`Watcher showCameraModal mudou para: ${newValue}`);
      if (newValue) {
        updateDebugInfo('Modal aberto, iniciando câmera...');
        // Pequeno delay para garantir que o modal está renderizado
        setTimeout(() => {
          startCamera();
        }, 300);
      } else {
        updateDebugInfo('Modal fechado, parando câmera...');
        stopCamera();
      }
    });

    return {
      showCameraModal,
      showPreviewModal,
      video,
      fileInput,
      capturedPhoto,
      uploading,
      cameraLoading,
      cameraError,
      cameraStatus,
      cameraStatusClass,
      isVideoReady,
      debugInfo,
      capturePhoto,
      savePhoto,
      retakePhoto,
      handleFileUpload,
      removePhoto,
      closeCameraModal,
      retryCamera
    };
  }
};
</script>

<style scoped>
.camera-video {
  transform: scaleX(-1); /* Espelhar a câmera */
}
</style> 