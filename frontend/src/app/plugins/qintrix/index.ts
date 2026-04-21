export { QintrixClient } from './client/qintrix-client.js'
export { QintrixApiError } from './http/api-error.js'

export type { QintrixClientConfig } from './types/client.js'
export type { HealthResponse, HealthServerState } from './types/health.js'
export type {
  CreateHtmlPrintJobInput,
  CreateImagePrintJobInput,
  CreatePdfPrintJobInput,
  CreatePrintJobInput,
  CreateTextPrintJobInput,
  JobsListQuery,
  JobsListResult,
  PrintJobDetails,
  PrintJobFailureCategory,
  PrintJobOptions,
  PrintJobPayloadMeta,
  PrintJobStatus,
  PrintJobSummary,
} from './types/jobs.js'
export type {
  PrinterConnectionDetails,
  PrinterConnectionType,
  PrinterDetails,

  PrinterSummary,
  PrinterTestResult,
} from './types/printers.js'
export type {
  QueueRunState,
  QueueStatus,
  QueueWorkerState,
  QueueWorkerStatus,
} from './types/queue.js'
