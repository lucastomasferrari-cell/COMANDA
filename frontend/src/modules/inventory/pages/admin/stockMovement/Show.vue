<script lang="ts" setup>
import {computed, onBeforeMount, ref} from 'vue'
import {useI18n} from 'vue-i18n'
import {useStockMovement} from '@/modules/inventory/composables/stockMovement.ts'
import {useAuth} from '@/modules/auth/composables/auth.ts'

const props = defineProps<{
  modelValue: boolean
  id: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()

const {getShowData} = useStockMovement()
const {t} = useI18n()
const {user} = useAuth()

const loading = ref(false)
const isNotFound = ref(false)
const item = ref<Record<string, any> | null>(null)

onBeforeMount(async () => {
  loading.value = true
  const response = await getShowData(props.id)
  if (response.status === 200) {
    item.value = response.data
  } else if (response.status === 404) {
    isNotFound.value = true
  }
  loading.value = false
})

const dialogModel = computed({
  get: () => props.modelValue,
  set: (val: boolean) => emit('update:modelValue', val),
})

const close = () => emit('update:modelValue', false)

</script>

<template>
  <VDialog v-model="dialogModel" max-width="900" scrollable>
    <VCard>
      <VCardTitle class="d-flex justify-space-between align-center">
        {{ t('admin::resource.show', {resource: t('inventory::stock_movements.stock_movement')}) }}
        <v-btn color="default" icon @click="close">
          <v-icon icon="tabler-x"/>
        </v-btn>
      </VCardTitle>
      <VCardText>
        <PageStateWrapper :loading="loading" :not-found="isNotFound">
          <VRow v-if="item">
            <VCol v-if="!user?.assigned_to_branch" cols="4" md="2">
              <BlockInfo :title="t('inventory::stock_movements.show.branch')" :value="item.branch.name"/>
            </VCol>
            <VCol cols="4" md="2">
              <BlockInfo :title="t('inventory::stock_movements.show.ingredient')" :value="item.ingredient.name"/>
            </VCol>
            <VCol cols="4" md="2">
              <BlockInfo :title="t('inventory::stock_movements.show.type')">
                <TableEnum :item="item" column="type"/>
              </BlockInfo>
            </VCol>
            <VCol cols="4" md="2">
              <BlockInfo :title="t('inventory::stock_movements.show.quantity')" :value="item.quantity"/>
            </VCol>
            <VCol cols="6" md="4">
              <BlockInfo :title="t('inventory::stock_movements.show.note')" :value="item.note"/>
            </VCol>
            <template v-if="item.source">
              <VCol cols="4" md="2">
                <BlockInfo :title="t('inventory::stock_movements.show.source_id')" :value="item.source.id"/>
              </VCol>
              <VCol cols="4" md="2">
                <BlockInfo :title="t('inventory::stock_movements.show.source_name')" :value="item.source.name"/>
              </VCol>
            </template>
            <VCol cols="4" md="2">
              <BlockInfo :title="t('inventory::stock_movements.show.created_at')" :value="item.created_at"/>
            </VCol>
            <VCol cols="4" md="2">
              <BlockInfo :title="t('inventory::stock_movements.show.updated_at')" :value="item.updated_at"/>
            </VCol>
          </VRow>
        </PageStateWrapper>
      </VCardText>
    </VCard>
  </VDialog>
</template>
