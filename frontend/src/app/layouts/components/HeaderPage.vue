<script lang="ts" setup>
import type {BreadcrumbItem} from 'vuetify/lib/components/VBreadcrumbs/VBreadcrumbs.js'
import { storeToRefs } from 'pinia'
import {computed} from 'vue'
import {type RouteLocationMatched, useRoute} from 'vue-router'
import {useAppStore} from '@/modules/core/stores/appStore'
import {useLanguage} from '@/modules/translation/composables/language'

interface AppRouteMeta {
  title?: string
  icon?: string
  breadcrumb?: boolean
  pageHeader?: boolean
  transParam?: Record<string, string>
}

type BreadcrumbWithRootItem = BreadcrumbItem & { isRoot: boolean }

const route = useRoute()
const meta = computed(() => route.meta as AppRouteMeta)

const {parseTitlePage} = useLanguage()
const appStore = useAppStore()
const { isRtl } = storeToRefs(appStore)

const parsePathParams = (path: string): { key: string; optional: boolean }[] => {
  const out: { key: string; optional: boolean }[] = []
  const re = /:(\w+)(\?)?/g
  let m: RegExpExecArray | null

  while ((m = re.exec(path))) {
    if (m[1]) {
      out.push({key: m[1], optional: Boolean(m[2])})
    }
  }

  return out
}

const resolvePathWithParams = (record: RouteLocationMatched): string | null => {
  let path = record.path
  const specs = parsePathParams(path)

  for (const {key, optional} of specs) {
    const v = route.params[key as keyof typeof route.params]

    if (v == null || v === '') {
      if (optional) {
        path = path.replace(new RegExp(`/\\:${key}\\?`), '')
        path = path.replace(new RegExp(`\\:${key}\\?`), '')
        continue
      }
      return null
    }

    const raw = Array.isArray(v) ? v[0] : v
    if (raw == null) return null

    const val = String(raw)

    path = path.replace(new RegExp(`:${key}\\?`, 'g'), val)
    path = path.replace(new RegExp(`:${key}`, 'g'), val)

  }

  return path.includes(':') ? null : path
}

const makeTo = (record: RouteLocationMatched) => {
  const lastMatched =
    route.matched.length > 0
      ? route.matched[route.matched.length - 1]
      : undefined

  const isCurrent = record === lastMatched

  if (isCurrent || record.redirect !== undefined) return undefined

  const path = resolvePathWithParams(record)
  return path ? {path, query: route.query} : undefined
}


const icon = computed(() => meta.value.icon ?? null)
// Breadcrumb y título quedan como opt-in explícito. El sidebar + tabs
// ya dicen dónde está el user — repetirlo en un h1 + breadcrumb arriba
// es ruido visual. Si alguna pantalla necesita título dinámico (ej.
// Reports con nombre del reporte), se setea `meta.pageHeader = true`.
const hiddenBreadcrumb = computed(() => meta.value.breadcrumb !== true)
const hiddenPageHeader = computed(() => meta.value.pageHeader !== true)

const title = computed(() =>
  meta.value.title
    ? parseTitlePage(meta.value.title, meta.value.transParam ?? {})
    : '',
)

const breadcrumbs = computed(() =>
  route.matched
    .map(r => {
      if (r.name === 'admin.reports.show') {
        r.meta = route.meta
      }

      const rMeta = r.meta as AppRouteMeta

      return {
        title: rMeta.title
          ? parseTitlePage(rMeta.title, rMeta.transParam ?? {})
          : null,
        isRoot: r.path === '/admin',
        to: makeTo(r),
      }
    })
    .filter(r => r.title != null || r.isRoot),
)
</script>


<template>
  <div v-if="!hiddenPageHeader"
       class="page-header d-flex align-center justify-between flex-wrap gap-2 mb-3">
    <div class="d-flex align-center gap-2">
      <VIcon v-if="icon" :icon="icon as string" class="text-primary" size="24"/>
      <h1 class="text-h5 font-weight-bold m-0">{{ title }}</h1>
    </div>
    <div
      v-if="!hiddenBreadcrumb"
      :class="{
        'ml-auto':!isRtl,
        'mr-auto':isRtl,
      }"
      class="d-flex justify-end align-center"
    >
      <VBreadcrumbs
        :items="breadcrumbs as unknown as BreadcrumbWithRootItem[]"
        class=" d-flex justify-end align-center"
      >
        <template #divider>
          <VIcon class=" text-medium-emphasis" icon="tabler-chevron-right" size="18"/>
        </template>
        <template #title="{ item }">
          <RouterLink v-if="item.to" :to="item.to"
                      class="text-medium-emphasis text-decoration-none">
            <span v-if="!(item as BreadcrumbWithRootItem).isRoot">
              {{ item.title }}
            </span>
            <VIcon v-else icon="tabler-smart-home" size="22"/>
          </RouterLink>
          <span v-else>{{ item.title }}</span>
        </template>
      </VBreadcrumbs>
    </div>
  </div>
</template>
