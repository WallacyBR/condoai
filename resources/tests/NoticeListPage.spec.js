import fs from 'fs';

const fileUrl = new URL('../js/pages/NoticeListPage.vue', import.meta.url);

test('lista de avisos possui tabela', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Listagem de Avisos');
  expect(content).toMatch('table');
});
