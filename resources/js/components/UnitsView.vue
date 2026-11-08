<template>
  <AuthenticatedLayout page-title="Unidades">
    <div class="space-y-8">
      <section>
        <h3 class="text-lg font-medium text-cyan-300 mb-2">Torres</h3>
        <form @submit.prevent="createTower" class="flex space-x-2 mb-4">
          <input v-model="newTower.name" class="border rounded px-2" placeholder="Nome da torre" />
          <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
        </form>
        <ul class="list-disc ml-5 text-cyan-100">
          <li v-for="t in towers" :key="t.id">{{ t.name }}</li>
        </ul>
      </section>

      <section>
        <h3 class="text-lg font-medium text-cyan-300 mb-2">Blocos</h3>
        <form @submit.prevent="createBlock" class="flex space-x-2 mb-4">
          <input v-model="newBlock.name" class="border rounded px-2" placeholder="Nome do bloco" />
          <select v-model="newBlock.tower_id" class="border rounded px-2">
            <option :value="null">Sem torre</option>
            <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
          </select>
          <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
        </form>
        <ul class="list-disc ml-5 text-cyan-100">
          <li v-for="b in blocks" :key="b.id">{{ b.name }} - {{ b.tower ? b.tower.name : 'Sem torre' }}</li>
        </ul>
      </section>

      <section>
        <h3 class="text-lg font-medium text-cyan-300 mb-2">Apartamentos/Casas</h3>
        <form @submit.prevent="createApartment" class="space-y-2 mb-4">
          <div class="flex space-x-2">
            <input v-model="newApartment.number" class="border rounded px-2" placeholder="Número" />
            <input v-model.number="newApartment.floor" type="number" class="border rounded px-2" placeholder="Andar" />
          </div>
          <div class="flex space-x-2">
            <select v-model="newApartment.tower_id" class="border rounded px-2">
              <option :value="null">Sem torre</option>
              <option v-for="t in towers" :value="t.id" :key="t.id">{{ t.name }}</option>
            </select>
            <select v-model="newApartment.block_id" class="border rounded px-2">
              <option :value="null">Sem bloco</option>
              <option v-for="b in blocks" :value="b.id" :key="b.id">{{ b.name }}</option>
            </select>
          </div>
          <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded">Salvar</button>
        </form>
        <ul class="list-disc ml-5 text-cyan-100">
          <li v-for="a in apartments" :key="a.id">
            {{ a.number }} - andar {{ a.floor }}
            <span v-if="a.tower"> - Torre {{ a.tower.name }}</span>
            <span v-if="a.block"> - Bloco {{ a.block.name }}</span>
          </li>
        </ul>
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
    const headers = { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` };

    const towers = ref([]);
    const blocks = ref([]);
    const apartments = ref([]);

    const newTower = ref({ name: '', condominium_id: null });
    const newBlock = ref({ name: '', tower_id: null, condominium_id: null });
    const newApartment = ref({ number: '', floor: 1, tower_id: null, block_id: null, condominium_id: null });

    const loadData = async () => {
      const respTowers = await fetch('/api/towers', { headers });
      towers.value = respTowers.ok ? await respTowers.json() : [];

      const respBlocks = await fetch('/api/blocks', { headers });
      blocks.value = respBlocks.ok ? await respBlocks.json() : [];

      const respApartments = await fetch('/api/apartments', { headers });
      apartments.value = respApartments.ok ? await respApartments.json() : [];
    };

    const createTower = async () => {
      if (!newTower.value.name) return;
      newTower.value.condominium_id = towers.value[0]?.condominium_id || 1;
      const resp = await fetch('/api/towers', { method: 'POST', headers, body: JSON.stringify(newTower.value) });
      if (resp.ok) {
        towers.value.push(await resp.json());
        newTower.value.name = '';
      }
    };

    const createBlock = async () => {
      if (!newBlock.value.name) return;
      newBlock.value.condominium_id = towers.value[0]?.condominium_id || 1;
      const resp = await fetch('/api/blocks', { method: 'POST', headers, body: JSON.stringify(newBlock.value) });
      if (resp.ok) {
        blocks.value.push(await resp.json());
        newBlock.value.name = '';
        newBlock.value.tower_id = null;
      }
    };

    const createApartment = async () => {
      if (!newApartment.value.number) return;
      newApartment.value.condominium_id = towers.value[0]?.condominium_id || blocks.value[0]?.condominium_id || 1;
      const resp = await fetch('/api/apartments', { method: 'POST', headers, body: JSON.stringify(newApartment.value) });
      if (resp.ok) {
        apartments.value.push(await resp.json());
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
