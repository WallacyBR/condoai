import fs from 'fs';

const fileUrl = new URL('../js/components/PackageInfoModal.vue', import.meta.url);

test('modal de informacoes existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Informa');
});
