import fs from 'fs';

const fileUrl = new URL('../js/components/SignaturePad.vue', import.meta.url);

test('componente de assinatura existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('<canvas');
});
