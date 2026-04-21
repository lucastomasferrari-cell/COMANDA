<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useRouter, useRoute } from 'vue-router'
  import { useDisplay } from 'vuetify'
  import authV1BottomShape from '@/modules/auth/assets/images/auth-v1-bottom-shape.svg?url'
  import authV1TopShape from '@/modules/auth/assets/images/auth-v1-top-shape.svg?url'
  import { useAuth } from '@/modules/auth/composables/auth.ts'
  import { useForm } from '@/modules/core/composables/form.ts'
  import { useAppStore } from '@/modules/core/stores/appStore.ts'

  const auth = useAuth()
  const appStore = useAppStore()
  const { t } = useI18n()
  const router = useRouter()
  const route = useRoute()
  const { smAndUp } = useDisplay()

  const form = useForm({
    identifier: '',
    password: '',
  })

  const isPasswordVisible = ref(false)

  const disabled = computed(() => form.loading.value || !form.state.identifier || !form.state.password)
  const isAddAccountMode = computed(() => route.query?.add_account === '1')
  const canReturnDashboard = computed(() => isAddAccountMode.value && auth.store.isAuthenticated)

  function submit () {
    form.submit(() => auth.login(form.state.identifier, form.state.password))
  }

  async function returnToDashboard () {
    await router.push({ name: 'admin.dashboard' })
  }
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center ">
    <div class="position-relative my-sm-16">
      <VImg
        class="text-primary auth-v1-top-shape d-none d-sm-block"
        :src="authV1TopShape"
      />

      <VImg
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
        :src="authV1BottomShape"
      />

      <VCard
        class="auth-card"
        :class="smAndUp ? 'pa-6' : 'pa-4'"
        max-width="460"
        :width="smAndUp ? '28rem' : ''"
      >
        <VCardItem class="justify-center">
          <RouterLink
            class="app-logo"
            to="/"
          >
            <img
              v-if="appStore.logo"
              alt="logo"
              height="50px"
              :src="appStore.logo"
              width="50px"
            >
            <h1 class="app-logo-title">
              {{ appStore.appName }}
            </h1>
          </RouterLink>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ t('user::auth.welcome_to_app', {app_name: appStore.appName}) }}
          </h4>
          <p class="mb-0">
            {{ t('user::auth.description', {app_name: appStore.appName}) }}
          </p>
        </VCardText>

        <VCardText>
          <VForm @submit.prevent="submit">
            <VRow>
              <VCol cols="12">
                <VTextField
                  v-model="form.state.identifier"
                  autofocus
                  :error="!!form.errors.value?.identifier"
                  :error-messages="form.errors.value?.identifier"
                  :label="t('user::attributes.auth.identifier')"
                  placeholder="admin@forkiva.app"
                  type="email"
                />
              </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="form.state.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye' : 'tabler-eye-off'"
                  autocomplete="password"
                  :error="!!form.errors.value?.password"
                  :error-messages="form.errors.value?.password"
                  :label="t('user::attributes.auth.password')"
                  :placeholder="t('user::attributes.auth.password')"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

              </VCol>
              <VCol cols="12">
                <VBtn
                  block
                  :disabled="disabled"
                  :loading="form.loading.value"
                  type="submit"
                >
                  {{ t('user::auth.login') }}
                </VBtn>
              </VCol>
              <VCol
                v-if="canReturnDashboard"
                cols="12"
              >
                <VBtn
                  block
                  color="secondary"
                  variant="outlined"
                  @click="returnToDashboard"
                >
                  {{ t('user::auth.return_to_dashboard') }}
                </VBtn>
              </VCol>

            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "@/assets/scss/template/pages/page-auth";
</style>
