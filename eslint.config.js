import blumilkDefault from '@blumilksoftware/eslint-config'

export default [
  { ignores: ['storage/**'] },
  ...blumilkDefault,
  {
    rules: {
      'n/no-unsupported-features/node-builtins': 'off',
    },
  },
]
