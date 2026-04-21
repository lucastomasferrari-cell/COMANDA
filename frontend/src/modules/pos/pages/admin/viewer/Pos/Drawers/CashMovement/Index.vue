<script lang="ts" setup>
  import { storeToRefs } from 'pinia'
  import type { PosMeta } from '@/modules/pos/contracts/posViewer.ts'
  import { computed, watch } from 'vue'
  import { useI18n } from 'vue-i18n'
  import { usePosCashMovement } from '@/modules/pos/composables/posCashMovement.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const props = defineProps<{
    modelValue: boolean
    registerId: number | null
    meta: PosMeta
  }>()

  const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
  }>()

  const open = computed({
    get: () => props.modelValue,
    set: val => emit('update:modelValue', val),
  })

  const { store } = usePosCashMovement()
  const { t } = useI18n()
  const appStore = useAppStore()
  const { isRtl } = storeToRefs(appStore)
  const drawerTransition = computed(() => isRtl.value ? 'slide-x-transition' : 'slide-x-reverse-transition')
  const form = useForm({
    pos_register_id: props.registerId,
    reference: null,
    amount: null,
    notes: null,
    reason: null,
    direction: 'out',
  })

  const isDisabled = computed(() =>
    !form.state.direction
    || !form.state.reason
    || !form.state.amount
    || Number.parseFloat(form.state.amount) < 1,
  )

  const close = () => {
    emit('update:modelValue', false)
    form.state.reference = null
    form.state.amount = null
    form.state.notes = null
    form.state.reason = null
    form.state.direction = 'out'
  }

  const submit = async () => {
    if (!isDisabled.value && !form.loading.value && await form.submit(() => store(form.state))) {
      close()
    }
  }

  const filteredReasons = computed(() => {
    return props.meta.reasons[form.state.direction] || []
  })

  watch(() => form.state.direction, () => {
    form.state.reason = null
  })
</script>

<template>
  <v-navigation-drawer
    v-model="open"
    color="grey-light"
    :location="isRtl?'left':'right'"
    temporary
    :transition="drawerTransition"
    :width="600"
  >
    <VCard color="grey-light">
      <VCardText>
        <div class="d-flex mb-4 justify-space-between align-center">
          <h3 class="d-flex align-center ga-1">
            <VIcon color="#16a085" icon="tabler-cash" size="25" />
            {{ t('pos::pos_viewer.cash_flow_management') }}
          </h3>
          <VBtn
            color="grey-200"
            icon="tabler-x"
            @click="close"
          />
        </div>
        <VForm @submit.prevent="submit">
          <VRow>
            <VCol cols="12">
              <div class="directions">
                <div
                  v-for="direction in meta.directions"
                  :key="direction.id"
                  class="direction-card"
                  :class="{ active: form.state.direction === direction.id }"
                  @click="form.state.direction=direction.id"
                >
                  <div class="direction-info">
                    <VIcon :color="direction.color" :icon="direction.icon" />
                    <span class="name">{{ direction.name }}</span>
                  </div>
                  <div class="checkbox">
                    <v-icon v-if="form.state.direction === direction.id" color="white" icon="tabler-check" />
                  </div>
                </div>
              </div>
            </VCol>
            <VCol cols="12">
              <VChipGroup
                v-model="form.state.reason"
                base-color="default"
                column
                mandatory
              >
                <VChip
                  v-for="reason in filteredReasons"
                  :key="reason.id"
                  class="ma-1"
                  color="primary"
                  size="x-large"
                  :value="reason.id"
                >
                  <VIcon :icon="reason.icon" start />
                  {{ reason.name }}
                </VChip>
              </VChipGroup>
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.state.amount"
                v-decimal-en
                :error="!!form.errors.value?.amount"
                :error-messages="form.errors.value?.amount"
                :label="t('pos::attributes.pos_cash_movements.amount')"
              >
                <template #prepend-inner>
                  {{ meta.currency }}
                </template>
              </VTextField>
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.state.reference"
                :error="!!form.errors.value?.reference"
                :error-messages="form.errors.value?.reference"
                :label="t('pos::attributes.pos_cash_movements.reference')"
              />
            </VCol>
            <VCol cols="12">
              <VTextarea
                v-model="form.state.notes"
                :error="!!form.errors.value?.notes"
                :error-messages="form.errors.value?.notes"
                :label="t('pos::attributes.pos_cash_movements.notes')"
                rows="3"
              />
            </VCol>
          </VRow>
          <VRow>
            <VCol class="d-flex justify-space-between ga-4" cols="12">
              <VBtn
                class="w-50"
                color="grey-300"
                :disabled="form.loading.value"
                size="x-large"
                @click="close"
              >
                <VIcon icon="tabler-x" start />
                {{ t('admin::admin.buttons.cancel') }}
              </VBtn>
              <VBtn
                class="w-50"
                color="primary"
                :disabled="isDisabled|| form.loading.value"
                :loading="form.loading.value"
                size="x-large"
                @click="submit"
              >
                <VIcon icon="tabler-cash" start />
                {{ t('admin::admin.buttons.submit') }}
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </v-navigation-drawer>
</template>

<style lang="scss" scoped>
.directions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 12px;
}

.direction-card {
  border: 1px dashed #e0e0e0;
  border-radius: 10px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.25s ease;
  position: relative;
}

.direction-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.direction-card .name {
  font-size: 1rem;
  font-weight: bold;

}

.direction-card .checkbox {
  height: 22px;
  width: 22px;
  border-radius: 50%;
  border: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.direction-card.active {
  border-color: rgb(var(--v-theme-primary));
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.direction-card.active .checkbox {
  background-color: rgb(var(--v-theme-primary));
  border-color: rgb(var(--v-theme-primary));
}
</style>
