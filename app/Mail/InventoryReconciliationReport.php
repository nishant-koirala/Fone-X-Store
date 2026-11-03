<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventoryReconciliationReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $mismatches
     */
    public function __construct(
        public array $mismatches
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ALERT: Inventory Reconciliation Drift Detected - FoneX Store',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inventory-reconciliation-report',
        );
    }
}
