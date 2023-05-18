<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisPeminjamanModel extends Model
{
    protected $table = 'tbl_jenis_peminjaman';
    protected $primaryKey = 'jenis_peminjaman_id';
    protected $allowedFields = ['form_id', 'jenis_peminjaman', 'created_on', 'created_by', 'updated_on', 'updated_by', 'is_deleted'];

   
}
