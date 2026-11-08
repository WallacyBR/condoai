import fs from 'fs';

const fileUrl = new URL('../js/components/VehiclesView.vue', import.meta.url);

test('componente existe', async () => {
  const component = await import('../js/components/VehiclesView.vue');
  expect(component.default).toBeTruthy();
});

test('lista exibe vaga quando presente', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('v.parking_spot');
});
