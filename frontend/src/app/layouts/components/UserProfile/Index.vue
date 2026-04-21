<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import LogoutDialog from '@/app/layouts/components/UserProfile/Dialogs/Logout.vue'
  import MyAccountDialog from '@/app/layouts/components/UserProfile/Dialogs/MyAccount.vue'
  import UpdatePassword from '@/app/layouts/components/UserProfile/Dialogs/UpdatePassword.vue'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useAuthStore } from '@/modules/auth/stores/authStore.ts'
  import RoleBadge from '@/modules/core/components/RoleBadge.vue'

  const auth = useAuth()
  const authStore = useAuthStore()
  const { t } = useI18n()
  const showLogoutDialog = ref(false)
  const showMyAccountDialog = ref(false)
  const showUpdatePasswordDialog = ref(false)
  const switchingAccountId = ref<number | null>(null)

  async function switchAccount (accountId: number) {
    if (accountId === authStore.getUser?.id) {
      return
    }
    switchingAccountId.value = accountId
    try {
      await auth.switchAccount(accountId)
    } finally {
      switchingAccountId.value = null
    }
  }

  async function addAccount () {
    await auth.goToAddAccount()
  }

  async function logoutAllAccounts () {
    await auth.logoutAll()
  }

  const accounts = computed(() => authStore.getAccounts)
</script>

<template>
  <div class="user-avatar-container">
    <VAvatar class="cursor-pointer profile-trigger" color="primary">
      <VImg :src="authStore.getUser?.profile_photo_url" />
      <VMenu
        activator="parent"
        location="bottom end"
        offset="14px"
        width="360"
      >
        <VCard class="profile-menu-card">
          <div class="profile-menu-header">
            <VAvatar color="primary" size="46">
              <VImg :src="authStore.getUser?.profile_photo_url" />
            </VAvatar>
            <div class="profile-menu-header-content">
              <div class="profile-menu-name-row">
                <p class="profile-menu-name">{{ authStore.getUser?.name }}</p>
                <RoleBadge v-if="authStore.getUser?.role" :role="authStore.getUser?.role" />
              </div>
              <p class="profile-menu-email">{{ authStore.getUser?.email }}</p>
            </div>
          </div>

          <div class="profile-menu-actions">
            <button class="menu-action-btn" type="button" @click="showMyAccountDialog=true">
              <span class="menu-action-label">
                <VIcon icon="tabler-user-hexagon" size="18" />
                <span>{{ t("admin::navbar.my_account") }}</span>
              </span>
            </button>
            <button class="menu-action-btn" type="button" @click="showUpdatePasswordDialog=true">
              <span class="menu-action-label">
                <VIcon icon="tabler-lock-square-rounded" size="18" />
                <span>{{ t("admin::navbar.update_password") }}</span>
              </span>
            </button>
          </div>

          <div class="accounts-section">
            <p class="accounts-title">{{ t("admin::navbar.accounts") }}</p>
            <button
              v-for="account in accounts"
              :key="account.user.id"
              class="account-item-btn"
              :class="{active: account.user.id === authStore.getUser?.id}"
              type="button"
              @click="switchAccount(account.user.id)"
            >
              <span class="account-item-left">
                <VAvatar class="account-avatar" size="34">
                  <VImg :src="account.user.profile_photo_url" />
                </VAvatar>
                <span class="account-item-text">
                  <span class="account-item-name-row">
                    <span class="account-item-name">{{ account.user.name }}</span>
                    <RoleBadge
                      v-if="account.user.role"
                      :role="account.user.role"
                    />
                  </span>
                  <span class="account-item-email">{{ account.user.email }}</span>
                </span>
              </span>
              <VProgressCircular
                v-if="switchingAccountId === account.user.id"
                color="primary"
                indeterminate
                :size="16"
                :width="2"
              />
            </button>
            <button class="account-item-btn add-account" type="button" @click="addAccount">
              <span class="account-item-left">
                <span class="add-account-icon">
                  <VIcon icon="tabler-plus" size="16" />
                </span>
                <span class="account-item-name">{{ t("admin::navbar.add_another_account") }}</span>
              </span>
            </button>
          </div>

          <div class="logout-section">
            <button
              v-if="accounts.length > 1"
              class="menu-action-btn logout-btn"
              type="button"
              @click="logoutAllAccounts"
            >
              <span class="menu-action-label">
                <VIcon icon="tabler-logout" size="18" />
                <span>{{ t("admin::navbar.logout_all_accounts") }}</span>
              </span>
            </button>
            <button class="menu-action-btn logout-btn" type="button" @click="showLogoutDialog=true">
              <span class="menu-action-label">
                <VIcon icon="tabler-logout" size="18" />
                <span>{{ t("admin::navbar.logout") }}</span>
              </span>
            </button>
          </div>
        </VCard>
      </VMenu>
    </VAvatar>
  </div>
  <MyAccountDialog v-if="showMyAccountDialog" v-model="showMyAccountDialog" />
  <LogoutDialog v-if="showLogoutDialog" v-model="showLogoutDialog" />
  <UpdatePassword v-if="showUpdatePasswordDialog" v-model="showUpdatePasswordDialog" />
