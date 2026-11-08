<template>
  <AuthenticatedLayout page-title="Entrega de Encomendas">
    <form @submit.prevent="submit" class="space-y-4">
      <input v-model="form.token" class="border rounded px-2 w-full" placeholder="Token" />
      <select v-model="form.apartment_id" class="border rounded px-2 w-full">
        <option :value="null">Unidade</option>
        <option v-for="a in apartments" :key="a.id" :value="a.id">{{ formatApartmentDescription(a) }}</option>
      </select>
      <select v-model="form.package_id" class="border rounded px-2 w-full">
        <option :value="null">Encomenda</option>
        <option v-for="p in packages" :key="p.id" :value="p.id">{{ p.code }}</option>
      </select>
      <label class="flex items-center space-x-2">
        <input type="checkbox" v-model="form.no_sign" />
        <span>Impossibilitado de assinar</span>
      </label>
      <SignaturePad v-if="!form.no_sign" v-model="form.signature" @mousedown="sigStart" @mouseup="sigEnd" @mousemove="sigMove" />
      <WebcamCapture v-if="form.no_sign" v-model="form.document_photo" />
      <button class="px-3 py-1 bg-cyan-600 text-white rounded">Entregar</button>
      <p v-if="message" class="text-sm" :class="messageType === 'success' ? 'text-green-400' : 'text-red-400'">
        {{ message }}
      </p>
    </form>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '../components/layouts/AuthenticatedLayout.vue';
import { apiRequest } from '../utils/api';
import { formatApartmentDescription } from '../utils/formatters';
import WebcamCapture from '../components/WebcamCapture.vue';
import SignaturePad from '../components/SignaturePad.vue';

export default {
  name: 'PackageDeliveryPage',
  components: { AuthenticatedLayout, WebcamCapture, SignaturePad },
  setup() {
    const form = ref({
      token: '',
      apartment_id: null,
      package_id: null,
      signature: '',
      document_photo: '',
      no_sign: false
    });
    const apartments = ref([]);
    const packages = ref([]);
    const message = ref('');
    const messageType = ref('error');

    const showMessage = (text, type = 'error') => {
      message.value = text;
      messageType.value = type;
    };
    const sigStart = () => {};
    const sigEnd = () => {};
    const sigMove = () => {};

    const load = async () => {
      apartments.value = await apiRequest('/apartments') || [];
    };

    watch(() => form.value.apartment_id, async (val) => {
      if (!val) return;
      packages.value = await apiRequest(`/packages?apartment_id=${val}&status=pending`) || [];
    });

    const submit = async () => {
      if (!form.value.package_id || !form.value.token) {
        showMessage('Informe a encomenda e o código de retirada.');
        return;
      }

      const data = {
        code: form.value.token,
        signature: form.value.signature,
        document_photo: form.value.document_photo
      };

      try {
        const packageId = form.value.package_id;
        const resp = await apiRequest(`/packages/${packageId}/withdraw`, {
          method: 'POST',
          body: JSON.stringify(data)
        });

        if (resp) {
          packages.value = packages.value.filter(p => p.id !== packageId);
          form.value.token = '';
          form.value.package_id = null;
          form.value.signature = '';
          form.value.document_photo = '';
          showMessage('Encomenda entregue com sucesso.', 'success');
        }
      } catch (error) {
        showMessage(error.message || 'Erro ao registrar entrega.');
      }
    };

    onMounted(load);

    return { form, apartments, packages, message, messageType, submit, formatApartmentDescription, sigStart, sigEnd, sigMove };
  }
};
</script>
