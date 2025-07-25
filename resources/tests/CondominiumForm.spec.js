

test('carrega dados do condomínio para edição', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('watch(() => props.condominium');
});
