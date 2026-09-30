import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/DashboardView.vue');

test('exibe card de topo', () => {
  expect(content).toMatch('TopCard');
});
