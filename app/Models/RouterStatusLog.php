<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'router_id',
        'status',
        'message',
        'metrics',
        'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'metrics' => 'array',
    ];

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }
}
