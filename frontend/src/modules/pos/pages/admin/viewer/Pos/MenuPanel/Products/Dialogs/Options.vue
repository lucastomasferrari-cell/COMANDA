<script lang="ts" setup>
  const props = defineProps<{
    option: Record<string, any>
    errors: Record<string, string>
    modelValue?: Record<string, any>
  }>()

  const emit = defineEmits(['update:modelValue'])

  const value = ref(props.modelValue?.[props.option.id] ?? (props.option.type.id === 'multiple_select' || props.option.type.id === 'checkbox' ? [] : null))

  watch(value, val => {
    const updated = { ...props.modelValue }
    if (val === '' || (Array.isArray(val) && val.length === 0)) {
      delete updated[props.option.id]
    } else {
      updated[props.option.id] = val
    }
    emit('update:modelValue', updated)
  })

  const items = props.option.values?.map((v: Record<string, any>) => {
    let price = ''
    if ((v.price?.amount || v.price) > 0) {
      price = `(${v.price_type === 'fixed' ? v.price.formatted : `${Number.parseFloat(v.price)}%`})`
    }
    return { id: v.id, name: `${v.label} ${price}` }
  }) || []
</script>

<template>
  <VRow>
    <VCol cols="12">
      <VSelect
        v-if="['select','multiple_select'].includes(option.type.id)"
        v-model="value"
        :chips="option.type.id=='multiple_select'"
        :clearable="!option.is_required"
        :error="!!errors?.[`options.${option.id}`]"
        :error-messages="errors?.[`options.${option.id}`]"
        item-title="name"
        item-value="id"
        :items="items"
        :multiple="option.type.id=='multiple_select'"
      >
        <template #label>
          {{ option.name }}&nbsp;<span v-if="option.is_required" class="text-error">*</span>
        </template>
      </VSelect>
      <VTextField
        v-if="option.type.id=='text'"
        v-model="value"
        :clearable="!option.is_required"
        :error="!!errors?.[`options.${option.id}`]"
        :error-messages="errors?.[`options.${option.id}`]"
      >
        <template #label>
          {{ option.name }}&nbsp;<span v-if="option.is_required" class="text-error">*</span>
        </template>
      </VTextField>
      <VTextarea
        v-else-if="option.type.id=='textarea'"
        v-model="value"
        auto-grow
        :clearable="!option.is_required"
        :error="!!errors?.[`options.${option.id}`]"
        :error-messages="errors?.[`options.${option.id}`]"
        rows="2"
      >
        <template #label>
          {{ option.name }}&nbsp;<span v-if="option.is_required" class="text-error">*</span>
        </template>
      </VTextarea>
      <DatePicker
        v-else-if="option.type.id=='date'"
        v-model="value"
        :clearable="!option.is_required"
        :error="!!errors?.[`options.${option.id}`]"
        :error-messages="errors?.[`options.${option.id}`]"
        :label="option.name"
        :min="new Date().toLocaleDateString('en-CA')"
        :required="option.is_required"
      />
      <TimePicker
        v-else-if="option.type.id=='time'"
        v-model="value"
        :clearable="!option.is_required"
        :error="!!errors?.[`options.${option.id}`]"
        :error-messages="errors?.[`options.${option.id}`]"
        :label="option.name"
        :required="option.is_required"
      />
      <div v-else-if="option.type.id=='checkbox'">
        {{ option.name }}&nbsp;<span v-if="option.is_required" class="text-error">*</span>
        <VCheckbox
          v-for="v in items"
          :key="v.id"
          v-model="value"
          :error="!!errors?.[`options.${option.id}`]"
          :error-messages="errors?.[`options.${option.id}`]"
          :label="v.name"
          :value="v.id"
        />
      </div>
      <VRadioGroup
        v-else-if="option.type.id=='radio'"
        v-model="value"
        :error="!!errors?.[`options.${option.id}`]"
        :error-messages="errors?.[`options.${option.id}`]"
      >
        <template #label>
          {{ option.name }}&nbsp;<span v-if="option.is_required" class="text-error">*</span>
        </template>
        <VRadio
          v-for="v in items"
          :key="v.id"
          :label="v.name"
          :value="v.id"
        />
      </VRadioGroup>
    </VCol>
  </VRow>
</template>
