import fs from 'fs';

const fileUrl = new URL('../js/components/UnitsListView.vue', import.meta.url);

test('exibe filtros principais', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Buscar unidades');
  expect(content).toMatch('Gerenciar Unidades');
});
