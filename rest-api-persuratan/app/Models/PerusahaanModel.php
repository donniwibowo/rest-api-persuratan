<?php

namespace App\Models;

use CodeIgniter\Model;

class PerusahaanModel extends Model
{
    protected $table = 'tbl_perusahaan';
    protected $primaryKey = 'perusahaan_id';
    protected $allowedFields = ['name', 'email', 'address', 'phone', 'created_on', 'created_by', 'updated_on', 'updated_by', 'is_deleted'];

   
}
