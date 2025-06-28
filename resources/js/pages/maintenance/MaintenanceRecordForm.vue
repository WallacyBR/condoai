<template>
  <AuthenticatedLayout page-title="Registrar Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <h2 class="text-xl font-semibold text-white mb-6">Formulário de Registro de Manutenção</h2>
        <!-- Adicionar campos para selecionar Agendamento/Equipamento se necessário -->
        <form @submit.prevent="submitRecord" class="space-y-6">
          <div>
            <label for="performed_at" class="form-label">Data e Hora da Realização:</label>
            <input type="datetime-local" id="performed_at" v-model="formData.performed_at" class="form-input">
          </div>

          <div>
            <label for="notes" class="form-label">Notas da Manutenção:</label>
            <textarea id="notes" v-model="formData.notes" rows="4" class="form-input" placeholder="Descreva o trabalho realizado..."></textarea>
          </div>

          <div>
            <label class="form-label">Fotos:</label>
            <FileUpload
              v-model="formData.photos"
              :multiple="true"
              :max-size="5"
              accept="image/*,.jpg,.jpeg,.png"
              :show-camera="true"
              class="mt-1"
            />
            <!-- Opcional: Adicionar WebcamCapture aqui -->
            <!-- <WebcamCapture @captured="addPhotoFromWebcam" class="mt-2"/> -->
          </div>

          <div>
            <label class="form-label">Assinatura do Técnico:</label>
            <div class="flex justify-center">
              <div class="w-full max-w-xl">
                <SignaturePad 
                  ref="signaturePadRef" 
                  v-model="formData.signature"
                  pen-color="#fff"
                  class="mt-1"
                  @change="onSignatureChange"
                />
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
              <NeonButton 
                type="button" 
                @click="cancelForm" 
                size="md" 
                variant="secondary"
                class="flex items-center"
              >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancelar
              </NeonButton>
              <NeonButton 
                type="submit" 
                :disabled="isSubmitting" 
                size="md"
                variant="success"
                :loading="isSubmitting"
                class="flex items-center"
              >
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ isSubmitting ? 'Salvando...' : 'Salvar Registro' }}
              </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import FileUpload from '@/components/FileUpload.vue'; // Assumindo que este componente emite 'update:modelValue' com um array de Files
import SignaturePad from '@/components/SignaturePad.vue'; // Assumindo que este tem um método para pegar a dataURL e limpar
// import WebcamCapture from '@/components/WebcamCapture.vue'; // Se for usar
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/api';
import NeonButton from '@/components/NeonButton.vue';

const router = useRouter();
const route = useRoute();
const signaturePadRef = ref(null);
const isSubmitting = ref(false);
const canUndoSignature = ref(false);

const scheduleId = ref(route.params.scheduleId);

const formData = reactive({
  maintenance_schedule_id: scheduleId.value, // Ou pegar da rota/prop
  performed_at: new Date().toISOString().slice(0, 16), // Default to now
  notes: '',
  photos: [], // Array de objetos File
  signature: null, // base64 data URL
  status: 'completed' // Default status
});

const onSignatureChange = (changeData) => {
  canUndoSignature.value = !changeData.isEmpty;
};

const clearSignature = () => {
  if (signaturePadRef.value) {
    signaturePadRef.value.clear();
  }
  formData.signature = null;
  canUndoSignature.value = false;
};

const undoSignature = () => {
  if (signaturePadRef.value && canUndoSignature.value) {
    signaturePadRef.value.undo();
  }
};

// const addPhotoFromWebcam = (imageDataUrl) => {
//   // Converter dataURL para File e adicionar ao formData.photos
//   // Esta é uma simplificação, a conversão pode ser mais complexa
//   const blob = dataURLtoBlob(imageDataUrl);
//   const file = new File([blob], `webcam_${Date.now()}.png`, { type: 'image/png' });
//   formData.photos.push(file);
// };

const submitRecord = async () => {
  isSubmitting.value = true;

  // Obter a assinatura se existir
  if (signaturePadRef.value && !signaturePadRef.value.isEmpty()) {
    formData.signature = signaturePadRef.value.getSignatureDataUrl();
  } else {
    formData.signature = null;
  }

  const submissionData = new FormData();
  submissionData.append('maintenance_schedule_id', formData.maintenance_schedule_id);
  submissionData.append('performed_at', formData.performed_at.replace('T', ' ') + ':00'); // Formato YYYY-MM-DD HH:MM:SS
  submissionData.append('notes', formData.notes);
  submissionData.append('status', formData.status);

  if (formData.signature) {
    submissionData.append('signature', formData.signature);
  }

  formData.photos.forEach((photoFile, index) => {
    submissionData.append(`photos[${index}]`, photoFile);
  });

  try {
    // console.log('Enviando dados:', Object.fromEntries(submissionData.entries()));
    await api.post('/maintenance/records', submissionData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    // Idealmente, redirecionar para a página de detalhes do registro ou lista
    router.push({ name: 'maintenance.dashboard' }); // Exemplo
    // Mostrar notificação de sucesso
  } catch (error) {
    console.error('Erro ao salvar registro de manutenção:', error.response?.data || error.message);
    // Mostrar notificação de erro
  } finally {
    isSubmitting.value = false;
  }
};

const cancelForm = () => {
  router.go(-1); // Voltar para a página anterior
};

</script>

<style scoped>
/* Reutilizar estilos do Design Manual para inputs, textareas, etc. */
.form-label {
  @apply block text-sm font-medium text-gray-300 mb-1;
}
.form-input, textarea.form-input {
  @apply block w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600/80 rounded-lg text-white placeholder-gray-400
  focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors shadow-sm
  dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:placeholder-gray-500;
}
/* Ajuste para datetime-local picker icon */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    filter: invert(0.8) sepia(0.5) saturate(5) hue-rotate(170deg);
}
</style>
