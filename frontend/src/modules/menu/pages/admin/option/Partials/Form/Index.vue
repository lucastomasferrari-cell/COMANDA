<script lang="ts" setup>
  import { computed, onBeforeMount, ref, watch } from 'vue'
  import { type RouteLocationRaw, useRouter } from 'vue-router'
  import { useOption } from '@/modules/menu/composables/option.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'
  import Information from './Information.vue'
  import Values from './Values.vue'

  const props = defineProps<{
    item?: Record<string, any> | null
    action: 'update' | 'create'
  }>()

  const { user } = useAuth()

  const { getFormMeta, update, store } = useOption()
  const router = useRouter()
  const appStore = useAppStore()

  const meta = ref({ branches: [] as Record<string, any>[], types: [], priceTypes: [] })
  const form = useForm({
    name: props.item?.name || {},
    branch_id: user?.assigned_to_branch ? user.branch_id : props.item?.branch?.id,
    type: props.item?.type?.id,
    is_required: props.item?.is_required || false,
    values: (props.item?.values || []).map((v: Record<string, any>) => ({
      ...v,
      price: (v.price?.amount === undefined ? v.price : v.price?.amount),
    })),
  })

  const submit = async () => {
    if (
      !form.loading.value
      && await form.submit(() => props.action == 'create' ? store(form.state) : update(props.item?.id, form.state))
    ) {
      await router.push({ name: 'admin.options.index' } as unknown as RouteLocationRaw)
    }
  }

  onBeforeMount(async () => {
    try {
      const response = (await getFormMeta()).data.body
      meta.value.branches = response.branches
      meta.value.types = response.types
      meta.value.priceTypes = response.price_types
    } catch {
    /* Empty */
    }
  })

  const isMultipleOptions = computed(() => hasMultipleOptions(form.state.type))

  const hasMultipleOptions = (type: string) => ['select', 'multiple_select', 'checkbox', 'radio'].includes(type)

  watch(() => form.state.type, (newValue, oldValue) => {
    if (!oldValue || (hasMultipleOptions(newValue) != hasMultipleOptions(oldValue))) {
      form.state.values = []
      if (isMultipleOptions.value) {
        form.state.values.push({
          label: {},
          price: null,
          price_type: 'fixed',
        })
      } else {
        form.state.values.push({
          price: null,
          price_type: 'fixed',
        })
      }
    }
  })

  const currency = computed(() => {
    if (form.state.branch_id) {
      const selectedBranch = meta.value.branches.find((item: Record<string, any>) => item.id === form.state.branch_id)
      if (selectedBranch) {
        return selectedBranch.currency
      }
    }
    return appStore.currency
  })
</script>

<template>
  <BaseForm
    v-slot="{ currentLanguage }"
    :action="action"
    has-multiple-language
    :loading="form.loading.value"
    resource="options"
    @submit="submit"
  >
    <VRow>
      <VCol cols="12" md="5">
        <Information
          :current-language="currentLanguage"
          :form="form"
          :meta="meta"
          :show-select-branch="action=='create' && !user?.assigned_to_branch"
        />
      </VCol>
      <VCol cols="12" md="7">
        <Values
          :currency="currency"
          :current-language="currentLanguage"
          :form="form"
          :is-multiple-options="isMultipleOptions"
          :meta="meta"
        />
      </VCol>
    </VRow>
  </BaseForm>
</template>
