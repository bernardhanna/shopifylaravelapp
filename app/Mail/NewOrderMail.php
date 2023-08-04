<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-15 12:49:58
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-27 08:30:28
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orders;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      // Get the admin email addresses from the configuration
        $adminEmails = config('app.admin_emails');
        $ccAddress = config('app.mail_cc_address');
        $bccAddress = config('app.mail_bcc_address');

        if (count($this->orders) > 0) {
            return $this->from(env('MAIL_TO_ADDRESS'))
                        ->subject('New Orders Synced')
                        ->cc($ccAddress) // Add the CC email address
                        ->bcc($bccAddress) // Add the BCC email address
                        ->to($adminEmails) // Use the admin email addresses from the configuration
                        ->view('emails.new_orders'); // Assuming you have a view at resources/views/emails/new_orders.blade.php
        } else {
            return $this->from(env('MAIL_TO_ADDRESS'))
                        ->subject('No New Orders')
                        ->cc($ccAddress) // Add the CC email address
                        ->bcc($bccAddress) // Add the BCC email address
                        ->to($adminEmails) // Use the admin email addresses from the configuration
                        ->view('emails.no_orders'); // Assuming you have a view at resources/views/emails/no_orders.blade.php
        }
    }
}
