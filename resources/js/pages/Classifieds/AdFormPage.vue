<template>
  <AuthenticatedLayout :page-title="isEditMode ? 'Editar Anúncio' : 'Criar Novo Anúncio'">
    <div class="max-w-4xl mx-auto space-y-6">
      <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-xl p-6 border-2 border-indigo-500/20">
        <h1 class="text-2xl font-bold text-white mb-6">{{ isEditMode ? 'Editar Anúncio' : 'Criar Novo Anúncio' }}</h1>
        
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Título <span class="text-red-500">*</span></label>
              <input
                type="text"
                id="title"
                v-model="form.title"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                placeholder="Ex: Bicicleta Caloi Usada"
              />
              <p v-if="errors.title" class="text-red-400 text-sm mt-1">{{ errors.title[0] }}</p>
            </div>
            <div>
              <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Preço</label>
              <input
                type="number"
                id="price"
                v-model="form.price"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                placeholder="Ex: 250.00"
                step="0.01"
              />
              <p v-if="errors.price" class="text-red-400 text-sm mt-1">{{ errors.price[0] }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Categoria <span class="text-red-500">*</span></label>
              <select
                id="category"
                v-model="form.category"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              >
                <option value="">Selecione uma categoria</option>
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
              <p v-if="errors.category" class="text-red-400 text-sm mt-1">{{ errors.category[0] }}</p>
            </div>
            <div>
              <label for="condition" class="block text-sm font-medium text-gray-300 mb-2">Condição <span class="text-red-500">*</span></label>
              <select
                id="condition"
                v-model="form.condition"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              >
                <option value="">Selecione a condição</option>
                <option v-for="cond in conditions" :key="cond" :value="cond">{{ cond }}</option>
              </select>
              <p v-if="errors.condition" class="text-red-400 text-sm mt-1">{{ errors.condition[0] }}</p>
            </div>
          </div>

          <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Descrição</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="5"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
              placeholder="Descreva seu item em detalhes..."
            ></textarea>
            <p v-if="errors.description" class="text-red-400 text-sm mt-1">{{ errors.description[0] }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
              <label for="location" class="block text-sm font-medium text-gray-300 mb-2">Localização</label>
              <input
                type="text"
                id="location"
                v-model="form.location"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                placeholder="Ex: Bloco A, Apt 101"
              />
              <p v-if="errors.location" class="text-red-400 text-sm mt-1">{{ errors.location[0] }}</p>
            </div>
            <div>
              <label for="contact_info" class="block text-sm font-medium text-gray-300 mb-2">Informações de Contato</label>
              <input
                type="text"
                id="contact_info"
                v-model="form.contact_info"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                placeholder="Ex: (XX) XXXXX-XXXX ou email@exemplo.com"
              />
              <p v-if="errors.contact_info" class="text-red-400 text-sm mt-1">{{ errors.contact_info[0] }}</p>
            </div>
          </div>

          <div class="mb-6">
            <label for="images" class="block text-sm font-medium text-gray-300 mb-2">Imagens</label>
            <input
              type="file"
              id="images"
              multiple
              @change="handleImageUpload"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              accept="image/*"
            />
            <p v-if="errors['images.0']" class="text-red-400 text-sm mt-1">{{ errors['images.0'][0] }}</p>
            <p v-if="errors.images" class="text-red-400 text-sm mt-1">{{ errors.images[0] }}</p>
          </div>

          <div v-if="previewImages.length > 0 || form.existing_image_ids.length > 0" class="mb-6">
            <h3 class="text-lg font-medium text-gray-300 mb-3">Imagens Selecionadas:</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
              <div v-for="(image, index) in previewImages" :key="index" class="relative group">
                <img :src="image.url" :alt="`Preview ${index}`" class="w-full h-32 object-cover rounded-lg border border-gray-700" />
                <button
                  type="button"
                  @click="removeNewImage(index)"
                  class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              <div v-for="(image_id, index) in form.existing_image_ids" :key="image_id" class="relative group">
                <img :src="`/storage/${existingAdImages.find(img => img.id === image_id)?.image_path}`" :alt="`Existing ${index}`" class="w-full h-32 object-cover rounded-lg border border-gray-700" />
                <button
                  type="button"
                  @click="removeExistingImage(image_id)"
                  class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-4 mt-8">
            <NeonButton type="button" variant="secondary" @click="router.back()">
              Cancelar
            </NeonButton>
            <NeonButton type="submit" :disabled="isSubmitting">
              <span v-if="isSubmitting" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processando...
              </span>
              <span v-else>
                {{ isEditMode ? 'Salvar Alterações' : 'Criar Anúncio' }}
              </span>
            </NeonButton>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/components/layouts/AuthenticatedLayout.vue';
import NeonButton from '@/components/NeonButton.vue';
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import adsService from '@/services/adsService';

const route = useRoute();
const router = useRouter();

const isEditMode = computed(() => route.name === 'classifieds.edit');
const adId = computed(() => route.params.id);

const form = ref({
  title: '',
  description: '',
  price: null,
  currency: 'BRL',
  category: '',
  condition: '',
  location: '',
  contact_info: '',
  images: [], // For new image files
  existing_image_ids: [], // For existing image IDs to keep
});

const previewImages = ref([]); // For displaying new image previews
const existingAdImages = ref([]); // For storing existing ad images data

const categories = ref([
  'Eletrônicos', 'Móveis', 'Serviços', 'Veículos', 'Imóveis', 'Outros'
]);
const conditions = ref([
  'Novo', 'Usado - Como Novo', 'Usado - Bom Estado', 'Usado - Com Marcas'
]);

const errors = ref({});
const isSubmitting = ref(false);

onMounted(async () => {
  if (isEditMode.value && adId.value) {
    try {
      const response = await adsService.getAd(adId.value);
      const adData = response.data;
      form.value.title = adData.title;
      form.value.description = adData.description;
      form.value.price = adData.price;
      form.value.currency = adData.currency;
      form.value.category = adData.category;
      form.value.condition = adData.condition;
      form.value.location = adData.location;
      form.value.contact_info = adData.contact_info;
      
      existingAdImages.value = adData.images; // Store full image objects
      form.value.existing_image_ids = adData.images.map(img => img.id); // Store only IDs for the form

    } catch (error) {
      console.error('Erro ao carregar anúncio para edição:', error);
      errors.value.general = 'Não foi possível carregar o anúncio para edição.';
    }
  }
});

function handleImageUpload(event) {
  form.value.images = Array.from(event.target.files);
  previewImages.value = form.value.images.map(file => ({
    url: URL.createObjectURL(file),
    file: file,
  }));
}

function removeNewImage(index) {
  previewImages.value.splice(index, 1);
  form.value.images.splice(index, 1);
}

function removeExistingImage(imageId) {
  form.value.existing_image_ids = form.value.existing_image_ids.filter(id => id !== imageId);
}

async function submitForm() {
  isSubmitting.value = true;
  errors.value = {};

  const formData = new FormData();
  for (const key in form.value) {
    if (key === 'images') {
      form.value.images.forEach((file, index) => {
        formData.append(`images[${index}]`, file);
      });
    } else if (key === 'existing_image_ids') {
      form.value.existing_image_ids.forEach((id, index) => {
        formData.append(`existing_image_ids[${index}]`, id);
      });
    } else if (form.value[key] !== null) {
      formData.append(key, form.value[key]);
    }
  }

  // For PUT/PATCH requests with FormData, you might need to spoof the method
  if (isEditMode.value) {
    formData.append('_method', 'PUT');
  }

  try {
    let response;
    if (isEditMode.value) {
      response = await adsService.updateAd(adId.value, formData);
    } else {
      response = await adsService.createAd(formData);
    }
    alert(`Anúncio ${isEditMode.value ? 'atualizado' : 'criado'} com sucesso!`);
    router.push({ name: 'classifieds.index' });
  } catch (error) {
    console.error('Erro ao salvar anúncio:', error);
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = 'Ocorreu um erro ao salvar o anúncio. Tente novamente.';
    }
  } finally {
    isSubmitting.value = false;
  }
}
</script>
