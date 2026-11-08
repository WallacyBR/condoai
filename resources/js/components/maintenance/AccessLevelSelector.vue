<template>
  <div class="access-level-selector space-y-4 p-4 bg-gray-700/30 rounded-lg border border-gray-600/40">
    <p class="text-sm font-medium text-gray-300">Quem pode visualizar o relatório do QR Code?</p>

    <div class="space-y-3">
      <!-- Opção Público -->
      <div
        @click="selectLevel('public')"
        :class="['radio-option', localCurrentLevel === 'public' ? 'radio-option-selected' : '']"
      >
        <input
          type="radio"
          id="access-public"
          name="qr_access_level"
          value="public"
          v-model="localCurrentLevel"
          class="radio-input"
        />
        <label for="access-public" class="radio-label">
          <span class="radio-title">Público</span>
          <span class="radio-description">Qualquer pessoa com o QR Code pode ver (mesmo sem login).</span>
        </label>
      </div>

      <!-- Opção Usuários Internos -->
      <div
        @click="selectLevel('internal_users')"
        :class="['radio-option', localCurrentLevel === 'internal_users' ? 'radio-option-selected' : '']"
      >
        <input
          type="radio"
          id="access-internal"
          name="qr_access_level"
          value="internal_users"
          v-model="localCurrentLevel"
          class="radio-input"
        />
        <label for="access-internal" class="radio-label">
          <span class="radio-title">Usuários do Sistema</span>
          <span class="radio-description">Apenas usuários autenticados do condomínio podem ver.</span>
        </label>
      </div>

      <!-- Opção Usuários Específicos -->
      <div
        @click="selectLevel('specific_users')"
        :class="['radio-option', localCurrentLevel === 'specific_users' ? 'radio-option-selected' : '']"
      >
        <input
          type="radio"
          id="access-specific"
          name="qr_access_level"
          value="specific_users"
          v-model="localCurrentLevel"
          class="radio-input"
        />
        <label for="access-specific" class="radio-label">
          <span class="radio-title">Usuários Específicos</span>
          <span class="radio-description">Apenas usuários selecionados podem ver.</span>
        </label>
      </div>
    </div>

    <!-- Seletor de Múltiplos Usuários (aparece se 'specific_users' estiver selecionado) -->
    <div v-if="localCurrentLevel === 'specific_users'" class="mt-4 transition-all duration-300 ease-in-out">
      <label for="specific-users-select" class="block text-sm font-medium text-gray-300 mb-1">
        Selecionar usuários permitidos:
      </label>
      <!--
        Aqui entraria um componente MultiSelectField, como o mencionado no DESIGN_MANUAL.md.
        Por enquanto, um placeholder.
        Exemplo: <MultiSelectField v-model="selectedUserIds" :options="availableUsers" ... />
      -->
      <div class="p-3 bg-gray-800 rounded-md border border-gray-600">
        <p class="text-gray-400 text-sm">
          (Placeholder para MultiSelectField: Listar usuários do condomínio aqui. IDs selecionados: {{ selectedUserIds }})
        </p>
        <select multiple v-model="selectedUserIds" class="form-input mt-2 h-32">
          <option v-for="user in mockAvailableUsers" :key="user.id" :value="user.id">{{ user.name }}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  currentLevel: {
    type: String,
    default: 'internal_users', // 'public', 'internal_users', 'specific_users'
  },
  equipmentId: { // Necessário para buscar usuários e salvar permissões
    type: [Number, String],
    required: false // Pode não ser necessário se o componente pai gerenciar a API call
  },
  initialSelectedUsers: { // IDs dos usuários já permitidos
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:level', 'update:selectedUsers']);

const localCurrentLevel = ref(props.currentLevel);
const selectedUserIds = ref([...props.initialSelectedUsers]);

// Mock de usuários disponíveis (em um app real, viria da API)
const mockAvailableUsers = ref([
  { id: 1, name: 'Síndico Fulano' },
  { id: 2, name: 'Zelador Beltrano' },
  { id: 3, name: 'Conselheiro Ciclano' },
  { id: 10, name: 'Morador VIP' }
]);

watch(localCurrentLevel, (newLevel) => {
  emit('update:level', newLevel);
  if (newLevel !== 'specific_users') {
    selectedUserIds.value = []; // Limpa usuários selecionados se não for específico
  }
  // Emitir usuários selecionados apenas se o nível for 'specific_users'
  emit('update:selectedUsers', newLevel === 'specific_users' ? selectedUserIds.value : []);
});

watch(selectedUserIds, (newUsers) => {
  if (localCurrentLevel.value === 'specific_users') {
    emit('update:selectedUsers', newUsers);
  }
});

const selectLevel = (level) => {
  localCurrentLevel.value = level;
};

onMounted(() => {
  // Se o nível inicial for 'specific_users', carregar os usuários permitidos
  // e os usuários disponíveis para seleção.
  // Ex: if (props.currentLevel === 'specific_users' && props.equipmentId) { fetchPermittedUsers(); fetchAvailableUsers(); }
});

// Estilos de formulário base (serão definidos globalmente ou via classes Tailwind)
const formInputClasses = 'mt-1 block w-full px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-md text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 sm:text-sm';

</script>

<style scoped>
/* Estilos baseados no DESIGN_MANUAL.md para Radio Buttons Explicativos */
.radio-option {
  @apply flex items-start p-3 bg-gray-800/50 rounded-lg border border-gray-700 hover:border-indigo-500/50 transition-colors cursor-pointer;
}
.radio-option-selected {
  @apply border-indigo-500/70 ring-2 ring-indigo-500/50 bg-indigo-900/30;
}
.radio-input {
  @apply h-5 w-5 mt-0.5 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800 shrink-0;
}
.radio-label {
  @apply ml-3 text-gray-300 cursor-pointer w-full;
}
.radio-title {
  @apply font-medium block;
}
.radio-description {
  @apply block text-xs text-gray-400 mt-0.5;
}
.form-input { /* Para o select de exemplo */
  @apply mt-1 block w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors;
}
</style>
