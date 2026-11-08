<template>
  <AuthenticatedLayout page-title="Teste FileUpload">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-6">Teste do Componente FileUpload</h2>
        
        <div class="space-y-6">
          <!-- Teste com imagens -->
          <div>
            <h3 class="text-lg font-medium text-white mb-3">Upload de Imagens (com câmera)</h3>
            <FileUpload
              v-model="imageFiles"
              :multiple="true"
              :max-size="5"
              accept="image/*,.jpg,.jpeg,.png"
              :show-camera="true"
            />
            <div class="mt-2 text-sm text-gray-400">
              Arquivos selecionados: {{ imageFiles.length }}
            </div>
          </div>

          <!-- Teste sem câmera -->
          <div>
            <h3 class="text-lg font-medium text-white mb-3">Upload de Documentos (sem câmera)</h3>
            <FileUpload
              v-model="documentFiles"
              :multiple="true"
              :max-size="10"
              accept=".pdf,.doc,.docx,.txt"
              :show-camera="false"
            />
            <div class="mt-2 text-sm text-gray-400">
              Arquivos selecionados: {{ documentFiles.length }}
            </div>
          </div>

          <!-- Teste qualquer arquivo -->
          <div>
            <h3 class="text-lg font-medium text-white mb-3">Upload de Qualquer Arquivo</h3>
            <FileUpload
              v-model="anyFiles"
              :multiple="true"
              :max-size="20"
              accept="*"
              :show-camera="true"
            />
            <div class="mt-2 text-sm text-gray-400">
              Arquivos selecionados: {{ anyFiles.length }}
            </div>
          </div>

          <!-- Informações de debug -->
          <div class="bg-gray-700/30 rounded-lg p-4">
            <h3 class="text-lg font-medium text-white mb-3">Informações de Debug</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="text-gray-300">Navegador suporta câmera:</span>
                <span :class="supportsCamera ? 'text-green-400' : 'text-red-400'">
                  {{ supportsCamera ? 'Sim' : 'Não' }}
                </span>
              </div>
              <div>
                <span class="text-gray-300">HTTPS:</span>
                <span :class="isHttps ? 'text-green-400' : 'text-red-400'">
                  {{ isHttps ? 'Sim' : 'Não' }}
                </span>
              </div>
              <div>
                <span class="text-gray-300">User Agent:</span>
                <span class="text-cyan-400">{{ userAgent }}</span>
              </div>
              
              <!-- Botão de teste da câmera -->
              <div class="mt-4 pt-4 border-t border-gray-600">
                <NeonButton
                  @click="testCamera"
                  size="sm"
                  variant="info"
                  class="flex items-center"
                >
                  <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Testar Câmera Diretamente
                </NeonButton>
                <div v-if="cameraTestResult" class="mt-2 text-xs" :class="cameraTestResult.success ? 'text-green-400' : 'text-red-400'">
                  {{ cameraTestResult.message }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import FileUpload from '@/components/FileUpload.vue';
import { ref, computed, onMounted } from 'vue';
import NeonButton from '@/components/NeonButton.vue';

const imageFiles = ref([]);
const documentFiles = ref([]);
const anyFiles = ref([]);

const supportsCamera = computed(() => {
  return 'mediaDevices' in navigator && 'getUserMedia' in navigator.mediaDevices;
});

const isHttps = computed(() => {
  return window.location.protocol === 'https:';
});

const userAgent = computed(() => {
  return navigator.userAgent.substring(0, 50) + '...';
});

const cameraTestResult = ref(null);

const testCamera = async () => {
  if (!supportsCamera.value) {
    cameraTestResult.value = {
      success: false,
      message: 'Seu navegador não suporta câmera.'
    };
    return;
  }

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    stream.getTracks().forEach(track => track.stop());
    cameraTestResult.value = {
      success: true,
      message: 'Câmera testada com sucesso!'
    };
  } catch (error) {
    cameraTestResult.value = {
      success: false,
      message: 'Erro ao testar câmera: ' + error.message
    };
  }
};

onMounted(() => {
  console.log('FileUpload Test Page mounted');
  console.log('Supports camera:', supportsCamera.value);
  console.log('Is HTTPS:', isHttps.value);
});
</script> 