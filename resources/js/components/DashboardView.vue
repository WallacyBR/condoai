<template>
  <div class="p-4">
    <button @click="logout" class="mb-4 px-4 py-2 bg-indigo-600 text-white rounded">Sair</button>
    <CondominiumList />
  </div>
</template>

<script>
import CondominiumList from './CondominiumList.vue';

export default {
  name: 'DashboardView',
  components: { CondominiumList },
  setup() {
    const logout = () => {
      localStorage.removeItem('token');
      window.location.href = '/';
    };
    return { logout };
  }
};
</script>
