<template>
  <div class="p-6 bg-gray-900 rounded-lg">
    <h2 class="text-xl font-bold text-white mb-4">Teste da Câmera</h2>
    
    <!-- Status da câmera -->
    <div class="mb-4">
      <p class="text-gray-300">Status: {{ cameraStatus }}</p>
      <p v-if="cameraError" class="text-red-400">Erro: {{ cameraError }}</p>
      <p v-if="isVideoReady" class="text-green-400">✓ Vídeo pronto para captura</p>
    </div>

    <!-- Botões -->
    <div class="space-y-2 mb-4">
      <button
        @click="startCamera"
        :disabled="cameraLoading"
        class="px-4 py-2 bg-blue-600 text-white rounded disabled:bg-gray-600"
      >
        {{ cameraLoading ? 'Iniciando...' : 'Iniciar Câmera' }}
      </button>
      
      <button
        @click="stopCamera"
        :disabled="!stream"
        class="px-4 py-2 bg-red-600 text-white rounded disabled:bg-gray-600 ml-2"
      >
        Parar Câmera
      </button>
      
      <button
        @click="capturePhoto"
        :disabled="!stream || cameraLoading || !isVideoReady"
        class="px-4 py-2 bg-green-600 text-white rounded disabled:bg-gray-600 ml-2"
      >
        {{ isVideoReady ? 'Capturar Foto' : 'Aguardando...' }}
      </button>
    </div>

    <!-- Vídeo -->
    <div class="mb-4">
      <video
        ref="video"
        class="w-full max-w-md bg-black rounded"
        autoplay
        playsinline
        muted
      ></video>
    </div>

    <!-- Foto capturada -->
    <div v-if="capturedPhoto" class="mb-4">
      <h3 class="text-white mb-2">Foto Capturada:</h3>
      <img :src="capturedPhoto" alt="Foto capturada" class="w-full max-w-md rounded" />
      <p class="text-gray-400 text-sm mt-2">Tamanho: {{ capturedPhoto.length }} caracteres</p>
    </div>

    <!-- Logs -->
    <div class="mt-4">
      <h3 class="text-white mb-2">Logs:</h3>
      <div class="bg-gray-800 p-3 rounded text-sm text-gray-300 max-h-40 overflow-y-auto">
        <div v-for="(log, index) in logs" :key="index" class="mb-1">
          {{ log }}
        </div>
      </div>
      <button
        @click="clearLogs"
        class="mt-2 px-3 py-1 bg-gray-700 text-white rounded text-xs"
      >
        Limpar Logs
      </button>
    </div>

    <!-- Teste manual -->
    <div class="mt-4 p-4 bg-gray-800 rounded">
      <h3 class="text-white mb-2">Teste Manual (Console):</h3>
      <button
        @click="testManualCamera"
        class="px-3 py-1 bg-yellow-600 text-white rounded text-sm"
      >
        Testar Câmera no Console
      </button>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'CameraTest',
  setup() {
    const video = ref(null);
    const stream = ref(null);
    const cameraLoading = ref(false);
    const cameraError = ref('');
    const cameraStatus = ref('Não iniciada');
    const capturedPhoto = ref('');
    const logs = ref([]);
    const isVideoReady = ref(false);

    const addLog = (message) => {
      const timestamp = new Date().toLocaleTimeString();
      logs.value.push(`${timestamp}: ${message}`);
      console.log(`[CameraTest] ${message}`);
    };

    const clearLogs = () => {
      logs.value = [];
    };

    const startCamera = async () => {
      try {
        addLog('Iniciando câmera...');
        cameraLoading.value = true;
        cameraError.value = '';
        cameraStatus.value = 'Solicitando permissão...';
        isVideoReady.value = false;

        // Verificar se a API está disponível
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          throw new Error('API MediaDevices não está disponível');
        }

        addLog('Solicitando acesso à câmera...');
        
        // Solicitar acesso à câmera
        stream.value = await navigator.mediaDevices.getUserMedia({
          video: {
            width: { ideal: 640 },
            height: { ideal: 480 },
            facingMode: 'user'
          }
        });

        addLog('Stream obtido com sucesso');

        // Configurar o vídeo
        if (video.value) {
          video.value.srcObject = stream.value;
          
          video.value.onloadedmetadata = () => {
            const width = video.value.videoWidth;
            const height = video.value.videoHeight;
            addLog(`Vídeo carregado: ${width}x${height}`);
            
            video.value.play().then(() => {
              addLog('Vídeo iniciado com sucesso');
              cameraLoading.value = false;
              cameraStatus.value = 'Câmera ativa';
              isVideoReady.value = true;
            }).catch(error => {
              addLog(`Erro ao iniciar vídeo: ${error.message}`);
              cameraError.value = error.message;
              cameraLoading.value = false;
            });
          };

          video.value.onerror = (error) => {
            addLog(`Erro no vídeo: ${error}`);
            cameraError.value = 'Erro no elemento de vídeo';
            cameraLoading.value = false;
          };
        } else {
          throw new Error('Elemento de vídeo não encontrado');
        }

      } catch (error) {
        addLog(`Erro ao iniciar câmera: ${error.message}`);
        cameraLoading.value = false;
        cameraError.value = error.message;
        cameraStatus.value = 'Erro';
      }
    };

    const stopCamera = () => {
      addLog('Parando câmera...');
      if (stream.value) {
        stream.value.getTracks().forEach(track => {
          addLog(`Parando track: ${track.kind}`);
          track.stop();
        });
        stream.value = null;
      }
      cameraLoading.value = false;
      cameraError.value = '';
      cameraStatus.value = 'Parada';
      isVideoReady.value = false;
    };

    const capturePhoto = () => {
      try {
        addLog('Capturando foto...');
        
        if (!video.value) {
          throw new Error('Elemento de vídeo não encontrado');
        }

        if (video.value.videoWidth === 0 || video.value.videoHeight === 0) {
          throw new Error('Vídeo não está pronto');
        }

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        canvas.width = video.value.videoWidth;
        canvas.height = video.value.videoHeight;

        addLog(`Canvas configurado: ${canvas.width}x${canvas.height}`);

        ctx.drawImage(video.value, 0, 0);
        capturedPhoto.value = canvas.toDataURL('image/jpeg', 0.8);

        addLog(`Foto capturada: ${canvas.width}x${canvas.height}, tamanho: ${capturedPhoto.value.length} chars`);

      } catch (error) {
        addLog(`Erro ao capturar foto: ${error.message}`);
        cameraError.value = error.message;
      }
    };

    const testManualCamera = () => {
      addLog('Executando teste manual no console...');
      
      // Testar se a API está disponível
      console.log('=== TESTE MANUAL DA CÂMERA ===');
      console.log('MediaDevices:', !!navigator.mediaDevices);
      console.log('getUserMedia:', !!navigator.mediaDevices?.getUserMedia);

      // Testar acesso à câmera
      navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
          console.log('✓ Câmera funcionando!', stream);
          addLog('Teste manual: Câmera funcionando');
          stream.getTracks().forEach(track => {
            console.log('Track:', track.kind, track.label);
            track.stop();
          });
        })
        .catch(error => {
          console.error('✗ Erro na câmera:', error);
          addLog(`Teste manual: Erro - ${error.message}`);
        });
    };

    return {
      video,
      stream,
      cameraLoading,
      cameraError,
      cameraStatus,
      capturedPhoto,
      logs,
      isVideoReady,
      startCamera,
      stopCamera,
      capturePhoto,
      clearLogs,
      testManualCamera
    };
  }
};
</script> 