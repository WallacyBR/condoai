

test('exibe filtros principais', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Buscar unidades');
  expect(content).toMatch('Gerenciar Unidades');
});
