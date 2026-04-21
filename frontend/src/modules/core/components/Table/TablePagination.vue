<script lang="ts" setup>
  import type { PaginationMeta } from '@/modules/core/contracts/Table.ts'
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{ pagination: PaginationMeta, perPage: number, disabled: boolean }>()
  const emit = defineEmits<{
    (e: 'update:page', page: number): void
    (e: 'update:perPage', perPage: number): void
  }>()

  const { t } = useI18n()

  const page = ref(props.pagination.current_page)
  const perPage = ref(props.perPage)

  watch(() => props.pagination.current_page, val => {
    page.value = val
  })

  watch(page, val => {
    emit('update:page', val)
  })

  watch(perPage, val => emit('update:perPage', val))

  const maxVisiblePages = 5

  const visiblePages = computed(() => {
    const total = props.pagination.last_page
    const current = page.value
    const pages: number[] = []

    if (total === 0) return pages

    let start = Math.max(current - Math.floor(maxVisiblePages / 2), 1)
    let end = start + maxVisiblePages - 1

    if (end > total) {
      end = total
      start = Math.max(end - maxVisiblePages + 1, 1)
    }

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    return pages
  })

  const hasLeadingEllipsis = computed(() => {
    const pages = visiblePages.value
    const firstPage = pages.length > 0 ? pages[0] : undefined
    return typeof firstPage === 'number' && firstPage > 1
  })

  const hasTrailingEllipsis = computed(() => {
    const pages = visiblePages.value
    const lastPage = pages.length > 0 ? pages[pages.length - 1] : undefined
    return typeof lastPage === 'number' && lastPage < props.pagination.last_page
  })
</script>

<template>
  <VRow class="py-4 px-6 align-center pagination">
    <VCol
      class="d-flex align-center text-medium-emphasis text-md-body-1"
      cols="12"
      md="4"
    >
      {{
        t('admin::admin.table.showing_from_to_of_total_entries', {
          from: pagination.from,
          to: pagination.to,
          total: pagination.total,
        })
      }}
    </VCol>

    <VCol
      class="d-flex align-center justify-center gap-2"
      cols="12"
      md="4"
    >
      <span class="text-caption">{{ t('admin::admin.table.show') }}</span>
      <VSelect
        v-model="perPage"
        class="ma-0"
        density="compact"
        :disabled="disabled"
        hide-details
        :items="[10, 15, 25, 40, 50, 100]"
        style="max-width: 80px"
        variant="outlined"
      />
      <span class="text-caption">{{ t('admin::admin.table.entries') }}</span>
    </VCol>

    <VCol
      class="d-flex justify-end align-center flex-wrap gap-1"
      cols="12"
      md="4"
    >
      <VBtn
        :disabled="disabled || page <= 1"
        icon="tabler-chevrons-left"
        size="small"
        variant="text"
        @click="page = 1"
      />
      <VBtn
        :disabled="disabled || page <= 1"
        icon="tabler-chevron-left"
        size="small"
        variant="text"
        @click="page--"
      />

      <span v-if="hasLeadingEllipsis" class="px-1">…</span>

      <VBtn
        v-for="n in visiblePages"
        :key="n"
        :color="page === n ? 'primary' : undefined"
        :disabled="disabled"
        size="small"
        :variant="page === n ? 'flat' : 'text'"
        @click="page = n"
      >
        {{ n }}
      </VBtn>

      <span v-if="hasTrailingEllipsis" class="px-1">
        …
      </span>

      <VBtn
        :disabled="disabled || page >= pagination.last_page"
        icon="tabler-chevron-right"
        size="small"
        variant="text"
        @click="page++"
      />
      <VBtn
        :disabled="disabled || page >= pagination.last_page"
        icon="tabler-chevrons-right"
        size="small"
        variant="text"
        @click="page = pagination.last_page"
      />
    </VCol>
  </VRow>
</template>
