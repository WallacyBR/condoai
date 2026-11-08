import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/LoginView.vue');

test('campos de email e senha estao presentes', () => {
  expect(content).toMatch('id="email"');
  expect(content).toMatch('id="password"');
});
