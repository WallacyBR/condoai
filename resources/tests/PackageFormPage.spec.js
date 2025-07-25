

test('pagina de formulario de encomenda existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('WebcamCapture');
});
