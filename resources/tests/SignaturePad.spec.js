import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/SignaturePad.vue');

test('componente de assinatura existe', () => {
  expect(content).toMatch('<canvas');
});
