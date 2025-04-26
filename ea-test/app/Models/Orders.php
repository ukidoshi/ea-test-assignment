<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nm_id',
        'g_number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'total_price',
        'discount_percent',
        'warehouse_name',
        'oblast',
        'income_id',
        'odid',
        'subject',
        'category',
        'brand',
        'is_cancel',
        'cancel_dt',
    ];
}
