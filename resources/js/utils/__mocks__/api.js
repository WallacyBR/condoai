import { vi } from 'vitest';

export const get = vi.fn();
export const post = vi.fn();
export const put = vi.fn();
export const deleteMethod = vi.fn();

const api = {
  get,
  post,
  put,
  delete: deleteMethod,
};

export default api;
