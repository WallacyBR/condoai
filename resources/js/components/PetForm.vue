<template>
  <Modal :show="show" @close="close">
    <h2 class="text-2xl font-semibold text-cyan-300 mb-4" v-text="title" />
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Nome</label>
        <input v-model="form.name" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Espécie</label>
        <input v-model="form.species" required class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Raça</label>
        <input v-model="form.breed" class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div>
        <label class="block text-sm text-cyan-300 mb-1">Foto</label>
        <input v-model="form.photo_path" class="w-full rounded-2xl px-4 py-2 bg-gray-800 border-2 border-cyan-500/20 text-gray-100" />
      </div>
      <div class="flex justify-end pt-4 space-x-2">
        <NeonButton type="submit">Salvar</NeonButton>
        <NeonButton type="button" @click="close" extraClass="bg-red-600/40">Cancelar</NeonButton>
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
  pet: { type: Object, default: null },
  apartmentId: { type: Number, required: true }
});
const emit = defineEmits(['close', 'saved']);

const defaultForm = () => ({
  name: '',
  species: '',
  breed: '',
  photo_path: '',
  apartment_id: props.apartmentId
});

const form = ref(defaultForm());

watch(() => props.pet, (value) => {
  form.value = value ? { ...value } : defaultForm();
});

const title = computed(() => props.pet ? 'Editar Pet' : 'Novo Pet');

const submit = async () => {
  const token = localStorage.getItem('token');
  const url = props.pet ? `/api/pets/${props.pet.id}` : '/api/pets';
  const method = props.pet ? 'PUT' : 'POST';
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
