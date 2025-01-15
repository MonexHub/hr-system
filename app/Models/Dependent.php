<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dependent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'type',
        'date_of_birth',
        'relationship',
        'po_box',
        'district_town_city',
        'county_id',
        'email',
        'phone',
        'mobile'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }
}
