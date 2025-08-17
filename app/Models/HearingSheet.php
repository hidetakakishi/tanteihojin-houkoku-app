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
        'company_name',
        'staff_name',
        'purpose',
        'user_id',
    ];

    public function preinfos()
    {
        return $this->hasMany(HearingPreinfo::class);
    }

    public function items()
    {
        return $this->hasMany(HearingItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function report()
    {
        return $this->hasOne(Report::class, 'hearing_sheet_id');
    }
}