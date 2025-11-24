<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'user_id',
        'amount',
        'payment_id', // Razorpay/Stripe Transaction ID
        'method',
        'status',
        'pdf_path' // PDF Receipt ka link
    ];

    // Relationships
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}