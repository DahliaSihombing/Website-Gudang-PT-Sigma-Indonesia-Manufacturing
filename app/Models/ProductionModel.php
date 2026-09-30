<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductionModel extends Model
{
    protected $table      = 'productions';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'date', 
        'shift', 
        'operator_name', 
        'material_name', 
        'material_amount', 
        'item_name', 
        'category', 
        'quantity', 
        'unit', 
        'weight_kg',
        'status', 
        'created_by',
        'subcon_id',
        'material_info', 
        'qty_ok', 
        'qty_ng', 
        'ng_repair_burry', 
        'ng_repair_belum', 
        'ng_repair_dimensi',
        'ng_reject_gompal', 
        'ng_reject_baret', 
        'ng_reject_hole', 
        'ng_reject_replating', 
        'ng_reject_dacon', 
        'ng_reject_panjang', 
        'ng_reject_pendek'
    ];
}