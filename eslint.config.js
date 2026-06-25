import blumilkDefault from '@blumilksoftware/eslint-config'

export default [
  ...blumilkDefault,
  {
    rules: {
      'n/no-unsupported-features/node-builtins': 'off',
    },
  },
]
