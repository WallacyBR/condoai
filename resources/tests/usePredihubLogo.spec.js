import { logoLight, logoDark, usePredihubLogo } from '../js/composables/usePredihubLogo.js';

jest.mock('../js/composables/useTheme.js', () => ({
  useTheme: jest.fn(() => ({
    isDark: { value: false }
  }))
}));

describe('usePredihubLogo', () => {
  test('exporta os caminhos dos logos claro e escuro', () => {
    expect(typeof logoLight).toBe('string');
    expect(typeof logoDark).toBe('string');
  });

  test('retorna logo claro quando o tema nao esta escuro', () => {
    const { logo } = usePredihubLogo();
    expect(logo.value).toBe(logoLight);
  });
});
