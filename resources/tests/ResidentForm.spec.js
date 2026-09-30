import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/ResidentForm.vue');

test('componente existe', () => {
  expect(content).toMatch('<template>');
});
