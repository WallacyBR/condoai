<template>
  <AuthenticatedLayout page-title="Escolha o Condomínio">
    <CondominiumList selectable @selected="selectCondominium" />
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';
import CondominiumList from './CondominiumList.vue';
import { useRouter } from 'vue-router';

export default {
  name: 'CondominiumSelectView',
  components: { AuthenticatedLayout, CondominiumList },
  setup() {
    const router = useRouter();

    const selectCondominium = (condo) => {
      localStorage.setItem('selectedCondominium', condo.id);
      localStorage.setItem('selectedCondominiumName', condo.name);
      router.push('/dashboard');
    };

    return { selectCondominium };
  }
};
</script>
