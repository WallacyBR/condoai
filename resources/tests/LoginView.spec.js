import fs from 'fs';

const fileUrl = new URL('../js/components/LoginView.vue', import.meta.url);

test('campos de email e senha estao presentes', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('id="email"');
  expect(content).toMatch('id="password"');
});
