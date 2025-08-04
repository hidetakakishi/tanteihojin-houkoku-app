<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportResult extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'report_content_id',
        'description',
        'image_paths',
        'date',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];

    public function content()
    {
        return $this->belongsTo(ReportContent::class, 'report_content_id');
    }
}