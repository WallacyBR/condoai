<template>
  <AuthenticatedLayout page-title="Equipamentos de Manutenção">
    <div class="p-4 sm:p-6 lg:p-8">
      <div class="bg-gray-800/50 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-cyan-500/30">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-white">Gerenciar Equipamentos</h2>
           <!-- Botão Novo - Usar NeonButton -->
          <button class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg shadow-md transition-colors">
            Novo Equipamento
          </button>
        </div>
        <p class="text-gray-300">
          Listagem e formulários para Equipamentos, incluindo QR Code e acesso.
        </p>

        <!-- Exemplo de exibição de QR Code -->
        <div class="mt-8 p-6 bg-gray-700/50 rounded-lg border border-gray-600/70" v-if="exampleEquipment.id">
          <h3 class="text-lg font-semibold text-cyan-400 mb-3">QR Code para: {{ exampleEquipment.name }}</h3>
          <QrCodeDisplay
            :value="exampleEquipment.qr_code_hash"
            :size="200"
            :filename-prefix="`qrcode_${exampleEquipment.name.replace(/\s+/g, '_')}`"
          />
          <div class="mt-4">
            <h4 class="text-md font-medium text-gray-200 mb-2">Configurar Acesso ao Relatório QR Code:</h4>
            <AccessLevelSelector
              :current-level="exampleEquipment.qr_code_access_level"
              @update:level="updateAccessLevel"
            />
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import QrCodeDisplay from '@/components/QrCodeDisplay.vue'; // Importar o componente
import AccessLevelSelector from '@/components/maintenance/AccessLevelSelector.vue'; // Será criado depois
import { ref } from 'vue';

// Lógica para CRUD de Equipamentos será adicionada aqui
// Exemplo de dados de equipamento
const exampleEquipment = ref({
  id: 1,
  name: "Bomba Principal Piscina",
  qr_code_hash: "abcdef1234567890abcdef1234567890", // Exemplo de hash
  qr_code_access_level: "internal_users" // 'public', 'internal_users', 'specific_users'
});

const updateAccessLevel = (newLevel) => {
  if (exampleEquipment.value) {
    exampleEquipment.value.qr_code_access_level = newLevel;
    // Aqui faria a chamada API para salvar a alteração no backend
    console.log(`Nível de acesso para ${exampleEquipment.value.name} atualizado para: ${newLevel}`);
  }
};

</script>
