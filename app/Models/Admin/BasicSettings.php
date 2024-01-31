<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BasicSettings extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'mail_config'               => 'object',
        'firebase_config'               => 'object',
        'push_notification_config'  => 'object',
        'broadcast_config'          => 'object',
        'email_verification'          => 'boolean',
        'email_notification'          => 'boolean',
        'kyc_verification'          => 'boolean',
    ];


    public function mailConfig() {

    }
    public function scopeSitename($query, $pageTitle)
    {
        $pageTitle = empty($pageTitle) ? '' : ' - ' . $pageTitle;
        return $this->site_name . $pageTitle;
    }
}
