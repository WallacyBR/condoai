

test('componente webcam existe', () => {
  const content = fs.readFileSync(fileUrl, 'utf8');
  expect(content).toMatch('<template>');
});
