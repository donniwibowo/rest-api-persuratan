<?php

namespace App\Models;

use CodeIgniter\Model;

class FormModel extends Model
{
    protected $table = 'tbl_form';
    protected $primaryKey = 'form_id';
    protected $allowedFields = ['form', 'created_on', 'created_by', 'updated_on', 'updated_by', 'is_deleted'];

   
}
