<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'unit', 'color', 'icon', 'description', 'subtypes', 'is_confidential', 'sort'];

    protected function casts(): array
    {
        return ['subtypes' => 'array', 'is_confidential' => 'boolean'];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function unitLabel(): string
    {
        return config("lsw.units.{$this->unit}", $this->unit);
    }
}
