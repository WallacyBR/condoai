<template>
  <form @submit.prevent="submit" class="space-y-2 p-4">
    <div>
      <label class="block text-sm">Início</label>
      <input type="datetime-local" v-model="start" class="border rounded w-full" />
    </div>
    <div>
      <label class="block text-sm">Fim</label>
      <input type="datetime-local" v-model="end" class="border rounded w-full" />
    </div>
    <button class="bg-cyan-600 text-white px-3 py-1 rounded">Reservar</button>
    <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
  </form>
</template>

<script>
import { ref } from 'vue';
export default {
  name: 'CommonAreaBookingForm',
  props: { areaId: { type: Number, required: true }, apartmentId: { type: Number, required: true } },
  emits: ['created'],
  setup(props, { emit }) {
    const start = ref('');
    const end = ref('');
    const error = ref('');
    const token = localStorage.getItem('token');

    const submit = async () => {
      error.value = '';
      const resp = await fetch('/api/common-area-bookings', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          common_area_id: props.areaId,
          apartment_id: props.apartmentId,
          start_at: start.value,
          end_at: end.value
        })
      });
      if (resp.ok) {
        emit('created');
        start.value = end.value = '';
      } else {
        const data = await resp.json();
        error.value = data.message || 'Erro ao reservar';
      }
    };

    return { start, end, error, submit };
  }
};
</script>
