<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'description', 'status'];

    /**
     * Get the staff members associated with the department.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
