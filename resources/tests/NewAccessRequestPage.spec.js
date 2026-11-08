let content;

beforeAll(() => {
  content = fs.readFileSync(fileUrl, 'utf8');
});

test('campo de busca de visitante verifica documento ao perder foco', () => {
  expect(content).toMatch('@blur="searchVisitor"');
});

test('carrega unidades pelo endpoint de apartments', () => {
  expect(content).toMatch("api.get('/apartments')");
  expect(content).not.toMatch('/users?profile_type=resident');
});

test('usa unidade de destino no contrato de criacao', () => {
  expect(content).toMatch('v-model="form.apartment_id"');
  expect(content).toMatch("formData.append('apartment_id', form.apartment_id)");
  expect(content).not.toMatch("formData.append('resident_id'");
});

test('template nao contem comentario HTML dentro de tag', () => {
  expect(content).not.toMatch(/<[^>]*<!--/);
});
