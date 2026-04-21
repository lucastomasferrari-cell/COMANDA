<script lang="ts" setup>
import {computed, onBeforeMount, ref} from 'vue'
import {useI18n} from 'vue-i18n'
import {useToast} from 'vue-toastification'
import {usePurchase} from '@/modules/inventory/composables/purchase.ts'
import {useForm} from '@/modules/core/composables/form.ts'

const props = defineProps<{
  modelValue: boolean
  id: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm'): void
}>()

const {t} = useI18n()
const {getShowData, markAsReceived} = usePurchase()
const toast = useToast()
const form = useForm({
  notes: null,
  reference: null,
  items: [] as Record<string, any>,
})

const purchase = ref<Record<string, any> | null>(null)
const loading = ref(true)

onBeforeMount(async () => {
  loading.value = true
  const response = await getShowData(props.id)

  if (response.status === 200) {
    purchase.value = response.data

    if (purchase.value?.items) {
      purchase.value.items = purchase.value.items.filter(
        (item: Record<string, any>) => item.quantity > item.received_quantity,
      )

      form.state.items = purchase.value.items.map((item: Record<string, any>) => ({
        id: item.id,
        received_quantity: 0,
      }))
    }

    loading.value = false
  } else if (response.status === 404) {
    toast.error(t('core::errors.an_unexpected_error_occurred'))
    close()
  } else {
    close()
  }
})

const dialogModel = computed({
  get: () => props.modelValue,
  set: (val: boolean) => emit('update:modelValue', val),
})

const close = () => emit('update:modelValue', false)

const confirm = async () => {
  if (
    !form.loading.value
    && await form.submit(() => markAsReceived(props.id, {
      ...form.state,
      items: form.state.items.filter((item: Record<string, any>) => item.received_quantity > 0),
    }))
  ) {
    emit('confirm')
    close()
  }
}

const btnConfirmDisabled = computed(() => {
  return !form.state.items.some((item: Record<string, any>) => item.received_quantity > 0)
})
</script>

<template>
  <VDialog v-model="dialogModel" max-width="800" persistent scrollable>
    <VCard>
      <VCardText>
        <div class="mb-3">
          <p class="font-weight-bold mb-1">
            {{ t('inventory::purchases.mark_as_received.dialog.title') }}
          </p>
          <p>
            {{ t('inventory::purchases.mark_as_received.dialog.description') }}
          </p>
        </div>
        <div v-if="loading" class="mt-10 d-flex align-center justify-center">
          <VProgressCircular
            color="primary"
            indeterminate
            size="40"
            width="3"
          />
        </div>
        <div v-else class="mt-5 mb-5">
          <v-table class="w-100 table-items">
            <thead>
            <tr>
              <th style="width: 20%">
                {{ t('inventory::purchases.mark_as_received.table.ingredient') }}
              </th>
              <th style="width: 20%">
                {{ t('inventory::purchases.mark_as_received.table.ordered_quantity') }}
              </th>
              <th style="width: 40%">
                {{ t('inventory::purchases.mark_as_received.table.received_quantity') }}
              </th>
              <th style="width: 20%">
                {{ t('inventory::purchases.mark_as_received.table.unit_cost') }}
              </th>
            </tr>
            </thead>
            <tbody>
            <template v-for="(item,index) in purchase?.items||[]" :key="index">
              <tr v-if="item.quantity > item.received_quantity">
                <td>{{ item.ingredient.name }}</td>
                <td>{{ item.quantity }} {{ item.ingredient.unit.symbol }}</td>
                <td>
                  <VTextField
                    v-model.number="form.state.items[index].received_quantity"
                    :error="!!form.errors.value?.[`items.${item.id}.received_quantity`]"
                    :error-messages="form.errors.value?.[`items.${item.id}.received_quantity`]"
                    min="0"
                    step="0.01"
                  >
                    <template #append-inner>
                      {{ item.ingredient.unit.symbol }}
                    </template>
                  </VTextField>
                </td>
                <td>{{ item.unit_cost.original.formatted }}</td>
              </tr>
            </template>
            </tbody>
          </v-table>
        </div>
        <VRow class="mt-4">
          <VCol cols="12" md="4">
            <VTextField
              v-model.number="form.state.reference"
              :error="!!form.errors.value?.reference"
              :error-messages="form.errors.value?.reference"
              :label="t('inventory::attributes.purchases.reference')"
            />
          </VCol>
          <VCol cols="8">
            <VTextField
              v-model.number="form.state.notes"
              :error="!!form.errors.value?.notes"
              :error-messages="form.errors.value?.notes"
              :label="t('inventory::attributes.purchases.notes')"
              clearable
            />
          </VCol>
        </VRow>
      </VCardText>

      <VCardActions>
        <VSpacer/>
        <VBtn :disabled="form.loading.value" color="default" text @click="close">
          {{ t('admin::admin.buttons.cancel') }}
        </VBtn>
        <VBtn
          :disabled="btnConfirmDisabled||loading||form.loading.value"
          :loading="form.loading.value"
          color="primary"
          @click="confirm"
        >
          {{ t('admin::admin.buttons.confirm') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss" scoped>
.table-items th {
  text-transform: capitalize
}
</style>
