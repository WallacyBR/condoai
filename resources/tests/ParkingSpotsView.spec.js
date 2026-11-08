import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/ParkingSpotsView.vue');

test('componente existe', () => {
  expect(content).toMatch('<template>');
});
