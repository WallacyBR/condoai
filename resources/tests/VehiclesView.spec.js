import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/VehiclesView.vue');

test('componente existe', () => {
  expect(content).toMatch('<template>');
});

test('lista exibe vaga quando presente', () => {
  expect(content).toMatch('v.parking_spot');
});
