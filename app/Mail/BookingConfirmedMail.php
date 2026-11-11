<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        $this->booking->loadMissing(['boat', 'meals', 'rentals']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: str_replace(':code', $this->booking->code, 'E-tiket :code sudah terbit'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.booking-confirmed');
    }
}
