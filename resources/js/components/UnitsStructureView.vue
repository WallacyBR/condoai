<template>
  <AuthenticatedLayout page-title="Unidades">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Card de Torres -->
      <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-6 border-2 border-cyan-500/20">
        <h3 class="text-lg font-medium text-cyan-300 mb-4">Torres</h3>
        <form @submit.prevent="createTower" class="space-y-4 mb-6">
          <input v-model="newTower.name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-colors" placeholder="Nome da torre" />
          <NeonButton type="submit" size="md" :loading="loadingTower" class="w-full">Salvar Torre</NeonButton>
        </form>

        <!-- Seção de Inserção em Lote - Torres -->
        <div class="border-t border-gray-700 pt-6 mb-6">
          <h4 class="text-sm font-medium text-cyan-300 mb-3">Inserção em Lote</h4>
          <div class="space-y-3">
            <input 
              v-model="batchTower.range" 
              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-colors" 
              placeholder="Ex: A a D ou 1 a 5" 
            />
            <div class="flex gap-2">
              <NeonButton @click="generateTowerBatch" size="sm" class="flex-1">Gerar Lista</NeonButton>
              <NeonButton 
                v-if="batchTower.preview.length > 0" 
                @click="confirmTowerBatch" 
                size="sm" 
                :loading="loadingTowerBatch" 
                class="flex-1"
              >
                Confirmar ({{ batchTower.preview.length }})
              </NeonButton>
            </div>
            <div v-if="batchTower.preview.length > 0" class="bg-gray-800/50 rounded-lg p-3 max-h-24 overflow-y-auto">
              <div class="text-xs text-cyan-200">Preview:</div>
              <div class="text-xs text-gray-300">{{ batchTower.preview.join(', ') }}</div>
            </div>
          </div>
        </div>

        <GlassScrollContainer color="cyan" show-fade animate-items max-height="240px">
          <ul class="space-y-2 text-cyan-100">
            <li v-for="t in towers" :key="t.id" class="bg-gray-800/50 p-3 rounded-lg flex justify-between items-center">
              <span>{{ t.name }}</span>
              <button 
                @click="showRemoveConfirmation('tower', t)" 
                :disabled="hasApartmentsInTower(t.id)"
                :class="[
                  'text-xs px-2 py-1 rounded transition-colors',
                  hasApartmentsInTower(t.id) 
                    ? 'bg-gray-600 text-gray-400 cursor-not-allowed' 
                    : 'bg-red-600 hover:bg-red-700 text-white'
                ]"
                :title="hasApartmentsInTower(t.id) ? 'Não é possível remover torre com apartamentos' : 'Remover torre'"
              >
                {{ hasApartmentsInTower(t.id) ? 'Bloqueado' : 'Remover' }}
              </button>
            </li>
          </ul>
        </GlassScrollContainer>
      </section>

      <!-- Card de Blocos -->
      <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-6 border-2 border-green-500/20">
        <h3 class="text-lg font-medium text-green-300 mb-4">Blocos</h3>
        <form @submit.prevent="createBlock" class="space-y-4 mb-6">
          <input v-model="newBlock.name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors" placeholder="Nome do bloco" />
          <select v-model="newBlock.tower_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
            <option :value="null">Sem torre</option>
            <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
          </select>
          <NeonButton type="submit" size="md" :loading="loadingBlock" class="w-full">Salvar Bloco</NeonButton>
        </form>

        <!-- Seção de Inserção em Lote - Blocos -->
        <div class="border-t border-gray-700 pt-6 mb-6">
          <h4 class="text-sm font-medium text-green-300 mb-3">Inserção em Lote</h4>
          <div class="space-y-3">
            <input 
              v-model="batchBlock.range" 
              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors" 
              placeholder="Ex: 1 a 5 ou A a J" 
            />
            <select v-model="batchBlock.tower_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
              <option :value="null">Sem torre</option>
              <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
            </select>
            <div class="flex gap-2">
              <NeonButton @click="generateBlockBatch" size="sm" class="flex-1">Gerar Lista</NeonButton>
              <NeonButton 
                v-if="batchBlock.preview.length > 0" 
                @click="confirmBlockBatch" 
                size="sm" 
                :loading="loadingBlockBatch" 
                class="flex-1"
              >
                Confirmar ({{ batchBlock.preview.length }})
              </NeonButton>
            </div>
            <div v-if="batchBlock.preview.length > 0" class="bg-gray-800/50 rounded-lg p-3 max-h-24 overflow-y-auto">
              <div class="text-xs text-green-200">Preview:</div>
              <div class="text-xs text-gray-300">{{ batchBlock.preview.join(', ') }}</div>
            </div>
          </div>
        </div>

        <GlassScrollContainer color="green" show-fade animate-items max-height="240px">
          <ul class="space-y-2 text-green-100">
            <li v-for="b in blocksSorted" :key="b.id" class="bg-gray-800/50 p-3 rounded-lg flex justify-between items-center">
              <div>
                <span>{{ b.name }}</span>
                <span class="text-xs text-gray-400 block">{{ b.tower ? b.tower.name : 'Sem torre' }}</span>
              </div>
              <button 
                @click="showRemoveConfirmation('block', b)" 
                :disabled="hasApartmentsInBlock(b.id)"
                :class="[
                  'text-xs px-2 py-1 rounded transition-colors',
                  hasApartmentsInBlock(b.id) 
                    ? 'bg-gray-600 text-gray-400 cursor-not-allowed' 
                    : 'bg-red-600 hover:bg-red-700 text-white'
                ]"
                :title="hasApartmentsInBlock(b.id) ? 'Não é possível remover bloco com apartamentos' : 'Remover bloco'"
              >
                {{ hasApartmentsInBlock(b.id) ? 'Bloqueado' : 'Remover' }}
              </button>
            </li>
          </ul>
        </GlassScrollContainer>
      </section>

      <!-- Card de Apartamentos -->
      <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-6 border-2 border-blue-500/20">
        <h3 class="text-lg font-medium text-blue-300 mb-4">Apartamentos/Casas</h3>
        <form @submit.prevent="createApartment" class="space-y-4 mb-6">
          <div class="grid grid-cols-2 gap-4">
            <input v-model="newApartment.number" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" placeholder="Número" />
            <input v-model.number="newApartment.floor" type="number" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" placeholder="Andar" />
          </div>
          <select v-model="newApartment.tower_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option :value="null">Sem torre</option>
              <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
          </select>
          <select v-model="newApartment.block_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option :value="null">Sem bloco</option>
              <option v-for="b in blocks" :value="b.id" :key="b.id">{{ b.name }}</option>
          </select>
          <NeonButton type="submit" size="md" :loading="loadingApartment" class="w-full">Salvar Unidade</NeonButton>
        </form>

        <!-- Seção de Inserção em Lote - Apartamentos -->
        <div class="border-t border-gray-700 pt-6 mb-6">
          <h4 class="text-sm font-medium text-blue-300 mb-3">Inserção em Lote</h4>
          <div class="space-y-3">
            <input 
              v-model="batchApartment.range" 
              class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" 
              placeholder="Ex: 101 a 108, 201 a 208" 
            />
            <select v-model="batchApartment.tower_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option :value="null">Sem torre</option>
              <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
            </select>
            <select v-model="batchApartment.block_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option :value="null">Sem bloco</option>
              <option v-for="b in blocks" :value="b.id" :key="b.id">{{ b.name }}</option>
            </select>
            <div class="flex gap-2">
              <NeonButton @click="generateApartmentBatch" size="sm" class="flex-1">Gerar Lista</NeonButton>
              <NeonButton 
                v-if="batchApartment.preview.length > 0" 
                @click="confirmApartmentBatch" 
                size="sm" 
                :loading="loadingApartmentBatch" 
                class="flex-1"
              >
                Confirmar ({{ batchApartment.preview.length }})
              </NeonButton>
            </div>
            <div v-if="batchApartment.preview.length > 0" class="bg-gray-800/50 rounded-lg p-3 max-h-24 overflow-y-auto">
              <div class="text-xs text-blue-200">Preview:</div>
              <div class="text-xs text-gray-300">{{ batchApartment.preview.join(', ') }}</div>
            </div>
          </div>
        </div>

        <GlassScrollContainer color="blue" show-fade animate-items max-height="240px">
          <ul class="space-y-2 text-blue-100">
            <li v-for="a in apartmentsSorted" :key="a.id" class="bg-gray-800/50 p-3 rounded-lg flex justify-between items-center">
              <div>
                <div class="font-semibold">Unidade {{ a.number }}</div>
                <div class="text-sm text-gray-400">
                  Andar {{ a.floor }}
                  <span v-if="a.tower"> - T: {{ a.tower.name }}</span>
                  <span v-if="a.block"> - B: {{ a.block.name }}</span>
                </div>
              </div>
              <button 
                @click="showRemoveConfirmation('apartment', a)" 
                class="text-xs px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white transition-colors"
                title="Remover apartamento"
              >
                Remover
              </button>
            </li>
          </ul>
        </GlassScrollContainer>
      </section>
    </div>

    <!-- Modal de Confirmação de Remoção -->
    <div v-if="removeConfirmation.show" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50" @click="closeRemoveConfirmation">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-6 border-2 border-red-500/30 max-w-md w-full mx-4" @click.stop>
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-red-500/20 rounded-full flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-white">Confirmar Remoção</h3>
            <p class="text-sm text-gray-400">Esta ação não pode ser desfeita</p>
          </div>
        </div>

                 <div class="mb-6">
           <p class="text-gray-300 mb-2">
             {{ removeConfirmation.message }}
           </p>
           <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-3">
             <p class="text-red-300 text-sm font-medium">
               {{ removeConfirmation.itemName }}
             </p>
             <!-- Informações adicionais para apartamentos -->
             <div v-if="removeConfirmation.type === 'apartment' && removeConfirmation.item" class="mt-2 pt-2 border-t border-red-500/20">
               <div class="space-y-1 text-xs text-gray-400">
                 <div>
                   <span class="text-gray-500">Andar:</span>
                   <span class="text-gray-300 ml-1">{{ removeConfirmation.item.floor }}</span>
                 </div>
                 <div v-if="removeConfirmation.item.tower">
                   <span class="text-gray-500">Torre:</span>
                   <span class="text-gray-300 ml-1">{{ removeConfirmation.item.tower.name }}</span>
                 </div>
                 <div v-if="removeConfirmation.item.block">
                   <span class="text-gray-500">Bloco:</span>
                   <span class="text-gray-300 ml-1">{{ removeConfirmation.item.block.name }}</span>
                 </div>
               </div>
             </div>
           </div>
         </div>

        <div class="flex gap-3">
          <button 
            @click="closeRemoveConfirmation"
            class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
          >
            Cancelar
          </button>
          <button 
            @click="confirmRemove"
            :disabled="removeConfirmation.loading"
            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-800 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center justify-center"
          >
            <span v-if="!removeConfirmation.loading">Remover</span>
            <div v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import NeonButton from './NeonButton.vue';
