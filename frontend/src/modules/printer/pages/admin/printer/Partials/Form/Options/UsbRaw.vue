<script lang="ts" setup>

  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    form: Record<string, any>
    meta: Record<string, any>
  }>()
  const { t } = useI18n()

  const { state, errors } = toRefs(props.form)

</script>

<template>
  <VRow>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.vendor_id"
        :error="!!errors?.[`options.vendor_id`]"
        :error-messages="errors?.[`options.vendor_id`]"
        :label="t('printer::attributes.printers.options.vendor_id')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.product_id"
        :error="!!errors?.[`options.product_id`]"
        :error-messages="errors?.[`options.product_id`]"
        :label="t('printer::attributes.printers.options.product_id')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VSelect
        v-model="state.options.paper_size"
        :error="!!errors?.options.paper_size"
        :error-messages="errors?.options.paper_size"
        item-title="name"
        item-value="id"
        :items="meta.paperSizes"
        :label="t('printer::attributes.printers.options.paper_size')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.interface_index"
        v-integer-en
        clearable
        :error="!!errors?.[`options.interface_index`]"
        :error-messages="errors?.[`options.interface_index`]"
        :label="t('printer::attributes.printers.options.interface_index')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VSelect
        v-model="state.options.endpoint"
        clearable
        :error="!!errors?.options.endpoint"
        :error-messages="errors?.options.endpoint"
        item-title="name"
        item-value="id"
        :items="meta.usbRawEndpoints"
        :label="t('printer::attributes.printers.options.endpoint')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.timeout_ms"
        v-integer-en
        clearable
        :error="!!errors?.[`options.timeout_ms`]"
        :error-messages="errors?.[`options.timeout_ms`]"
        :label="t('printer::attributes.printers.options.timeout_ms')"
      />
    </VCol>
    <VCol cols="12" md="6">
      <VTextField
        v-model="state.options.copies"
        v-integer-en
        clearable
        :error="!!errors?.[`options.copies`]"
        :error-messages="errors?.[`options.copies`]"
        :label="t('printer::attributes.printers.options.copies')"
      />
    </VCol>
    <VCol cols="12" md="4">
      <VCheckbox
        v-model="state.options.detach_kernel_driver"
        :label="t('printer::attributes.printers.options.detach_kernel_driver')"
      />
    </VCol>
  </VRow>
</template>
