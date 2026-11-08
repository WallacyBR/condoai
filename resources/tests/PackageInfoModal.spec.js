import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/PackageInfoModal.vue');

test('modal de informacoes existe', () => {
  expect(content).toMatch('<template>');
});
