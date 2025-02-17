<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'action',
        'status_from',
        'status_to',
        'remarks',
        'acted_by'
    ];

    // Relationships
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    // Scopes
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
