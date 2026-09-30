<template>
  <AuthenticatedLayout page-title="Configurações">
    <div class="space-y-6">
      <div v-if="feedback.message" :class="feedback.success ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-100' : 'border-rose-500/40 bg-rose-500/10 text-rose-100'" class="rounded-2xl border px-4 py-3 text-sm shadow-lg backdrop-blur-xl">
        {{ feedback.message }}
      </div>

      <!-- Profile Settings -->
      <div class="overflow-hidden rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/90 via-slate-800/90 to-slate-900/90 shadow-2xl backdrop-blur-xl">
        <div class="border-b border-cyan-500/20 px-6 py-4">
          <h3 class="text-lg font-semibold text-white">Perfil</h3>
          <p class="mt-1 text-sm text-slate-300">Dados carregados diretamente do perfil autenticado no backend.</p>
        </div>
        <div class="p-6">
          <div v-if="loadingProfile" class="py-10 text-center text-sm text-slate-300">Carregando dados do perfil...</div>

          <div v-else class="flex items-center space-x-6">
            <div class="flex-shrink-0">
              <div class="flex h-24 w-24 items-center justify-center rounded-full border border-cyan-500/30 bg-cyan-500/10">
                <span class="text-2xl font-semibold text-cyan-200">{{ userInitials }}</span>
              </div>
            </div>
            <div class="flex-1">
              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-200">Nome</label>
                  <input v-model="profile.name" type="text" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-200">Email</label>
                  <input v-model="profile.email" type="email" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-200">Telefone</label>
                  <input v-model="profile.phone" type="tel" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
                </div>
                <div>
                  <label class="mb-1 block text-sm font-medium text-slate-200">Cargo</label>
                  <input :value="profile.role || 'Não informado'" type="text" disabled class="w-full cursor-not-allowed rounded-2xl border border-slate-700 bg-slate-900/60 px-4 py-2 text-slate-400" />
                </div>
              </div>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button :disabled="loadingProfile" @click="updateProfile" class="rounded-2xl bg-gradient-to-r from-cyan-500 to-fuchsia-500 px-4 py-2 font-medium text-slate-950 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
              Salvar alterações
            </button>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div class="overflow-hidden rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/90 via-slate-800/90 to-slate-900/90 shadow-2xl backdrop-blur-xl">
        <div class="border-b border-cyan-500/20 px-6 py-4">
          <h3 class="text-lg font-semibold text-white">Segurança</h3>
        </div>
        <div class="p-6">
          <div class="space-y-6">
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-200">Senha atual</label>
              <input v-model="security.currentPassword" type="password" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-200">Nova senha</label>
              <input v-model="security.newPassword" type="password" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
            </div>
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-200">Confirmar nova senha</label>
              <input v-model="security.confirmPassword" type="password" class="w-full rounded-2xl border border-cyan-500/20 bg-slate-950/60 px-4 py-2 text-white focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500/30" />
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button @click="updatePassword" class="rounded-2xl bg-gradient-to-r from-cyan-500 to-fuchsia-500 px-4 py-2 font-medium text-slate-950 transition hover:opacity-90">
              Atualizar senha
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="overflow-hidden rounded-3xl border border-cyan-500/20 bg-gradient-to-br from-slate-900/90 via-slate-800/90 to-slate-900/90 shadow-2xl backdrop-blur-xl">
        <div class="border-b border-cyan-500/20 px-6 py-4">
          <h3 class="text-lg font-semibold text-white">Notificações</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="flex items-center justify-between rounded-2xl border border-cyan-500/10 bg-slate-950/40 px-4 py-3">
              <div>
                <h4 class="text-sm font-medium text-white">Notificações por email</h4>
                <p class="text-sm text-slate-400">Receba atualizações importantes por email.</p>
              </div>
              <label class="relative inline-flex cursor-pointer items-center">
                <input v-model="notifications.email" type="checkbox" class="peer sr-only">
                <div class="h-6 w-11 rounded-full bg-slate-700 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-400 after:bg-white after:transition-all peer-checked:bg-cyan-500 peer-checked:after:translate-x-full"></div>
              </label>
            </div>
            <div class="flex items-center justify-between rounded-2xl border border-cyan-500/10 bg-slate-950/40 px-4 py-3">
              <div>
                <h4 class="text-sm font-medium text-white">Notificações push</h4>
                <p class="text-sm text-slate-400">Receba notificações em tempo real.</p>
              </div>
              <label class="relative inline-flex cursor-pointer items-center">
                <input v-model="notifications.push" type="checkbox" class="peer sr-only">
                <div class="h-6 w-11 rounded-full bg-slate-700 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-400 after:bg-white after:transition-all peer-checked:bg-cyan-500 peer-checked:after:translate-x-full"></div>
              </label>
            </div>
            <div class="flex items-center justify-between rounded-2xl border border-cyan-500/10 bg-slate-950/40 px-4 py-3">
              <div>
                <h4 class="text-sm font-medium text-white">Notificações SMS</h4>
                <p class="text-sm text-slate-400">Receba alertas importantes por SMS.</p>
              </div>
              <label class="relative inline-flex cursor-pointer items-center">
                <input v-model="notifications.sms" type="checkbox" class="peer sr-only">
                <div class="h-6 w-11 rounded-full bg-slate-700 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-400 after:bg-white after:transition-all peer-checked:bg-cyan-500 peer-checked:after:translate-x-full"></div>
              </label>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button @click="updateNotifications" class="rounded-2xl bg-gradient-to-r from-cyan-500 to-fuchsia-500 px-4 py-2 font-medium text-slate-950 transition hover:opacity-90">
              Salvar preferências
            </button>
          </div>
        </div>
      </div>

      <PWASettings />
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { computed, onMounted, reactive, ref } from 'vue';
import api from '../utils/api';
import auth from '../services/auth';
import { formatApiValidationError } from '../utils/helpers';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import PWASettings from './PWASettings.vue';

