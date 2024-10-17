<?php

namespace App\Models;

use App\Enums\RequestFileName;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MediaFile extends BaseModel
{
    use HasFactory;

    public static function REQUEST_FILE_NAMES(): array
    {
        return array_map(fn($status) => $status->value, RequestFileName::cases());
    }

    protected $fillable = [
        'type', 'file_name', 'file_path', 'file_size', 'width', 'height', 'mime_type', 'mediable_id', 'mediable_type'
    ];

    public function mediable()
    {
        return $this->morphTo();
    }
}
