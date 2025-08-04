<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HearingSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'interview_datetime',
        'investigation_type',
        'client_name',
        'staff_name',
        'purpose',
    ];

    public function preinfos()
    {
        return $this->hasMany(HearingPreinfo::class);
    }

    public function items()
    {
        return $this->hasMany(HearingItem::class);
    }
}