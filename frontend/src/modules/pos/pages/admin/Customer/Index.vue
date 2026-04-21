<script lang="ts" setup>
  import type { Cart, CartItem } from '@/modules/cart/composables/cart.ts'
  import { computed, ref } from 'vue'
  import { useI18n } from 'vue-i18n'

  const cart = ref<Cart | null>(null)
  let source: EventSource | null = null
  const route = useRoute()
  const { t } = useI18n()

  onMounted(() => {
    const cartId = (route.params as Record<string, any>).cartId
    source = new EventSource(
      import.meta.env.VITE_API_URL + `/v1/pos/customer/viewer/${cartId}`,
    )

    source.addEventListener('cart_snapshot', (event: MessageEvent) => {
      cart.value = JSON.parse(event.data) as Cart
    })

    // eslint-disable-next-line unicorn/prefer-add-event-listener
    source.onerror = () => {
      console.warn('SSE connection error')
    }
  })

  onBeforeUnmount(() => {
    if (source) {
      source.close()
      source = null
    }
  })

  const customer = computed<Record<string, any> | null>(() => cart.value?.customer ?? null)
  const items = computed<CartItem[]>(() => {
    const rawItems = cart.value?.items ?? []
    return rawItems.map(item => {
      const optionsRaw = (item as Record<string, any>)?.options
      const optionsArray = Array.isArray(optionsRaw)
        ? optionsRaw
        : (optionsRaw && typeof optionsRaw === 'object'
          ? Object.values(optionsRaw)
          : [])
      return {
        ...item,
        options: optionsArray.map((opt: Record<string, any>) => ({
          ...opt,
          values: Array.isArray(opt.values) ? opt.values : [],
        })),
      } as CartItem
    })
  })
  const itemCount = computed(() => cart.value?.quantity ?? items.value.reduce((acc, item) => acc + item.qty, 0))
  const orderTypeName = computed(() => cart.value?.orderType?.name ?? '—')

  const customerName = computed(() => customer.value?.name || 'Guest Customer')
  const customerPhone = computed(() => customer.value?.phone || '—')
  const customerEmail = computed(() => customer.value?.email || '')
  const customerTier = computed(() => customer.value?.tier?.name || '')
  const customerPoints = computed(() => customer.value?.points_balance ?? null)
  const customerTierIcon = computed(() => customer.value?.tier?.icon?.url || '')

  const initials = computed(() => {
    const name = customerName.value || ''
    return name
      .split(' ')
      .filter(Boolean)
      .slice(0, 2)
      .map((part: string) => part[0]?.toUpperCase())
      .join('') || 'G'
  })

  function formatOptionValues (values: Array<{
    label?: string
    name?: string
    id?: string | number
    price?: { formatted?: string }
  }>) {
    return values
      .map(val => {
        return formatOptionValue(val)
      })
      .filter(Boolean)
      .join(', ')
  }

  function formatOptionValue (val: {
    label?: string
    name?: string
    id?: string | number
    price?: { formatted?: string, amount?: number }
  }) {
    const name = val.label || val.name || val.id
    const priceAmount = (val.price as Record<string, any> | undefined)?.amount
    const hasNonZeroPrice = typeof priceAmount === 'number'
      ? priceAmount > 0
      : !!val.price?.formatted && !/^0([.,]0+)?/.test(val.price.formatted)
    const price = hasNonZeroPrice && val.price?.formatted ? ` (${val.price.formatted})` : ''
    return name ? `${name}${price}` : ''
  }
</script>

