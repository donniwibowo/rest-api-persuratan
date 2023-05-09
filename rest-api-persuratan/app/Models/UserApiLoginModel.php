<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\UserModel;

class UserApiLoginModel extends Model
{
    protected $table = 'tbl_user_api_login';
    protected $primaryKey = 'api_login_id';
    protected $allowedFields = ['user_id', 'clock_in', 'clock_out'];

    public static function doClockOut($api_login_id) {
        $apiLoginModel = new UserApiLoginModel();
        $api_data = [
            'clock_out' => date('Y-m-d H:i:s'),
        ];

        return $apiLoginModel->update($api_login_id, $api_data);
    }

    public static function getUserID($user_token) {
        $api_login_id = UserModel::decrypt($user_token);
        $api_login_model = new UserApiLoginModel();
        $api_login_data = $api_login_model->find($api_login_id);
        if($api_login_data) {
            return $api_login_data['user_id'];
        }

        return 0;
    } 
}
