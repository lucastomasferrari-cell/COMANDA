<script lang="ts" setup>
  import { computed, reactive, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import draggable from 'vuedraggable'
  import { useOption } from '@/modules/menu/composables/option.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import Option from './Option.vue'

  const props = defineProps<{ form: any, currentLanguage: Record<string, any>, meta: Record<string, any> }>()
  const { t } = useI18n()
  const { can } = useAuth()
  const { getShowData: showOptionData } = useOption()

  const template = reactive({
    value: null,
    loading: false,
  })

  const addOption = () => {
    props.form.state.options.push({
      name: {},
      type: null,
      is_required: false,
      is_global: false,
      values: [],
    })
  }

  const insertTemplate = async () => {
    if (can('admin.options.show') && template.value) {
      template.loading = true
      const response = await showOptionData(template.value, true)
      if (response.status === 200) {
        props.form.state.options.push({
          ...response.data,
          type: response.data.type.id,
          id: null,
          values: response.data.values.map((v: Record<string, any>) => ({
            ...v, label: v.label || {},
            id: null,
            price: (v.price?.amount === undefined ? v.price : v.price?.amount),
          })),
        })
      }
      template.loading = false
      template.value = null
    }
  }

  const expansionPanels = ref<number[]>(Array.from({ length: props.form.state.options.length }, (_, i) => i))

  watch(
    () => props.form.state.options.length,
    (newLen, oldLen) => {
      if (newLen > oldLen) {
        expansionPanels.value = [...expansionPanels.value, newLen - 1]
      }
    },
  )

  const isCollapseAll = computed(() => expansionPanels.value.length === 0)

  const switchExpansionPanels = () => {
    expansionPanels.value = isCollapseAll.value ? Array.from({ length: props.form.state.options.length }, (_, i) => i) : []
  }

  onMounted(() => {
    if (props.form.state.options.length === 0) {
      addOption()
    }
  })
</script>

<template>
  <VCard class="mt-3">

    <VCardTitle class="d-flex justify-space-between align-center mb-2">
      <div class="d-flex align-center">
        <VIcon class="me-2" icon="tabler-adjustments" size="20" />
        <span>{{ t('product::products.form.cards.options') }}</span>
      </div>

      <div class="d-flex gap-2">
        <VTooltip :text="t(`product::products.form.${isCollapseAll ? 'expand_all':'collapse_all'}`)">
          <template #activator="{ props }">
            <VBtn
              color="default"
              icon
              size="small"
              v-bind="props"
              variant="text"
              @click="switchExpansionPanels"
            >
              <VIcon :icon="isCollapseAll ?'tabler-chevrons-down':'tabler-chevrons-up'" />
            </VBtn>
          </template>
        </VTooltip>
      </div>
    </VCardTitle>
    <VCardText>
      <VRow>
        <VCol cols="12">
          <VExpansionPanels
            v-model="expansionPanels"
            elevation="0"
            multiple
          >
            <draggable
              animation="180"
              chosen-class="drag-chosen"
              class="w-100"
              :disabled="form.state.options.length < 2"
              drag-class="drag-dragging"
              ghost-class="drag-ghost"
              group="options"
              handle=".drag-handle"
              item-key="id"
              :list="form.state.options"
            >
              <template #item="{ element: option, index }">
                <Option
                  :current-language="currentLanguage"
                  :form="form"
                  :index="index"
                  :meta="meta"
                  :option="option"
                />
              </template>
            </draggable>
          </VExpansionPanels>
        </VCol>

        <VCol cols="12">
          <div class="d-flex align-center justify-space-between">
            <VBtn color="secondary" @click="addOption">
              <VIcon icon="tabler-adjustments-plus" start />
              {{ t('product::products.form.add_option') }}
            </VBtn>

            <div v-if="can('admin.options.show')" class="d-flex align-center gap-3">
              <VSelect
                v-model="template.value"
                item-title="name"
                item-value="id"
                :items="meta.optionTemplates"
                :label="t('product::products.form.select_template')"
                style="min-width:200px"
              />

              <VBtn
                color="secondary"
                :disabled="!template.value || template.loading"
                :loading="template.loading"
                @click="insertTemplate"
              >
                <VIcon icon="tabler-plus" start />
                {{ t('product::products.form.insert') }}
              </VBtn>
            </div>
          </div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
