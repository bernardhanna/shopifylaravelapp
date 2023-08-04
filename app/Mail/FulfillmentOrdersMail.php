<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-15 15:23:34
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-27 08:31:01
 */


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FulfillmentOrdersMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fulfilledOrders;

    public function __construct($fulfilledOrders)
    {
        $this->fulfilledOrders = $fulfilledOrders;
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

        if (count($this->fulfilledOrders) > 0) {
            return $this->from(env('MAIL_FROM_ADDRESS'))
                        ->subject('New Orders Have Been Fulfilled')
                        ->cc($ccAddress) // Add the CC email address
                        ->bcc($bccAddress) // Add the BCC email address
                        ->to($adminEmails) // Use the admin email addresses from the configuration
                        ->view('emails.fulfilled_orders');
        } else {
            return $this->from(env('MAIL_FROM_ADDRESS'))
                        ->subject('No New Orders Have Been Fulfilled')
                        ->cc($ccAddress) // Add the CC email address
                        ->bcc($bccAddress) // Add the BCC email address
                        ->to($adminEmails) // Use the admin email addresses from the configuration
                        ->view('emails.no_fulfilled_orders');
        }
    }
}
