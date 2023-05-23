<?php

namespace App\Models;

use CodeIgniter\Model;

class PermohonanModel extends Model
{
    protected $table = 'tbl_permohonan';
    protected $primaryKey = 'permohonan_id';
    protected $allowedFields = ['permohonan_id', 'form_id', 'jenis_peminjaman_id', 'pdf_filename', 'perihal', 'nrp', 'nama', 'universitas', 'keterangan', 'date_start', 'date_end', 'status', 'is_open_for_notif', 'response_by', 'alasan', 'lampiran', 'created_on', 'created_by', 'updated_on', 'updated_by', 'is_deleted'];

   
}
