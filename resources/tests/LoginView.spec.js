

test('campos de email e senha estao presentes', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('id="email"');
  expect(content).toMatch('id="password"');
});
