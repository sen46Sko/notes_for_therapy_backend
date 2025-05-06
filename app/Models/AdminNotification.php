<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    /**
     *
     *
     * @var array
     */
    protected $fillable = [
        'admin_id',
        'type',
        'subtype',
        'title',
        'content',
        'read',
        'read_at',
    ];

    /**
     *
     *
     * @var array
     */
    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     *
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     *
     */
    public function markAsRead()
    {
        $this->read = true;
        $this->read_at = now();
        $this->save();

        return $this;
    }
}