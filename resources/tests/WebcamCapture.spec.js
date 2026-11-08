import fs from 'fs';

const fileUrl = new URL('../js/components/WebcamCapture.vue', import.meta.url);

test('componente webcam existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('<template>');
});
