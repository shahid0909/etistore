<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    /**
     * Get the staff members associated with the Designation.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
