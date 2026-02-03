<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTemplate extends Model
{
    protected $table = 'template_chats';
    protected $fillable = ['request', 'response'];
}