<template>
  <div class="customer-viewer">
    <VContainer class="viewer-container" fluid>
      <VRow class="mb-4" dense>
        <VCol cols="12">
          <VCard class="profile-card" variant="flat">
            <VCardText>
              <div class="profile-grid">
                <div class="profile-main">
                  <VAvatar class="profile-avatar" size="60">
                    <span class="text-h5 font-weight-bold">{{ initials }}</span>
                  </VAvatar>
                  <div>
                    <div class="text-h6 font-weight-bold">
                      {{ customerName }}
                    </div>
                    <div class="text-body-2 text-medium-emphasis">
                      {{ customerPhone }}
                      <span v-if="customerEmail"> · {{ customerEmail }}</span>
                    </div>
                    <div class="chip-row">
                      <VChip size="small" variant="tonal">
                        {{ t('pos::pos_viewer.order_type') }}
                        &nbsp;
                        <span class="text-primary font-weight-bold">{{ orderTypeName }}</span>
                      </VChip>
                      <VChip size="small" variant="tonal">
                        {{ t('pos::pos_viewer.items') }}
                        &nbsp;
                        <span class="text-primary font-weight-bold">{{ itemCount }}</span>
                      </VChip>
                      <VChip v-if="cart?.order?.id" size="small" variant="tonal">
                        {{ t('pos::pos_viewer.order') }}
                        &nbsp;<span class="text-primary">{{ cart?.order?.name }}</span>
                      </VChip>
                    </div>
                  </div>
                </div>
                <div class="profile-stats">
                  <div v-if="customerTier || customerPoints != null" class="stat-card loyalty-card">
                    <div class="loyalty-header">
                      <div>
                        <div class="stat-label">{{ t('pos::pos_viewer.loyalty') }}</div>
                        <div class="loyalty-tier">
                          {{ customerTier || 'Member' }}
                        </div>
                      </div>
                      <VAvatar v-if="customerTierIcon" class="tier-avatar" size="34">
                        <VImg cover :src="customerTierIcon" />
                      </VAvatar>
                      <VIcon v-else icon="tabler-award" size="20" />
                    </div>
                    <div class="loyalty-points">
                      <span class="points-value">{{ customerPoints ?? 0 }}</span>
                      <span class="points-label">Pts</span>
                    </div>
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>

        </VCol>
        <VCol cols="12" md="8">
          <VCard class="items-card" variant="flat">
            <VCardTitle class="d-flex align-center justify-space-between">
              <span>{{ t('pos::pos_viewer.items') }}</span>
            </VCardTitle>
            <VCardText>
              <div v-if="items.length === 0" class="empty-state">
                <VIcon icon="tabler-basket-off" size="40" />
                <div class="text-body-2 text-medium-emphasis">
                  {{ t('pos::pos_viewer.no_items_added_yet') }}
                </div>
              </div>
              <div v-else class="items-grid">
                <div
                  v-for="item in items"
                  :key="item.id"
                  class="item-row"
                >
                  <div class="item-line">
                    <div class="item-main">
                      <div class="item-name">
                        {{ item.item?.name }}
                      </div>
                      <div class="item-meta">
                        <span class="meta-pill">Qty {{ item.qty }}</span>
                        <span>Unit {{ item.unitPrice?.formatted || '—' }}</span>
                        <span class="meta-dot">•</span>
                        <span>Tax {{ item.taxTotal?.formatted || '—' }}</span>
                      </div>
                    </div>
                    <div class="item-total">
                      {{ item.total?.formatted || '—' }}
                    </div>
                  </div>
                  <div v-if="item.options?.length" class="option-list">
                    <div
                      v-for="opt in item.options"
                      :key="opt.id"
                      class="option-row"
                    >
                      <span class="option-title">{{ opt.name || 'Option' }}</span>
                      <span class="option-values">
                        <span
                          v-for="(value, index) in opt.values || []"
                          :key="value.id || value.label || index"
                          class="option-chip"
                        >
                          {{ formatOptionValue(value) }}
                        </span>
                      </span>
                    </div>
                  </div>
                  <div v-else class="option-empty">
                    {{ t('pos::pos_viewer.no_options_selected') }}
                  </div>
                </div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" md="4">
          <VCard class="summary-card" variant="flat">
            <VCardTitle class="d-flex align-center justify-space-between">
              <span>
                {{ t('pos::pos_viewer.order_summary') }}
              </span>
            </VCardTitle>
            <VCardText>
              <div class="summary-row">
                <span>{{ t('pos::pos_viewer.subtotal') }}</span>
                <span>{{ cart?.subTotal?.formatted || '—' }}</span>
              </div>
              <div v-if="cart?.discount?.id" class="summary-row">
                <span>{{ t('pos::pos_viewer.discount') }}</span>
                <span class="text-success">- {{ cart?.discount?.value?.formatted || '—' }}</span>
              </div>
              <div
                v-for="tax in cart?.taxes || []"
                :key="tax.id"
                class="summary-row"
              >
                <span>{{ tax.name }}</span>
                <span>{{ tax.amount?.formatted || '—' }}</span>
              </div>
              <div class="summary-divider" />
              <div class="summary-row total-row">
                <span>{{ t('pos::pos_viewer.total') }}</span>
                <span>{{ cart?.total?.formatted || '—' }}</span>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<style lang="scss" scoped>
.customer-viewer {
  min-height: 100vh;
}

