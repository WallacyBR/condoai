export const get = jest.fn();
export const post = jest.fn();
export const put = jest.fn();
export const delete = jest.fn();

const api = {
  get,
  post,
  put,
  delete,
};

export default api;