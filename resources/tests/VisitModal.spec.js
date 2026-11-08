import fs from 'fs';

const fileUrl = new URL('../js/components/VisitModal.vue', import.meta.url);

test('renderiza modal de visita', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Nova Visita');
  expect(content).toMatch('Nome do Visitante');
});
