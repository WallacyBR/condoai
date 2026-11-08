<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="space-y-6 max-w-4xl mx-auto">
      <!-- Header unificado com botão voltar -->
      <PageHeader
        :title="pageTitle"
        :subtitle="pageDescription"
        show-back
        @back="router.push('/vehicles')"
      />

      <!-- Form Card -->
      <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Vehicle Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- License Plate -->
            <div>
              <label for="license_plate" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Placa do Veículo *
              </label>
              <input
                v-model="form.license_plate"
                type="text"
                id="license_plate"
                name="license_plate"
                placeholder="ABC-1234"
                required
                :class="inputClasses"
                @input="formatLicensePlate"
              />
              <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Formato: ABC-1234 ou ABC1234</p>
            </div>

            <!-- Model -->
            <div>
              <label for="model" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Modelo *
              </label>
              <input
                v-model="form.model"
                type="text"
                id="model"
                name="model"
                placeholder="Honda Civic, Toyota Corolla..."
                required
                :class="inputClasses"
              />
            </div>

            <!-- Color -->
            <div>
              <label for="color" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Cor *
              </label>
              <input
                v-model="form.color"
                type="text"
                id="color"
                name="color"
                placeholder="Branco, Preto, Azul..."
                required
                :class="inputClasses"
              />
            </div>

            <!-- Year -->
            <div>
              <label for="year" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                Ano
              </label>
              <input
                v-model="form.year"
                type="number"
                id="year"
                name="year"
                placeholder="2020"
                min="1900"
                :max="new Date().getFullYear() + 1"
                :class="inputClasses"
              />
            </div>
          </div>

          <!-- Apartment Selection -->
          <div>
            <label for="apartment_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Apartamento *
            </label>
            <select
              v-model="form.apartment_id"
              id="apartment_id"
              name="apartment_id"
              required
              :class="selectClasses"
            >
              <option value="">Selecione o apartamento</option>
              <option
                v-for="apartment in apartments"
                :key="apartment.id"
                :value="apartment.id"
              >
                {{ formatApartmentDescription(apartment) }}
              </option>
            </select>
          </div>

          <!-- Parking Spot Selection -->
          <div>
            <label for="parking_spot_id" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Vaga de Estacionamento
            </label>
            <select
              v-model="form.parking_spot_id"
              id="parking_spot_id"
              name="parking_spot_id"
              :class="selectClasses"
            >
              <option value="">Sem vaga fixa</option>
              <option
                v-for="spot in availableParkingSpots"
                :key="spot.id"
                :value="spot.id"
              >
                Vaga {{ spot.identifier }}
                <span v-if="spot.apartment">
                  ({{ formatApartmentDescription(spot.apartment) }})
                </span>
              </option>
            </select>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
              Apenas vagas disponíveis ou já vinculadas a este veículo são exibidas
            </p>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Observações
            </label>
            <textarea
              v-model="form.notes"
              id="notes"
              name="notes"
              rows="3"
              placeholder="Informações adicionais sobre o veículo..."
              :class="inputClasses"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
            <AppButton
              type="button"
              @click="router.push('/vehicles')"
              size="md"
              variant="secondary"
            >
              Cancelar
            </AppButton>
            <AppButton
              type="submit"
              size="md"
              variant="primary"
              :loading="isSubmitting"
            >
              <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isEditing ? 'Atualizar Veículo' : 'Cadastrar Veículo' }}
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import NeonButton from '../components/NeonButton.vue';
import AppButton from '../components/ui/AppButton.vue';
import PageHeader from '../components/ui/PageHeader.vue';
import { formatApartmentDescription } from '../utils/formatters.js';
import { apiRequest } from '../utils/api';

export default {
  name: 'VehicleFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton,
    AppButton,
    PageHeader
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const isEditing = computed(() => !!route.params.id);
    
    const form = ref({
      license_plate: '',
      model: '',
      color: '',
      year: '',
      apartment_id: '',
      parking_spot_id: '',
      notes: ''
    });

    const apartments = ref([]);
    const parkingSpots = ref([]);
    const isSubmitting = ref(false);
    const loading = ref(false);

    const pageTitle = computed(() => {
      return isEditing.value ? 'Editar Veículo' : 'Novo Veículo';
    });

    const pageDescription = computed(() => {
      return isEditing.value 
        ? 'Atualize as informações do veículo' 
        : 'Cadastre um novo veículo no condomínio';
    });

    const inputClasses = 'w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors shadow-sm';
    
    const selectClasses = 'w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors shadow-sm';

    const availableParkingSpots = computed(() => {
      return parkingSpots.value.filter(spot => 
        !spot.rented_to_apartment_id || 
        (isEditing.value && spot.vehicles && spot.vehicles.some(v => v.id == route.params.id))
      );
    });

    const loadData = async () => {
      loading.value = true;

      try {
        // Load apartments and parking spots
        const [apartmentsData, parkingSpotsData] = await Promise.all([
          apiRequest('/apartments'),
          apiRequest('/parking-spots')
        ]);

        apartments.value = Array.isArray(apartmentsData) ? apartmentsData : (apartmentsData?.data || []);
        parkingSpots.value = Array.isArray(parkingSpotsData) ? parkingSpotsData : (parkingSpotsData?.data || []);

        // Load vehicle data if editing
        if (isEditing.value) {
          const vehicle = await apiRequest(`/vehicles/${route.params.id}`);
          form.value = {
            license_plate: vehicle.license_plate || '',
            model: vehicle.model || '',
            color: vehicle.color || '',
            year: vehicle.year || '',
            apartment_id: vehicle.apartment_id || '',
            parking_spot_id: vehicle.parking_spot_id || '',
            notes: vehicle.notes || ''
          };
        }
      } catch (e) {
        apartments.value = [];
        parkingSpots.value = [];
      } finally {
        loading.value = false;
      }
    };

    const formatLicensePlate = () => {
      let value = form.value.license_plate.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
      
      if (value.length <= 7) {
        // Format: ABC1234 -> ABC-1234
        if (value.length > 3) {
          value = value.substring(0, 3) + '-' + value.substring(3);
        }
        form.value.license_plate = value;
      }
    };

    const handleSubmit = async () => {
      isSubmitting.value = true;

      try {
        const payload = {
          ...form.value,
          // Remove empty values
          parking_spot_id: form.value.parking_spot_id || null,
          year: form.value.year || null,
          notes: form.value.notes || null
        };

        const url = isEditing.value ? `/vehicles/${route.params.id}` : '/vehicles';
        const method = isEditing.value ? 'PUT' : 'POST';

        await apiRequest(url, {
          method,
          body: JSON.stringify(payload)
        });

        router.push('/vehicles');
      } catch (e) {
        isSubmitting.value = false;
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(loadData);

    return {
      router,
      form,
      apartments,
      parkingSpots,
      isSubmitting,
      loading,
      isEditing,
      pageTitle,
      pageDescription,
      inputClasses,
      selectClasses,
      availableParkingSpots,
      formatLicensePlate,
      handleSubmit,
      formatApartmentDescription
    };
  }
};
</script>
