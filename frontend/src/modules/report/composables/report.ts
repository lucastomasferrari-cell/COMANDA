import type { RouteLocationRaw } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { formatCurrentDateForFileName } from '@/modules/core/utils/formatters.ts'
import { exportReport } from '@/modules/report/api/report.api.ts'
import { reports } from '@/modules/report/pages/admin/report/reports.ts'

export function useReport () {
  const router = useRouter()
  const { can } = useAuth()

  const goToReport = async (key: string) => {
    if (hasReportPermission(key)) {
      await router.push({ name: 'admin.reports.show', params: { key } } as unknown as RouteLocationRaw)
    }
  }

  const hasGroupPermissions = (groupKey: string) => reports[groupKey].some((report: Record<string, any>) => hasReportPermission(report.key))
  const hasReportPermission = (key: string) => can(`admin.reports.${key}`)

  const getReportIcon = (key: string) => Object.values(reports)
    .flat()
    .find((report: Record<string, any>) => report.key === key)?.icon || 'tabler-chart-histogram'
  const reportExists = (key: string) => Object.keys(reports).some((groupKey: string) => reports[groupKey].find((report: Record<string, any>) => report.key === key))

  const downloadExportFile = async (key: string, method: string, filters: Record<string, any>) => {
    try {
      const response = await exportReport(key, method, filters)
      const blob = response.data
      const filename = `export-${key}-${formatCurrentDateForFileName()}.${method}`
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', filename)
      document.body.append(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
    } catch {
      useToast().error('Export failed. Please try again later.')
    }
  }
  return {
    goToReport,
    hasGroupPermissions,
    hasReportPermission,
    getReportIcon,
    reportExists,
    downloadExportFile,
  }
}
