<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

  protected $fillable = [
    'conversation_id',
    'sender_id',
    'content',
    'read_at',
    'deleted_for_everyone',
      'file_path',
    'file_type',
    'delivered_at' 
];

    protected $casts = [
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |-------------------------
    | RELATIONS
    |-------------------------
    */

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /*
    |-------------------------
    | HELPERS (WHATSAPP STYLE)
    |-------------------------
    */

    // Message lu ou non
    public function isRead()
    {
        return $this->read_at !== null;
    }

    // Message supprimé
    public function isDeleted()
    {
        return $this->deleted_at !== null;
    }
}