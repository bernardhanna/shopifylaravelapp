<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-06 12:03:26
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-27 08:29:27
 */


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    /**
     * Create a new message instance.
     *
     * @param array $emailData
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $adminEmails = config('app.admin_emails');
        $ccAddress = config('app.mail_cc_address');
        $bccAddress = config('app.mail_bcc_address');

        return $this->from(env('MAIL_TO_ADDRESS'))
            ->subject('Order Error: Missing SKU')
            ->cc($ccAddress) // Add the CC email address
            ->bcc($bccAddress) // Add the BCC email address
            ->to($adminEmails) // Use the admin email addresses from the configuration
            ->view('emails.order-error')
            ->with($this->emailData);
    }
}
