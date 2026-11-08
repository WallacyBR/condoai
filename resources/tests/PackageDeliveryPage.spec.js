

test('pagina de entrega existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('SignaturePad');
});
