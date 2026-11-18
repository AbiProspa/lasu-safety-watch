<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Report extends Model
{
    protected $fillable = [
        'reference', 'category_id', 'subtype', 'title', 'description', 'severity', 'status',
        'location_name', 'latitude', 'longitude', 'occurred_at', 'photo_path', 'is_anonymous',
        'is_sos', 'reporter_id', 'reporter_contact', 'assigned_to', 'verified_at', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'verified_at' => 'datetime',
            'resolved_at' => 'datetime',
            'is_anonymous' => 'boolean',
            'is_sos' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Report $report) {
            $report->reference ??= self::newReference();
        });
    }

    /** Tracking code such as LSW-7K3P9Q (no confusing 0/O/1/I characters). */
    public static function newReference(): string
    {
        do {
            $code = 'LSW-'.collect(range(1, 6))->map(fn () => '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'[random_int(0, 31)])->implode('');
        } while (self::where('reference', $code)->exists());

        return $code;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ReportUpdate::class)->latest();
    }

    /** Reports anyone may see on the public map. */
    public function scopePublic(Builder $q): Builder
    {
        return $q->whereIn('status', config('lsw.public_statuses'))
            ->whereHas('category', fn ($c) => $c->where('is_confidential', false));
    }

    /** Reports a staff member is allowed to manage. */
    public function scopeVisibleTo(Builder $q, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $q;
        }

        return $q->whereHas('category', fn ($c) => $c->where('unit', $user->unit()));
    }

    public function statusLabel(): string
    {
        return config("lsw.statuses.{$this->status}", $this->status);
    }

    public function severityLabel(): string
    {
        return config("lsw.severities.{$this->severity}", $this->severity);
    }

    public function reporterName(): string
    {
        if ($this->is_anonymous || ! $this->reporter) {
            return 'Anonymous';
        }

        return $this->reporter->name;
    }

    public function shortDescription(int $len = 120): string
    {
        return Str::limit($this->description, $len);
    }
}
