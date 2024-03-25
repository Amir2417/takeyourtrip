<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletToBankLogs extends JsonResource{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $statusInfo = [
            "success" =>      1,
            "pending" =>      2,
            "rejected" =>     3,
        ];
        return [
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => $this->type,
            'request_amount' => getAmount($this->request_amount,2),
            'payable' => getAmount($this->payable,2),
            'exchange_rate' => getAmount($this->details->data->exchange_rate),
            'total_charge' => getAmount($this->charge->total_charge,2),
            "confirm" =>$this->confirm??false,
            "credentials" =>$this->details->data->bank,
            'status' => $this->stringStatus->value ,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,
            'default_currency' => get_default_currency_code() ,
            'bank_currency' => $this->details->data->bank_currency ,
        ];
    }
}