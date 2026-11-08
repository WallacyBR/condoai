<template>
  <Modal :show="show" @close="close">
    <h2 class="text-2xl font-semibold text-cyan-300 mb-4" v-text="title" />
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Nome</label>
        <input v-model="form.name" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Documento</label>
        <input v-model="form.document" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Endereço</label>
        <input v-model="form.address" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div class="flex space-x-2">
        <div class="flex-1">
          <label class="block text-sm text-cyan-300 mb-1">Cidade</label>
          <input v-model="form.city" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
        </div>
        <div class="w-20">
          <label class="block text-sm text-cyan-300 mb-1">UF</label>
          <input v-model="form.state" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
        </div>
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">CEP</label>
        <input v-model="form.zip_code" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Telefone</label>
        <input v-model="form.phone" class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Email</label>
        <input v-model="form.email" type="email" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Responsável</label>
        <input v-model="form.responsible" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Domínio</label>
        <input v-model="form.domain" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div class="flex items-center space-x-2">
        <input id="active" type="checkbox" v-model="form.active" class="rounded" />
        <label for="active" class="text-sm text-cyan-300">Ativo</label>
      </div>
      <div class="flex justify-end pt-4 space-x-2">
        <NeonButton type="submit">Salvar</NeonButton>
                    <NeonButton type="button" @click="close" variant="cancel">Cancelar</NeonButton>
      </div>
    </form>
  </Modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import Modal from './Modal.vue';
import NeonButton from './NeonButton.vue';

const props = defineProps({
  show: { type: Boolean, default: false },
  condominium: { type: Object, default: null }
});
const emit = defineEmits(['close', 'saved']);

const defaultForm = () => ({
  name: '',
  document: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  phone: '',
  email: '',
  responsible: '',
  domain: '',
  active: true
});

const form = ref(defaultForm());

watch(() => props.condominium, (value) => {
  form.value = value ? { ...value } : defaultForm();
});

const title = computed(() => props.condominium ? 'Editar Condomínio' : 'Novo Condomínio');

const submit = async () => {
  const token = localStorage.getItem('token');
      const url = props.condominium ? `/condominiums/${props.condominium.id}` : '/condominiums';
  const method = props.condominium ? 'PUT' : 'POST';
  const response = await fetch(url, {
    method,
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(form.value)
  });
  if (response.ok) emit('saved');
};

const close = () => emit('close');
</script>
