

test('exibe card de topo', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('TopCard');
});
