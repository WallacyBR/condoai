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
              accept=".jpg,.jpeg,.png"
              class="mt-1"
            />
            <!-- Opcional: Adicionar WebcamCapture aqui -->
            <!-- <WebcamCapture @captured="addPhotoFromWebcam" class="mt-2"/> -->
          </div>

          <div>
            <label class="form-label">Assinatura do Técnico:</label>
            <SignaturePad ref="signaturePadRef" class="mt-1 border border-gray-600 rounded-md h-48 bg-gray-700/20" />
            <button type="button" @click="clearSignature" class="mt-2 text-xs text-cyan-400 hover:text-cyan-300">Limpar Assinatura</button>
          </div>

          <div class="flex justify-end space-x-3">
              <button type="button" @click="cancelForm" class="px-4 py-2 text-gray-300 hover:bg-gray-700 rounded-lg transition-colors">Cancelar</button>
              <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors disabled:opacity-50">
                {{ isSubmitting ? 'Salvando...' : 'Salvar Registro' }}
              </button>
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
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/utils/api'; // Supondo que api.js lida com base URL e token

const router = useRouter();
const signaturePadRef = ref(null);
const isSubmitting = ref(false);

// Simular ID do agendamento (viria de props ou rota)
const scheduleId = ref(1);

const formData = reactive({
  maintenance_schedule_id: scheduleId.value, // Ou pegar da rota/prop
  performed_at: new Date().toISOString().slice(0, 16), // Default to now
  notes: '',
  photos: [], // Array de objetos File
  signature: null, // base64 data URL
  status: 'completed' // Default status
});

const clearSignature = () => {
  if (signaturePadRef.value) {
    signaturePadRef.value.clearSignature(); // Supondo que o componente tem este método
  }
  formData.signature = null;
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

  if (signaturePadRef.value && !signaturePadRef.value.isEmpty()) { // Supondo método isEmpty
    formData.signature = signaturePadRef.value.getSignatureDataUrl(); // Supondo método getSignatureDataUrl
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
