<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    endpoint: { type: String, required: true }
});

const items = ref([]);

onMounted(async () => {
    const response = await axios.get(props.endpoint);
    items.value = response.data;
});
</script>

<template>
  <table class="table" v-if="items.length">
    <thead>
      <tr>
        <th v-for="(value, key) in items[0]" :key="key">{{ key }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <td v-for="(value, key) in item" :key="key">{{ value }}</td>
      </tr>
    </tbody>
  </table>
  <p v-else>Nenhum registro encontrado.</p>
</template>
