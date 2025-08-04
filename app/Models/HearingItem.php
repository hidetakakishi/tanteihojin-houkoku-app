<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HearingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'hearing_sheet_id',
        'item_label',
    ];

    public function sheet()
    {
        return $this->belongsTo(HearingSheet::class, 'hearing_sheet_id');
    }
}
