<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionItem extends Model
{
    protected $fillable = [
        'collection_id',
        'open_source_alternative_id',
        'position',
        'note',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }
}
