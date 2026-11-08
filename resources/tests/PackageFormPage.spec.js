import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('pages/PackageFormPage.vue');

test('pagina de formulario de encomenda existe', () => {
  expect(content).toMatch('<template>');
});
