<template>
  <div class="space-y-6">
    <!-- Cabeçalho Integrado -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Assembleias e Votações Digitais</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
          Participe de deliberações oficiais, registre votos digitais com validade jurídica e consulte atas.
        </p>
      </div>
      <button
        @click="showCreateModal = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm transition-colors shadow-sm"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Convocar Assembleia
      </button>
    </div>

    <!-- Filtros de Status -->
    <div class="flex flex-wrap gap-2 pb-2 border-b border-slate-200 dark:border-slate-800">
      <button
        v-for="filter in statusFilters"
        :key="filter.value"
        @click="selectedStatus = filter.value"
        class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-colors"
        :class="selectedStatus === filter.value
          ? 'bg-blue-600 text-white shadow-sm'
          : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700/60'"
      >
        {{ filter.label }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full"></div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="filteredAssemblies.length === 0"
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center"
    >
      <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
        </svg>
      </div>
      <h3 class="text-base font-semibold text-slate-800 dark:text-slate-200">Nenhuma assembleia encontrada</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        Não há assembleias cadastradas com este filtro de status no momento.
      </p>
    </div>

    <!-- Lista de Assembleias -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="assembly in filteredAssemblies"
        :key="assembly.id"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all"
      >
        <div>
          <!-- Tags e Status -->
          <div class="flex items-center justify-between gap-2 mb-3">
            <span
              class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider"
              :class="getStatusBadgeClass(assembly.status)"
            >
              {{ getStatusLabel(assembly.status) }}
            </span>

            <span class="text-xs font-medium text-slate-400 dark:text-slate-500 capitalize">
              {{ assembly.type }} • {{ assembly.modality }}
            </span>
          </div>

          <!-- Título e Descrição -->
          <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-snug">
            {{ assembly.title }}
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 line-clamp-2">
            {{ assembly.description || 'Sem descrição adicional.' }}
          </p>

          <!-- Metadados de Data e Local -->
          <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-3 text-xs text-slate-600 dark:text-slate-300">
            <div>
              <span class="text-slate-400 block text-[11px]">1ª Convocação</span>
              <span class="font-medium">{{ formatDateTime(assembly.first_call_at) }}</span>
            </div>
            <div>
              <span class="text-slate-400 block text-[11px]">Pautas Deliberativas</span>
              <span class="font-medium">{{ assembly.agendas_count || (assembly.agendas ? assembly.agendas.length : 0) }} pautas</span>
            </div>
          </div>
        </div>

        <!-- Ações do Card -->
        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
          <span v-if="assembly.status === 'em_andamento'" class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Votação em Andamento
          </span>
          <span v-else class="text-xs text-slate-400">
            Organizado por {{ assembly.creator?.name || 'Administração' }}
          </span>

          <router-link
            :to="`/assemblies/${assembly.id}`"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-900 dark:text-white transition-colors"
          >
            Acessar Assembleia
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>
        </div>
      </div>
    </div>

    <!-- Modal de Criação / Convocação -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl my-8">
        <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-800">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Convocar Nova Assembleia</h2>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="createAssembly" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Título da Assembleia</label>
            <input
              v-model="newAssembly.title"
              type="text"
              required
              placeholder="Ex: Assembleia Geral Ordinária de Eleição e Orçamento"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipo</label>
              <select
                v-model="newAssembly.type"
                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="ordinaria">Ordinária</option>
                <option value="extraordinaria">Extraordinária</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Modalidade</label>
              <select
                v-model="newAssembly.modality"
                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="virtual">100% Virtual</option>
                <option value="hibrida">Híbrida (Presencial e Online)</option>
                <option value="presencial">Presencial</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">1ª Convocação</label>
              <input
                v-model="newAssembly.first_call_at"
                type="datetime-local"
                required
                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">2ª Convocação (opcional)</label>
              <input
                v-model="newAssembly.second_call_at"
                type="datetime-local"
                class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Link da Videoconferência (Meet / Zoom / Teams)</label>
            <input
              v-model="newAssembly.meeting_link"
              type="url"
              placeholder="https://meet.google.com/..."
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Descrição e Instruções</label>
            <textarea
              v-model="newAssembly.description"
              rows="2"
              placeholder="Orientações aos condôminos, regras de participação..."
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Pautas Iniciais -->
          <div class="pt-2">
            <div class="flex justify-between items-center mb-2">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Pautas da Assembleia</label>
              <button
                type="button"
                @click="addAgendaField"
                class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
              >
                + Adicionar Pauta
              </button>
            </div>

            <div class="space-y-3">
              <div
                v-for="(agenda, idx) in newAssembly.agendas"
                :key="idx"
                class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 flex gap-2 items-start"
              >
                <div class="flex-1 space-y-2">
                  <input
                    v-model="agenda.title"
                    type="text"
                    required
                    placeholder="Título da pauta (ex: Prestação de contas 2025)"
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                  />
                  <div class="flex gap-2">
                    <select
                      v-model="agenda.voting_type"
                      class="px-2 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="sim_nao_abstencao">Sim / Não / Abstenção</option>
                      <option value="multipla_escolha">Múltipla Escolha</option>
                    </select>
                    <select
                      v-model="agenda.weight_type"
                      class="px-2 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                    >
                      <option value="unitario">1 Voto por Unidade</option>
                      <option value="fracao_ideal">Peso por Fração Ideal</option>
                    </select>
                  </div>
                </div>
                <button
                  v-if="newAssembly.agendas.length > 1"
                  type="button"
                  @click="removeAgendaField(idx)"
                  class="p-1 text-red-500 hover:text-red-700"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl disabled:opacity-50"
            >
              {{ saving ? 'Convocando...' : 'Confirmar e Publicar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AssembliesView',
  data() {
    return {
      loading: true,
      saving: false,
      assemblies: [],
      selectedStatus: 'all',
      statusFilters: [
        { label: 'Todas', value: 'all' },
        { label: 'Em Andamento', value: 'em_andamento' },
        { label: 'Agendadas', value: 'agendada' },
        { label: 'Encerradas', value: 'encerrada' },
      ],
      showCreateModal: false,
      newAssembly: {
        title: '',
        type: 'ordinaria',
        modality: 'virtual',
        first_call_at: '',
        second_call_at: '',
        meeting_link: '',
        description: '',
        agendas: [
          { title: 'Aprovação das contas', voting_type: 'sim_nao_abstencao', weight_type: 'unitario' },
        ],
      },
    };
  },
  computed: {
    filteredAssemblies() {
      if (this.selectedStatus === 'all') {
        return this.assemblies;
      }
      return this.assemblies.filter((a) => a.status === this.selectedStatus);
    },
  },
  mounted() {
    this.fetchAssemblies();
  },
  methods: {
    async fetchAssemblies() {
      this.loading = true;
      try {
        const response = await axios.get('/api/assemblies');
        this.assemblies = response.data.data || response.data || [];
      } catch (err) {
        console.error('Erro ao carregar assembleias:', err);
      } finally {
        this.loading = false;
      }
    },
    addAgendaField() {
      this.newAssembly.agendas.push({
        title: '',
        voting_type: 'sim_nao_abstencao',
        weight_type: 'unitario',
      });
    },
    removeAgendaField(idx) {
      this.newAssembly.agendas.splice(idx, 1);
    },
    async createAssembly() {
      this.saving = true;
      try {
        await axios.post('/api/assemblies', this.newAssembly);
        this.showCreateModal = false;
        this.newAssembly = {
          title: '',
          type: 'ordinaria',
          modality: 'virtual',
          first_call_at: '',
          second_call_at: '',
          meeting_link: '',
          description: '',
          agendas: [
            { title: 'Aprovação das contas', voting_type: 'sim_nao_abstencao', weight_type: 'unitario' },
          ],
        };
        await this.fetchAssemblies();
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao convocar assembleia.');
      } finally {
        this.saving = false;
      }
    },
    getStatusLabel(status) {
      const map = {
        agendada: 'Agendada',
        em_andamento: 'Em Andamento',
        encerrada: 'Encerrada',
        cancelada: 'Cancelada',
      };
      return map[status] || status;
    },
    getStatusBadgeClass(status) {
      const map = {
        agendada: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900',
        em_andamento: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900',
        encerrada: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
        cancelada: 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-900',
      };
      return map[status] || 'bg-slate-100 text-slate-600';
    },
    formatDateTime(dateStr) {
      if (!dateStr) return '---';
      try {
        const d = new Date(dateStr);
        return d.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
      } catch (e) {
        return dateStr;
      }
    },
  },
};
</script>
