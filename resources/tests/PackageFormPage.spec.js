import fs from 'fs';

const fileUrl = new URL('../js/pages/PackageFormPage.vue', import.meta.url);

test('pagina de formulario de encomenda existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('WebcamCapture');
});
