import fs from 'fs';

const fileUrl = new URL('../js/components/PackagesView.vue', import.meta.url);

test('componente de encomendas existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('<template>');
});
