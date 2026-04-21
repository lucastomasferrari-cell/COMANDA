<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useAuth } from '@/modules/auth/composables/auth.ts'

  const props = defineProps<{
    reward: Record<string, any>
    processing: Record<string, any>
  }>()
  const emit = defineEmits(['redeem-reward'])

  const { t } = useI18n()
  const { can } = useAuth()

  const redeemReward = () => {
    if (can('admin.loyalty_gifts.redeem')) {
      emit('redeem-reward', props.reward, 1)
    }
  }
</script>

<template>
  <v-col
    :key="reward.id"
    cols="12"
    lg="3"
    md="4"
    sm="6"
  >
    <v-hover v-slot="{ props:hoverProps }">
      <v-card
        class="reward-item transition d-flex flex-column"
        rounded="xl"
        v-bind="hoverProps"
      >
        <div class="mt-2 d-flex align-center justify-center" style="height: 100px;">
          <VImg
            v-if="reward.icon"
            contain
            height="100px"
            :src="reward.icon?.preview_image_url"
          />
          <VIcon
            v-else
            color="gray"
            icon="tabler-photo"
            size="80"
          />
        </div>

        <v-card-text class="d-flex flex-column justify-space-between flex-grow-1 pa-4">
          <div>
            <div class="text-subtitle-1 font-weight-bold mb-1">
              {{ reward.name }}
            </div>
            <div class="text-body-2 text-medium-emphasis mb-3">
              {{ reward.description }}
            </div>
          </div>

          <div>
            <div class="d-flex justify-space-between align-center mb-3">
              <v-chip
                class="text-white font-weight-bold"
                color="primary"
                size="small"
              >
                {{ reward.points_cost }} Pts
              </v-chip>

              <v-icon
                v-if="reward.is_eligible"
                color="success"
                icon="tabler-circle-dashed-check"
              />
            </div>

            <v-btn
              v-if="can('admin.loyalty_gifts.redeem')"
              block
              class="font-weight-bold"
              :color="reward.is_eligible ? 'success' : 'grey-darken-1'"
              :disabled="!reward.is_eligible || processing.loading"
              :loading="processing.loading && processing.id==reward.id"
              rounded="lg"
              :variant="reward.is_eligible ? 'elevated' : 'flat'"
              @click="redeemReward"
            >
              {{ t(`pos::pos_viewer.${reward.is_eligible ? 'redeem_now' : 'not_eligible'}`) }}
            </v-btn>
          </div>
        </v-card-text>
      </v-card>
    </v-hover>
  </v-col>
</template>

<style lang="scss" scoped>
.reward-item {
  height: 100%;
  min-height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background-color: rgba(var(--v-theme-surface), 1);
  transition: all 0.25s ease;
  border: 2px dashed #ededed;
  padding: 0.5rem;

  &:hover {
    transform: translateY(-5px);
  }
}

.reward-image {
  margin-top: 10px;
  object-fit: contain !important;
}
</style>
