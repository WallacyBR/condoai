<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="space-y-6">
      <!-- Header Card - OBRIGATÓRIO conforme DESIGN_MANUAL.md -->
      <div :class="headerCardClasses">
        <div class="flex items-center space-x-4">
          <div :class="headerIconClasses">
            <svg class="h-8 w-8" :class="headerIconColor" viewBox="0 0 24 24">
              <path v-if="isEditing" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              <path v-else fill="currentColor" d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM16.5 6C17.6 6 18.5 6.9 18.5 8C18.5 9.1 17.6 10 16.5 10C15.4 10 14.5 9.1 14.5 8C14.5 6.9 15.4 6 16.5 6ZM7.5 6C8.6 6 9.5 6.9 9.5 8C9.5 9.1 8.6 10 7.5 10C6.4 10 5.5 9.1 5.5 8C5.5 6.9 6.4 6 7.5 6ZM12 11.5C15 11.5 17.5 14 17.5 17.5C17.5 20 15.5 22 12 22C8.5 22 6.5 20 6.5 17.5C6.5 14 9 11.5 12 11.5Z"/>
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
        <NeonButton @click="router.push('/pets')" size="md" variant="secondary">
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Voltar para Pets
        </NeonButton>
      </div>

      <!-- Form Card -->
      <div class="bg-gradient-to-br from-gray-900/90 via-gray-800/90 to-gray-900/90 backdrop-blur-xl rounded-2xl p-6 border border-gray-700/50">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Pet Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                Nome do Pet *
              </label>
              <input
                v-model="form.name"
                type="text"
                id="name"
                name="name"
                placeholder="Rex, Mimi, Thor..."
                required
                :class="inputClasses"
              />
            </div>

            <!-- Species -->
            <div>
              <label for="species" class="block text-sm font-medium text-gray-300 mb-2">
                Espécie *
              </label>
              <select
                v-model="form.species"
                id="species"
                name="species"
                required
                :class="selectClasses"
                @change="onSpeciesChange"
              >
                <option value="">Selecione a espécie</option>
                <option value="Cão">Cão</option>
                <option value="Gato">Gato</option>
                <option value="Pássaro">Pássaro</option>
                <option value="Peixe">Peixe</option>
                <option value="Hamster">Hamster</option>
                <option value="Coelho">Coelho</option>
                <option value="Tartaruga">Tartaruga</option>
                <option value="Outro">Outro</option>
              </select>
            </div>

            <!-- Custom Species (if Other) -->
            <div v-if="form.species === 'Outro'" class="md:col-span-2">
              <label for="custom_species" class="block text-sm font-medium text-gray-300 mb-2">
                Especifique a Espécie *
              </label>
              <input
                v-model="form.custom_species"
                type="text"
                id="custom_species"
                name="custom_species"
                placeholder="Especifique a espécie do animal"
                required
                :class="inputClasses"
              />
            </div>

            <!-- Breed -->
            <div>
              <label for="breed" class="block text-sm font-medium text-gray-300 mb-2">
                Raça
              </label>
              <input
                v-model="form.breed"
                type="text"
                id="breed"
                name="breed"
                :placeholder="breedPlaceholder"
                :class="inputClasses"
              />
            </div>

            <!-- Gender -->
            <div>
              <label for="gender" class="block text-sm font-medium text-gray-300 mb-2">
                Sexo
              </label>
              <select
                v-model="form.gender"
                id="gender"
                name="gender"
                :class="selectClasses"
              >
                <option value="">Não informado</option>
                <option value="Macho">Macho</option>
                <option value="Fêmea">Fêmea</option>
              </select>
            </div>

            <!-- Age -->
            <div>
              <label for="age" class="block text-sm font-medium text-gray-300 mb-2">
                Idade
              </label>
              <input
                v-model="form.age"
                type="text"
                id="age"
                name="age"
                placeholder="3 anos, 6 meses..."
                :class="inputClasses"
              />
            </div>

            <!-- Weight -->
            <div>
              <label for="weight" class="block text-sm font-medium text-gray-300 mb-2">
                Peso
              </label>
              <input
                v-model="form.weight"
                type="text"
                id="weight"
                name="weight"
                placeholder="5.2 kg, 300g..."
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

          <!-- Photo Upload -->
          <div>
            <label for="photo" class="block text-sm font-medium text-gray-300 mb-2">
              Foto do Pet
            </label>
            <div class="space-y-3">
              <!-- Photo Upload Card -->
              <div class="bg-gray-800/30 border-2 border-dashed border-green-500/30 rounded-xl p-6 text-center hover:border-green-500/50 transition-colors">
                <input
                  type="file"
                  id="photo"
                  name="photo"
                  accept="image/*"
                  @change="handlePhotoUpload"
                  class="hidden"
                />
                <label for="photo" class="cursor-pointer block">
                  <div class="mx-auto flex justify-center">
                    <svg class="h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <p class="mt-2 text-sm text-green-300">
                    Clique para selecionar uma foto
                  </p>
                  <p class="text-xs text-gray-400">
                    PNG, JPG, JPEG até 5MB
                  </p>
                </label>
              </div>
              
              <!-- Photo Preview -->
              <div v-if="photoPreview" class="relative">
                <img
                  :src="photoPreview"
                  alt="Preview"
                  class="w-full h-40 object-cover rounded-xl border border-gray-700/50"
                />
                <button
                  type="button"
                  @click="removePhoto"
                  class="absolute top-2 right-2 p-2 bg-red-500/80 hover:bg-red-500 text-white rounded-full transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Alternative: URL Input -->
              <div class="text-center">
                <p class="text-sm text-gray-400 mb-2">ou</p>
                <input
                  v-model="form.photo_url"
                  type="url"
                  placeholder="Cole a URL de uma foto online"
                  :class="inputClasses"
                />
              </div>
            </div>
          </div>

          <!-- Health Information -->
          <div>
            <label for="health_info" class="block text-sm font-medium text-gray-300 mb-2">
              Informações de Saúde
            </label>
            <textarea
              v-model="form.health_info"
              id="health_info"
              name="health_info"
              rows="3"
              placeholder="Vacinação, medicamentos, alergias, condições especiais..."
              :class="inputClasses"
            ></textarea>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">
              Observações Gerais
            </label>
            <textarea
              v-model="form.notes"
              id="notes"
              name="notes"
              rows="3"
              placeholder="Comportamento, preferências, informações adicionais..."
              :class="inputClasses"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-700/50">
            <NeonButton
              type="button"
              @click="router.push('/pets')"
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
              {{ isEditing ? 'Atualizar Pet' : 'Cadastrar Pet' }}
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
  name: 'PetFormPage',
  components: {
    AuthenticatedLayout,
    NeonButton
  },
  setup() {
    const router = useRouter();
    const route = useRoute();
    const isEditing = computed(() => !!route.params.id);
    
    const form = ref({
      name: '',
      species: '',
      custom_species: '',
      breed: '',
      gender: '',
      age: '',
      weight: '',
      apartment_id: '',
      photo_url: '',
      health_info: '',
      notes: ''
    });

    const apartments = ref([]);
    const isSubmitting = ref(false);
    const loading = ref(false);
    const photoPreview = ref(null);
    const photoFile = ref(null);

    const pageTitle = computed(() => {
      return isEditing.value ? 'Editar Pet' : 'Novo Pet';
    });

    const pageDescription = computed(() => {
      return isEditing.value 
        ? 'Atualize as informações do animal de estimação' 
        : 'Cadastre um novo animal de estimação no condomínio';
    });

    const breedPlaceholder = computed(() => {
      const placeholders = {
        'Cão': 'Labrador, Golden Retriever, Poodle...',
        'Gato': 'Persa, Siamês, Maine Coon...',
        'Pássaro': 'Canário, Calopsita, Papagaio...',
        'Peixe': 'Betta, Guppy, Tetra...',
        'Hamster': 'Sírio, Anão Russo...',
        'Coelho': 'Mini Lop, Angorá...',
        'Tartaruga': 'Tigre d\'água, Jabuti...'
      };
      return placeholders[form.value.species] || 'Especifique a raça';
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

    const inputClasses = 'w-full px-4 py-3 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 backdrop-blur-sm transition-all duration-200';
    
    const selectClasses = 'w-full px-4 py-3 bg-gray-800/50 border border-gray-600/50 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-500/50 backdrop-blur-sm transition-all duration-200';

    const loadData = async () => {
      loading.value = true;
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');
      const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

      try {
        // Load apartments
        const aptsRes = await fetch(`/api/apartments?condominium_id=${condo}`, { headers });
        if (aptsRes.ok) apartments.value = await aptsRes.json();

        // Load pet data if editing
        if (isEditing.value) {
          const petRes = await fetch(`/api/pets/${route.params.id}`, { headers });
          if (petRes.ok) {
            const pet = await petRes.json();
            form.value = {
              name: pet.name || '',
              species: pet.species || '',
              custom_species: pet.custom_species || '',
              breed: pet.breed || '',
              gender: pet.gender || '',
              age: pet.age || '',
              weight: pet.weight || '',
              apartment_id: pet.apartment_id || '',
              photo_url: pet.photo || '',
              health_info: pet.health_info || '',
              notes: pet.notes || ''
            };
            
            // Set photo preview if exists
            if (pet.photo) {
              photoPreview.value = pet.photo;
            }
          }
        }
      } catch (e) {
        console.error('Erro ao carregar dados:', e);
      } finally {
        loading.value = false;
      }
    };

    const onSpeciesChange = () => {
      if (form.value.species !== 'Outro') {
        form.value.custom_species = '';
      }
      form.value.breed = ''; // Clear breed when species changes
    };

    const handlePhotoUpload = (event) => {
      const file = event.target.files[0];
      if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
          alert('Por favor, selecione apenas arquivos de imagem.');
          return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
          alert('A foto deve ter no máximo 5MB.');
          return;
        }

        photoFile.value = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
          photoPreview.value = e.target.result;
          form.value.photo_url = ''; // Clear URL when file is selected
        };
        reader.readAsDataURL(file);
      }
    };

    const removePhoto = () => {
      photoPreview.value = null;
      photoFile.value = null;
      form.value.photo_url = '';
      // Reset file input
      const fileInput = document.getElementById('photo');
      if (fileInput) fileInput.value = '';
    };

    const handleSubmit = async () => {
      isSubmitting.value = true;
      const token = localStorage.getItem('token');
      const condo = localStorage.getItem('selectedCondominium');

      try {
        // Prepare form data
        const formData = new FormData();
        
        // Basic fields
        formData.append('name', form.value.name);
        formData.append('species', form.value.species === 'Outro' ? form.value.custom_species : form.value.species);
        formData.append('breed', form.value.breed || '');
        formData.append('gender', form.value.gender || '');
        formData.append('age', form.value.age || '');
        formData.append('weight', form.value.weight || '');
        formData.append('apartment_id', form.value.apartment_id);
        formData.append('health_info', form.value.health_info || '');
        formData.append('notes', form.value.notes || '');
        formData.append('condominium_id', condo);

        // Photo handling
        if (photoFile.value) {
          formData.append('photo', photoFile.value);
        } else if (form.value.photo_url) {
          formData.append('photo_url', form.value.photo_url);
        }

        const url = isEditing.value ? `/pets/${route.params.id}` : '/pets';
        const method = isEditing.value ? 'PUT' : 'POST';

        // For PUT requests, append _method field
        if (isEditing.value) {
          formData.append('_method', 'PUT');
        }

        const response = await fetch(url, {
          method: isEditing.value ? 'POST' : 'POST', // Use POST for both due to file upload
          headers: {
            'Authorization': `Bearer ${token}`
          },
          body: formData
        });

        if (response.ok) {
          router.push('/pets');
        } else {
          const error = await response.json();
          console.error('Erro ao salvar pet:', error);
          // Here you could show an error message to the user
        }
      } catch (e) {
        console.error('Erro ao salvar pet:', e);
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(loadData);

    return {
      router,
      form,
      apartments,
      isSubmitting,
      loading,
      isEditing,
      pageTitle,
      pageDescription,
      breedPlaceholder,
      headerCardClasses,
      headerIconClasses,
      headerIconColor,
      headerDescriptionColor,
      inputClasses,
      selectClasses,
      photoPreview,
      onSpeciesChange,
      handlePhotoUpload,
      removePhoto,
      handleSubmit,
      formatApartmentDescription
    };
  }
};
</script> 