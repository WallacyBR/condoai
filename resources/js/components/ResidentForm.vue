<template>
  <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
    <h3 class="text-lg font-semibold text-white mb-4">
      {{ isEditing ? 'Editar Morador' : 'Novo Morador' }}
    </h3>
    
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Nome -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
          Nome *
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="Nome completo do morador"
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-400">{{ errors.name }}</p>
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
          Email *
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="email@exemplo.com"
        />
        <p v-if="errors.email" class="mt-1 text-sm text-red-400">{{ errors.email }}</p>
      </div>


      <!-- Tipo de Pessoa -->
      <div>
        <label for="person_type" class="block text-sm font-medium text-gray-300 mb-2">
          Tipo de Pessoa *
        </label>
        <select
          id="person_type"
          v-model="form.person_type"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        >
          <option value="fisica">Física</option>
          <option value="juridica">Jurídica</option>
        </select>
        <p v-if="errors.person_type" class="mt-1 text-sm text-red-400">{{ errors.person_type }}</p>
      </div>

      <!-- CPF -->
      <div>
        <label for="cpf" class="block text-sm font-medium text-gray-300 mb-2">
          CPF
        </label>
        <input
          id="cpf"
          v-model="form.cpf"
          type="text"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="000.000.000-00"
        />
        <p v-if="errors.cpf" class="mt-1 text-sm text-red-400">{{ errors.cpf }}</p>
      </div>

      <!-- Foto -->
      <div>
        <label for="photo" class="block text-sm font-medium text-gray-300 mb-2">Foto</label>
        <input id="photo" type="file" @change="handlePhoto" class="w-full text-gray-300" />
        <p v-if="errors.photo" class="mt-1 text-sm text-red-400">{{ errors.photo }}</p>
      </div>

      <!-- Telefone Celular -->
      <div>
        <label for="cell_phone" class="block text-sm font-medium text-gray-300 mb-2">
          Telefone Celular
        </label>
        <input
          id="cell_phone"
          v-model="form.cell_phone"
          type="tel"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="(11) 99999-9999"
        />
        <p v-if="errors.cell_phone" class="mt-1 text-sm text-red-400">{{ errors.cell_phone }}</p>
      </div>

      <!-- Telefone Comercial -->
      <div>
        <label for="commercial_phone" class="block text-sm font-medium text-gray-300 mb-2">
          Telefone Comercial
        </label>
        <input
          id="commercial_phone"
          v-model="form.commercial_phone"
          type="tel"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="(11) 3444-5555"
        />
        <p v-if="errors.commercial_phone" class="mt-1 text-sm text-red-400">{{ errors.commercial_phone }}</p>
      </div>

      <!-- Whatsapp -->
      <div>
        <label for="whatsapp" class="block text-sm font-medium text-gray-300 mb-2">Whatsapp</label>
        <input
          id="whatsapp"
          v-model="form.whatsapp"
          type="tel"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="(11) 99999-9999"
        />
        <p v-if="errors.whatsapp" class="mt-1 text-sm text-red-400">{{ errors.whatsapp }}</p>
      </div>

      <!-- Observações -->
      <div>
        <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">Observações</label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="3"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        ></textarea>
        <p v-if="errors.notes" class="mt-1 text-sm text-red-400">{{ errors.notes }}</p>
      </div>

      <!-- Funções -->
      <div class="flex space-x-4">
        <label class="inline-flex items-center text-gray-300">
          <input type="checkbox" v-model="form.is_porter" class="h-4 w-4 text-indigo-600 rounded bg-gray-800 border-gray-600" />
          <span class="ml-2">Porteiro</span>
        </label>
        <label class="inline-flex items-center text-gray-300">
          <input type="checkbox" v-model="form.is_janitor" class="h-4 w-4 text-indigo-600 rounded bg-gray-800 border-gray-600" />
          <span class="ml-2">Zelador</span>
        </label>
        <label class="inline-flex items-center text-gray-300">
          <input type="checkbox" v-model="form.is_cleaning" class="h-4 w-4 text-indigo-600 rounded bg-gray-800 border-gray-600" />
          <span class="ml-2">Limpeza</span>
        </label>
      </div>

      <!-- Unidade -->
      <div>
        <label for="apartment_id" class="block text-sm font-medium text-gray-300 mb-2">
          Unidade
          <span v-if="form.condominium_id && apartments.length > 0" class="text-xs text-green-400">
            ({{ apartments.length }} unidades disponíveis)
          </span>
          <span v-else-if="form.condominium_id && !loadingApartments" class="text-xs text-orange-400">
            (nenhuma unidade encontrada)
          </span>
          <span v-else-if="form.condominium_id" class="text-xs text-blue-400">
            (carregando...)
          </span>
        </label>
        
        <!-- Select de unidades quando condomínio está selecionado -->
        <select
          v-if="form.condominium_id && apartments.length > 0"
          id="apartment_id"
          v-model="form.apartment_id"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        >
          <option value="">Selecione uma unidade</option>
          <option 
            v-for="apartment in apartments" 
            :key="apartment.id" 
            :value="apartment.id"
            class="bg-gray-800"
          >
            {{ formatApartmentDescription(apartment) }}
          </option>
        </select>
        
        <!-- Input numérico como fallback -->
        <input
          v-else
          id="apartment_id"
          v-model="form.apartment_id"
          type="number"
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
          placeholder="Digite o ID da unidade"
        />
        
        <!-- Mensagens informativas -->
        <div v-if="form.condominium_id && apartments.length === 0 && !loadingApartments" class="mt-1 text-xs text-orange-400">
          ⚠️ Nenhuma unidade encontrada neste condomínio. Verifique se existem unidades cadastradas.
        </div>
        <div v-if="!form.condominium_id" class="mt-1 text-xs text-gray-500">
          ℹ️ Selecione um condomínio primeiro para carregar as unidades disponíveis.
        </div>
        
        <p v-if="errors.apartment_id" class="mt-1 text-sm text-red-400">{{ errors.apartment_id }}</p>
      </div>

      <!-- Unidades do Proprietário (visível apenas para proprietários) -->
      <div v-if="form.classification === 'proprietario'">
        <label for="owned_apartments" class="block text-sm font-medium text-gray-300 mb-2">
          Unidades Possuídas
        </label>
        <VueMultiselect
          id="owned_apartments"
          v-model="form.owned_apartment_ids"
          :options="apartments"
          :multiple="true"
          :close-on-select="false"
          :clear-on-select="false"
          :preserve-search="true"
          placeholder="Selecione as unidades"
          label="number"
          track-by="id"
          :preselect-first="false"
        >
          <template #option="{ option }">
            <span>{{ formatApartmentDescription(option) }}</span>
          </template>
        </VueMultiselect>
        <p class="mt-1 text-xs text-gray-500">
          Selecione todas as unidades que este morador possui.
        </p>

        <!-- Cards das unidades possuídas -->
        <div v-if="form.owned_apartment_ids && form.owned_apartment_ids.length > 0" class="mt-4 space-y-3">
            <h4 class="text-sm font-medium text-gray-400">Unidades Selecionadas:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div 
                v-for="apartment in form.owned_apartment_ids" 
                :key="apartment.id" 
                class="bg-gray-800/50 border border-gray-700 rounded-lg p-3 flex items-center justify-between"
              >
                <span class="text-sm text-white">{{ formatApartmentDescription(apartment) }}</span>
                <button 
                  @click="removeOwnedApartment(apartment)"
                  type="button"
                  class="p-1 rounded-full text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition-colors"
                  title="Desvincular unidade"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
        </div>
      </div>

      <!-- Classificação -->
      <div>
        <label for="classification" class="block text-sm font-medium text-gray-300 mb-2">
          Classificação *
        </label>
        <select
          id="classification"
          v-model="form.classification"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        >
          <option value="">Selecione uma classificação</option>
          <option value="proprietario">Proprietário</option>
          <option value="morador">Morador</option>
          <option value="inquilino">Inquilino</option>
        </select>
        <p class="mt-1 text-xs text-gray-500">
          <strong>Proprietário:</strong> Proprietário do imóvel (pode ser morador ou não)<br>
          <strong>Morador:</strong> Pessoa que reside no imóvel<br>
          <strong>Inquilino:</strong> Pessoa que aluga o imóvel
        </p>
        <p v-if="errors.classification" class="mt-1 text-sm text-red-400">{{ errors.classification }}</p>
      </div>

      <!-- Status Ativo -->
      <div class="flex items-center">
        <input
          id="active"
          v-model="form.active"
          type="checkbox"
          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
        />
        <label for="active" class="ml-2 block text-sm text-gray-300">
          Morador ativo
        </label>
      </div>

      <!-- Condomínio -->
      <div v-if="condominiums.length > 0">
        <label for="condominium_id" class="block text-sm font-medium text-gray-300 mb-2">
          Condomínio *
        </label>
        <select
          id="condominium_id"
          v-model="form.condominium_id"
          required
          class="w-full px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
        >
          <option value="">Selecione um condomínio</option>
          <option 
            v-for="condominium in condominiums" 
            :key="condominium.id" 
            :value="condominium.id"
          >
            {{ condominium.name }}
          </option>
        </select>
        <p v-if="errors.condominium_id" class="mt-1 text-sm text-red-400">{{ errors.condominium_id }}</p>
      </div>

      <!-- Botões -->
      <div class="flex space-x-3 pt-4">
        <NeonButton 
          type="submit" 
          :loading="loading"
          class="flex-1"
        >
          {{ isEditing ? 'Atualizar' : 'Criar' }} Morador
        </NeonButton>
        <NeonButton 
          type="button" 
          @click="$emit('cancel')"
          variant="secondary"
          class="flex-1"
        >
          Cancelar
        </NeonButton>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import NeonButton from './NeonButton.vue';
