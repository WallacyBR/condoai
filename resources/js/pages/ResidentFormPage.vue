<template>
  <AuthenticatedLayout :page-title="isEditing ? 'Editar Morador' : 'Novo Morador'">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">
            {{ isEditing ? 'Editar Morador' : 'Novo Morador' }}
          </h1>
          <p class="text-gray-400 mt-1">
            {{ isEditing ? 'Atualize as informações do morador' : 'Cadastre um novo morador no sistema' }}
          </p>
        </div>
        <NeonButton @click="goBack" variant="secondary" size="md">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar
        </NeonButton>
      </div>

      <!-- Formulário -->
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-8 border-2 border-indigo-500/20">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Dados Básicos -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Dados Básicos
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nome Completo -->
              <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                  Nome Completo *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="Digite o nome completo"
                  :class="{ 'border-red-500': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-400">{{ errors.name }}</p>
              </div>

              <!-- RG -->
              <div>
                <label for="rg" class="block text-sm font-medium text-gray-300 mb-2">
                  RG
                </label>
                <input
                  id="rg"
                  v-model="form.rg"
                  type="text"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="00.000.000-0"
                  :class="{ 'border-red-500': errors.rg }"
                />
                <p v-if="errors.rg" class="mt-1 text-sm text-red-400">{{ errors.rg }}</p>
              </div>

              <!-- CPF -->
              <div>
                <label for="cpf" class="block text-sm font-medium text-gray-300 mb-2">
                  CPF *
                </label>
                <input
                  id="cpf"
                  v-model="form.cpf"
                  type="text"
                  required
                  @input="watchCPF"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="000.000.000-00"
                  :class="{ 'border-red-500': errors.cpf }"
                />
                <p v-if="errors.cpf" class="mt-1 text-sm text-red-400">{{ errors.cpf }}</p>
              </div>
            </div>
          </div>

          <!-- Contato -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Informações de Contato
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Telefone -->
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">
                  Telefone
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  @input="watchPhone"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="(11) 99999-9999"
                  :class="{ 'border-red-500': errors.phone }"
                />
                <p v-if="errors.phone" class="mt-1 text-sm text-red-400">{{ errors.phone }}</p>
              </div>

              <!-- WhatsApp -->
              <div>
                <label for="whatsapp" class="block text-sm font-medium text-gray-300 mb-2">
                  WhatsApp
                </label>
                <input
                  id="whatsapp"
                  v-model="form.whatsapp"
                  type="tel"
                  @input="watchWhatsApp"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="(11) 99999-9999"
                  :class="{ 'border-red-500': errors.whatsapp }"
                />
                <p v-if="errors.whatsapp" class="mt-1 text-sm text-red-400">{{ errors.whatsapp }}</p>
              </div>

              <!-- Email -->
              <div class="md:col-span-2">
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                  Email *
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  placeholder="email@exemplo.com"
                  :class="{ 'border-red-500': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-400">{{ errors.email }}</p>
              </div>
            </div>
          </div>

          <!-- Endereço para Boletos -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Endereço para Recebimento de Boletos
            </h3>
            
            <div class="space-y-4">
              <!-- Opção de endereço -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-3">
                  Endereço para boletos *
                </label>
                <div class="space-y-3">
                  <label class="flex items-center space-x-3 cursor-pointer">
                    <input
                      v-model="form.billing_address_type"
                      type="radio"
                      value="condominium"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                    />
                    <span class="text-gray-300">Usar endereço do condomínio</span>
                  </label>
                  <label class="flex items-center space-x-3 cursor-pointer">
                    <input
                      v-model="form.billing_address_type"
                      type="radio"
                      value="custom"
                      class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-600 bg-gray-800"
                    />
                    <span class="text-gray-300">Usar endereço personalizado</span>
                  </label>
                </div>
              </div>

              <!-- Endereço personalizado -->
              <div v-if="form.billing_address_type === 'custom'" class="space-y-4 p-4 bg-gray-800/50 rounded-lg border border-gray-700/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- CEP -->
                  <div>
                    <label for="cep" class="block text-sm font-medium text-gray-300 mb-2">
                      CEP
                    </label>
                    <input
                      id="cep"
                      v-model="form.cep"
                      type="text"
                      @input="watchCEP"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      placeholder="00000-000"
                      :class="{ 'border-red-500': errors.cep }"
                    />
                    <p v-if="errors.cep" class="mt-1 text-sm text-red-400">{{ errors.cep }}</p>
                  </div>

                  <!-- Endereço -->
                  <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-300 mb-2">
                      Endereço
                    </label>
                    <input
                      id="address"
                      v-model="form.address"
                      type="text"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      placeholder="Rua, número, complemento"
                      :class="{ 'border-red-500': errors.address }"
                    />
                    <p v-if="errors.address" class="mt-1 text-sm text-red-400">{{ errors.address }}</p>
                  </div>

                  <!-- Bairro -->
                  <div>
                    <label for="neighborhood" class="block text-sm font-medium text-gray-300 mb-2">
                      Bairro
                    </label>
                    <input
                      id="neighborhood"
                      v-model="form.neighborhood"
                      type="text"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      placeholder="Bairro"
                      :class="{ 'border-red-500': errors.neighborhood }"
                    />
                    <p v-if="errors.neighborhood" class="mt-1 text-sm text-red-400">{{ errors.neighborhood }}</p>
                  </div>

                  <!-- Cidade -->
                  <div>
                    <label for="city" class="block text-sm font-medium text-gray-300 mb-2">
                      Cidade
                    </label>
                    <input
                      id="city"
                      v-model="form.city"
                      type="text"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      placeholder="Cidade"
                      :class="{ 'border-red-500': errors.city }"
                    />
                    <p v-if="errors.city" class="mt-1 text-sm text-red-400">{{ errors.city }}</p>
                  </div>

                  <!-- Estado -->
                  <div>
                    <label for="state" class="block text-sm font-medium text-gray-300 mb-2">
                      Estado
                    </label>
                    <select
                      id="state"
                      v-model="form.state"
                      class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      :class="{ 'border-red-500': errors.state }"
                    >
                      <option value="">Selecione o estado</option>
                      <option value="AC">Acre</option>
                      <option value="AL">Alagoas</option>
                      <option value="AP">Amapá</option>
                      <option value="AM">Amazonas</option>
                      <option value="BA">Bahia</option>
                      <option value="CE">Ceará</option>
                      <option value="DF">Distrito Federal</option>
                      <option value="ES">Espírito Santo</option>
                      <option value="GO">Goiás</option>
                      <option value="MA">Maranhão</option>
                      <option value="MT">Mato Grosso</option>
                      <option value="MS">Mato Grosso do Sul</option>
                      <option value="MG">Minas Gerais</option>
                      <option value="PA">Pará</option>
                      <option value="PB">Paraíba</option>
                      <option value="PR">Paraná</option>
                      <option value="PE">Pernambuco</option>
                      <option value="PI">Piauí</option>
                      <option value="RJ">Rio de Janeiro</option>
                      <option value="RN">Rio Grande do Norte</option>
                      <option value="RS">Rio Grande do Sul</option>
                      <option value="RO">Rondônia</option>
                      <option value="RR">Roraima</option>
                      <option value="SC">Santa Catarina</option>
                      <option value="SP">São Paulo</option>
                      <option value="SE">Sergipe</option>
                      <option value="TO">Tocantins</option>
                    </select>
                    <p v-if="errors.state" class="mt-1 text-sm text-red-400">{{ errors.state }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Situação -->
          <div class="space-y-4">
            <h3 class="text-xl font-semibold text-indigo-300 border-b border-indigo-500/30 pb-2">
              Situação
            </h3>
            
            <div class="space-y-4">
              <!-- Classificação -->
              <div>
                <label for="classification" class="block text-sm font-medium text-gray-300 mb-2">
                  Classificação *
                </label>
                <select
                  id="classification"
                  v-model="form.classification"
                  required
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                  :class="{ 'border-red-500': errors.classification }"
                >
                  <option value="">Selecione uma classificação</option>
                  <option value="proprietario">Proprietário</option>
                  <option value="morador">Morador</option>
                  <option value="inquilino">Inquilino</option>
                </select>
                <div class="mt-2 space-y-1 text-sm text-gray-400">
                  <p><strong>Proprietário:</strong> Proprietário do imóvel (pode ser morador ou não)</p>
                  <p><strong>Morador:</strong> Pessoa que reside no imóvel</p>
                  <p><strong>Inquilino:</strong> Pessoa que aluga o imóvel</p>
                  <p class="text-yellow-400"><strong>Nota:</strong> Proprietário não pode ser inquilino</p>
                </div>
                <p v-if="errors.classification" class="mt-1 text-sm text-red-400">{{ errors.classification }}</p>
              </div>

              <!-- Status Ativo -->
              <div class="flex items-center space-x-3">
                <input
                  id="active"
                  v-model="form.active"
                  type="checkbox"
                  class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-600 rounded bg-gray-800"
                />
                <label for="active" class="text-gray-300">
                  Morador ativo
                </label>
              </div>
            </div>
          </div>

          <!-- Botões -->
          <div class="flex justify-end space-x-4 pt-6 border-t border-gray-700">
            <NeonButton 
              type="button" 
              @click="goBack"
              variant="secondary"
              size="lg"
            >
              Cancelar
            </NeonButton>
            <NeonButton 
              type="submit" 
              :loading="loading"
              size="lg"
            >
              <svg v-if="!loading" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isEditing ? 'Atualizar' : 'Cadastrar' }} Morador
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';

