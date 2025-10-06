<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'snmp_community',
        'snmp_version',
        'snmp_port',
        'snmp_v3_username',
        'snmp_v3_auth_protocol',
        'snmp_v3_auth_password',
        'snmp_v3_priv_protocol',
        'snmp_v3_priv_password',
        'snmp_v3_security_level',
        'category_id',
        'location',
        'latitude',
        'longitude',
        'description',
        'status',
        'is_active',
        'last_checked_at',
    ];

    protected $casts = [
        'snmp_port' => 'integer',
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $hidden = [
        'snmp_community',
        'snmp_v3_auth_password',
        'snmp_v3_priv_password',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(RouterCategory::class, 'category_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RouterStatusLog::class);
    }

    public function hasCoordinates(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }
}
