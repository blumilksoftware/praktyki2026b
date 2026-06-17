import '../css/app.css'
import { createApp, h, type DefineComponent } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createI18n } from 'vue-i18n'
import pl from './lang/pl.json'
import en from './lang/en.json'

const appName = import.meta.env.VITE_APP_NAME || 'Applikuj'

const i18n = createI18n({
  legacy: false,
  locale: 'pl',
  fallbackLocale: 'pl',
  messages: { pl, en },
})

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: async (name) => await resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(i18n)
      .mount(el)
  },
  progress: {
    color: '#4B5563',
  },
})
