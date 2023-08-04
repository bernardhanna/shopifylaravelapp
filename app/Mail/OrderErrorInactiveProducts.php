<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-06-07 08:45:51
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-06-27 08:29:56
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderErrorInactiveProducts extends Mailable
{
    use Queueable, SerializesModels;

    public $orderNumber;
    public $lineItems;

    /**
     * Create a new message instance.
     *
     * @param  string  $orderNumber
     * @param  array  $lineItems
     * @return void
     */
    public function __construct($orderNumber, $lineItems)
    {
        $this->orderNumber = $orderNumber;
        $this->lineItems = $lineItems;
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
                    ->subject('Order Error: Product does not have an Active Status')
                    ->cc($ccAddress) // Add the CC email address
                    ->bcc($bccAddress) // Add the BCC email address
                    ->to($adminEmails) // Use the admin email addresses from the configuration
                    ->view('emails.order_error_inactive_products')
                    ->with([
                        'order_number' => $this->orderNumber,
                        'line_items' => $this->lineItems,
                    ]);
    }
}