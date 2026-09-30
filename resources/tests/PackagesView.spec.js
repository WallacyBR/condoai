import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/PackagesView.vue');

test('botão de info presente', () => {
  expect(content).toMatch('PackageInfoModal');
});
