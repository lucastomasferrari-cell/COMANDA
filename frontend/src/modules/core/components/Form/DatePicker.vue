<script lang="ts" setup>
  import { format } from 'date-fns'
  import { ref, watch } from 'vue'

  const props = defineProps<{
    modelValue?: string | null
    label?: string
    error?: boolean
    clearable?: boolean
    errorMessages?: string
    min?: string
    max?: string
    required?: boolean
  }>()

  const emit = defineEmits(['update:modelValue'])

  const menu = ref(false)
  const internalValue = ref(props.modelValue)

  watch(() => props.modelValue, val => {
    internalValue.value = val
  })

  function updateValue (val: string | null) {
    if (val) {
      const formatted = format(new Date(val), 'yyyy-MM-dd')
      emit('update:modelValue', formatted)
      menu.value = false
    }
  }

  function clearValue () {
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
        clearable
        :error="error"
        :error-messages="errorMessages"
        :model-value="props.modelValue"
        prepend-inner-icon="tabler-calendar"
        readonly
        v-bind="menuActivatorProps"
        @click:clear="clearValue"
      >
        <template v-if="label" #label>
          {{ props.label }}&nbsp;<span v-if="required" class="text-error">*</span>
        </template>
      </VTextField>
    </template>

    <VDatePicker
      v-model="internalValue"
      color="primary"
      :max="max"
      :min="min"
      show-adjacent-months
      @update:model-value="updateValue"
    />
  </VMenu>
</template>
