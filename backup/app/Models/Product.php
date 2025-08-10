<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'subcategory_id',
        'is_returnable',
        'unit',
        'description'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Update current stock.
     */
    public function updateStock($quantity, $operation = 'add')
    {
        // Get or create inventory record for the product
        $inventory = $this->inventory()->first();

        if (!$inventory) {
            // If inventory does not exist, create a new record
            $inventory = Inventory::create([
                'product_id' => $this->id,
                'current_stock' => 0,
            ]);
        }

        // Update the current stock based on the operation
        if ($operation === 'add') {
            $inventory->current_stock += $quantity;
        } elseif ($operation === 'subtract') {
            $inventory->current_stock -= $quantity;
        }

        // Save the updated inventory
        $inventory->save();
    }

    /**
     * Relationship with Purchase.
     */
    public function purchases()
    {
        return $this->hasMany(Purchases::class);
    }
}
