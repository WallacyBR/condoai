let content;

beforeAll(() => {
  content = fs.readFileSync(fileUrl, 'utf8');
});

test('modal usa unidade por id no contrato de criacao', () => {
  expect(content).toMatch('v-model="form.apartment_id"');
  expect(content).toMatch('apartment_id: form.value.apartment_id');
  expect(content).not.toMatch('resident_id: form.value.resident_id');
});

test('modal carrega unidades pelo endpoint de apartments', () => {
  expect(content).toMatch("api.get('/apartments')");
  expect(content).not.toMatch('resident_name');
  expect(content).not.toMatch('tower_block');
});
