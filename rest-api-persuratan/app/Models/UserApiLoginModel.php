<?php

namespace App\Models;

use CodeIgniter\Model;

class UserApiLoginModel extends Model
{
    protected $table = 'tbl_user_api_login';
    protected $primaryKey = 'api_login_id';
    protected $allowedFields = ['user_id', 'clock_in', 'clock_out'];
}
