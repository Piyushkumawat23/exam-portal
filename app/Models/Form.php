<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'last_date'
    ];

    // Relationship: Ek form ke multiple submissions ho sakte hain
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
    public function form() {
    return $this->belongsTo(Form::class);
}

}