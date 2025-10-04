<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id', 'product_id', 'requested_qty', 'approved_qty', 'status', 'remarks'
    ];

    public function requisition() {
        return $this->belongsTo(Requisition::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
