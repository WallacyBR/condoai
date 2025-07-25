

test('modal de informacoes existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('Informa');
});
