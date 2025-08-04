<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HearingPreinfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'hearing_sheet_id',
        'label',
        'value',
        'image_path',
    ];

    public function sheet()
    {
        return $this->belongsTo(HearingSheet::class, 'hearing_sheet_id');
    }
}