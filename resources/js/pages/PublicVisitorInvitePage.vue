<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex flex-col items-center justify-center p-4">
    <!-- Card Principal -->
    <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden">
      <!-- Topo Header com Status -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-white text-center relative">
        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
          </svg>
        </div>
        <h1 class="text-xl font-bold tracking-tight">Convite Digital de Acesso</h1>
        <p class="text-xs text-blue-100 mt-1">{{ invite?.condominium?.name || 'Condomínio Residencial' }}</p>

        <span
          class="inline-block mt-3 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider"
          :class="statusBadgeClass"
        >
          {{ statusLabel }}
        </span>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full mx-auto mb-3"></div>
        <p class="text-sm text-slate-500 dark:text-slate-400">Carregando convite seguro...</p>
      </div>

      <!-- Erro / Não encontrado -->
      <div v-else-if="error" class="p-8 text-center">
        <div class="w-12 h-12 bg-red-100 dark:bg-red-950 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h2 class="text-base font-semibold text-slate-900 dark:text-white">Convite não encontrado</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ error }}</p>
      </div>

      <!-- Conteúdo do Convite -->
      <div v-else-if="invite" class="p-6 space-y-6">
        <!-- QR Code Express -->
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-5 border border-slate-100 dark:border-slate-800 flex flex-col items-center">
          <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
            Apresente na portaria para entrada expressa
          </p>
          <div class="p-3 bg-white rounded-xl shadow-sm border border-slate-200">
            <!-- QR Code renderizado via SVG nativo de dados -->
            <img
              :src="qrCodeUrl"
              alt="QR Code de Acesso"
              class="w-48 h-48 object-contain"
            />
          </div>
          <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-2 font-mono">
            Token: {{ formatToken(invite.invite_token) }}
          </p>
        </div>

        <!-- Detalhes da Visita -->
        <div class="space-y-3">
          <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400">Visitante</span>
            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ invite.visitor_name }}</span>
          </div>

          <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400">Unidade Destino</span>
            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">
              Apto {{ invite.apartment?.number || '---' }}
              <span v-if="invite.apartment?.block" class="text-xs font-normal text-slate-400">({{ invite.apartment.block }})</span>
            </span>
          </div>

          <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400">Anfitrião</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ invite.resident_name || 'Morador' }}</span>
          </div>

          <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-500 dark:text-slate-400">Data e Hora</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
              {{ formatDate(invite.visit_date) }} às {{ invite.visit_time || '14:00' }}
            </span>
          </div>

          <div v-if="invite.purpose" class="flex justify-between items-center py-2">
            <span class="text-xs text-slate-500 dark:text-slate-400">Finalidade</span>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ invite.purpose }}</span>
          </div>
        </div>

        <!-- Instruções -->
        <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 rounded-xl p-3 text-xs text-blue-700 dark:text-blue-300">
          <strong>Atenção:</strong> Apresente este passe digital ao operador ou aproxime o QR Code do totem de autoatendimento da portaria. Tenha em mãos um documento com foto.
        </div>
      </div>
    </div>

    <!-- Rodapé -->
    <p class="text-xs text-slate-400 mt-6 text-center">
      Powered by PrediHub • Sistema Inteligente de Gestão Condominial
    </p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'PublicVisitorInvitePage',
  data() {
    return {
      loading: true,
      error: null,
      invite: null,
    };
  },
  computed: {
    token() {
      return this.$route.params.token;
    },
    qrCodeUrl() {
      if (!this.invite?.invite_token) return '';
      // Gera URL rápida do QR code com alta resolução
      const data = encodeURIComponent(`PREDIHUB_INVITE:${this.invite.invite_token}`);
      return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${data}&margin=10`;
    },
    statusLabel() {
      const map = {
        authorized: 'Acesso Autorizado',
        pending: 'Aguardando Aprovação',
        rejected: 'Acesso Não Autorizado',
        completed: 'Visita Concluída',
      };
      return map[this.invite?.status] || 'Autorizado';
    },
    statusBadgeClass() {
      const map = {
        authorized: 'bg-emerald-400/20 text-emerald-100 border border-emerald-400/40',
        pending: 'bg-amber-400/20 text-amber-100 border border-amber-400/40',
        rejected: 'bg-rose-400/20 text-rose-100 border border-rose-400/40',
        completed: 'bg-slate-400/20 text-slate-100 border border-slate-400/40',
      };
      return map[this.invite?.status] || 'bg-white/20 text-white border border-white/30';
    },
  },
  mounted() {
    this.fetchInvite();
  },
  methods: {
    async fetchInvite() {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.get(`/api/public/invites/${this.token}`);
        this.invite = response.data;
      } catch (err) {
        this.error = err.response?.data?.message || 'Não foi possível validar este convite. Verifique o link informado.';
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('pt-BR');
      } catch (e) {
        return dateStr;
      }
    },
    formatToken(token) {
      if (!token) return '';
      return `${token.substring(0, 8)}...${token.substring(token.length - 6)}`;
    },
  },
};
</script>
