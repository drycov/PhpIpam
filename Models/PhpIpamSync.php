<?php

namespace App\Plugins\PhpIpam\Models;

use Illuminate\Database\Eloquent\Model;

class PhpIpamSync extends Model
{
    /**
     * Имя таблицы
     */
    protected $table = 'phpipam_sync';

    /**
     * Разрешённые поля для массового заполнения
     */
    protected $fillable = [
        'device_id',
        'ip_address',
        'subnet',
        'status',
        'hostname',
        'description',
        'last_seen',
    ];

    /**
     * Автоматически управлять created_at / updated_at
     */
    public $timestamps = true;

    /**
     * Кастинг полей в нативные типы PHP
     */
    protected $casts = [
        'device_id' => 'integer',
        'last_seen' => 'datetime',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Индексы и вспомогательные методы
     */
    protected $attributes = [
        'status' => 'unknown',
    ];

    /**
     * Удобный аксессор — получить укороченный IP
     */
    public function getShortIpAttribute(): string
    {
        return explode('/', $this->ip_address ?? '')[0];
    }

    /**
     * Удобный аксессор для форматированного статуса
     */
    public function getStatusBadgeAttribute(): string
    {
        $status = strtolower($this->status ?? 'unknown');
        return match ($status) {
            'active' => '<span class="badge bg-success">Active</span>',
            'reserved' => '<span class="badge bg-warning text-dark">Reserved</span>',
            'offline', 'inactive' => '<span class="badge bg-danger">Offline</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    /**
     * Отношение с моделью устройств LibreNMS (если есть)
     */
    public function device()
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id', 'device_id');
    }
}
