<script lang="ts" setup>
  import type { ConvertedMoney } from '@/modules/core/contracts/Money.ts'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  defineProps<{
    money: ConvertedMoney
    classes?: string[] | Record<string, unknown>
    prefix?: string
  }>
    ()
  const { user } = useAuth()
</script>

<template>
  <VTooltip
    v-if="money['original']"
    :disabled="user?.assigned_to_branch || money['converted'].currency == money['original'].currency"
    :text="money['converted'].formatted"
  >
    <template #activator="{ props:tooltipProps }">
      <span :class="classes" v-bind="tooltipProps">
        {{ prefix || '' }}{{ money["original"].formatted }}
      </span>
    </template>
  </VTooltip>
  <span v-else :class="classes">
    {{ prefix || '' }}{{ money?.formatted }}
  </span>
</template>
