import fs from 'fs';

const fileUrl = new URL('../js/components/ParkingSpotsView.vue', import.meta.url);

test('componente existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('<template>');
});
