<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <div :class="headerCardClasses">
        <div class="flex items-center space-x-4">
          <div :class="headerIconClasses">
            <svg class="h-8 w-8" :class="headerIconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="isEditing" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <div>
            <h1 class="text-2xl font-bold text-white">{{ pageTitle }}</h1>
            <p :class="headerDescriptionColor">{{ pageDescription }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex items-center space-x-2">
        <NeonButton @click="router.push('/vehicles')" size="md" variant="secondary">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar para Veículos
        </NeonButton>
      </div>

      <!-- Form Card -->
      <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Vehicle Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- License Plate -->
            <div>
              <label for="license_plate" class="block text-sm font-medium text-gray-300 mb-2">
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
              <p class="mt-1 text-xs text-gray-500">Formato: ABC-1234 ou ABC1234</p>
            </div>

            <!-- Model -->
            <div>
              <label for="model" class="block text-sm font-medium text-gray-300 mb-2">
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
              <label for="color" class="block text-sm font-medium text-gray-300 mb-2">
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
              <label for="year" class="block text-sm font-medium text-gray-300 mb-2">
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
            <label for="apartment_id" class="block text-sm font-medium text-gray-300 mb-2">
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
            <label for="parking_spot_id" class="block text-sm font-medium text-gray-300 mb-2">
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
            <p class="mt-1 text-xs text-gray-500">
              Apenas vagas disponíveis ou já vinculadas a este veículo são exibidas
            </p>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">
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
          <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-700/50">
            <NeonButton
              type="button"
              @click="router.push('/vehicles')"
              size="md"
              variant="secondary"
              class="flex-1 sm:flex-none"
            >
              Cancelar
            </NeonButton>
            <NeonButton
              type="submit"
              size="md"
              :loading="isSubmitting"
              class="flex-1"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              {{ isEditing ? 'Atualizar Veículo' : 'Cadastrar Veículo' }}
            </NeonButton>
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
import { formatApartmentDescription } from '../utils/formatters.js';

export default {
  name: 'VehicleFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton
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

    // Header card classes based on context
    const headerCardClasses = computed(() => {
      return isEditing.value
        ? 'bg-gradient-to-r from-orange-600/20 to-amber-600/20 rounded-2xl p-6 border-2 border-orange-500/30'
        : 'bg-gradient-to-r from-green-600/20 to-emerald-600/20 rounded-2xl p-6 border-2 border-green-500/30';
    });

    const headerIconClasses = computed(() => {
      return isEditing.value
        ? 'h-16 w-16 rounded-full bg-orange-100 flex items-center justify-center border-2 border-orange-500/30'
        : 'h-16 w-16 rounded-full bg-green-100 flex items-center justify-center border-2 border-green-500/30';
    });

    const headerIconColor = computed(() => {
      return isEditing.value ? 'text-orange-600' : 'text-green-600';
    });

    const headerDescriptionColor = computed(() => {
      return isEditing.value ? 'text-orange-300' : 'text-green-300';
    });

    const inputClasses = 'w-full px-4 py-3 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 backdrop-blur-sm transition-all duration-200';
    
    const selectClasses = 'w-full px-4 py-3 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 backdrop-blur-sm transition-all duration-200';

    const availableParkingSpots = computed(() => {
      return parkingSpots.value.filter(spot => 
        !spot.rented_to_apartment_id || 
        (isEditing.value && spot.vehicles && spot.vehicles.some(v => v.id == route.params.id))
      );
    });

    const loadData = async () => {
      loading.value = true;
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        // Load apartments and parking spots
        const [aptsRes, spotsRes] = await Promise.all([
          fetch(`/api/apartments?condominium_id=${condo}`, { headers }),
          fetch(`/api/parking-spots?condominium_id=${condo}`, { headers })
        ]);

        if (aptsRes.ok) apartments.value = await aptsRes.json();
        if (spotsRes.ok) parkingSpots.value = await spotsRes.json();

        // Load vehicle data if editing
        if (isEditing.value) {
          const vehicleRes = await fetch(`/api/vehicles/${route.params.id}`, { headers });
          if (vehicleRes.ok) {
            const vehicle = await vehicleRes.json();
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
        }
      } catch (e) {
        console.error('Erro ao carregar dados:', e);
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
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        const payload = {
          ...form.value,
          condominium_id: condo,
          // Remove empty values
          parking_spot_id: form.value.parking_spot_id || null,
          year: form.value.year || null,
          notes: form.value.notes || null
        };

        const url = isEditing.value ? `/vehicles/${route.params.id}` : '/vehicles';
        const method = isEditing.value ? 'PUT' : 'POST';

        const response = await fetch(url, {
          method,
          headers,
          body: JSON.stringify(payload)
        });

        if (response.ok) {
          router.push('/vehicles');
        } else {
          const error = await response.json();
          console.error('Erro ao salvar veículo:', error);
          // Here you could show an error message to the user
        }
      } catch (e) {
        console.error('Erro ao salvar veículo:', e);
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
      headerCardClasses,
      headerIconClasses,
      headerIconColor,
      headerDescriptionColor,
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