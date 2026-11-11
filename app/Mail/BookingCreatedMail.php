<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing(['boat', 'meals', 'rentals']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: str_replace(':code', $this->booking->code, 'Pesanan :code diterima, selesaikan pembayaran'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking-created');
    }
}
