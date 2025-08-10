<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'description', 'status'];

        /**
     * Get the sub category associated with the department.
     */
    public function subCategory()
    {
        return $this->hasMany(SubCategory::class);
    }
}
