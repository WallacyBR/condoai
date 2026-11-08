<template>
  <AuthenticatedLayout page-title="Logs de Acesso">
    <div class="space-y-6">
      <TopCard title="Logs de Acesso" subtitle="Auditoria de autenticação e uso de passkeys" :icon="LogIcon" />

      <section class="grid gap-3 rounded-lg border border-gray-700 bg-gray-900/70 p-4 md:grid-cols-5">
        <input v-model="filters.search" class="security-input" placeholder="Usuário ou email" />
        <select v-model="filters.method" class="security-input">
          <option value="">Método</option>
          <option value="password">Senha</option>
          <option value="passkey">Passkey</option>
          <option value="logout">Logout</option>
        </select>
        <select v-model="filters.status" class="security-input">
          <option value="">Status</option>
          <option value="success">Sucesso</option>
          <option value="failed">Falha</option>
        </select>
        <input v-model="filters.start_date" class="security-input" type="date" />
        <NeonButton :loading="loading" @click="loadLogs">Filtrar</NeonButton>
      </section>

      <section v-if="error" class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
        {{ error }}
      </section>

      <section class="overflow-x-auto rounded-lg border border-gray-700 bg-gray-900/70">
        <table class="w-full min-w-[860px] text-sm">
          <thead class="bg-gray-950/60 text-left text-gray-400">
            <tr>
              <th class="p-3">Data</th>
              <th class="p-3">Usuário</th>
              <th class="p-3">Método</th>
              <th class="p-3">Status</th>
              <th class="p-3">IP</th>
              <th class="p-3">Motivo</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id" class="border-t border-gray-800 text-gray-200">
              <td class="p-3">{{ date(log.occurred_at) }}</td>
              <td class="p-3">
                <p>{{ log.user?.name || 'Não identificado' }}</p>
                <p class="text-xs text-gray-500">{{ log.email }}</p>
              </td>
              <td class="p-3">{{ methodLabel(log.method) }}</td>
              <td class="p-3">
                <span :class="log.status === 'success' ? 'bg-emerald-500/10 text-emerald-200' : 'bg-red-500/10 text-red-200'" class="rounded-full px-2 py-1 text-xs">
                  {{ log.status === 'success' ? 'Sucesso' : 'Falha' }}
                </span>
              </td>
              <td class="p-3">{{ log.ip_address || '-' }}</td>
              <td class="p-3 text-gray-400">{{ log.reason || '-' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="!logs.length && !loading" class="py-16 text-center text-gray-400">Nenhum log encontrado.</div>
        <div v-if="loading" class="py-16 text-center text-gray-400">Carregando logs...</div>
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

const LogIcon = { render: () => h('span', { class: 'text-cyan-300' }, 'LOG') };
const logs = ref([]);
const loading = ref(false);
const error = ref('');
const filters = ref({ search: '', method: '', status: '', start_date: '', end_date: '' });
const date = (value) => new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value));
const methodLabel = (method) => ({ password: 'Senha', passkey: 'Passkey', logout: 'Logout', failed: 'Falha' }[method] || method);

async function loadLogs() {
  loading.value = true;
  error.value = '';
  const params = new URLSearchParams();
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value) params.set(key, value);
  });

  try {
    const response = await apiRequest(`/security/access-logs?${params.toString()}`);
    logs.value = response.data || [];
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível carregar os logs.';
  } finally {
    loading.value = false;
  }
}

onMounted(loadLogs);
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
