<template>
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Lançamentos</h1>
    <ul>
      <li v-for="trx in transactions" :key="trx.id" class="mb-2">
        {{ trx.description }} - {{ trx.amount }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useTransactionsStore } from './stores/transactionsStore';

const store = useTransactionsStore();
const { transactions } = store;

onMounted(() => {
  store.fetchTransactions();
});
</script>
