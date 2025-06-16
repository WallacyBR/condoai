<template>
  <div class="p-6">
    <h3 class="font-semibold mb-2">Reservas</h3>
    <ul class="space-y-1">
      <li v-for="b in bookings" :key="b.id" class="text-sm">
        {{ new Date(b.start_at).toLocaleString() }} - {{ new Date(b.end_at).toLocaleString() }}
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, watch, onMounted } from 'vue';
export default {
  name: 'CommonAreaCalendar',
  props: { areaId: { type: Number, required: true } },
  setup(props) {
    const bookings = ref([]);
    const token = localStorage.getItem('token');

    const load = async () => {
      const resp = await fetch(`/api/common-area-bookings?common_area_id=${props.areaId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      bookings.value = resp.ok ? await resp.json() : [];
    };

    watch(() => props.areaId, load);
    onMounted(load);
    return { bookings };
  }
};
</script>
