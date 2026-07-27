<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'status_dari', 'status_ke', 'keterangan', 'changed_at'])]
class StatusHistory extends Model
{
    protected $table = 'status_history';

    public $timestamps = false;

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
