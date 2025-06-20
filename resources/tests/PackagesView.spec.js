import fs from 'fs';

const fileUrl = new URL('../js/components/PackagesView.vue', import.meta.url);

test('bot\u00e3o de info presente', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Info');
});
