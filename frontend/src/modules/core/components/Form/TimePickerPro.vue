<script lang="ts" setup>
  import { format } from 'date-fns'
  import { computed, ref, watch } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    modelValue?: string | null
    label: string
    error?: boolean
    clearable?: boolean
    errorMessages?: string
    hideDetails?: boolean | 'auto'
    min?: string
    max?: string
    use24h?: boolean
    required?: boolean
    disabled?: boolean
  }>()

  const emit = defineEmits(['update:modelValue'])

  const { t } = useI18n()

  const menu = ref(false)
  const selectedHour = ref<number | null>(null)
  const selectedMinute = ref<number | null>(null)
  const selectedMeridiem = ref<'AM' | 'PM'>('AM')

  const hours = computed(() => {
    if (props.use24h) {
      return Array.from({ length: 24 }, (_, i) => i)
    }
    return Array.from({ length: 12 }, (_, i) => i + 1)
  })
  const minutes = Array.from({ length: 60 }, (_, i) => String(i).padStart(2, '0'))
  const meridiems = computed<Array<'AM' | 'PM'>>(() => (props.use24h ? [] : ['AM', 'PM']))

  const displayValue = computed(() => {
    if (!props.modelValue) return ''
    const parts = parseTimeValue(props.modelValue)
    if (!parts) return props.modelValue

    const date = new Date()
    date.setHours(parts.hour24, parts.minute, 0, 0)
    return format(date, props.use24h ? 'HH:mm' : 'hh:mm a')
  })

  function applySelection () {
    if (selectedHour.value === null || selectedMinute.value === null) {
      return
    }

    let hours24 = selectedHour.value
    if (!props.use24h) {
      hours24 = selectedHour.value % 12
      if (selectedMeridiem.value === 'PM') {
        hours24 += 12
      }
      if (selectedMeridiem.value === 'AM' && hours24 === 12) {
        hours24 = 0
      }
    }

    const date = new Date()
    date.setHours(hours24, Number(selectedMinute.value), 0, 0)

    const formatted = props.use24h
      ? format(date, 'HH:mm')
      : format(date, 'hh:mm a')

    emit('update:modelValue', formatted)
  }

  function parseTimeValue (value: string) {
    const time24Match = value.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/)
    if (time24Match) {
      const hour24 = Number(time24Match[1])
      const minute = Number(time24Match[2])
      if (!Number.isNaN(hour24) && !Number.isNaN(minute)) {
        return { hour24, minute }
      }
    }

    const time12Match = value.match(/^(\d{1,2}):(\d{2})\s*([AaPp][Mm])$/)
    if (time12Match) {
      if (!time12Match[3]) return null
      const hour12 = Number(time12Match[1])
      const minute = Number(time12Match[2])
      const meridiem = time12Match[3].toUpperCase() as 'AM' | 'PM'
      if (!Number.isNaN(hour12) && !Number.isNaN(minute)) {
        let hour24 = hour12 % 12
        if (meridiem === 'PM') {
          hour24 += 12
        }
        return { hour24, minute }
      }
    }

    return null
  }

  function syncFromModel () {
    const value = props.modelValue || ''
    if (!value) {
      const now = new Date()
      const hour24 = now.getHours()
      selectedMeridiem.value = hour24 >= 12 ? 'PM' : 'AM'
      selectedHour.value = props.use24h ? hour24 : (hour24 % 12 === 0 ? 12 : hour24 % 12)
      selectedMinute.value = now.getMinutes()
      return
    }

    const parts = parseTimeValue(value)
    if (!parts) return

    selectedMeridiem.value = parts.hour24 >= 12 ? 'PM' : 'AM'
    selectedHour.value = props.use24h ? parts.hour24 : (parts.hour24 % 12 === 0 ? 12 : parts.hour24 % 12)
    selectedMinute.value = parts.minute
  }

  function clearValue () {
    emit('update:modelValue', '')
  }

  function openMenu () {
    if (props.disabled) {
      return
    }
    if (!menu.value) {
      syncFromModel()
      menu.value = true
    }
  }

  watch(
    () => props.modelValue,
    () => {
      syncFromModel()
    },
    { immediate: true },
  )
</script>

