<template>
  <AuthenticatedLayout page-title="Painel de Controle de Acesso">
    <div class="space-y-6">
      <HeaderCard
        title="Controle de Acesso da Portaria"
        description="Gerencie as solicitações de entrada e saída de visitantes e veículos."
        icon-path="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
        icon-color="indigo"
      />

      <!-- Botão para Nova Solicitação -->
      <div class="flex justify-end">
        <NeonButton @click="abrirModalNovaSolicitacao" size="md" variant="primary_action">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
          Nova Solicitação de Acesso
        </NeonButton>
      </div>

      <!-- Painel de Solicitações -->
      <SolicitacoesAcessoPainel />

      <!-- Modal para Nova Solicitação -->
      <NovaSolicitacaoModal
        :show="mostrarModalNovaSolicitacao"
        @close="fecharModalNovaSolicitacao"
        @solicitacao-criada="handleSolicitacaoCriada"
      />

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '../../components/layouts/AuthenticatedLayout.vue';
import HeaderCard from '../../components/layouts/HeaderCard.vue'; // Supondo que existe ou será criado
import NeonButton from '../../components/NeonButton.vue';
import SolicitacoesAcessoPainel from '../../components/access_control/SolicitacoesAcessoPainel.vue'; // Será criado
import NovaSolicitacaoModal from '../../components/access_control/NovaSolicitacaoModal.vue'; // Será criado
import { ref } from 'vue';
import { useToast } from '../../composables/useToast';

const { successToast, errorToast } = useToast();

const mostrarModalNovaSolicitacao = ref(false);

const abrirModalNovaSolicitacao = () => {
  mostrarModalNovaSolicitacao.value = true;
};

const fecharModalNovaSolicitacao = () => {
  mostrarModalNovaSolicitacao.value = false;
};

const handleSolicitacaoCriada = () => {
  successToast('Solicitação de acesso criada com sucesso!');
  fecharModalNovaSolicitacao();
  // Aqui você pode adicionar lógica para recarregar o painel de solicitações, se necessário
};

</script>
