<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'ip_address',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function markRead(): void
    {
        $this->update([
            'status' => 'read',
            'read_at' => $this->read_at ?? now(),
        ]);
    }
}
