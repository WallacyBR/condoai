import fs from 'fs';
const fileUrl = new URL('../js/components/DashboardView.vue', import.meta.url);

test('exibe card de topo', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('TopCard');
});
