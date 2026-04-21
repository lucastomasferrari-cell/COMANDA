import type {
  NavigationGuardNext,
  RouteLocationNormalized,
  RouteLocationNormalizedLoaded,
  RouteLocationRaw,
} from 'vue-router'
import { useAuth } from '@/modules/auth/composables/auth.ts'
import { useReport } from '../composables/report.ts'

export function reportAccessMiddleware (to: RouteLocationNormalized, from: RouteLocationNormalizedLoaded, next: NavigationGuardNext) {
  const { can } = useAuth()
  const { getReportIcon, reportExists } = useReport()

  const reportKey: string = (to.params as Record<string, any>).key

  if (!reportExists(reportKey)) {
    if (can('admin.reports.index')) {
      next({ name: 'admin.reports.index' } as unknown as RouteLocationRaw)
    } else {
      next({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
    }
  } else if (can(`admin.reports.${reportKey}`)) {
    to.meta.title = `report::reports.definitions.${reportKey}.title`
    to.meta.icon = getReportIcon(reportKey)
    next()
  } else if (can('admin.reports.index')) {
    next({ name: 'admin.reports.index' } as unknown as RouteLocationRaw)
  } else {
    next({ name: 'admin.dashboard' } as unknown as RouteLocationRaw)
  }
}
