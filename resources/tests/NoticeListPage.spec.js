import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('pages/NoticeListPage.vue');

test('lista de avisos possui layout', () => {
  expect(content).toMatch('Avisos do Condomínio');
  expect(content).toMatch('grid');
});
