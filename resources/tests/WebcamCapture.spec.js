import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/WebcamCapture.vue');

test('componente webcam existe', () => {
  expect(content).toMatch('<template>');
});
