import { config } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages: {
    en: {
      auth: {
        fields: {
          showPassword: 'Show password',
          hidePassword: 'Hide password',
        },
      },
    },
  },
})

config.global.plugins = [i18n]
