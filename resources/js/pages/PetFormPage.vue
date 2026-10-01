<template>
  <AuthenticatedLayout :page-title="pageTitle">
    <div class="space-y-6 max-w-4xl mx-auto">
      <!-- Header unificado com botão voltar -->
      <PageHeader
        :title="pageTitle"
        :subtitle="pageDescription"
        show-back
        @back="router.push('/pets')"
      />

      <!-- Form Card -->
      <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Pet Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label for="name" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
              <label for="species" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
              <label for="custom_species" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
              <label for="breed" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
              <label for="gender" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
              <label for="age" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
              <label for="weight" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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

          <!-- Photo Upload -->
          <div>
            <label for="photo" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
              Foto do Pet
            </label>
            <div class="space-y-3">
              <!-- Photo Upload Card -->
              <div class="bg-slate-50 dark:bg-slate-800/40 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-6 text-center hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors">
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
                    <svg class="h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <p class="mt-2 text-xs font-medium text-indigo-600 dark:text-indigo-400">
                    Clique para selecionar uma foto
                  </p>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    PNG, JPG, JPEG até 5MB
                  </p>
                </label>
              </div>
              
              <!-- Photo Preview -->
              <div v-if="photoPreview" class="relative">
                <img
                  :src="photoPreview"
                  alt="Preview"
                  class="w-full h-40 object-cover rounded-xl border border-slate-200 dark:border-slate-800"
                />
                <button
                  type="button"
                  @click="removePhoto"
                  class="absolute top-2 right-2 p-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-full transition-colors shadow-sm"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Alternative: URL Input -->
              <div class="text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-1.5">ou informe uma URL</p>
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
            <label for="health_info" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
            <label for="notes" class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1.5">
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
          <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200 dark:border-slate-800">
            <AppButton
              type="button"
              @click="router.push('/pets')"
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
              {{ isEditing ? 'Atualizar Pet' : 'Cadastrar Pet' }}
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
  name: 'PetFormPage',
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

    const inputClasses = 'w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors shadow-sm';
    
    const selectClasses = 'w-full px-3.5 py-2.5 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors shadow-sm';

    const loadData = async () => {
      loading.value = true;

      try {
        // Load apartments
        const apartmentsData = await apiRequest('/apartments');
        apartments.value = Array.isArray(apartmentsData) ? apartmentsData : (apartmentsData?.data || []);

        // Load pet data if editing
        if (isEditing.value) {
          const pet = await apiRequest(`/pets/${route.params.id}`);
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
      } catch (e) {
        apartments.value = [];
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

        // Photo handling
        if (photoFile.value) {
          formData.append('photo', photoFile.value);
        } else if (form.value.photo_url) {
          formData.append('photo_url', form.value.photo_url);
        }

        const url = isEditing.value ? `/pets/${route.params.id}` : '/pets';

        // For PUT requests, append _method field
        if (isEditing.value) {
          formData.append('_method', 'PUT');
        }

        await apiRequest(url, {
          method: 'POST',
          body: formData
        });

        router.push('/pets');
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
      isSubmitting,
      loading,
      isEditing,
      pageTitle,
      pageDescription,
      breedPlaceholder,
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
