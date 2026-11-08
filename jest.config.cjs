module.exports = {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\.vue$': '@vue/vue3-jest',
    '^.+\.(js|vue)$' : 'babel-jest'
  },
  moduleFileExtensions: ['js', 'vue'],
  testPathIgnorePatterns: ['/vendor/'],
  transformIgnorePatterns: [
    '/node_modules/(?!(date-fns|pinia)/)'
  ],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1',
    '\.(jpg|jpeg|png|svg)$': '<rootDir>/resources/tests/__mocks__/fileMock.cjs'
  }
};