<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'current_stock', 'reorder_level', 'location'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
