<?php

namespace App\Notifications\User\WalletToBank;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CreateEmailNotification extends Notification
{
    use Queueable;
    public $user;
    public $data;
    public $trx_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data,$trx_id)
    {
        $this->user = $user;
        $this->data = $data;
        $this->trx_id = $trx_id;
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
        $user                   = $this->user;
        $data                   = $this->data;
        $trx_id                 = $this->trx_id;
        $status                 = "Pending";
        $date =  Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');
        return (new MailMessage)
        ->greeting("Hello ".$user->fullname." !")
        ->subject("Wallet To Bank Transfer Information")
        ->line("Your Wallet To Bank transfer request send successfully, details of wallet to bank :")
        ->line("Transaction Id: " .$trx_id)
        ->line("Request Amount: " . $data->request_amount)
        ->line("Fees & Charges: " . $data->total_charge)
        ->line("Total Payable Amount: " . $data->total_payable)
        ->line("Received Amount: " . $data->receive_money)
        ->line("Status: ". $status)
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
