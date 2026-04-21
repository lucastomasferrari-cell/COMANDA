import { reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'

export function useForm<T extends Record<string, any>> (initialFields: T) {
  const { t } = useI18n()
  const state = reactive<T>({ ...initialFields })
  const loading = ref(false)

  const errors = ref<Record<keyof T | 'general' | string, string>>(
    Object.keys(initialFields).reduce((acc, key) => {
      acc[key as keyof T] = ''
      return acc
    }, {} as Record<keyof T | string, string>),
  )

  function resetErrors () {
    for (const key in errors.value) {
      errors.value[key] = ''
    }
  }

  async function submit (callback: () => Promise<any>, withResponse = false): Promise<boolean | Record<string, any>> {
    loading.value = true
    const toast = useToast()

    try {
      const response = await callback()
      if (response?.data?.message) {
        toast.success(response.data.message)
      }
      resetErrors()
      if (withResponse) {
        return response
      }
    } catch (error: any) {
      resetErrors()
      if ((error?.response?.status === 422 || error?.config?.url?.includes('/auth/login')) && error.response?.data?.errors) {
        const apiErrors = error.response.data.errors
        for (const field in apiErrors) {
          errors.value[field as keyof T] = apiErrors[field][0]
        }
      } else if (error.response?.data?.message) {
        errors.value.general = error.response?.data?.message
        if (error?.config?.url?.includes('/auth/login')) {
          errors.value['identifier'] = error.response?.data?.message
        } else {
          toast.error(error.response?.data?.message)
        }
      } else if (error?.response?.status === 401) {
        toast.error(t('core::errors.unauthorized'))
      } else {
        toast.error(t('core::errors.an_unexpected_error_occurred'))
      }
      return false
    } finally {
      loading.value = false
    }
    return true
  }

  return {
    state,
    loading,
    errors,
    submit,
  }
}
