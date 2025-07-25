

test('renderiza modal de visita', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Nova Visita');
  expect(content).toMatch('Nome do Visitante');
});
