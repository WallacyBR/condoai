module.exports = {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\.vue$': '@vue/vue3-jest',
    '^.+\.(js|vue)$' : 'babel-jest'
  },
  moduleFileExtensions: ['js', 'vue'],
  testMatch: ['<rootDir>/resources/tests/**/*.spec.js'],
  setupFilesAfterEnv: ['<rootDir>/resources/tests/setup.cjs'],
  testPathIgnorePatterns: [
    '/vendor/',
    '\\.vitest\\.spec\\.js$'
  ],
  transformIgnorePatterns: [
    '/node_modules/(?!(date-fns|pinia)/)'
  ],
  moduleNameMapper: {
    '^@vue/test-utils$': '<rootDir>/node_modules/@vue/test-utils/dist/vue-test-utils.cjs.js',
    '^vitest$': '<rootDir>/resources/tests/__mocks__/vitest.cjs',
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '\.(jpg|jpeg|png|svg)$': '<rootDir>/resources/tests/__mocks__/fileMock.cjs'
  }
};
