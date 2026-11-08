const fs = require('fs');
const path = require('path');
const { config } = require('@vue/test-utils');

global.fs = fs;

const projectRoot = path.resolve(__dirname, '../..');
const sourceRoot = path.join(projectRoot, 'resources/js');
const componentIndex = new Map();

function indexVueFiles(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    const fullPath = path.join(dir, entry.name);

    if (entry.isDirectory()) {
      indexVueFiles(fullPath);
      continue;
    }

    if (entry.isFile() && entry.name.endsWith('.vue')) {
      componentIndex.set(path.basename(entry.name, '.vue'), fullPath);
    }
  }
}

indexVueFiles(sourceRoot);

const RouterLinkStub = {
  name: 'RouterLink',
  props: ['to'],
  template: '<a><slot /></a>'
};

config.global.stubs = {
  ...(config.global.stubs || {}),
  RouterLink: RouterLinkStub,
  'router-link': RouterLinkStub
};

jest.mock('vue-router', () => {
  const { reactive } = require('vue');
  const route = reactive({
    path: '/',
    name: 'test',
    params: {},
    query: {},
    meta: {}
  });

  global.__mockVueRoute = route;

  return {
    useRouter: () => ({
      push: global.__routerPush,
      replace: jest.fn(),
      back: jest.fn()
    }),
    useRoute: () => route,
    createRouter: () => ({
      beforeEach: jest.fn(),
      afterEach: jest.fn(),
      push: jest.fn(),
      replace: jest.fn(),
      isReady: jest.fn(() => Promise.resolve()),
      install: jest.fn()
    }),
    createWebHistory: () => ({}),
    RouterLink: RouterLinkStub
  };
});

function configureLegacyGlobals() {
  const state = expect.getState();
  const specName = path.basename(state.testPath || '', '.spec.js');
  const componentPath = componentIndex.get(specName);

  global.fileUrl = componentPath || '';

  if (componentPath && !global[specName]) {
    try {
      global[specName] = require(componentPath).default;
    } catch (error) {
      global[specName] = { name: specName };
    }
  }
}

beforeAll(() => {
  configureLegacyGlobals();
});

beforeEach(() => {
  global.__routerPush = jest.fn();
  if (global.__mockVueRoute) {
    global.__mockVueRoute.path = '/';
    global.__mockVueRoute.name = 'test';
    global.__mockVueRoute.params = {};
    global.__mockVueRoute.query = {};
    global.__mockVueRoute.meta = {};
  }
  configureLegacyGlobals();
});