</template>

<style lang="scss" scoped>
.user-avatar-container {
  border-radius: 50%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.16);
  padding: 0.13rem;
}

.profile-trigger {
  box-shadow: 0 6px 18px rgba(var(--v-theme-on-surface), 0.14);
}

.profile-menu-card {
  border-radius: 18px;
  padding: 0.85rem;
  background: linear-gradient(180deg, rgb(var(--v-theme-surface)) 0%, rgba(var(--v-theme-primary), 0.04) 100%);
}

.profile-menu-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.4rem 0.35rem 0.7rem;
}

.profile-menu-header-content {
  min-width: 0;
  flex: 1;
}

.profile-menu-name-row {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.profile-menu-name {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: rgb(var(--v-theme-on-surface));
}

.profile-menu-email {
  margin: 0.2rem 0 0;
  font-size: 0.85rem;
  color: rgba(var(--v-theme-on-surface), 0.68);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-menu-actions,
.logout-section {
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  border-radius: 14px;
  overflow: hidden;
}

.menu-action-btn {
  width: 100%;
  border: 0;
  background: transparent;
  text-align: left;
  padding: 0.72rem 0.78rem;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.menu-action-btn + .menu-action-btn {
  border-top: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}

.menu-action-btn:hover {
  background: rgba(var(--v-theme-primary), 0.06);
}

.menu-action-label {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
}

.accounts-section {
  margin-top: 0.75rem;
}

.accounts-title {
  margin: 0 0 0.45rem;
  padding-left: 0.32rem;
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  font-weight: 700;
  color: rgba(var(--v-theme-on-surface), 0.62);
}

.account-item-btn {
  width: 100%;
  border: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  background: rgb(var(--v-theme-surface));
  border-radius: 12px;
  text-align: left;
  padding: 0.6rem 0.68rem;
  margin-bottom: 0.42rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  transition: all 0.15s ease;
}

.account-item-btn:hover {
  border-color: rgba(var(--v-theme-on-surface), 0.2);
  background: rgba(var(--v-theme-primary), 0.04);
}

.account-item-btn.active {
  border-color: rgba(var(--v-theme-primary), 0.38);
  background: rgba(var(--v-theme-primary), 0.12);
}

.account-item-left {
  display: inline-flex;
  align-items: center;
  gap: 0.58rem;
  min-width: 0;
}

.account-item-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.account-item-name {
  color: rgb(var(--v-theme-on-surface));
  font-size: 0.9rem;
  font-weight: 600;
}

.account-item-name-row {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  min-width: 0;
}

.account-item-email {
  color: rgba(var(--v-theme-on-surface), 0.66);
  font-size: 0.78rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.account-avatar {
  background: rgba(var(--v-theme-on-surface), 0.08);
}

.add-account {
  margin-bottom: 0;
}

.add-account-icon {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.14);
  color: rgb(var(--v-theme-primary));
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.logout-section {
  margin-top: 0.75rem;
}

.logout-btn .menu-action-label {
  color: rgb(var(--v-theme-error));
}
</style>
