<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastDelivery extends Model
{
  protected $fillable = [
    'broadcast_id',
    'recipient_name',
    'recipient_phone',
    'channel',
    'status',
    'error_message',
    'delivered_at',
  ];
}
