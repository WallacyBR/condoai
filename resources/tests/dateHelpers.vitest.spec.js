import { describe, it, expect } from 'vitest';
import { formatDate } from '../js/utils/dateHelpers';

describe('formatDate', () => {
  it('formata datas ISO sem deslocar o dia no fuso local', () => {
    expect(formatDate('2026-01-01')).toBe('01/01/2026');
    expect(formatDate('2026-12-31')).toBe('31/12/2026');
  });
});
