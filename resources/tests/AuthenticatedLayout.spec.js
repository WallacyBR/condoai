import fs from 'fs';

const fileUrl = new URL('../js/components/layouts/AuthenticatedLayout.vue', import.meta.url);

let content;

beforeAll(() => {
  content = fs.readFileSync(fileUrl, 'utf8');
});

test('barra superior responsiva existe', () => {
  expect(content).toMatch('id="mobile-top-bar"');
});

test('botao do menu esta configurado', () => {
  expect(content).toMatch('@click="toggleSidebar"');
});
