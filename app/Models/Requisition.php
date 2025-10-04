<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id', 'department_id', 'designation_id',
        'rationale', 'status', 'approver_id', 'approved_at', 'remarks'
    ];

    public function staff() {
        return $this->belongsTo(Staff::class);
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function designation() {
        return $this->belongsTo(Designation::class);
    }

    public function items() {
        return $this->hasMany(RequisitionItem::class);
    }
    public function approvers() {
    return $this->hasMany(RequisitionApprover::class);
}
}
