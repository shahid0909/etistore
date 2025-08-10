<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'category_id',
        'designation',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