import GlassScrollContainer from './GlassScrollContainer.vue';

export default {
  name: 'UnitsView',
  components: { AuthenticatedLayout, NeonButton, GlassScrollContainer },
  setup() {
    const token = localStorage.getItem('token');
    const selectedCondo = localStorage.getItem('selectedCondominium');
    
    // Debug: Verificar token e condomínio
    console.log('Token disponível:', !!token);
    console.log('Token preview:', token ? token.substring(0, 20) + '...' : 'null');
    console.log('Condomínio selecionado:', selectedCondo);
    
    const headers = { 
      'Content-Type': 'application/json', 
      'Accept': 'application/json',
      Authorization: `Bearer ${token}` 
    };
    

    const towers = ref([]);
    const blocks = ref([]);
    const apartments = ref([]);

    // Estados de loading para cada operação
    const loadingTower = ref(false);
    const loadingBlock = ref(false);
    const loadingApartment = ref(false);
    const loadingTowerBatch = ref(false);
    const loadingBlockBatch = ref(false);
    const loadingApartmentBatch = ref(false);

    const newTower = ref({ name: '', condominium_id: null });
    const newBlock = ref({ name: '', tower_id: null, condominium_id: null });
    const newApartment = ref({ number: '', floor: 1, tower_id: null, block_id: null, condominium_id: null });

    // Estados para inserção em lote
    const batchTower = ref({ range: '', preview: [] });
    const batchBlock = ref({ range: '', tower_id: null, preview: [] });
    const batchApartment = ref({ range: '', tower_id: null, block_id: null, preview: [] });

    // Estado para modal de confirmação de remoção
    const removeConfirmation = ref({
      show: false,
      type: null,
      item: null,
      message: '',
      itemName: '',
      loading: false
    });

    // Função utilitária para expandir intervalos (DRY)
    const expandRange = (rangeText) => {
      const results = [];
      const ranges = rangeText.split(',').map(r => r.trim());

      ranges.forEach(range => {
        const match = range.match(/^(.+?)\s+a\s+(.+?)$/);
        if (!match) {
          // Se não é um intervalo, adiciona como item único
          if (range) results.push(range);
          return;
        }

        const [, start, end] = match;

        // Verifica se é numérico
        if (/^\d+$/.test(start) && /^\d+$/.test(end)) {
          const startNum = parseInt(start);
          const endNum = parseInt(end);
          const numericResults = [];
          for (let i = startNum; i <= endNum; i++) {
            numericResults.push(i.toString());
          }
          // Adiciona os resultados numéricos já ordenados
          results.push(...numericResults);
        }
        // Verifica se é alfabético (uma letra)
        else if (/^[A-Za-z]$/.test(start) && /^[A-Za-z]$/.test(end)) {
          const startCode = start.toUpperCase().charCodeAt(0);
          const endCode = end.toUpperCase().charCodeAt(0);
          for (let i = startCode; i <= endCode; i++) {
            results.push(String.fromCharCode(i));
          }
        }
        // Se não conseguir processar, adiciona como está
        else {
          results.push(range);
        }
      });

      // Ordenação robusta: numérica para números, alfabética para letras
      return results.sort((a, b) => {
        // Remove espaços e zeros à esquerda
        const cleanA = a.trim().replace(/^0+/, '');
        const cleanB = b.trim().replace(/^0+/, '');
        const numA = parseInt(cleanA, 10);
        const numB = parseInt(cleanB, 10);
        // Se ambos são números válidos, ordena numericamente
        if (!isNaN(numA) && !isNaN(numB)) {
          return numA - numB;
        }
        // Se ambos são letras, ordena alfabeticamente
        if (/^[A-Za-z]+$/.test(a) && /^[A-Za-z]+$/.test(b)) {
          return a.localeCompare(b);
        }
        // Caso misto, ordena como string
        return a.localeCompare(b);
      });
    };

    // Função para calcular andar baseado no número do apartamento
    const getFloorFromApartmentNumber = (number) => {
      const numStr = number.toString();
      if (numStr.length >= 3) {
        return parseInt(numStr.charAt(0));
      }
      return 1; // Padrão para números menores
    };

    const loadTowers = async () => {
      try {
        console.log('Carregando torres para o condomínio:', selectedCondo);
        console.log('Headers da requisição:', headers);
        
        const respTowers = await fetch(`/api/towers?condominium_id=${selectedCondo}`, { headers });
        
        console.log('Status da resposta torres:', respTowers.status);
        console.log('Headers da resposta torres:', respTowers.headers);
        
        if (respTowers.ok) {
          towers.value = await respTowers.json();
          console.log('Torres carregadas:', towers.value.length);
        } else {
          // Tentar ler o texto da resposta para debug
          const errorText = await respTowers.text();
          console.error('Erro ao carregar torres:', respTowers.status, errorText);
          towers.value = [];
        }
      } catch (error) {
        console.error('Exceção ao carregar torres:', error);
        towers.value = [];
      }
    };

    const loadBlocks = async () => {
      try {
        console.log('Carregando blocos para o condomínio:', selectedCondo);
        
        const respBlocks = await fetch(`/api/blocks?condominium_id=${selectedCondo}`, { headers });
        
        console.log('Status da resposta blocos:', respBlocks.status);
        
        if (respBlocks.ok) {
          blocks.value = await respBlocks.json();
          console.log('Blocos carregados:', blocks.value.length);
        } else {
          // Tentar ler o texto da resposta para debug
          const errorText = await respBlocks.text();
          console.error('Erro ao carregar blocos:', respBlocks.status, errorText);
          blocks.value = [];
        }
      } catch (error) {
        console.error('Exceção ao carregar blocos:', error);
        blocks.value = [];
      }
    };

    const loadApartments = async () => {
      try {
        console.log('Carregando apartamentos para o condomínio:', selectedCondo);
        
        const respApartments = await fetch(`/api/apartments?condominium_id=${selectedCondo}`, { headers });
        
        console.log('Status da resposta apartamentos:', respApartments.status);
        
        if (respApartments.ok) {
          apartments.value = await respApartments.json();
          console.log('Apartamentos carregados:', apartments.value.length);
        } else {
          // Tentar ler o texto da resposta para debug
          const errorText = await respApartments.text();
          console.error('Erro ao carregar apartamentos:', respApartments.status, errorText);
          apartments.value = [];
        }
      } catch (error) {
        console.error('Exceção ao carregar apartamentos:', error);
        apartments.value = [];
      }
    };

    const loadData = async () => {
      await Promise.all([loadTowers(), loadBlocks(), loadApartments()]);
    };

    const createTower = async () => {
      if (!newTower.value.name) return;
      loadingTower.value = true;
      try {
        newTower.value.condominium_id = selectedCondo;
        const resp = await fetch('/api/towers', { method: 'POST', headers, body: JSON.stringify(newTower.value) });
        if (resp.ok) {
          await loadTowers();
          newTower.value.name = '';
        } else {
          console.error('Erro ao criar torre:', resp.status);
          alert('Erro ao criar torre.');
        }
      } catch (error) {
        console.error('Exceção ao criar torre:', error);
        alert('Erro ao criar torre. Verifique sua conexão.');
      } finally {
        loadingTower.value = false;
      }
    };

    const createBlock = async () => {
      if (!newBlock.value.name) return;
      loadingBlock.value = true;
      try {
        newBlock.value.condominium_id = selectedCondo;
        const resp = await fetch('/api/blocks', { method: 'POST', headers, body: JSON.stringify(newBlock.value) });
        if (resp.ok) {
          await loadBlocks();
          newBlock.value.name = '';
          newBlock.value.tower_id = null;
        } else {
          console.error('Erro ao criar bloco:', resp.status);
          alert('Erro ao criar bloco.');
        }
      } catch (error) {
        console.error('Exceção ao criar bloco:', error);
        alert('Erro ao criar bloco. Verifique sua conexão.');
      } finally {
        loadingBlock.value = false;
      }
    };

    const createApartment = async () => {
      if (!newApartment.value.number) return;
      loadingApartment.value = true;
      try {
        newApartment.value.condominium_id = selectedCondo;
        const resp = await fetch('/api/apartments', { method: 'POST', headers, body: JSON.stringify(newApartment.value) });
        if (resp.ok) {
          await loadApartments();
          newApartment.value.number = '';
          newApartment.value.floor = 1;
          // Mantém os valores de torre e bloco para facilitar adição em sequência
          // newApartment.value.tower_id = null;
          // newApartment.value.block_id = null;
        } else {
          console.error('Erro ao criar apartamento:', resp.status);
          
          // Tentar ler a resposta de erro para mostrar mensagem mais específica
          try {
            const errorData = await resp.json();
            if (errorData.errors && errorData.errors.number) {
              alert(`Erro de validação: ${errorData.errors.number[0]}`);
            } else if (errorData.message) {
              alert(`Erro: ${errorData.message}`);
            } else {
              alert('Erro ao criar apartamento. Verifique se o número já não existe no bloco/torre selecionado.');
            }
          } catch (parseError) {
            alert('Erro ao criar apartamento. Verifique se o número já não existe no bloco/torre selecionado.');
          }
        }
      } catch (error) {
        console.error('Exceção ao criar apartamento:', error);
        alert('Erro ao criar apartamento. Verifique sua conexão.');
      } finally {
        loadingApartment.value = false;
      }
    };

    // Funções para inserção em lote - Torres
    const generateTowerBatch = () => {
      if (!batchTower.value.range) return;
      batchTower.value.preview = expandRange(batchTower.value.range);
    };

    const confirmTowerBatch = async () => {
      if (batchTower.value.preview.length === 0) return;
      loadingTowerBatch.value = true;
      try {
        const promises = batchTower.value.preview.map(async (name) => {
          const response = await fetch('/api/towers', {
            method: 'POST',
            headers,
            body: JSON.stringify({
              name,
              condominium_id: selectedCondo
            })
          });
          
          if (!response.ok) {
            const errorText = await response.text();
            console.error(`Erro ao criar torre ${name}:`, errorText);
            throw new Error(`Erro ao criar torre ${name}: ${response.status}`);
          }
          
          return response.json();
        });
        
        await Promise.all(promises);
        await loadTowers();
        batchTower.value.range = '';
        batchTower.value.preview = [];
      } catch (error) {
        console.error('Erro na criação em lote de torres:', error);
        alert('Erro ao criar torres em lote. Verifique o console para mais detalhes.');
      } finally {
        loadingTowerBatch.value = false;
      }
    };

    // Funções para inserção em lote - Blocos
    const generateBlockBatch = () => {
      if (!batchBlock.value.range) return;
      batchBlock.value.preview = expandRange(batchBlock.value.range);
    };

    const confirmBlockBatch = async () => {
      if (batchBlock.value.preview.length === 0) return;
      loadingBlockBatch.value = true;
      try {
        const promises = batchBlock.value.preview.map(async (name) => {
          const response = await fetch('/api/blocks', {
            method: 'POST',
            headers,
            body: JSON.stringify({
              name,
              tower_id: batchBlock.value.tower_id,
              condominium_id: selectedCondo
            })
          });
          
          if (!response.ok) {
            const errorText = await response.text();
            console.error(`Erro ao criar bloco ${name}:`, errorText);
            throw new Error(`Erro ao criar bloco ${name}: ${response.status}`);
          }
          
          return response.json();
        });
        
        await Promise.all(promises);
        await loadBlocks();
        batchBlock.value.range = '';
        batchBlock.value.preview = [];
      } catch (error) {
        console.error('Erro na criação em lote de blocos:', error);
        alert('Erro ao criar blocos em lote. Verifique o console para mais detalhes.');
      } finally {
        loadingBlockBatch.value = false;
      }
    };

    // Funções para inserção em lote - Apartamentos
    const generateApartmentBatch = () => {
      if (!batchApartment.value.range) return;
      batchApartment.value.preview = expandRange(batchApartment.value.range);
      
      // Verificar se há números duplicados no bloco/torre selecionado
      if (apartments.value.length > 0) {
        const existingNumbers = apartments.value
          .filter(apt => {
            if (batchApartment.value.block_id) {
              return apt.block_id === batchApartment.value.block_id;
            } else if (batchApartment.value.tower_id) {
              return apt.tower_id === batchApartment.value.tower_id;
            }
            return true; // Se não tem bloco nem torre, verifica no condomínio
          })
          .map(apt => apt.number);

        const duplicateNumbers = batchApartment.value.preview.filter(number => 
          existingNumbers.includes(number)
        );

        if (duplicateNumbers.length > 0) {
          console.warn(`Atenção: Os seguintes números já existem no bloco/torre selecionado: ${duplicateNumbers.join(', ')}`);
        }
      }
    };

    const confirmApartmentBatch = async () => {
      if (batchApartment.value.preview.length === 0) return;
      loadingApartmentBatch.value = true;
      try {
        // Verificar se os números já existem no bloco/torre selecionado
        const existingNumbers = apartments.value
          .filter(apt => {
            if (batchApartment.value.block_id) {
              return apt.block_id === batchApartment.value.block_id;
            } else if (batchApartment.value.tower_id) {
              return apt.tower_id === batchApartment.value.tower_id;
            }
            return true; // Se não tem bloco nem torre, verifica no condomínio
          })
          .map(apt => apt.number);

        const duplicateNumbers = batchApartment.value.preview.filter(number => 
          existingNumbers.includes(number)
        );

        if (duplicateNumbers.length > 0) {
          alert(`Os seguintes números já existem no bloco/torre selecionado: ${duplicateNumbers.join(', ')}. Remova-os da lista antes de continuar.`);
          loadingApartmentBatch.value = false;
          return;
        }

        const promises = batchApartment.value.preview.map(async (number) => {
          const response = await fetch('/api/apartments', {
            method: 'POST',
            headers,
            body: JSON.stringify({
              number,
              floor: getFloorFromApartmentNumber(number),
              tower_id: batchApartment.value.tower_id,
              block_id: batchApartment.value.block_id,
              condominium_id: selectedCondo
            })
          });
          
          if (!response.ok) {
            const errorText = await response.text();
            console.error(`Erro ao criar apartamento ${number}:`, errorText);
            
            // Tentar parsear o erro para mostrar mensagem mais específica
            try {
              const errorData = JSON.parse(errorText);
              if (errorData.errors && errorData.errors.number) {
                throw new Error(`Apartamento ${number}: ${errorData.errors.number[0]}`);
              } else if (errorData.message) {
                throw new Error(`Apartamento ${number}: ${errorData.message}`);
              } else {
                throw new Error(`Apartamento ${number}: Erro de validação - número pode já existir no bloco/torre selecionado`);
              }
            } catch (parseError) {
              throw new Error(`Apartamento ${number}: Erro de validação - número pode já existir no bloco/torre selecionado`);
            }
          }
          
          return response.json();
        });
        
        await Promise.all(promises);
        await loadApartments();
        batchApartment.value.range = '';
        batchApartment.value.preview = [];
      } catch (error) {
        console.error('Erro na criação em lote de apartamentos:', error);
        alert(`Erro ao criar apartamentos em lote: ${error.message}`);
      } finally {
        loadingApartmentBatch.value = false;
      }
    };

    // Funções de validação de dependências
    const hasApartmentsInTower = (towerId) => {
      return apartments.value.some(a => a.tower_id === towerId);
    };

    const hasApartmentsInBlock = (blockId) => {
      return apartments.value.some(a => a.block_id === blockId);
    };

    // Funções do modal de confirmação
    const showRemoveConfirmation = (type, item) => {
      // Validações de dependência antes de mostrar o modal
      if (type === 'tower' && hasApartmentsInTower(item.id)) {
        alert('Não é possível remover esta torre pois existem apartamentos associados a ela.');
        return;
      }
      
      if (type === 'block' && hasApartmentsInBlock(item.id)) {
        alert('Não é possível remover este bloco pois existem apartamentos associados a ele.');
        return;
      }

      // Configurar mensagens baseadas no tipo
      let message = '';
      let itemName = '';
      
      switch (type) {
        case 'tower':
          message = 'Tem certeza que deseja remover esta torre?';
          itemName = `Torre: ${item.name}`;
          break;
        case 'block':
          message = 'Tem certeza que deseja remover este bloco?';
          itemName = `Bloco: ${item.name}`;
          break;
        case 'apartment':
          message = 'Tem certeza que deseja remover este apartamento?';
          itemName = `Unidade: ${item.number}`;
          break;
      }

      removeConfirmation.value = {
        show: true,
        type,
        item,
        message,
        itemName,
        loading: false
      };
    };

    const closeRemoveConfirmation = () => {
      removeConfirmation.value.show = false;
      removeConfirmation.value.loading = false;
    };

    const confirmRemove = async () => {
      const { type, item } = removeConfirmation.value;
      removeConfirmation.value.loading = true;

      try {
        let resp;
        let endpoint = '';
        
        switch (type) {
          case 'tower':
            endpoint = `/api/towers/${item.id}`;
            break;
          case 'block':
            endpoint = `/api/blocks/${item.id}`;
            break;
          case 'apartment':
            endpoint = `/api/apartments/${item.id}`;
            break;
        }

        resp = await fetch(endpoint, {
          method: 'DELETE',
          headers
        });
        
        if (resp.ok) {
          // Atualizar listas baseadas no tipo removido
          switch (type) {
            case 'tower':
              await Promise.all([loadTowers(), loadBlocks()]);
              break;
            case 'block':
              await loadBlocks();
              break;
            case 'apartment':
              await loadApartments();
              break;
          }
          closeRemoveConfirmation();
        } else {
          alert('Erro ao remover item. Tente novamente.');
          removeConfirmation.value.loading = false;
        }
      } catch (error) {
        alert('Erro ao remover item. Verifique sua conexão.');
        removeConfirmation.value.loading = false;
      }
    };

    const blocksSorted = computed(() => {
      return [...blocks.value].sort((a, b) => {
        const numA = parseInt(a.name, 10);
        const numB = parseInt(b.name, 10);
        if (!isNaN(numA) && !isNaN(numB)) {
          return numA - numB;
        }
        return a.name.localeCompare(b.name);
      });
    });

    const apartmentsSorted = computed(() => {
      return [...apartments.value].sort((a, b) => {
        // Primeiro critério: Bloco
        const blockA = a.block ? a.block.name : '';
        const blockB = b.block ? b.block.name : '';
        
        if (blockA !== blockB) {
          // Ordenação numérica para blocos se ambos forem números
          const numA = parseInt(blockA, 10);
          const numB = parseInt(blockB, 10);
          if (!isNaN(numA) && !isNaN(numB)) {
            return numA - numB;
          }
          // Ordenação alfabética para blocos não numéricos
          return blockA.localeCompare(blockB);
        }
        
        // Segundo critério: Torre (se não há diferença de bloco)
        const towerA = a.tower ? a.tower.name : '';
        const towerB = b.tower ? b.tower.name : '';
        
        if (towerA !== towerB) {
          // Ordenação numérica para torres se ambos forem números
          const numA = parseInt(towerA, 10);
          const numB = parseInt(towerB, 10);
          if (!isNaN(numA) && !isNaN(numB)) {
            return numA - numB;
          }
          // Ordenação alfabética para torres não numéricas
          return towerA.localeCompare(towerB);
        }
        
        // Terceiro critério: Número do apartamento
        const numA = parseInt(a.number, 10);
        const numB = parseInt(b.number, 10);
        
        // Se ambos são números válidos, ordena numericamente
        if (!isNaN(numA) && !isNaN(numB)) {
          return numA - numB;
        }
        
        // Caso contrário, ordena como string
        return a.number.localeCompare(b.number);
      });
    });

    onMounted(() => {
      loadData();
    });

    return { 
      towers, 
      blocks, 
      apartments, 
      newTower, 
      newBlock, 
      newApartment, 
      createTower, 
      createBlock, 
      createApartment,
      loadingTower,
      loadingBlock,
      loadingApartment,
      // Batch functionality
      batchTower,
      batchBlock,
      batchApartment,
      generateTowerBatch,
      confirmTowerBatch,
      generateBlockBatch,
      confirmBlockBatch,
      generateApartmentBatch,
      confirmApartmentBatch,
      loadingTowerBatch,
      loadingBlockBatch,
      loadingApartmentBatch,
      // Remove confirmation modal
      removeConfirmation,
      showRemoveConfirmation,
      closeRemoveConfirmation,
      confirmRemove,
      hasApartmentsInTower,
      hasApartmentsInBlock,
      blocksSorted,
      apartmentsSorted
    };
  }
};
</script>
