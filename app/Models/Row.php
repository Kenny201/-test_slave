<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Row extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'date',
    ];

    public function getDateAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
