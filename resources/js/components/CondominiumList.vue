<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Condomínios</h2>
    <ul>
      <li v-for="condo in condominiums" :key="condo.id" class="mb-2">
        {{ condo.name }} - {{ condo.city }}/{{ condo.state }}
      </li>
    </ul>
    <p v-if="error" class="text-red-600">{{ error }}</p>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'CondominiumList',
  setup() {
    const condominiums = ref([]);
    const error = ref('');

    const loadData = async () => {
      try {
        const token = localStorage.getItem('token');
        const response = await fetch('/api/condominiums', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        if (response.ok) {
          condominiums.value = await response.json();
        } else {
          error.value = 'Falha ao carregar dados';
        }
      } catch (e) {
        error.value = 'Erro de conexão';
      }
    };

    onMounted(loadData);

    return { condominiums, error };
  }
};
</script>
