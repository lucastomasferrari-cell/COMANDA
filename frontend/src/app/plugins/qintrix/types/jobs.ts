export type PrintJobStatus =
  | 'accepted'
  | 'rendering'
  | 'queued'
  | 'processing'
  | 'completed'
  | 'failed'
  | 'canceled'
  | 'retry_scheduled';

export type PrintJobFailureCategory =
  | 'validation_failure'
  | 'render_failure'
  | 'execution_failure'
  | 'queue_failure'
  | 'auth_failure';

export interface PrintJobSummary {
  id: string;
  printerId?: string | null;
  status: PrintJobStatus;
  contentType?: 'text' | 'html' | 'pdf' | 'image' | null;
  title: string;
  referenceType?: string | null;
  referenceId?: string | null;
  retryCount: number;
  createdAt: string;
  updatedAt: string;
}

export interface PrintJobDetails extends PrintJobSummary {
  appId?: string | null;
  copies: number;
  payloadSourceType?: string | null;
  payloadSummary?: string | null;
  artifactPath?: string | null;
  artifactMimeType?: string | null;
  artifactSize?: number | null;
  artifactCreatedAt?: string | null;
  artifactChecksum?: string | null;
  options?: Record<string, unknown> | null;
  meta?: Record<string, unknown> | null;
  source?: string | null;
  idempotencyKey?: string | null;
  failureCategory?: PrintJobFailureCategory | null;
  failureMessage?: string | null;
  lastRetryAt?: string | null;
  nextRetryAt?: string | null;
  queuedAt?: string | null;
  startedAt?: string | null;
  completedAt?: string | null;
  canceledAt?: string | null;
}

export interface JobsListQuery {
  page?: number;
  perPage?: number;
  sortBy?: string;
  sortDirection?: 'asc' | 'desc';
  status?: PrintJobStatus;
  printerId?: string;
  contentType?: 'text' | 'html' | 'pdf' | 'image';
  referenceType?: string;
  referenceId?: string;
  search?: string;
}

export interface JobsListResult {
  items: PrintJobSummary[];
  totalCount: number;
  page: number;
  pageSize: number;
}

export interface PrintJobOptions extends Record<string, unknown> {
  paperSize?: string;
  silent?: boolean;
}

export interface PrintJobPayloadMeta extends Record<string, unknown> {
  referenceType?: string;
  referenceId?: string;
  source?: string;
}

interface BaseCreatePrintJobInput {
  printerId: string;
  copies?: number;
  options?: PrintJobOptions;
  meta?: PrintJobPayloadMeta;
  idempotencyKey?: string;
}

export interface CreateTextPrintJobInput extends BaseCreatePrintJobInput {
  contentType: 'text';
  payload: {
    content: string;
    fileName?: string;
    mimeType?: 'text/plain' | string;
  };
}

export interface CreateHtmlPrintJobInput extends BaseCreatePrintJobInput {
  contentType: 'html';
  payload: {
    content: string;
    fileName?: string;
    mimeType?: 'text/html' | string;
  };
}

export interface CreatePdfPrintJobInput extends BaseCreatePrintJobInput {
  contentType: 'pdf';
  payload: {
    content: string;
    fileName?: string;
    mimeType?: 'application/pdf' | string;
  };
}

export interface CreateImagePrintJobInput extends BaseCreatePrintJobInput {
  contentType: 'image';
  payload: {
    content: string;
    fileName?: string;
    mimeType?: string;
  };
}

export type CreatePrintJobInput =
  | CreateTextPrintJobInput
  | CreateHtmlPrintJobInput
  | CreatePdfPrintJobInput
  | CreateImagePrintJobInput;

