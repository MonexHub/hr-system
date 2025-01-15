<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',     // Add this
        'leave_type_id',   // Add this
        'total_days',
        'days_taken',
        'days_remaining',
        'year'
    ];

    protected $casts = [
        'year' => 'integer'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id')
            ->where('leave_type_id', $this->leave_type_id)
            ->whereYear('start_date', $this->year);
    }
}
