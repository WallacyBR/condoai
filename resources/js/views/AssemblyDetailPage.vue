<template>
  <div class="space-y-6">
    <!-- Voltar e Header -->
    <div class="flex items-center gap-3">
      <router-link
        to="/assemblies"
        class="p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors shadow-sm"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
          {{ assembly?.title || 'Detalhes da Assembleia' }}
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          {{ assembly?.type ? (assembly.type.toUpperCase() + ' • ' + assembly.modality.toUpperCase()) : '' }}
        </p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="animate-spin w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full"></div>
    </div>

    <div v-else-if="assembly" class="space-y-6">
      <!-- Banner Informativo / Controle Síndico -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <span
                class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                :class="getStatusBadgeClass(assembly.status)"
              >
                {{ getStatusLabel(assembly.status) }}
              </span>

              <span v-if="assembly.status === 'em_andamento'" class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                Deliberações Acontecendo Agora
              </span>
            </div>

            <p class="text-sm text-slate-600 dark:text-slate-300 max-w-2xl">
              {{ assembly.description || 'Sem observações cadastradas.' }}
            </p>

            <div class="flex flex-wrap gap-4 text-xs text-slate-500 pt-2">
              <span><strong>1ª Convocação:</strong> {{ formatDateTime(assembly.first_call_at) }}</span>
              <span v-if="assembly.second_call_at"><strong>2ª Convocação:</strong> {{ formatDateTime(assembly.second_call_at) }}</span>
              <span v-if="assembly.location"><strong>Local:</strong> {{ assembly.location }}</span>
            </div>
          </div>

          <!-- Botões de Ação -->
          <div class="flex flex-wrap gap-2">
            <a
              v-if="assembly.meeting_link"
              :href="assembly.meeting_link"
              target="_blank"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Entrar na Videoconferência
            </a>

            <!-- Ações Síndico -->
            <button
              v-if="assembly.status === 'agendada'"
              @click="startAssembly"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors"
            >
              Iniciar Assembleia
            </button>

            <button
              v-if="assembly.status === 'em_andamento'"
              @click="closeAssembly"
              class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition-colors"
            >
              Encerrar Assembleia
            </button>
          </div>
        </div>
      </div>

      <!-- Pautas e Votações Digitais -->
      <div>
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-bold text-slate-900 dark:text-white">Pautas e Deliberações</h2>
          <span class="text-xs text-slate-500">{{ assembly.agendas?.length || 0 }} pautas no edital</span>
        </div>

        <div class="space-y-4">
          <div
            v-for="agenda in assembly.agendas"
            :key="agenda.id"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4"
          >
            <!-- Cabeçalho da Pauta -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
              <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center">
                  #{{ agenda.order }}
                </span>
                <div>
                  <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ agenda.title }}</h3>
                  <p class="text-xs text-slate-500">{{ agenda.description || 'Pauta para deliberação' }}</p>
                </div>
              </div>

              <!-- Status da Pauta e Ações Síndico -->
              <div class="flex items-center gap-2">
                <span
                  class="px-2.5 py-1 rounded-full text-[11px] font-semibold uppercase tracking-wider"
                  :class="getAgendaStatusBadgeClass(agenda.status)"
                >
                  {{ getAgendaStatusLabel(agenda.status) }}
                </span>

                <button
                  v-if="agenda.status === 'pendente' && assembly.status === 'em_andamento'"
                  @click="openAgenda(agenda.id)"
                  class="px-3 py-1 rounded-xl text-xs font-medium bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-200"
                >
                  Abrir Votação
                </button>

                <button
                  v-if="agenda.status === 'aberta'"
                  @click="closeAgenda(agenda.id)"
                  class="px-3 py-1 rounded-xl text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200"
                >
                  Fechar Votação
                </button>
              </div>
            </div>

            <!-- Área de Voto do Morador (Se Aberta) -->
            <div
              v-if="agenda.status === 'aberta'"
              class="bg-blue-50/60 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 rounded-xl p-4 space-y-3"
            >
              <div class="flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300">
                  Registrar Voto da Sua Unidade
                </span>
                <span v-if="agenda.user_has_voted" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Voto computado: {{ agenda.user_vote?.chosen_option }}
                </span>
              </div>

              <!-- Botões de Opção -->
              <div v-if="!agenda.user_has_voted" class="flex flex-wrap gap-2 pt-1">
                <button
                  v-for="opt in getOptionsList(agenda)"
                  :key="opt"
                  @click="castVote(agenda.id, opt)"
                  :disabled="voting"
                  class="px-4 py-2.5 rounded-xl text-xs font-bold border transition-all"
                  :class="getOptionButtonClass(opt)"
                >
                  {{ opt }}
                </button>
              </div>
            </div>

            <!-- Apuração em Tempo Real -->
            <div v-if="agenda.tally" class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
              <div class="flex justify-between items-center text-xs text-slate-500">
                <span class="font-semibold uppercase tracking-wider text-[11px]">Resultado Parcial da Votação</span>
                <span>{{ agenda.tally.total_votes }} votos registrados (Peso: {{ agenda.tally.total_weight }})</span>
              </div>

              <div class="space-y-2">
                <div
                  v-for="(data, optionName) in agenda.tally.breakdown"
                  :key="optionName"
                  class="space-y-1"
                >
                  <div class="flex justify-between text-xs font-medium text-slate-700 dark:text-slate-300">
                    <span>{{ optionName }}</span>
                    <span>{{ data.count }} votos ({{ data.percentage }}%)</span>
                  </div>
                  <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full transition-all duration-500"
                      :class="getProgressBarClass(optionName)"
                      :style="{ width: `${data.percentage}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ata Preliminar Oficial (Se Encerrada) -->
      <div v-if="assembly.status === 'encerrada' && assembly.minutes" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ata da Assembleia</h2>
            <p class="text-xs text-slate-500">Minuta compilada automaticamente com resultados e quórum oficial</p>
          </div>
          <button
            @click="copyMinutes"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors"
          >
            {{ copied ? 'Copiado!' : 'Copiar Texto da Ata' }}
          </button>
        </div>

        <pre class="p-4 bg-slate-50 dark:bg-slate-950/60 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-mono whitespace-pre-wrap leading-relaxed border border-slate-200 dark:border-slate-800">{{ assembly.minutes }}</pre>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AssemblyDetailPage',
  data() {
    return {
      loading: true,
      voting: false,
      copied: false,
      assembly: null,
    };
  },
  computed: {
    assemblyId() {
      return this.$route.params.id;
    },
  },
  mounted() {
    this.fetchAssemblyDetails();
  },
  methods: {
    async fetchAssemblyDetails() {
      this.loading = true;
      try {
        const response = await axios.get(`/api/assemblies/${this.assemblyId}`);
        this.assembly = response.data;
      } catch (err) {
        console.error('Erro ao carregar detalhes da assembleia:', err);
      } finally {
        this.loading = false;
      }
    },
    async startAssembly() {
      if (!confirm('Deseja iniciar as deliberações desta assembleia agora?')) return;
      try {
        await axios.post(`/api/assemblies/${this.assemblyId}/start`);
        await this.fetchAssemblyDetails();
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao iniciar assembleia.');
      }
    },
    async closeAssembly() {
      if (!confirm('Deseja encerrar a assembleia? Todas as pautas abertas serão fechadas e a ata será gerada.')) return;
      try {
        await axios.post(`/api/assemblies/${this.assemblyId}/close`);
        await this.fetchAssemblyDetails();
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao encerrar assembleia.');
      }
    },
    async openAgenda(agendaId) {
      try {
        await axios.post(`/api/assemblies/${this.assemblyId}/agendas/${agendaId}/open`);
        await this.fetchAssemblyDetails();
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao abrir votação.');
      }
    },
    async closeAgenda(agendaId) {
      try {
        await axios.post(`/api/assemblies/${this.assemblyId}/agendas/${agendaId}/close`);
        await this.fetchAssemblyDetails();
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao fechar votação.');
      }
    },
    async castVote(agendaId, option) {
      if (!confirm(`Confirma o registro do seu voto: "${option}"?`)) return;
      this.voting = true;
      try {
        const apartmentId = this.assembly.user_apartment_ids?.[0] || 1;
        await axios.post(`/api/assemblies/${this.assemblyId}/agendas/${agendaId}/vote`, {
          apartment_id: apartmentId,
          chosen_option: option,
        });
        await this.fetchAssemblyDetails();
      } catch (err) {
        alert(err.response?.data?.message || 'Erro ao registrar voto.');
      } finally {
        this.voting = false;
      }
    },
    copyMinutes() {
      if (!this.assembly?.minutes) return;
      navigator.clipboard.writeText(this.assembly.minutes);
      this.copied = true;
      setTimeout(() => {
        this.copied = false;
      }, 2500);
    },
    getOptionsList(agenda) {
      if (Array.isArray(agenda.options) && agenda.options.length > 0) {
        return agenda.options;
      }
      return ['A favor', 'Contra', 'Abstenção'];
    },
    getOptionButtonClass(opt) {
      if (opt.toLowerCase().includes('favor') || opt.toLowerCase().includes('sim')) {
        return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800';
      }
      if (opt.toLowerCase().includes('contra') || opt.toLowerCase().includes('não')) {
        return 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800';
      }
      return 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
    },
    getProgressBarClass(opt) {
      if (opt.toLowerCase().includes('favor') || opt.toLowerCase().includes('sim')) {
        return 'bg-emerald-500';
      }
      if (opt.toLowerCase().includes('contra') || opt.toLowerCase().includes('não')) {
        return 'bg-rose-500';
      }
      return 'bg-blue-500';
    },
    getStatusLabel(status) {
      const map = { agendada: 'Agendada', em_andamento: 'Em Andamento', encerrada: 'Encerrada', cancelada: 'Cancelada' };
      return map[status] || status;
    },
    getStatusBadgeClass(status) {
      const map = {
        agendada: 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-900',
        em_andamento: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900',
        encerrada: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
      };
      return map[status] || 'bg-slate-100 text-slate-600';
    },
    getAgendaStatusLabel(status) {
      const map = { pendente: 'Aguardando Abertura', aberta: 'Votação Aberta', encerrada: 'Concluída' };
      return map[status] || status;
    },
    getAgendaStatusBadgeClass(status) {
      const map = {
        pendente: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
        aberta: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        encerrada: 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
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
