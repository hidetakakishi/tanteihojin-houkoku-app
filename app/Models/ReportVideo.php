<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportVideo extends Model
{
    protected $fillable = ['report_id', 'video_path'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}