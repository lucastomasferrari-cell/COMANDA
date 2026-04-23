<?php

namespace Modules\AuditLog\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyAntifraudReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $summary)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reporte anti-fraude del día — ' . ($this->summary['date'] ?? now()->toDateString()),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'auditlog::mail.daily_antifraud_report',
            with: [
                'summary' => $this->summary,
            ],
        );
    }
}
