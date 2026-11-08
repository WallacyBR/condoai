const path = require('path');

if (global.expect && !global.expect._toBeTypeOfAdded) {
  global.expect._toBeTypeOfAdded = true;
  global.expect.extend({
    toBeTypeOf(received, expected) {
      const pass = typeof received === expected;
      return {
        pass,
        message: () => `expected ${received} to be of type ${expected}`,
      };
    },
  });
}

const resolvePath = (moduleName) => {
  if (typeof moduleName === 'string' && moduleName.startsWith('.')) {
    return path.resolve(process.cwd(), 'resources/tests', moduleName);
  }
  return moduleName;
};

const getJest = () => (typeof jest !== 'undefined' ? jest : (global.jest || {}));

const vi = {
  fn: (...args) => getJest().fn(...args),
  spyOn: (...args) => getJest().spyOn(...args),
  mock: (...args) => {
    const j = getJest();
    const target = resolvePath(args[0]);
    return j['mock'](target, ...args.slice(1));
  },
  doMock: (...args) => {
    const j = getJest();
    const target = resolvePath(args[0]);
    return j['doMock'](target, ...args.slice(1));
  },
  unmock: (moduleName) => {
    const j = getJest();
    const target = resolvePath(moduleName);
    return j['unmock'](target);
  },
  clearAllMocks: () => getJest().clearAllMocks?.(),
  resetAllMocks: () => getJest().resetAllMocks?.(),
  restoreAllMocks: () => getJest().restoreAllMocks?.(),
  useFakeTimers: (...args) => getJest().useFakeTimers?.(...args),
  useRealTimers: (...args) => getJest().useRealTimers?.(...args),
  setSystemTime: (...args) => getJest().setSystemTime?.(...args),
  advanceTimersByTime: (...args) => getJest().advanceTimersByTime?.(...args),
};

module.exports = {
  describe: (...args) => global.describe(...args),
  it: (...args) => global.it(...args),
  test: (...args) => global.test(...args),
  expect: global.expect,
  beforeEach: (...args) => global.beforeEach(...args),
  afterEach: (...args) => global.afterEach(...args),
  beforeAll: (...args) => global.beforeAll(...args),
  afterAll: (...args) => global.afterAll(...args),
  vi,
};
