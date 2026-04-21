<script lang="ts" setup>
  import { format, isValid, parseISO } from 'date-fns'
  import { computed, ref, watch } from 'vue'

  const props = defineProps<{
    modelValue?: string | null
    label: string
    error?: boolean
    clearable?: boolean
    errorMessages?: string
    min?: string
    max?: string
    required?: boolean
    use24h?: boolean
    displayFormat?: string
    valueFormat?: string
  }>()

  const emit = defineEmits(['update:modelValue'])

  const menu = ref(false)
  const step = ref<'date' | 'time'>('date')

  // always a Date
  const tempDateTime = ref<Date>(new Date())

  const displayFmt = computed(() => props.displayFormat ?? 'yyyy-MM-dd hh:mm a')
  const valueFmt = computed(() => props.valueFormat ?? 'yyyy-MM-dd HH:mm:ss')

  const displayValue = computed(() => {
    if (props.modelValue && isValid(new Date(props.modelValue))) {
      return format(new Date(props.modelValue), displayFmt.value)
    }
    return ''
  })

  // sync from parent
  watch(
    () => props.modelValue,
    val => {
      tempDateTime.value = val && isValid(new Date(val)) ? parseISO(val.replace(' ', 'T')) : new Date()
    },
    { immediate: true },
  )

  const updateDate = (val: string | Date | null) => {
    if (!val) return
    const d = val instanceof Date ? val : parseISO(String(val))
    tempDateTime.value.setFullYear(d.getFullYear(), d.getMonth(), d.getDate())
    step.value = 'time'
  }

  const updateTime = (val: string | Date | null) => {
    if (!val) return

    let h, m
    if (val instanceof Date) {
      h = val.getHours()
      m = val.getMinutes()
    } else {
      const [hh, mm] = String(val).split(':').map(Number)
      h = hh || 0
      m = mm || 0
    }

    tempDateTime.value.setHours(h, m, 0, 0)

    if (isValid(tempDateTime.value)) {
      emit('update:modelValue', format(tempDateTime.value, valueFmt.value))
    }
  }

  const updateTimeMinute = () => {
    menu.value = false
    step.value = 'date'
  }
  const clearValue = () => {
    emit('update:modelValue', null)
    tempDateTime.value = new Date()
  }
</script>

<template>
  <VMenu
    v-model="menu"
    :close-on-content-click="false"
    max-width="320"
    transition="scale-transition"
  >
    <template #activator="{ props: menuActivatorProps }">
      <VTextField
        clearable
        :error="error"
        :error-messages="errorMessages"
        :model-value="displayValue"
        prepend-inner-icon="tabler-calendar"
        readonly
        v-bind="menuActivatorProps"
        @click:clear="clearValue"
      >
        <template #label>
          {{ props.label }} <span v-if="required" class="text-error">*</span>
        </template>
      </VTextField>
    </template>

    <VCard width="330">
      <!-- Step 1: Date -->
      <VDatePicker
        v-if="step === 'date'"
        color="primary"
        :max="max"
        :min="min"
        :model-value="tempDateTime"
        show-adjacent-months
        @update:model-value="updateDate"
      />

      <VTimePicker
        v-else
        color="primary"
        :format="props.use24h ? '24hr' : 'ampm'"
        :model-value="tempDateTime"
        @update:minute="updateTimeMinute"
        @update:model-value="updateTime"
      />
    </VCard>
  </VMenu>
</template>