.viewer-container {
  padding-block: 1.5rem 3rem;
  padding-inline: 1.5rem;
}

.viewer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.7rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
  margin-bottom: 0.25rem;
}

.live-chip {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-weight: 600;
}

.live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgb(var(--v-theme-success));
  box-shadow: 0 0 0 4px rgba(var(--v-theme-success), 0.15);
}

.profile-card {
  border-radius: 20px;
  background: linear-gradient(
      135deg,
      rgba(var(--v-theme-primary), 0.08),
      rgba(var(--v-theme-surface-variant), 0.6)
  );
}

.profile-grid {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 1.5rem;
  align-items: center;
}

.profile-main {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.profile-avatar {
  background: rgba(var(--v-theme-primary), 0.16);
  color: rgb(var(--v-theme-primary));
}

.chip-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.inline-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.1rem 0.45rem;
  margin-right: 0.4rem;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.08);
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.profile-stats {
  display: flex;
  justify-content: flex-end;
}

.stat-card {
  padding: 0.75rem;
  border-radius: 14px;
  background: rgba(var(--v-theme-background), 0.7);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.06);
}

.stat-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(var(--v-theme-on-surface), 0.6);
  margin-bottom: 0.3rem;
}

.stat-value {
  font-size: 1rem;
  font-weight: 700;
}

.loyalty-card {
  background: rgba(var(--v-theme-background), 0.7);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.06);
  display: grid;
  gap: 0.4rem;
}

.loyalty-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.loyalty-tier {
  font-size: 1rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
}

.tier-avatar {
  background: rgba(var(--v-theme-primary), 0.15);
}

.loyalty-points {
  display: flex;
  align-items: baseline;
  gap: 0.4rem;
}

.points-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: rgb(var(--v-theme-on-surface));
}

.points-label {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.summary-card,
.items-card {
  border-radius: 20px;
  background: rgba(var(--v-theme-surface), 0.9);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.06);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.4rem 0;
  font-size: 0.95rem;
}

.summary-divider {
  height: 1px;
  background: rgba(var(--v-theme-on-surface), 0.08);
  margin: 0.6rem 0;
}

.total-row {
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
}

.items-grid {
  display: grid;
  gap: 0.6rem;
}

.item-row {
  padding: 0.75rem 0.9rem;
  border-radius: 12px;
  background: rgba(var(--v-theme-background), 0.96);
  border: 1px solid rgba(var(--v-theme-on-surface), 0.06);
}

.item-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.item-main {
  display: grid;
  gap: 0.35rem;
}

.item-name {
  font-size: 0.98rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
}

.item-meta {
  font-size: 0.82rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.meta-dot {
  font-size: 0.75rem;
  opacity: 0.5;
}

.item-total {
  font-size: 0.95rem;
  font-weight: 600;
  color: rgba(var(--v-theme-on-surface), 0.85);
  white-space: nowrap;
}

.meta-pill {
  padding: 0.1rem 0.45rem;
  border-radius: 999px;
  background: rgba(var(--v-theme-on-surface), 0.06);
  font-size: 0.72rem;
  font-weight: 600;
}

.option-list {
  display: grid;
  gap: 0.35rem;
  margin-top: 0.6rem;
}

.option-row {
  display: grid;
  grid-template-columns: 120px 1fr;
  gap: 0.6rem;
  align-items: center;
}

.option-title {
  font-size: 0.74rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(var(--v-theme-on-surface), 0.55);
  font-weight: 600;
}

.option-values {
  display: flex;
  flex-wrap: wrap;
  gap: 0.3rem;
}

.option-chip {
  padding: 0.15rem 0.45rem;
  border-radius: 8px;
  font-size: 0.75rem;
  color: rgba(var(--v-theme-on-surface), 0.85);
  background: rgba(var(--v-theme-on-surface), 0.04);
}

.option-empty {
  margin-top: 0.55rem;
  font-size: 0.8rem;
  color: rgba(var(--v-theme-on-surface), 0.55);
}

.option-empty {
  margin-top: 0.85rem;
  font-size: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.6);
}

.empty-state {
  display: grid;
  place-items: center;
  gap: 0.6rem;
  padding: 2.5rem 0;
}

@media (max-width: 960px) {
  .viewer-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.8rem;
  }

  .profile-main {
    flex-direction: column;
    align-items: flex-start;
  }

  .profile-grid {
    grid-template-columns: 1fr;
  }

  .profile-stats {
    justify-content: flex-start;
  }
}
</style>
