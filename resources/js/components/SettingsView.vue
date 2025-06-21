<template>
  <AuthenticatedLayout page-title="Configurações">
    <div class="space-y-6">
      <!-- Profile Settings -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Perfil</h3>
        </div>
        <div class="p-6">
          <div class="flex items-center space-x-6">
            <div class="flex-shrink-0">
              <div class="h-24 w-24 rounded-full bg-indigo-100 flex items-center justify-center">
                <span class="text-2xl text-indigo-600 font-medium">{{ userInitials }}</span>
              </div>
            </div>
            <div class="flex-1">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                  <input
                    type="text"
                    v-model="profile.name"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input
                    type="email"
                    v-model="profile.email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                  <input
                    type="tel"
                    v-model="profile.phone"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                  <input
                    type="text"
                    v-model="profile.role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button
              @click="updateProfile"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
            >
              Salvar Alterações
            </button>
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Segurança</h3>
        </div>
        <div class="p-6">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Senha Atual</label>
              <input
                type="password"
                v-model="security.currentPassword"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
              <input
                type="password"
                v-model="security.newPassword"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
              <input
                type="password"
                v-model="security.confirmPassword"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button
              @click="updatePassword"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
            >
              Atualizar Senha
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Notificações</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-medium text-gray-900">Notificações por Email</h4>
                <p class="text-sm text-gray-500">Receba atualizações importantes por email</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.email" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-medium text-gray-900">Notificações Push</h4>
                <p class="text-sm text-gray-500">Receba notificações em tempo real</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.push" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-medium text-gray-900">Notificações SMS</h4>
                <p class="text-sm text-gray-500">Receba alertas importantes por SMS</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="notifications.sms" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
          </div>
          <div class="mt-6 flex justify-end">
            <button
              @click="updateNotifications"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200"
            >
              Salvar Preferências
            </button>
          </div>
        </div>
      </div>

      <!-- PWA Settings -->
      <PWASettings />
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, reactive, computed } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import PWASettings from './PWASettings.vue';

export default {
  name: 'SettingsView',
  components: { 
    AuthenticatedLayout,
    PWASettings
  },
  setup() {
    const profile = reactive({
      name: 'Usuário Exemplo',
      email: 'usuario@exemplo.com',
      phone: '(11) 99999-9999',
      role: 'Administrador'
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

    const userInitials = computed(() => {
      return profile.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase();
    });

    const updateProfile = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/profile', {
          method: 'PUT',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(profile)
        });
        
        if (response.ok) {
          // Mostrar mensagem de sucesso
          console.log('Perfil atualizado com sucesso');
        } else {
          // Mostrar mensagem de erro
          console.error('Erro ao atualizar perfil');
        }
      } catch (e) {
        console.error('Erro de conexão:', e);
      }
    };

    const updatePassword = async () => {
      if (security.newPassword !== security.confirmPassword) {
        // Mostrar mensagem de erro
        console.error('As senhas não coincidem');
        return;
      }

      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/profile/password', {
          method: 'PUT',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            current_password: security.currentPassword,
            new_password: security.newPassword
          })
        });
        
        if (response.ok) {
          // Limpar campos
          security.currentPassword = '';
          security.newPassword = '';
          security.confirmPassword = '';
          // Mostrar mensagem de sucesso
          console.log('Senha atualizada com sucesso');
        } else {
          // Mostrar mensagem de erro
          console.error('Erro ao atualizar senha');
        }
      } catch (e) {
        console.error('Erro de conexão:', e);
      }
    };

    const updateNotifications = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/profile/notifications', {
          method: 'PUT',
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(notifications)
        });
        
        if (response.ok) {
          // Mostrar mensagem de sucesso
          console.log('Preferências de notificação atualizadas com sucesso');
        } else {
          // Mostrar mensagem de erro
          console.error('Erro ao atualizar preferências de notificação');
        }
      } catch (e) {
        console.error('Erro de conexão:', e);
      }
    };

    return {
      profile,
      security,
      notifications,
      userInitials,
      updateProfile,
      updatePassword,
      updateNotifications
    };
  }
};
</script> 