import { formatApartmentDescription } from '../utils/formatters.js';
import { useRouter } from 'vue-router';
import VueMultiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import api from '@/utils/api';

export default {
  name: 'ResidentForm',
  components: { NeonButton, VueMultiselect },
  props: {
    resident: {
      type: Object,
      default: null
    },
    condominiums: {
      type: Array,
      default: () => []
    }
  },
  emits: ['submit', 'cancel'],
  setup(props, { emit }) {
    const loading = ref(false);
    const errors = ref({});
    const apartments = ref([]);
    const loadingApartments = ref(false);
    const router = useRouter();

    const form = reactive({
      name: '',
      email: '',
      person_type: 'fisica',
      cpf: '',
      photo: '',
      cell_phone: '',
      commercial_phone: '',
      whatsapp: '',
      notes: '',
      is_porter: false,
      is_janitor: false,
      is_cleaning: false,
      apartment_id: '',
      classification: '',
      active: true,
      condominium_id: '',
      owned_apartment_ids: [],
    });

    const isEditing = computed(() => !!props.resident);

    // Função para carregar apartamentos do condomínio
    const loadApartments = async (condominiumId) => {
      if (!condominiumId) {
        apartments.value = [];
        return;
      }

      try {
        loadingApartments.value = true;
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/apartments?condominium_id=${condominiumId}`, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          apartments.value = await response.json();
        } else {
          console.error('Erro ao carregar unidades:', response.statusText);
          apartments.value = [];
        }
      } catch (error) {
        console.error('Erro ao carregar unidades:', error);
        apartments.value = [];
      } finally {
        loadingApartments.value = false;
      }
    };

    // Watcher para carregar apartamentos quando condomínio muda
    watch(() => form.condominium_id, (newCondominiumId) => {
      if (newCondominiumId) {
        loadApartments(newCondominiumId);
      } else {
        apartments.value = [];
        form.apartment_id = '';
      }
    });

    // Preencher formulário quando editar
    const fillFormForEditing = (resident) => {
        if (resident) {
            form.name = resident.name || '';
            form.email = resident.email || '';
            form.person_type = resident.person_type || 'fisica';
            form.cpf = resident.cpf || '';
            form.photo = resident.photo || '';
            form.cell_phone = resident.cell_phone || '';
            form.commercial_phone = resident.commercial_phone || '';
            form.whatsapp = resident.whatsapp || '';
            form.notes = resident.notes || '';
            form.is_porter = resident.is_porter || false;
            form.is_janitor = resident.is_janitor || false;
            form.is_cleaning = resident.is_cleaning || false;
            form.apartment_id = resident.apartment_id || '';
            form.classification = resident.raw_classification || resident.classification || '';
            form.active = resident.active !== undefined ? resident.active : true;
            form.condominium_id = resident.condominium_id || '';
            
            if (resident.owned_apartments && Array.isArray(resident.owned_apartments)) {
                form.owned_apartment_ids = apartments.value.filter(apt => 
                    resident.owned_apartments.some(ownedApt => ownedApt.id === apt.id)
                );
            } else {
                form.owned_apartment_ids = [];
            }
        }
    };

    // Watcher para preencher o formulário quando o morador e os apartamentos estiverem prontos
    watch([() => props.resident, apartments], ([newResident, newApartments]) => {
      if (isEditing.value && newResident && newApartments.length > 0) {
        newResident.classification = newResident.raw_classification || newResident.classification;
        fillFormForEditing(newResident);
      }
    }, { immediate: true, deep: true });

    const removeOwnedApartment = (apartmentToRemove) => {
        form.owned_apartment_ids = form.owned_apartment_ids.filter(
            apartment => apartment.id !== apartmentToRemove.id
        );
    };

    const handlePhoto = (event) => {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => {
        form.photo = reader.result;
      };
      reader.readAsDataURL(file);
    };

    const handleSubmit = async () => {
      loading.value = true;
      errors.value = {};

      try {
        const token = localStorage.getItem('token');
        const url = isEditing.value 
          ? `/api/residents/${props.resident.id}`
          : '/api/residents';
        
        const method = isEditing.value ? 'PUT' : 'POST';

        const payload = { ...form };

        if (payload.classification === 'proprietario' && Array.isArray(payload.owned_apartment_ids)) {
          payload.owned_apartment_ids = payload.owned_apartment_ids.map(apartment => apartment.id);
        } else {
          delete payload.owned_apartment_ids;
        }
        
        const response = await fetch(url, {
          method,
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
          if (data.errors) {
            errors.value = data.errors;
          } else {
            errors.value = { general: data.message || 'Erro ao salvar morador' };
          }
          return;
        }

        emit('submit', data);
      } catch (error) {
        console.error('Erro ao salvar morador:', error);
        errors.value = { general: 'Erro de conexão. Tente novamente.' };
      } finally {
        loading.value = false;
      }
    };

    onMounted(async () => {
        await loadCondominiums();
        if (isEditing.value && props.resident) {
            form.condominium_id = props.resident.condominium_id;
            if (form.condominium_id) {
                await loadApartments(form.condominium_id);
            }
            // A chamada para fillFormForEditing será feita pelo watcher agora
        } else if (localStorage.getItem('selectedCondominium')) {
            form.condominium_id = localStorage.getItem('selectedCondominium');
        }
    });

    return {
      form,
      loading,
      errors,
      isEditing,
      handleSubmit,
      handlePhoto,
      formatApartmentDescription,
      apartments,
      loadingApartments,
      removeOwnedApartment,
    };
  }
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>
<style>
/* Custom styles for Vue-Multiselect to match the dark theme */
.multiselect__tags {
  @apply bg-gray-800 border-gray-700 rounded-lg text-white placeholder-gray-500;
}
.multiselect__input {
  @apply bg-gray-800 text-white placeholder-gray-500;
}
.multiselect__content-wrapper {
  @apply bg-gray-800 border-gray-700;
}
.multiselect__option--highlight {
  @apply bg-indigo-500;
}
.multiselect__option--selected {
  @apply bg-indigo-600;
}
.multiselect__tag {
  @apply bg-indigo-500;
}
.multiselect__tag-icon:hover {
  @apply bg-indigo-700;
}
</style> 