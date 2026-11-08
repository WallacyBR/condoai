<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Contas</h1>
    <ul>
      <li v-for="account in accounts" :key="account.id" class="mb-2">
        {{ account.name }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAccountsStore } from './stores/accountsStore';

const store = useAccountsStore();
const { accounts } = store;

onMounted(() => {
  store.fetchAccounts();
});
</script>
