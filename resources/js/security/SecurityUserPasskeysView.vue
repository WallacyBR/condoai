<template>
  <AuthenticatedLayout page-title="Passkeys dos Usuários">
    <div class="space-y-6">
      <TopCard title="Passkeys dos Usuários" subtitle="Gestão administrativa de credenciais do condomínio" :icon="UsersIcon" />

      <section class="grid gap-3 rounded-lg border border-gray-700 bg-gray-900/70 p-4 md:grid-cols-[1fr_auto]">
        <select v-model="selectedUserId" class="security-input">
          <option value="">Selecione um usuário</option>
          <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }} - {{ user.email }}</option>
        </select>
        <NeonButton :loading="loadingPasskeys" @click="loadUserPasskeys">Consultar</NeonButton>
      </section>

      <section v-if="error" class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
        {{ error }}
      </section>
      <section v-if="success" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-200">
        {{ success }}
      </section>

      <section v-if="selectedUser" class="rounded-lg border border-gray-700 bg-gray-900/70 p-5">
        <h2 class="text-lg font-semibold text-white">{{ selectedUser.name }}</h2>
        <p class="text-sm text-gray-400">{{ selectedUser.email }}</p>
      </section>

      <section v-if="passkeys.length" class="grid gap-4 md:grid-cols-2">
        <article v-for="passkey in passkeys" :key="passkey.id" class="rounded-lg border border-gray-700 bg-gray-900/70 p-5">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h3 class="font-semibold text-white">{{ passkey.name }}</h3>
              <p class="mt-1 text-sm text-gray-400">Criada em {{ date(passkey.created_at) }}</p>
              <p class="mt-1 text-sm text-gray-400">Último uso: {{ passkey.last_used_at ? date(passkey.last_used_at) : 'Nunca usada' }}</p>
            </div>
            <span :class="passkey.revoked_at ? 'bg-red-500/10 text-red-200' : 'bg-emerald-500/10 text-emerald-200'" class="rounded-full px-3 py-1 text-xs font-semibold">
              {{ passkey.revoked_at ? 'Revogada' : 'Ativa' }}
            </span>
          </div>
          <div class="mt-5 flex justify-end">
            <NeonButton v-if="!passkey.revoked_at" size="sm" variant="danger" @click="revoke(passkey)">
              Revogar
            </NeonButton>
          </div>
        </article>
      </section>

      <section v-else-if="selectedUserId && !loadingPasskeys" class="rounded-lg border border-gray-700 bg-gray-900/60 py-16 text-center text-gray-400">
        Nenhuma passkey encontrada para este usuário.
      </section>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { h, onMounted, ref } from 'vue';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import TopCard from '../components/ui/TopCard.vue';
import { apiRequest } from '../utils/api';

const UsersIcon = { render: () => h('span', { class: 'text-cyan-300' }, 'USR') };
const users = ref([]);
const selectedUserId = ref('');
const selectedUser = ref(null);
const passkeys = ref([]);
const loadingUsers = ref(false);
const loadingPasskeys = ref(false);
const error = ref('');
const success = ref('');
const date = (value) => new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value));

async function loadUsers() {
  loadingUsers.value = true;
  error.value = '';
  try {
    const response = await apiRequest('/users?per_page=100');
    users.value = response.data || [];
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível carregar os usuários.';
  } finally {
    loadingUsers.value = false;
  }
}

async function loadUserPasskeys() {
  if (!selectedUserId.value) return;
  loadingPasskeys.value = true;
  error.value = '';
  success.value = '';
  try {
    const response = await apiRequest(`/security/users/${selectedUserId.value}/passkeys`);
    selectedUser.value = response.user;
    passkeys.value = response.passkeys || [];
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível carregar as passkeys do usuário.';
  } finally {
    loadingPasskeys.value = false;
  }
}

async function revoke(passkey) {
  error.value = '';
  success.value = '';
  try {
    await apiRequest(`/security/users/${selectedUserId.value}/passkeys/${passkey.id}`, { method: 'DELETE' });
    await loadUserPasskeys();
    success.value = 'Passkey revogada.';
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível revogar a passkey.';
  }
}

onMounted(loadUsers);
</script>

<style scoped>
.security-input {
  width: 100%;
  border: 1px solid rgb(75 85 99);
  border-radius: .5rem;
  background: rgb(31 41 55);
  color: white;
  padding: .65rem .75rem;
}
</style>
