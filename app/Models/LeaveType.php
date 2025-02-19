<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'requires_attachment',
        'is_paid',
        'is_active',
        'min_days_before_request',
        'max_days_per_request',
        'max_days_per_year',
        'requires_ceo_approval',
        'created_by'
    ];

    protected $casts = [
        'requires_attachment' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'requires_ceo_approval' => 'boolean',
    ];

}
