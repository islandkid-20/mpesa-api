<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'total_price', 'amount_paid', 'order_number', 'status'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
