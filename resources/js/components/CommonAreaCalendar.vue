<template>
  <div class="ml-4 border border-cyan-600 p-2 rounded-lg">
    <CommonAreaBookingForm @submit="submitBooking" />
    <ul class="text-sm text-cyan-100 list-disc ml-4">
      <li v-for="b in bookings" :key="b.id">
        {{ new Date(b.start_time).toLocaleString() }} - {{ new Date(b.end_time).toLocaleString() }}
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import CommonAreaBookingForm from './CommonAreaBookingForm.vue';

export default {
  name: 'CommonAreaCalendar',
  props: { area: { type: Object, required: true } },
  setup(props) {
    const token = localStorage.getItem('token');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const bookings = ref([]);
    const condo = localStorage.getItem('selectedCondominium');

    const loadBookings = async () => {
      const resp = await fetch(`/api/common-areas/${props.area.id}/bookings`, { headers });
      bookings.value = resp.ok ? await resp.json() : [];
    };

    const submitBooking = async (form) => {
      const apartment = localStorage.getItem('selectedApartment');
      if (!apartment) return;
      form.apartment_id = apartment;
      const resp = await fetch(`/api/common-areas/${props.area.id}/bookings`, {
        method: 'POST',
        headers,
        body: JSON.stringify(form)
      });
      if (resp.ok) {
        bookings.value.push(await resp.json());
      }
    };

    onMounted(loadBookings);

    return { bookings, submitBooking };
  }
};
</script>
