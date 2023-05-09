<?php namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\UserApiLoginModel;
use App\Models\PermohonanModel;
use App\Models\FormModel;
use App\Models\JenisPeminjamanModel;

class Form extends ResourceController
{
    use ResponseTrait;
   
   public function getallformtype($user_token) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $formModel = new FormModel();
            $formData = $formModel->where('is_deleted', 0)->findAll();

            if($formData) {
                $response = array(
                    'status' => 200,
                    'data' => $formData
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => $formData->errors()
                );    
            }
        } else {
            $response = array(
                'status' => 201,
                'message' => 'Invalid user token'
            );
        }

        return $this->respond($response);
   }

   public function getalljenispeminjaman($user_token) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $jenis_peminjaman_model = new JenisPeminjamanModel();
            $jenis_peminjaman_data = $jenis_peminjaman_model->where('is_deleted', 0)->findAll();

            if($jenis_peminjaman_data) {
                $response = array(
                    'status' => 200,
                    'data' => $jenis_peminjaman_data
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => $jenis_peminjaman_data->errors()
                );    
            }
        } else {
            $response = array(
                'status' => 201,
                'message' => 'Invalid user token'
            );
        }

        return $this->respond($response);
   }
}
