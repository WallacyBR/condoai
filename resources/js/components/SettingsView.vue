<template>
  <AuthenticatedLayout page-title="Configurações">
    <div class="space-y-6">
      <div 
        v-if="feedback.message" 
        :class="feedback.success 
          ? 'border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200' 
          : 'border-rose-200 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-200'" 
        class="rounded-xl border px-4 py-3 text-xs font-medium shadow-sm"
      >
        {{ feedback.message }}
      </div>

      <!-- Configurações de Perfil -->
      <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 px-6 py-4">
          <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Perfil</h3>
          <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Dados carregados diretamente do perfil autenticado no backend.</p>
        </div>
        <div class="p-6">
          <div v-if="loadingProfile" class="py-10 text-center text-xs text-slate-500 dark:text-slate-400">
            Carregando dados do perfil...
          </div>

          <div v-else class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <div class="shrink-0">
              <div class="flex h-20 w-20 items-center justify-center rounded-full border border-indigo-100 dark:border-indigo-900/50 bg-indigo-50 dark:bg-indigo-950/50">
                <span class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ userInitials }}</span>
              </div>
            </div>
            <div class="flex-1 w-full">
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nome</label>
                  <input 
                    v-model="profile.name" 
                    type="text" 
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Email</label>
                  <input 
                    v-model="profile.email" 
                    type="email" 
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Telefone</label>
                  <input 
                    v-model="profile.phone" 
                    type="tel" 
                    class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
                  />
                </div>
                <div>
                  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Cargo</label>
                  <input 
                    :value="profile.role || 'Não informado'" 
                    type="text" 
                    disabled 
                    class="w-full px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-500 dark:text-slate-400 text-sm cursor-not-allowed" 
                  />
                </div>
              </div>

              <div class="mt-6 flex justify-end">
                <AppButton 
                  :disabled="loadingProfile" 
                  @click="updateProfile" 
                  variant="primary"
                  size="sm"
                >
                  Salvar Alterações
                </AppButton>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Configurações de Segurança -->
      <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 px-6 py-4">
          <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Segurança</h3>
          <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Atualize sua senha de acesso periodicamente.</p>
        </div>
        <div class="p-6">
          <div class="space-y-4 max-w-xl">
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Senha atual</label>
              <input 
                v-model="security.currentPassword" 
                type="password" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
              />
            </div>
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Nova senha</label>
              <input 
                v-model="security.newPassword" 
                type="password" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
              />
            </div>
            <div>
              <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Confirmar nova senha</label>
              <input 
                v-model="security.confirmPassword" 
                type="password" 
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 shadow-sm" 
              />
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <AppButton @click="updatePassword" variant="primary" size="sm">
              Atualizar Senha
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Configurações de Notificações -->
      <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <div class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 px-6 py-4">
          <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Notificações</h3>
          <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Escolha como prefere receber avisos e notificações do sistema.</p>
        </div>
        <div class="p-6">
          <div class="space-y-3">
            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/40 p-4">
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notificações por email</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Receba atualizações importantes diretamente na sua caixa de entrada.</p>
              </div>
              <label class="relative inline-flex cursor-pointer items-center">
                <input v-model="notifications.email" type="checkbox" class="peer sr-only">
                <div class="h-6 w-11 rounded-full bg-slate-300 dark:bg-slate-700 peer-checked:bg-indigo-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full shadow-sm"></div>
              </label>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/40 p-4">
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notificações push</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Receba alertas em tempo real no seu navegador ou dispositivo móvel.</p>
              </div>
              <label class="relative inline-flex cursor-pointer items-center">
                <input v-model="notifications.push" type="checkbox" class="peer sr-only">
                <div class="h-6 w-11 rounded-full bg-slate-300 dark:bg-slate-700 peer-checked:bg-indigo-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full shadow-sm"></div>
              </label>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-800/40 p-4">
              <div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notificações SMS</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Receba avisos urgentes via mensagem SMS.</p>
              </div>
              <label class="relative inline-flex cursor-pointer items-center">
                <input v-model="notifications.sms" type="checkbox" class="peer sr-only">
                <div class="h-6 w-11 rounded-full bg-slate-300 dark:bg-slate-700 peer-checked:bg-indigo-600 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-full shadow-sm"></div>
              </label>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <AppButton @click="updateNotifications" variant="primary" size="sm">
              Salvar Preferências
            </AppButton>
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
import AppButton from './ui/AppButton.vue';
import PWASettings from './PWASettings.vue';

export default {
  name: 'SettingsView',
  components: {
    AuthenticatedLayout,
    AppButton,
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
