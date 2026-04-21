<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import {
    useActivityLog,
  } from '@/modules/activity/composables/activityLog.ts'

  const { getShowData } = useActivityLog()
  const route = useRoute()
  const { t } = useI18n()

  const loading = ref(false)
  const isNotFound = ref(false)
  const data = ref<Record<string, any> | null>(null)
  const properties = computed(() => data.value?.properties)

  const parsedChanges = computed(() => {
    const oldProps = properties.value.old || {}
    const newProps = properties.value.attributes || {}

    const allKeys = new Set([
      ...Object.keys(oldProps),
      ...Object.keys(newProps),
    ])

    const parse = (val: any) => {
      if (val === null || val === '') return '-'
      if (typeof val === 'string') {
        try {
          const parsed = JSON.parse(val)
          if (typeof parsed === 'object') return Object.values(parsed).join(', ')
          return parsed
        } catch {
          return val
        }
      }
      return val
    }

    const result: Array<{ key: string, oldValue: any, newValue: any }> = []

    for (const key of allKeys) {
      result.push({
        key,
        oldValue: parse(oldProps[key]),
        newValue: parse(newProps[key]),
      })
    }

    return result
  })

  onMounted(async () => {
    loading.value = true
    const response = await getShowData((route.params as Record<string, any>).id)
    if (response.status === 200) {
      data.value = response.data
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })

  const hasOldData = computed(() => {
    const old = data.value?.properties?.old
    return old && typeof old === 'object' && Object.keys(old).length > 0
  })
</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <template v-if="data!=null">
      <VRow>
        <VCol cols="12" md="6">
          <VCard>
            <VCardText>
              <div>
                <p class="font-weight-bold mb-0">{{ t('activitylog::activity_logs.show.user') }}</p>
                <p>{{ data?.user.name }} - {{ data?.user.email }}</p>
              </div>
              <div>
                <p class="font-weight-bold mb-0">{{
                  t('activitylog::activity_logs.show.subject')
                }}</p>
                <p>{{ data?.subject }}</p>
              </div>
              <div>
                <p class="font-weight-bold mb-0">{{
                  t('activitylog::activity_logs.show.description')
                }}</p>
                <p>{{ data?.description }}</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" md="3">
          <VCard>
            <VCardText>
              <div>
                <p class="font-weight-bold mb-0">{{
                  t('activitylog::activity_logs.show.log_name')
                }}</p>
                <p>{{ data?.log_name }} </p>
              </div>
              <div>
                <p class="font-weight-bold mb-0">{{
                  t('activitylog::activity_logs.show.event')
                }}</p>
                <p>{{ data?.event }}</p>
              </div>
              <div>
                <p class="font-weight-bold mb-0">{{
                  t('activitylog::activity_logs.show.logged_at')
                }}</p>
                <p>{{ data?.created_at }}</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" md="3">
          <VCard>
            <VCardText>
              <div>
                <p class="font-weight-bold mb-0">{{ t('activitylog::activity_logs.show.ip') }}</p>
                <p>{{ data?.ip }} </p>
              </div>
              <div>
                <p class="font-weight-bold mb-0">{{
                  t('activitylog::activity_logs.show.http_method')
                }}</p>
                <p>{{ data?.properties?.info?.http_method }}</p>
              </div>
              <div>
                <p class="font-weight-bold mb-2">{{
                  t('activitylog::activity_logs.show.agent')
                }}</p>
                <p>
                  <TableAgent :item="{agent: data?.agent}" />
                </p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
      <VRow>
        <VCol class="12">
          <VCard class="pa-4">
            <VCardTitle class="text-h6 font-weight-bold">
              {{ t('activitylog::activity_logs.show.changes') }}
            </VCardTitle>
            <VTable density="compact">
              <thead class="text-capitalize">
                <tr>
                  <th class="text-left font-weight-bold">
                    {{ t('activitylog::activity_logs.show.field') }}
                  </th>
                  <th v-if="hasOldData" class="text-left font-weight-bold">
                    {{ t('activitylog::activity_logs.show.old') }}
                  </th>
                  <th class="text-left font-weight-bold">
                    {{ t('activitylog::activity_logs.show.new') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in parsedChanges" :key="row.key">
                  <td class="font-weight-medium">{{ row.key }}</td>
                  <td v-if="hasOldData" class="text-medium-emphasis">{{ row.oldValue }}</td>
                  <td class="text-medium-emphasis">{{ row.newValue }}</td>
                </tr>
              </tbody>
            </VTable>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </PageStateWrapper>
</template>
