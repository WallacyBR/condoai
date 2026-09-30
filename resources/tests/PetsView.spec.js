import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/PetsView.vue');

test('componente existe', () => {
  expect(content).toMatch('<template>');
});
