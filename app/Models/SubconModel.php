<?php

namespace App\Models;

use CodeIgniter\Model;

class SubconModel extends Model
{
    protected $table = 'subcons';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'date', 
        'type', 
        'item_name', 
        'vendor_name', 
        'quantity', 
        'weight_kg',
        'reference_id', 
        'monitoring', 
        'status'
    ];
}