export default {
  name: 'SettingsView',
  components: {
    AuthenticatedLayout,
    PWASettings
  },
  setup() {
    const loadingProfile = ref(true);
    const feedback = reactive({
      message: '',
      success: true
    });

    const profile = reactive({
      name: '',
      email: '',
      phone: '',
      role: ''
    });

    const security = reactive({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    });

    const notifications = reactive({
      email: true,
      push: true,
      sms: false
    });

    const setFeedback = (message, success = true) => {
      feedback.message = message;
      feedback.success = success;
    };

    const applyProfileData = data => {
      profile.name = data.name || '';
      profile.email = data.email || '';
      profile.phone = data.phone || '';
      profile.role = data.role || '';
      notifications.email = Boolean(data.notifications?.email);
      notifications.push = Boolean(data.notifications?.push);
      notifications.sms = Boolean(data.notifications?.sms);
    };

    const loadProfile = async () => {
      loadingProfile.value = true;
      try {
        const { data } = await api.get('/profile');
        applyProfileData(data);
      } catch (error) {
        console.error('Erro ao carregar perfil:', error);
        setFeedback('Não foi possível carregar as configurações do usuário.', false);
      } finally {
        loadingProfile.value = false;
      }
    };

    const userInitials = computed(() => {
      if (!profile.name) {
        return '??';
      }

      return profile.name
        .split(' ')
        .filter(Boolean)
        .map(name => name[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
    });

    const updateProfile = async () => {
      try {
        const { data } = await api.put('/profile', {
          name: profile.name,
          email: profile.email,
          phone: profile.phone || null
        });

        applyProfileData(data);
        setFeedback('Perfil atualizado com sucesso.');
      } catch (error) {
        console.error('Erro ao atualizar perfil:', error);
        setFeedback(formatApiValidationError(error, 'Erro ao atualizar o perfil.'), false);
      }
    };

    const updatePassword = async () => {
      if (security.newPassword !== security.confirmPassword) {
        setFeedback('As senhas informadas não coincidem.', false);
        return;
      }

      try {
        const { data } = await api.put('/profile/password', {
          current_password: security.currentPassword,
          new_password: security.newPassword,
          new_password_confirmation: security.confirmPassword
        });

        security.currentPassword = '';
        security.newPassword = '';
        security.confirmPassword = '';
        setFeedback(data?.message || 'Senha atualizada com sucesso. Faça login novamente.');
        await auth.logout();
      } catch (error) {
        console.error('Erro ao atualizar senha:', error);
        setFeedback(formatApiValidationError(error, 'Erro ao atualizar a senha.'), false);
      }
    };

    const updateNotifications = async () => {
      try {
        await api.put('/profile/notifications', { ...notifications });
        setFeedback('Preferências de notificação atualizadas com sucesso.');
      } catch (error) {
        console.error('Erro ao atualizar notificações:', error);
        setFeedback(formatApiValidationError(error, 'Erro ao atualizar as notificações.'), false);
      }
    };

    onMounted(loadProfile);

    return {
      feedback,
      loadingProfile,
      notifications,
      profile,
      security,
      updateNotifications,
      updatePassword,
      updateProfile,
      userInitials
    };
  }
};
</script>
