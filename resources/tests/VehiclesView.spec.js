

test('componente existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('<template>');
});

test('lista exibe vaga quando presente', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('v.parking_spot');
});
