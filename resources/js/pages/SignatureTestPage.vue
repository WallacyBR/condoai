<template>
  <AuthenticatedLayout page-title="Teste de Assinatura">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-6">Teste do Componente SignaturePad</h2>
        
        <div class="space-y-6">
          <!-- Configurações -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Cor da Caneta:</label>
              <input 
                type="color" 
                v-model="penColor" 
                class="form-input h-12 w-full"
              >
            </div>
            <div>
              <label class="form-label">Tamanho do Canvas:</label>
              <select v-model="canvasSize" class="form-input">
                <option value="small">Pequeno (300x150)</option>
                <option value="medium">Médio (400x200)</option>
                <option value="large">Grande (500x250)</option>
              </select>
            </div>
          </div>

          <!-- SignaturePad -->
          <div>
            <label class="form-label">Assinatura:</label>
            <SignaturePad 
              ref="signaturePadRef"
              v-model="signature"
              :width="canvasDimensions.width"
              :height="canvasDimensions.height"
              :pen-color="penColor"
              @change="onSignatureChange"
              class="mt-1"
            />
          </div>

          <!-- Controles -->
          <div class="flex flex-wrap gap-2">
            <button 
              @click="clearSignature" 
              class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors"
            >
              Limpar Assinatura
            </button>
            <button 
              @click="undoSignature" 
              :disabled="!canUndo"
              class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              Desfazer
            </button>
            <button 
              @click="saveSignature" 
              :disabled="!signature"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              Salvar Assinatura
            </button>
            <button 
              @click="loadSignature" 
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
            >
              Carregar Assinatura
            </button>
          </div>

          <!-- Informações -->
          <div class="bg-gray-700/30 rounded-lg p-4">
            <h3 class="text-lg font-medium text-white mb-3">Informações da Assinatura</h3>
            <div class="space-y-2 text-sm">
              <div>
                <span class="text-gray-300">Está vazia:</span>
                <span :class="signatureInfo.isEmpty ? 'text-red-400' : 'text-green-400'">
                  {{ signatureInfo.isEmpty ? 'Sim' : 'Não' }}
                </span>
              </div>
              <div>
                <span class="text-gray-300">Tamanho do data URL:</span>
                <span class="text-cyan-400">{{ signatureInfo.dataUrlLength }} caracteres</span>
              </div>
              <div v-if="signature">
                <span class="text-gray-300">Preview:</span>
                <img 
                  :src="signature" 
                  alt="Preview da assinatura" 
                  class="mt-2 max-w-xs border border-gray-600 rounded"
                >
              </div>
            </div>
          </div>

          <!-- Console de Log -->
          <div class="bg-gray-900/50 rounded-lg p-4">
            <h3 class="text-lg font-medium text-white mb-3">Log de Eventos</h3>
            <div class="bg-gray-800 rounded p-3 h-32 overflow-y-auto">
              <div v-for="(log, index) in logs" :key="index" class="text-xs text-gray-300 mb-1">
                <span class="text-cyan-400">{{ log.timestamp }}</span>: {{ log.message }}
              </div>
            </div>
            <button 
              @click="clearLogs" 
              class="mt-2 text-xs text-gray-400 hover:text-gray-300"
            >
              Limpar Logs
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import SignaturePad from '@/components/SignaturePad.vue';
import { ref, reactive, computed } from 'vue';

const signaturePadRef = ref(null);
const signature = ref(null);
const penColor = ref('#00d4ff');
const canvasSize = ref('medium');
const canUndo = ref(false);
const logs = ref([]);

const signatureInfo = computed(() => ({
  isEmpty: !signature.value,
  dataUrlLength: signature.value ? signature.value.length : 0
}));

const canvasDimensions = computed(() => {
  const sizes = {
    small: { width: 300, height: 150 },
    medium: { width: 400, height: 200 },
    large: { width: 500, height: 250 }
  };
  return sizes[canvasSize.value];
});

const addLog = (message) => {
  const timestamp = new Date().toLocaleTimeString();
  logs.value.unshift({ timestamp, message });
  if (logs.value.length > 50) {
    logs.value = logs.value.slice(0, 50);
  }
};

const onSignatureChange = (changeData) => {
  canUndo.value = !changeData.isEmpty;
  addLog(`Assinatura mudou - Vazia: ${changeData.isEmpty}`);
};

const clearSignature = () => {
  signaturePadRef.value?.clear();
  addLog('Assinatura limpa');
};

const undoSignature = () => {
  signaturePadRef.value?.undo();
  addLog('Última ação desfeita');
};

const saveSignature = () => {
  if (signature.value) {
    addLog('Assinatura salva com sucesso');
    console.log('Assinatura salva:', signature.value);
  }
};

const loadSignature = () => {
  // Simular carregamento de uma assinatura existente
  const sampleSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
  signaturePadRef.value?.fromData([]);
  addLog('Assinatura de exemplo carregada');
};

const clearLogs = () => {
  logs.value = [];
};

// Log inicial
addLog('Página de teste carregada');
</script>

<style scoped>
.form-label {
  @apply block text-sm font-medium text-gray-300 mb-1;
}

.form-input {
  @apply block w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600/80 rounded-lg text-white placeholder-gray-400
  focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors shadow-sm;
}
</style> 