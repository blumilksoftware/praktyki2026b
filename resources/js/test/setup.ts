import { config } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import en from '@/lang/en.json'

const i18n = createI18n({
  legacy: false,
  locale: 'en',
  fallbackLocale: 'en',
  messages: { en },
})

config.global.plugins = [i18n]
