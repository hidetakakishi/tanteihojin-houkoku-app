<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'report_key',
        'hearing_sheet_id',
        'staff_comment',
    ];

    public function hearingSheet()
    {
        return $this->belongsTo(HearingSheet::class);
    }

    public function contents()
    {
        return $this->hasMany(ReportContent::class);
    }
}