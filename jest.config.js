export default {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.js$': 'babel-jest'
  },
  moduleFileExtensions: ['js', 'vue'],
  testPathIgnorePatterns: ['/vendor/'],
  transformIgnorePatterns: [],
  moduleNameMapper: {
    '\\.(jpg|jpeg|png|svg)$': '<rootDir>/resources/tests/__mocks__/fileMock.cjs'
  }
};
