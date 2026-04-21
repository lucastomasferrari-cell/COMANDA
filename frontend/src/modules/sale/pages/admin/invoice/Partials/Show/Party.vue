<script lang="ts" setup>
  import { computed } from 'vue'
  import { useI18n } from 'vue-i18n'

  const props = defineProps<{
    party: Record<string, any>
  }>()

  const { t } = useI18n()

  const isSeller = computed(() => props.party?.type?.id === 'seller')

  const title = computed(() =>
    t(`invoice::invoices.show.${isSeller.value ? 'seller' : 'billing_address'}`),
  )
</script>

<template>
  <div class="party-info-card">
    <p class="section-title mb-3">
      {{ title }}
    </p>

    <div class="party-details">
      <div v-if="party.legal_name" class="detail-name mb-2">
        {{ party.legal_name }}
      </div>

      <div
        v-if="party.address_line1 || party.address_line2"
        class="detail-text"
      >
        {{ party.address_line1 }}
        <template v-if="party.address_line2">
          , {{ party.address_line2 }}
        </template>
      </div>

      <div
        v-if="party.city || party.state || party.country?.name"
        class="detail-text"
      >
        <span v-if="party.city">{{ party.city }}, </span>
        <span v-if="party.state">{{ party.state }}, </span>
        <span v-if="party.country?.name">{{ party.country.name }}</span>
      </div>

      <div v-if="party.postal_code" class="detail-text">
        {{ party.postal_code }}
      </div>

      <div
        v-if="party.vat_tin"
        class="detail-text"
      >
        <span class="font-weight-bold">{{ t('invoice::invoices.show.vat_tin') }}</span>: {{ party.vat_tin }} -
        <span class="font-weight-bold">{{ t('invoice::invoices.show.cr_number') }}</span>: {{ party.cr_number }}
      </div>

      <div v-if="party.email || party.phone" class="detail-contact">
        <span v-if="party.email">{{ party.email }}</span>
        <span v-if="party.phone"> <span v-if="party.email"> | </span> {{ party.phone }}</span>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.section-title {
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
}

.detail-name {
  font-weight: 700;
  font-size: 15px;
}

.detail-text {
  font-size: 13px;
}

.detail-contact {
  font-size: 13px;
  font-weight: 500;
}
</style>
