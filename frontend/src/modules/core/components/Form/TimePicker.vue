<script lang="ts" setup>
import {format} from 'date-fns'

const props = defineProps<{
  modelValue?: string | null
  label: string
  error?: boolean
  clearable?: boolean
  errorMessages?: string
  min?: string
  max?: string
  use24h?: boolean
  required?: boolean
}>()

const emit = defineEmits(['update:modelValue'])

const menu = ref(false)

const internalValue = ref<string | null>(null)


const updateValue = (val: string | null) => {
  if (!val) {
    // user cleared the picker
    emit('update:modelValue', '')
    internalValue.value = null
    menu.value = false
    return
  }

  internalValue.value = val

  // val from VTimePicker is usually 'HH:mm'
  const [hStr, mStr] = val.split(':')
  const hours = Number(hStr)
  const minutes = Number(mStr)

  const date = new Date()
  date.setHours(hours, minutes, 0, 0)

  const formatted = props.use24h
    ? format(date, 'HH:mm')
    : format(date, 'hh:mm a')

  emit('update:modelValue', formatted)
  menu.value = false
}

const clearValue = () => {
  internalValue.value = null
  emit('update:modelValue', '')
}
</script>

<template>
  <VMenu
    v-model="menu"
    :close-on-content-click="false"
    offset-y
    transition="scale-transition"
  >
    <template #activator="{ props: menuActivatorProps }">
      <VTextField
        :error="props.error"
        :error-messages="props.errorMessages"
        :model-value="props.modelValue"
        clearable
        prepend-inner-icon="tabler-clock"
        readonly
        v-bind="menuActivatorProps"
        @click:clear="clearValue"
      >
        <template #label>
          {{ props.label }}&nbsp;
          <span v-if="props.required" class="text-error">*</span>
        </template>
      </VTextField>
    </template>

    <VTimePicker
      v-model="internalValue"
      :format="props.use24h ? '24hr' : 'ampm'"
      :max="props.max"
      :min="props.min"
      @update:model-value="updateValue"
    />
  </VMenu>
</template>
