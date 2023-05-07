<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'tbl_user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['is_superadmin', 'fullname', 'email', 'password', 'phone', 'address', 'position', 'status', 'status_email', 'secret_key', 'encryption_key', 'encryption_iv', 'created_on', 'created_by', 'updated_on', 'updated_by', 'is_deleted'];

    static $skey = "3917b28f82e9d0f630f48e89f2c91de7"; // you can change it
    static $ciphering = "AES-128-CTR";
    static $options = 0;
    static $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    static $digits = 16;


    public static function encrypt($input, $encryption_key = '', $encryption_iv = '')
    {
        
        if($encryption_key == '') {
            $encryption_key = 'G72FHT1O9UDJCAQB';
        }

        if($encryption_iv == '') {
            $encryption_iv = '3155634379930093';
        }
        
        return openssl_encrypt($input, self::$ciphering, $encryption_key, self::$options, $encryption_iv);
      
    }

    public static function decrypt($input, $decryption_key = '', $decryption_iv = '')
    {
        
        if($decryption_key == '') {
            $decryption_key = 'G72FHT1O9UDJCAQB';
        }

        if($decryption_iv == '') {
            $decryption_iv = '3155634379930093';
        }

        return openssl_decrypt($input, self::$ciphering, $decryption_key, self::$options, $decryption_iv);

    }

    public function getLoginInformation() {
        $result = array(
            'user_id'       => $this->user_id,
            'is_superadmin' => $this->is_superadmin,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'fullname'      => ucwords(strtolower($this->fullname)),
           
        );

        return $result;
    }
}
