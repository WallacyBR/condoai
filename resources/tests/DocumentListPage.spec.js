import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('pages/DocumentListPage.vue');

test('lista de documentos possui filtros', () => {
  expect(content).toMatch('Documentos do Condomínio');
  expect(content).toMatch('Filtrar por nome do arquivo');
});
