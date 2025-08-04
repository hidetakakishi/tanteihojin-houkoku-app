<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportContent extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'report_id',
        'summary',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function results()
    {
        return $this->hasMany(ReportResult::class);
    }
}