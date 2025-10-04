<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionApprover extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id', 'approver_id', 'level',
        'status', 'action_at', 'remarks'
    ];

    public function requisition() {
        return $this->belongsTo(Requisition::class);
    }

    public function approver() {
        return $this->belongsTo(Staff::class, 'approver_id');
    }
}
