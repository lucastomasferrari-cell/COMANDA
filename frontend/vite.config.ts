import { fileURLToPath, URL } from 'node:url'

import vue from '@vitejs/plugin-vue'
import AutoImport from 'unplugin-auto-import/vite'
import Fonts from 'unplugin-fonts/vite'
import Components from 'unplugin-vue-components/vite'
import { VueRouterAutoImports } from 'unplugin-vue-router'
import vueRouter from 'unplugin-vue-router/vite'
import { defineConfig } from 'vite'
import { VitePWA } from 'vite-plugin-pwa'
import vueDevTools from 'vite-plugin-vue-devtools'
import Vuetify, { transformAssetUrls } from 'vite-plugin-vuetify'
import svgLoader from 'vite-svg-loader'

export default defineConfig(({ command }) => ({
  plugins: [
    vue({
      template: { transformAssetUrls },
    }),
    vueRouter({
      dts: 'src/typed-router.d.ts',
      watch: command === 'serve',
    }),
    Components({
      dirs: ['src/modules/core/components'],
      dts: true,
      resolvers: [
        componentName => {
          // Auto import `VueApexCharts`
          if (componentName === 'VueApexCharts') {
            return { name: 'default', from: 'vue3-apexcharts', as: 'VueApexCharts' }
          }
        },
      ],
    }),
    ...(command === 'serve' ? [vueDevTools()] : []),
    AutoImport({
      imports: [
        'vue',
        VueRouterAutoImports,
        {
          pinia: ['defineStore', 'storeToRefs'],
        },
        '@vueuse/core', '@vueuse/math',
      ],
      dts: 'src/auto-imports.d.ts',
      eslintrc: {
        enabled: true,
      },
      vueTemplate: true,
      ignore: ['useCookies', 'useStorage'],
    }),
    Fonts({
      fontsource: {
        families: [
          {
            name: 'Cairo',
            weights: [200, 300, 400, 500, 600, 700, 800, 900],
            styles: ['normal'],
          },
        ],
      },
    }),
    svgLoader(),
    VitePWA({
      registerType: 'autoUpdate',
      injectRegister: false,
      manifest: false,
      includeAssets: ['favicon.ico', 'logo.svg'],
      workbox: {
        cleanupOutdatedCaches: true,
        clientsClaim: true,
        maximumFileSizeToCacheInBytes: 10 * 1024 * 1024,
      },
      devOptions: {
        enabled: true,
        suppressWarnings: true,
      },
    }),
    Vuetify({
      autoImport: true,
      styles: {
        configFile: 'src/assets/scss/styles/variables/_vuetify.scss',
      },
    }),
  ],
  optimizeDeps: {
    exclude: [
      'vuetify',
      'vue-router',
      'unplugin-vue-router/runtime',
      'unplugin-vue-router/data-loaders',
      'unplugin-vue-router/data-loaders/basic',
    ],
  },
  define: { 'process.env': {} },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('src', import.meta.url)),
      '@configured-variables': fileURLToPath(new URL('src/assets/scss/styles/variables/_template.scss', import.meta.url)),
    },
  },
  server: {
    port: 3101,
  },
  build: {
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      output: {
        manualChunks (id) {
          if (!id.includes('node_modules')) {
            return
          }

          if (id.includes('vuetify')) {
            return 'vendor-vuetify'
          }

          if (id.includes('leaflet')) {
            return 'vendor-leaflet'
          }

          if (id.includes('apexcharts') || id.includes('vue3-apexcharts')) {
            return 'vendor-charts'
          }

          if (id.includes('tiptap') || id.includes('prosemirror')) {
            return 'vendor-editor'
          }

          if (id.includes('vue-router')) {
            return 'vendor-router'
          }

          if (id.includes('pinia')) {
            return 'vendor-store'
          }

          if (id.includes('axios')) {
            return 'vendor-http'
          }

          if (id.includes('vue-i18n')) {
            return 'vendor-i18n'
          }

          if (id.includes('@vueuse')) {
            return 'vendor-utils'
          }

          if (id.includes('lodash')) {
            return 'vendor-lodash'
          }

          if (id.includes('xlsx')) {
            return 'vendor-xlsx'
          }

          if (id.includes('sweetalert2')) {
            return 'vendor-swal'
          }

          return 'vendor'
        },
      },
    },
  },
}))
