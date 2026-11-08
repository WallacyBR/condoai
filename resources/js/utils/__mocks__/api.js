const createMockFn = () => {
  if (typeof jest !== 'undefined' && typeof jest.fn === 'function') {
    return jest.fn();
  }
  if (typeof vi !== 'undefined' && typeof vi.fn === 'function') {
    return vi.fn();
  }
  return () => {};
};

export const get = createMockFn();
export const post = createMockFn();
export const put = createMockFn();
export const deleteMethod = createMockFn();
export { deleteMethod as delete };

const api = {
  get,
  post,
  put,
  delete: deleteMethod,
};

export default api;
