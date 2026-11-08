

test('renderiza formulario da unidade', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('N\u00famero da Unidade');
  expect(content).toMatch('Tipo *');
});
