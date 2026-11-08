<template>
  <form @submit.prevent="$emit('submit', form)" class="space-x-2">
    <input v-model="form.start_time" type="datetime-local" class="border rounded px-2" />
    <input v-model="form.end_time" type="datetime-local" class="border rounded px-2" />
    <button type="submit" class="px-2 py-1 bg-cyan-600 text-white rounded">Reservar</button>
  </form>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'CommonAreaBookingForm',
  emits: ['submit'],
  setup() {
    const form = ref({ start_time: '', end_time: '' });
    return { form };
  }
};
</script>