export default {
  name: 'ResidentFormPage',
  components: { AuthenticatedLayout, NeonButton },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const loading = ref(false);
    const errors = ref({});

    const form = reactive({
      name: '',
      rg: '',
      cpf: '',
      phone: '',
      whatsapp: '',
      email: '',
      billing_address_type: 'condominium',
      cep: '',
      address: '',
      neighborhood: '',
      city: '',
      state: '',
      classification: '',
      active: true
    });

    const isEditing = computed(() => !!route.params.id);

    // Função para formatar CPF
    const formatCPF = (value) => {
      const numbers = value.replace(/\D/g, '');
      return numbers.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    };

    // Função para formatar telefone
    const formatPhone = (value) => {
      const numbers = value.replace(/\D/g, '');
      if (numbers.length === 11) {
        return numbers.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
      } else if (numbers.length === 10) {
        return numbers.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
      }
      return numbers;
    };

    // Função para formatar CEP
    const formatCEP = (value) => {
      const numbers = value.replace(/\D/g, '');
      return numbers.replace(/(\d{5})(\d{3})/, '$1-$2');
    };

    // Watchers para formatação
    const watchCPF = () => {
      if (form.cpf) {
        form.cpf = formatCPF(form.cpf);
      }
    };

    const watchPhone = () => {
      if (form.phone) {
        form.phone = formatPhone(form.phone);
      }
    };

    const watchWhatsApp = () => {
      if (form.whatsapp) {
        form.whatsapp = formatPhone(form.whatsapp);
      }
    };

    const watchCEP = () => {
      if (form.cep) {
        form.cep = formatCEP(form.cep);
      }
    };

    // Carregar dados do morador se estiver editando
    const loadResident = async () => {
      if (!isEditing.value) return;

      try {
        loading.value = true;
        const token = localStorage.getItem('token');
        const response = await fetch(`/api/residents/${route.params.id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          const resident = await response.json();
          
          // Preencher formulário com dados do morador
          Object.assign(form, {
            name: resident.name || '',
            rg: resident.rg || '',
            cpf: resident.cpf || '',
            phone: resident.phone || '',
            whatsapp: resident.whatsapp || '',
            email: resident.email || '',
            billing_address_type: resident.billing_address_type || 'condominium',
            cep: resident.cep || '',
            address: resident.address || '',
            neighborhood: resident.neighborhood || '',
            city: resident.city || '',
            state: resident.state || '',
            classification: resident.classification || '',
            active: resident.active !== undefined ? resident.active : true
          });
        } else {
          console.error('Erro ao carregar morador:', response.statusText);
          router.push('/residents');
        }
      } catch (error) {
        console.error('Erro ao carregar morador:', error);
        router.push('/residents');
      } finally {
        loading.value = false;
      }
    };

    // Validação do formulário
    const validateForm = () => {
      errors.value = {};

      // Validação de CPF
      if (form.cpf) {
        const cpfNumbers = form.cpf.replace(/\D/g, '');
        if (cpfNumbers.length !== 11) {
          errors.value.cpf = 'CPF deve ter 11 dígitos';
        }
      }

      // Validação de email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(form.email)) {
        errors.value.email = 'Email inválido';
      }

      // Validação de endereço personalizado
      if (form.billing_address_type === 'custom') {
        if (!form.address) {
          errors.value.address = 'Endereço é obrigatório quando usar endereço personalizado';
        }
        if (!form.city) {
          errors.value.city = 'Cidade é obrigatória quando usar endereço personalizado';
        }
        if (!form.state) {
          errors.value.state = 'Estado é obrigatório quando usar endereço personalizado';
        }
      }

      return Object.keys(errors.value).length === 0;
    };

    // Submissão do formulário
    const handleSubmit = async () => {
      if (!validateForm()) {
        return;
      }

      try {
        loading.value = true;
        const token = localStorage.getItem('token');
        
        // Preparar dados para envio
        const data = {
          ...form,
          condominium_id: localStorage.getItem('selectedCondominium')
        };

        // Remover campos vazios do endereço se usar endereço do condomínio
        if (form.billing_address_type === 'condominium') {
          delete data.cep;
          delete data.address;
          delete data.neighborhood;
          delete data.city;
          delete data.state;
        }

        const url = isEditing.value 
          ? `/api/residents/${route.params.id}`
          : '/api/residents';
        
        const method = isEditing.value ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method,
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
          if (result.errors) {
            errors.value = result.errors;
          } else {
            errors.value = { general: result.message || 'Erro ao salvar morador' };
          }
          return;
        }

        // Redirecionar para lista de moradores com mensagem de sucesso
        router.push({
          path: '/residents',
          query: { 
            success: isEditing.value ? 'Morador atualizado com sucesso!' : 'Morador cadastrado com sucesso!' 
          }
        });

      } catch (error) {
        console.error('Erro ao salvar morador:', error);
        errors.value = { general: 'Erro de conexão. Tente novamente.' };
      } finally {
        loading.value = false;
      }
    };

    const goBack = () => {
      router.push('/residents');
    };

    // Carregar dados ao montar o componente
    onMounted(() => {
      loadResident();
    });

    return {
      form,
      loading,
      errors,
      isEditing,
      handleSubmit,
      goBack,
      watchCPF,
      watchPhone,
      watchWhatsApp,
      watchCEP
    };
  }
};
</script> 