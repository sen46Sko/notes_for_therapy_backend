<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminRegisterLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'uuid',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
