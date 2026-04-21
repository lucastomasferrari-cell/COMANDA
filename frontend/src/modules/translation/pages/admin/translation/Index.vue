<script lang="ts" setup>
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { useLanguage } from '@/modules/translation/composables/language.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  interface TranslationRecord {
    key: string
    translations: Record<string, string>
  }

  type TranslationRow = Record<string, string> & { key: string }

  const appStore = useAppStore()
  const { update, loadData } = useLanguage()
  const { t } = useI18n()
  const { can } = useAuth()

  const records = ref<TranslationRecord[]>([])
  const loading = ref(true)
  const editing = ref<{
    key: string
    lang: string
    oldValue: string | null
    value: string
  } | null>(null)
  const search = ref('')
  const debouncedSearch = ref('')

  onMounted(async () => {
    records.value = (await loadData()) || []
    loading.value = false
  })

  debouncedWatch(search, value => {
    debouncedSearch.value = value.trim().toLowerCase()
  }, { debounce: 200, maxWait: 500 })

  const headers = computed(() => [
    { title: t('translation::translations.table.key'), value: 'key' },
    ...appStore.supportedLanguages.map((lang: { id: string, name: string }) => ({
      title: lang.name,
      value: lang.id,
    })),
  ])

  const rows = computed<TranslationRow[]>(() =>
    records.value.map((item: TranslationRecord) => ({
      key: item.key,
      ...item.translations,
    })),
  )

  const saveEdit = () => {
    if (editing.value != null) {
      const record = records.value.find(item => item.key === editing.value?.key)
      const newValue = editing.value.value

      if (record) {
        record.translations[editing.value.lang] = newValue
      }

      if (editing.value.oldValue != newValue) {
        update(
          editing.value.key,
          editing.value.lang,
          newValue,
        )
      }
    }
    editing.value = null
  }

  const filteredRecords = computed(() => {
    if (!debouncedSearch.value) return rows.value

    return rows.value.filter(row =>
      Object.values(row).some(value =>
        typeof value === 'string' && value?.toLowerCase().includes(debouncedSearch.value),
      ),
    )
  })

  const switchEditMode = (item: Record<string, any>, lang: Record<string, any>) => {
    if (can('admin.translations.edit')) {
      editing.value = {
        key: item.key,
        lang: lang.id,
        oldValue: item[lang.id],
        value: item[lang.id] ?? '',
      }
    }
  }
</script>

<template>
  <VDataTable
    :headers="headers"
    :hide-default-footer="loading"
    item-value="key"
    :items="filteredRecords"
    :loading="loading"
  >
    <template #top>
      <VRow class="pa-4 pb-0">
        <VSpacer />
        <VCol cols="12" md="3">
          <VTextField
            v-model="search"
            clearable
            :disabled="loading"
            :placeholder="t('admin::admin.table.search_placeholder')"
            prepend-inner-icon="tabler-search"
          />
        </VCol>
      </VRow>
    </template>
    <template #body="{ items }">
      <tr v-if="loading || items.length === 0">
        <td class="text-center pa-6" :colspan="headers.length">
          <div class="d-flex flex-column align-center justify-center">
            <template v-if="loading">
              <VProgressCircular class="mb-2" color="primary" indeterminate />
              <div class="text-caption text-medium-emphasis">
                {{ t('admin::admin.table.loading') }}
              </div>
            </template>
            <template v-else-if="items.length === 0 && search">
              <div class="text-caption text-medium-emphasis">
                {{ t('admin::admin.table.no_matching_records_found') }}
              </div>
            </template>
          </div>
        </td>
      </tr>
      <tr v-for="item in items" :key="item.key">
        <td class="font-weight-medium">{{ item.key }}</td>
        <td
          v-for="lang in appStore.supportedLanguages"
          :key="lang.id"
          class="translation-cell"
        >
          <div v-if="editing?.key === item.key && editing?.lang === lang.id">
            <VTextField
              v-model="editing.value"
              autofocus
              density="compact"
              hide-details
              @keyup.enter="saveEdit"
            >
              <template #append>
                <VBtn
                  color="default"
                  icon
                  size="small"
                  variant="text"
                  @click.stop="editing = null"
                >
                  <VIcon icon="tabler-x" />
                </VBtn>
                <VBtn
                  color="primary"
                  icon
                  size="small"
                  variant="text"
                  @click.stop="saveEdit"
                >
                  <VIcon icon="tabler-check" />
                </VBtn>
              </template>
            </VTextField>
          </div>
          <div
            v-else
            style="cursor: pointer;"
            @click="switchEditMode(item,lang)"
          >
            <span
              class="text-translation"
              :class="{ 'translation-cell--empty': !item[lang.id],'text-error':!item[lang.id],'text-blue':item[lang.id] }"
            >
              {{ item[lang.id as string] || t('translation::translations.table.empty') }}
            </span>
          </div>
        </td>
      </tr>
    </template>
  </VDataTable>
</template>

<style scoped>
.translation-cell {
  padding: 8px 12px;
  max-width: 320px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 14px;
  color: #333;

  .translation-cell--empty {
    font-style: italic;
  }

  .text-blue {
    color: #3498db;
  }
}

.translation-cell:hover {
  .text-translation {
    text-decoration: underline;
  }
}
</style>
