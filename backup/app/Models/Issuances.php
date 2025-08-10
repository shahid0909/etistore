<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuances extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'department_id',
        'product_id',
        'quantity',
        'issued_by',
        'description',
    ];

    // Relationships
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(Admin::class, 'issued_by');
    }
}
