<script lang="ts" setup>
  import { useI18n } from 'vue-i18n'
  import { useInvoice } from '@/modules/sale/composables/invoice.ts'
  import Allocations from './Partials/Show/Allocations.vue'
  import Footer from './Partials/Show/Footer.vue'
  import Header from './Partials/Show/Header.vue'
  import InvoiceInfo from './Partials/Show/InvoiceInfo.vue'
  import Lines from './Partials/Show/Lines.vue'
  import Party from './Partials/Show/Party.vue'
  import QrCode from './Partials/Show/QrCode.vue'
  import Summary from './Partials/Show/Summary.vue'

  const { getShowData, print, download } = useInvoice()
  const route = useRoute()
  const { t } = useI18n()

  const loading = ref(false)
  const isNotFound = ref(false)
  const item = ref<Record<string, any> | null>(null)

  onBeforeMount(async () => {
    loading.value = true
    const response = await getShowData((route.params as Record<string, any>).id)
    if (response.status === 200) {
      item.value = response.data
    } else if (response.status === 404) {
      isNotFound.value = true
    }
    loading.value = false
  })

</script>

<template>
  <PageStateWrapper :loading="loading" :not-found="isNotFound">
    <v-container v-if="item" fluid>
      <VRow justify="center">
        <VCol class="d-flex ga-3 justify-end" cols="12" lg="9" md="11">
          <VBtn @click="print(item)">
            <VIcon icon="tabler-printer" start />
            {{ t('admin::admin.buttons.print') }}
          </VBtn>
          <VBtn color="secondary" @click="download(item)">
            <VIcon icon="tabler-download" start />
            {{ t('admin::admin.buttons.download') }}
          </VBtn>
        </VCol>
        <VCol cols="12" lg="9" md="11">
          <VCard>
            <VCardText>
              <Header :invoice="item" />
              <InvoiceInfo :invoice="item" />
              <VRow class="mt-1">
                <VCol cols="12" md="4">
                  <Party :party="item.seller" />
                </VCol>
                <VCol cols="12" md="4">
                  <Party :party="item.buyer" />
                </VCol>
                <VCol class="d-flex justify-end" cols="12" md="4">
                  <QrCode v-if="item.qrcode" :qrcode="item.qrcode" />
                </VCol>
              </VRow>
              <Lines :lines="item.lines" />

              <VRow class="mt-3 pb-3 border-b-dashed">
                <VCol cols="12" md="5">
                  <Allocations v-if="item.allocations.length>0" :allocations="item.allocations" />
                </VCol>
                <VCol cols="12" md="5" offset-md="2">
                  <Summary :invoice="item" />
                </VCol>
              </VRow>
              <Footer :invoice="item" />
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </v-container>
  </PageStateWrapper>
</template>