<template>
  <VMenu
    v-model="menu"
    :close-on-content-click="false"
    :disabled="props.disabled"
    offset-y
    transition="scale-transition"
  >
    <template #activator="{ props: menuActivatorProps }">
      <VTextField
        clearable
        :disabled="props.disabled"
        :error="props.error"
        :error-messages="props.errorMessages"
        :model-value="displayValue"
        prepend-inner-icon="tabler-clock"
        readonly
        v-bind="menuActivatorProps"
        @click="openMenu"
        @click:clear="clearValue"
      >
        <template #label>
          {{ props.label }}&nbsp;
          <span v-if="props.required" class="text-error">*</span>
        </template>
      </VTextField>
    </template>

    <VCard class="time-picker-pro" min-width="340">
      <VCardTitle class="d-flex align-center justify-space-between">
        <div class="d-flex align-center">
          <VIcon class="me-2" icon="tabler-clock" size="20" />
          <span class="text-subtitle-1">{{ props.label }}</span>
        </div>
        <VChip color="primary" size="small" variant="tonal">
          {{ displayValue || '--:--' }}
        </VChip>
      </VCardTitle>
      <VCardText class="pt-0">
        <div class="time-picker-pro-grid">
          <div class="time-picker-pro-column">
            <div class="time-picker-pro-header">{{
              String(selectedHour ?? '--').padStart(2, '0')
            }}
            </div>
            <VList class="time-picker-pro-list" density="compact">
              <VListItem
                v-for="hour in hours"
                :key="hour"
                :class="{'time-picker-pro-item--active': selectedHour === hour}"
                @click="() => { selectedHour = hour; applySelection() }"
              >
                <VListItemTitle class="text-center">{{
                  String(hour).padStart(2, '0')
                }}
                </VListItemTitle>
              </VListItem>
            </VList>
          </div>
          <div class="time-picker-pro-column">
            <div class="time-picker-pro-header">{{
              String(selectedMinute ?? '--').padStart(2, '0')
            }}
            </div>
            <VList class="time-picker-pro-list" density="compact">
              <VListItem
                v-for="minute in minutes"
                :key="minute"
                :class="{'time-picker-pro-item--active': String(selectedMinute).padStart(2, '0') === minute}"
                @click="() => { selectedMinute = Number(minute); applySelection() }"
              >
                <VListItemTitle class="text-center">{{ minute }}</VListItemTitle>
              </VListItem>
            </VList>
          </div>
          <div v-if="!props.use24h" class="time-picker-pro-column">
            <div class="time-picker-pro-header">{{ selectedMeridiem }}</div>
            <VList class="time-picker-pro-list" density="compact">
              <VListItem
                v-for="period in meridiems"
                :key="period"
                :class="{'time-picker-pro-item--active': selectedMeridiem === period}"
                @click="() => { selectedMeridiem = period; applySelection() }"
              >
                <VListItemTitle class="text-center">{{ period }}</VListItemTitle>
              </VListItem>
            </VList>
          </div>
        </div>
      </VCardText>
      <VDivider />
      <VCardActions class="px-4 py-3 d-flex justify-space-between">
        <VBtn color="error" variant="text" @click="clearValue">
          {{ t('admin::admin.buttons.clear') }}
        </VBtn>
        <VBtn variant="text" @click="menu = false">
          {{ t('admin::admin.buttons.close') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VMenu>
</template>

<style lang="scss" scoped>
.time-picker-pro {
  border: 1px solid rgba(var(--v-theme-secondary), 0.25);
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.16);
  background: linear-gradient(
      180deg,
      rgba(var(--v-theme-secondary), 0.08),
      rgba(var(--v-theme-secondary), 0.02)
  );
}

.time-picker-pro-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.time-picker-pro-column {
  display: grid;
  grid-template-rows: auto 1fr;
  gap: 8px;
}

.time-picker-pro-header {
  text-align: center;
  font-weight: 600;
  color: #ffffff;
  background: rgb(var(--v-theme-secondary));
  padding: 8px 10px;
  border-radius: 6px;
  letter-spacing: 0.5px;
}

.time-picker-pro-list {
  max-height: 260px;
  overflow: auto;
  border: 1px solid rgba(var(--v-theme-secondary), 0.2);
  border-radius: 8px;
  background: #ffffff;
}

.time-picker-pro-list :deep(.v-list-item) {
  min-height: 36px;
}

.time-picker-pro-list :deep(.v-list-item:hover) {
  background: rgba(var(--v-theme-secondary), 0.08);
}

.time-picker-pro-item--active {
  background: rgb(var(--v-theme-secondary));
  color: #ffffff;
}

.time-picker-pro-item--active :deep(.v-list-item-title) {
  color: #ffffff;
  font-weight: 600;
}
</style>
