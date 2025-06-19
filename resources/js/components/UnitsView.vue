<template>
  <AuthenticatedLayout page-title="Unidades">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Card de Torres -->
      <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-6 border-2 border-cyan-500/20">
        <h3 class="text-lg font-medium text-cyan-300 mb-4">Torres</h3>
        <form @submit.prevent="createTower" class="space-y-4 mb-6">
          <input v-model="newTower.name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-colors" placeholder="Nome da torre" />
          <button type="submit" class="w-full px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors font-semibold">Salvar Torre</button>
        </form>
        <div class="max-h-60 overflow-y-auto pr-2">
          <ul class="space-y-2 text-cyan-100">
            <li v-for="t in towers" :key="t.id" class="bg-gray-800/50 p-3 rounded-lg flex justify-between items-center">
              <span>{{ t.name }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Card de Blocos -->
      <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-6 border-2 border-green-500/20">
        <h3 class="text-lg font-medium text-green-300 mb-4">Blocos</h3>
        <form @submit.prevent="createBlock" class="space-y-4 mb-6">
          <input v-model="newBlock.name" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors" placeholder="Nome do bloco" />
          <select v-model="newBlock.tower_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
            <option :value="null">Sem torre</option>
            <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
          </select>
          <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">Salvar Bloco</button>
        </form>
        <div class="max-h-60 overflow-y-auto pr-2">
          <ul class="space-y-2 text-green-100">
            <li v-for="b in blocks" :key="b.id" class="bg-gray-800/50 p-3 rounded-lg flex justify-between items-center">
              <span>{{ b.name }}</span>
              <span class="text-xs text-gray-400">{{ b.tower ? b.tower.name : 'Sem torre' }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Card de Apartamentos -->
      <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-3xl shadow-xl p-6 border-2 border-blue-500/20">
        <h3 class="text-lg font-medium text-blue-300 mb-4">Apartamentos/Casas</h3>
        <form @submit.prevent="createApartment" class="space-y-4 mb-6">
          <div class="grid grid-cols-2 gap-4">
            <input v-model="newApartment.number" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" placeholder="Número" />
            <input v-model.number="newApartment.floor" type="number" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" placeholder="Andar" />
          </div>
          <select v-model="newApartment.tower_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option :value="null">Sem torre</option>
              <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
          </select>
          <select v-model="newApartment.block_id" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              <option :value="null">Sem bloco</option>
              <option v-for="b in blocks" :value="b.id" :key="b.id">{{ b.name }}</option>
          </select>
          <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">Salvar Unidade</button>
        </form>
        <div class="max-h-60 overflow-y-auto pr-2">
          <ul class="space-y-2 text-blue-100">
            <li v-for="a in apartments" :key="a.id" class="bg-gray-800/50 p-3 rounded-lg">
              <div class="font-semibold">Unidade {{ a.number }}</div>
              <div class="text-sm text-gray-400">
                Andar {{ a.floor }}
                <span v-if="a.tower"> - T: {{ a.tower.name }}</span>
                <span v-if="a.block"> - B: {{ a.block.name }}</span>
              </div>
            </li>
          </ul>
        </div>
      </section>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from './layouts/AuthenticatedLayout.vue';

export default {
  name: 'UnitsView',
  components: { AuthenticatedLayout },
  setup() {
    const token = localStorage.getItem('token');
    const selectedCondo = localStorage.getItem('selectedCondominium');
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const towers = ref([]);
    const blocks = ref([]);
    const apartments = ref([]);

    const newTower = ref({ name: '', condominium_id: null });
    const newBlock = ref({ name: '', tower_id: null, condominium_id: null });
    const newApartment = ref({ number: '', floor: 1, tower_id: null, block_id: null, condominium_id: null });

    const loadTowers = async () => {
      const respTowers = await fetch(`/api/towers?condominium_id=${selectedCondo}`, { headers });
      towers.value = respTowers.ok ? await respTowers.json() : [];
    };

    const loadBlocks = async () => {
      const respBlocks = await fetch(`/api/blocks?condominium_id=${selectedCondo}`, { headers });
      blocks.value = respBlocks.ok ? await respBlocks.json() : [];
    };

    const loadApartments = async () => {
      const respApartments = await fetch(`/api/apartments?condominium_id=${selectedCondo}`, { headers });
      apartments.value = respApartments.ok ? await respApartments.json() : [];
    };

    const loadData = async () => {
      await Promise.all([loadTowers(), loadBlocks(), loadApartments()]);
    };

    const createTower = async () => {
      if (!newTower.value.name) return;
      newTower.value.condominium_id = selectedCondo;
      const resp = await fetch('/api/towers', { method: 'POST', headers, body: JSON.stringify(newTower.value) });
      if (resp.ok) {
        await loadTowers();
        newTower.value.name = '';
      }
    };

    const createBlock = async () => {
      if (!newBlock.value.name) return;
      newBlock.value.condominium_id = selectedCondo;
      const resp = await fetch('/api/blocks', { method: 'POST', headers, body: JSON.stringify(newBlock.value) });
      if (resp.ok) {
        await loadBlocks();
        newBlock.value.name = '';
        newBlock.value.tower_id = null;
      }
    };

    const createApartment = async () => {
      if (!newApartment.value.number) return;
      newApartment.value.condominium_id = selectedCondo;
      const resp = await fetch('/api/apartments', { method: 'POST', headers, body: JSON.stringify(newApartment.value) });
      if (resp.ok) {
        await loadApartments();
        newApartment.value.number = '';
        newApartment.value.floor = 1;
        newApartment.value.tower_id = null;
        newApartment.value.block_id = null;
      }
    };

    onMounted(loadData);

    return { towers, blocks, apartments, newTower, newBlock, newApartment, createTower, createBlock, createApartment };
  }
};
</script>
