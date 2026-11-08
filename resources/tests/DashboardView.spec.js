import { readLegacyComponentSource } from './helpers/legacyFileContent.js';

const content = readLegacyComponentSource('components/DashboardView.vue');

test('exibe métricas com StatsCard', () => {
  expect(content).toMatch('StatsCard');
});
