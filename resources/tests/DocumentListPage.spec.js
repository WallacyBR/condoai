

test('lista de documentos possui filtros', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Documentos do Condomínio');
  expect(content).toMatch('Filtrar por nome do arquivo');
});
