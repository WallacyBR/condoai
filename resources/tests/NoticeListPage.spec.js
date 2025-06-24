import fs from 'fs';

const fileUrl = new URL('../js/pages/NoticeListPage.vue', import.meta.url);

test('lista de avisos possui layout', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Avisos do Condomínio');
  expect(content).toMatch('grid');
});
