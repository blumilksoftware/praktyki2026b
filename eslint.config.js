import blumilkDefault from '@blumilksoftware/eslint-config/typescript-config.js'

export default [
  ...blumilkDefault,
  {
    rules: {
      'n/no-unsupported-features/node-builtins': 'off',
    },
  },
]
