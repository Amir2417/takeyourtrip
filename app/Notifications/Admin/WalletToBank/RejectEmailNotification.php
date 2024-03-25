<?php

namespace App\Notifications\Admin\WalletToBank;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RejectEmailNotification extends Notification
{
    use Queueable;
    public $form_data;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($form_data)
    {
        $this->form_data = $form_data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data       = $this->form_data;
        $date       = Carbon::now();

        $dateTime   = $date->format('Y-m-d h:i:s A');
        return (new MailMessage)
                    ->greeting("Hello ".$data['data']->user->fullname." !")
                    ->subject("Wallet To Bank Transfer Information")
                    ->line("Your Wallet To Bank transfer request send successfully, details of wallet to bank")
                    ->line("Transaction Id: " .$data['data']->trx_id)
                    ->line("Request Amount: " . $data['data']->request_amount)
                    ->line("Fees & Charges: " . $data['data']->details->data->total_charge)
                    ->line("Total Payable Amount: " . $data['data']->details->data->total_payable)
                    ->line("Received Amount: " . $data['data']->details->data->receive_money)
                    ->line("Status: ". $data['status'])
                    ->line("Reject Reason: ". $data['reject_reason'])
                    ->line("Date And Time: " .$dateTime)
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
