import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('pages/PackageDeliveryPage.vue');

test('pagina de entrega existe', () => {
  expect(content).toMatch('SignaturePad');
});
