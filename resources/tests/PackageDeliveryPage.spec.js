import fs from 'fs';

const fileUrl = new URL('../js/pages/PackageDeliveryPage.vue', import.meta.url);

test('pagina de entrega existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('SignaturePad');
});
