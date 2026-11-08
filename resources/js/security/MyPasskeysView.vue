<template>
  <AuthenticatedLayout page-title="Minhas Passkeys">
    <div class="space-y-6">
      <TopCard title="Minhas Passkeys" subtitle="Credenciais de acesso cadastradas neste usuário" :icon="SecurityIcon">
        <template #actions>
          <NeonButton :loading="creating" :disabled="creating || !supportsPasskeys" @click="createPasskey">
            Nova passkey
          </NeonButton>
        </template>
      </TopCard>

      <section class="rounded-lg border border-gray-700 bg-gray-900/70 p-4">
        <label class="text-sm text-gray-300">
          Nome da nova passkey
          <input v-model="newName" class="security-input mt-1" placeholder="Ex.: MacBook, iPhone, Windows Hello" />
        </label>
      </section>

      <section v-if="error" class="rounded-lg border border-red-500/40 bg-red-500/10 p-4 text-sm text-red-200">
        {{ error }}
      </section>
      <section v-if="success" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-200">
        {{ success }}
      </section>

      <section v-if="loading" class="rounded-lg border border-gray-700 bg-gray-900/60 py-16 text-center text-gray-400">
        Carregando passkeys...
      </section>

      <section v-else-if="passkeys.length" class="grid gap-4 md:grid-cols-2">
        <article v-for="passkey in passkeys" :key="passkey.id" class="rounded-lg border border-gray-700 bg-gray-900/70 p-5">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-white">{{ passkey.name }}</h2>
              <p class="mt-1 text-sm text-gray-400">Criada em {{ date(passkey.created_at) }}</p>
              <p class="mt-1 text-sm text-gray-400">Último uso: {{ passkey.last_used_at ? date(passkey.last_used_at) : 'Nunca usada' }}</p>
            </div>
            <span :class="statusClass(passkey)" class="rounded-full px-3 py-1 text-xs font-semibold">
              {{ passkey.revoked_at ? 'Revogada' : 'Ativa' }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap gap-2 text-xs text-gray-300">
            <span v-for="transport in passkey.transports || []" :key="transport" class="rounded-full bg-cyan-500/10 px-2 py-1 text-cyan-200">
              {{ transport }}
            </span>
          </div>

          <div class="mt-5 flex justify-end">
            <NeonButton v-if="!passkey.revoked_at" size="sm" variant="danger" @click="revoke(passkey)">
              Excluir
            </NeonButton>
          </div>
        </article>
      </section>

      <section v-else class="rounded-lg border border-gray-700 bg-gray-900/60 py-16 text-center text-gray-400">
        Nenhuma passkey cadastrada.
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
import { browserSupportsPasskeys, createPasskeyCredential, passkeyOriginProblem } from '../utils/webauthn';

const SecurityIcon = { render: () => h('span', { class: 'text-cyan-300' }, 'PK') };
const passkeys = ref([]);
const loading = ref(false);
const creating = ref(false);
const error = ref('');
const success = ref('');
const newName = ref('');
const supportsPasskeys = ref(false);
const passkeyHelp = ref('');

const date = (value) => new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(value));
const statusClass = (passkey) => passkey.revoked_at ? 'bg-red-500/10 text-red-200' : 'bg-emerald-500/10 text-emerald-200';

async function loadPasskeys() {
  loading.value = true;
  error.value = '';
  try {
    passkeys.value = await apiRequest('/security/passkeys');
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível carregar as passkeys.';
  } finally {
    loading.value = false;
  }
}

async function createPasskey() {
  if (!supportsPasskeys.value) {
    error.value = passkeyHelp.value || 'Este navegador ou origem não permite criar passkeys neste momento.';
    return;
  }

  creating.value = true;
  error.value = '';
  success.value = '';

  try {
    const options = await apiRequest('/security/passkeys/register/options', {
      method: 'POST',
      body: JSON.stringify({ name: newName.value || null })
    });
    const credential = await createPasskeyCredential(options.publicKey);
    await apiRequest('/security/passkeys/register/verify', {
      method: 'POST',
      body: JSON.stringify({
        token: options.token,
        name: newName.value || null,
        credential
      })
    });
    newName.value = '';
    success.value = 'Passkey criada com sucesso.';
    await loadPasskeys();
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível criar a passkey.';
  } finally {
    creating.value = false;
  }
}

async function revoke(passkey) {
  error.value = '';
  success.value = '';
  try {
    await apiRequest(`/security/passkeys/${passkey.id}`, { method: 'DELETE' });
    success.value = 'Passkey excluída.';
    await loadPasskeys();
  } catch (requestError) {
    error.value = requestError.message || 'Não foi possível excluir a passkey.';
  }
}

onMounted(() => {
  passkeyHelp.value = passkeyOriginProblem();
  supportsPasskeys.value = browserSupportsPasskeys();
  if (passkeyHelp.value) {
    error.value = passkeyHelp.value;
  }
  loadPasskeys();
});
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
