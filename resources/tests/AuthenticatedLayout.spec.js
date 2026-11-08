import { beforeAll } from 'vitest';
import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

let content;

beforeAll(() => {
  content = readLegacyComponentSource('components/layouts/AuthenticatedLayout.vue');
});

test('barra superior responsiva existe', () => {
  expect(content).toMatch('id="mobile-top-bar"');
});

test('botao do menu esta configurado', () => {
  expect(content).toMatch('@click="toggleSidebar"');
});
