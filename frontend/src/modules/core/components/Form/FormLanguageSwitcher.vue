<script lang="ts" setup>
import type {SupportedLanguages} from "@/modules/core/contracts/AppState.ts";
import {useAppStore} from "@/modules/core/stores/appStore.ts";

const props = defineProps<{ modelValue: SupportedLanguages }>()

const emit = defineEmits(['update:modelValue'])

const selectedLang = ref<SupportedLanguages>(props.modelValue)

watch(selectedLang, val => {
  emit('update:modelValue', val)
})

watch(() => props.modelValue, val => {
  if (val.id !== selectedLang.value.id) {
    selectedLang.value = val
  }
})
const appStore = useAppStore()
</script>

<template>
  <div style="width: 120px">
    <VSelect
      v-model="selectedLang"
      :items="appStore.supportedLanguages"
      density="compact"
      hide-details
      item-title="name"
      item-value="id"
      return-object
      variant="outlined"
    >
      <template #prepend-inner>
        <VIcon>tabler-language</VIcon>
      </template>
    </VSelect>
  </div>
</template>
