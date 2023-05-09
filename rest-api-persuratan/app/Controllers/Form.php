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
            $form_model = new FormModel();
            $form_data = $form_model->where('is_deleted', 0)->findAll();

            if($form_data) {
                $response = array(
                    'status' => 200,
                    'data' => $form_data
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => 'No data found'
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
                    'message' => 'No data found'
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

   public function getallpermohonan($user_token, $keyword="") {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            $permohonan_model = new PermohonanModel();

            $where = 'is_deleted = 0';
            if($keyword != '') {
                $where = 'is_deleted = 0 AND (perihal LIKE "%'.$keyword.'%" OR nrp LIKE "%'.$keyword.'%" OR nama LIKE "%'.$keyword.'%" OR universitas LIKE "%'.$keyword.'%" OR status LIKE "%'.$keyword.'%") ORDER BY updated_on DESC';
            }
            
            $permohonan_data = $permohonan_model->where($where)->findAll();

            if($permohonan_data) {
                $user_model = new UserModel();
                $login_user_data = $user_model->find($user_id);

                $data = array();
                foreach($permohonan_data as $permohonan) {
                    if($permohonan['created_by'] == $user_id || ($login_user_data['is_superadmin'] == 1 && strtolower($permohonan['status']) != 'draft')) {

                        $form_model = new FormModel();
                        $form_model = $form_model->find($permohonan['form_id']);

                        $jenis_peminjaman = '';
                        if($permohonan['jenis_peminjaman_id'] != '' && $permohonan['jenis_peminjaman_id'] > 0) {
                            $jenis_peminjaman_model = new JenisPeminjamanModel();
                            $jenis_peminjaman_model = $jenis_peminjaman_model->find($permohonan['jenis_peminjaman_id']);

                            if($jenis_peminjaman_model) {
                                $jenis_peminjaman = $jenis_peminjaman_model['jenis_peminjaman'];
                            }
                        }

                        $response_by = '-';
                        if($permohonan['response_by'] != '' && $permohonan['response_by'] > 0) {
                            $user_response = $user_model->find($permohonan['response_by']);

                            if($user_response) {
                                $response_by = $user_response['fullname'];
                            }
                        }

                        $user_create = $user_model->find($permohonan['created_by']);

                        $user_update = $user_model->find($permohonan['updated_by']);

                        $data[] = array(
                            'permohonan_id' => $permohonan['permohonan_id'],
                            'form'          => $form_model['form'],
                            'jenis_peminjaman' => $jenis_peminjaman,
                            'pdf_filename' => $permohonan['pdf_filename'],
                            'perihal'       => ucwords(strtolower($permohonan['perihal'])),
                            'nrp'       => $permohonan['nrp'],
                            'nama'      => ucwords(strtolower($permohonan['nama'])),
                            'universitas'   => strtoupper(strtolower($permohonan['universitas'])),
                            'keterangan'    => ucwords(strtolower($permohonan['keterangan'])),
                            'date_start'    => date('d M Y', strtotime($permohonan['date_start'])),
                            'date_end'      => date('d M Y', strtotime($permohonan['date_end'])),
                            'status'        => ucwords(strtolower($permohonan['status'])),
                            'is_open_for_notif'     => $permohonan['is_open_for_notif'],
                            'response_by'   => $response_by,
                            'alasan'        => $permohonan['alasan'],
                            'created_on'    => date('d M Y', strtotime($permohonan['created_on'])),
                            'created_by'    => $user_create['fullname'],
                            'updated_on'    => date('d M Y', strtotime($permohonan['updated_on'])),
                            'updated_by'    => $user_update['fullname'],
                            'has_edit_access' => $permohonan['created_by'] == $user_id ? "1" : "0",
                        );
                    }
                }


                $response = array(
                    'status' => 200,
                    'data' => $data
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => 'No data found'
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

   public function countunreadnotif($user_token) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            $permohonan_model = new PermohonanModel();
            $numOfUnreadNotif = $permohonan_model
                                ->where('is_deleted', 0)
                                ->where('is_open_for_notif', 0)
                                ->where('created_by', $user_id)
                                ->countAll();
            $response = array(
                'status' => 200,
                'data' => $numOfUnreadNotif
            );

        } else {
            $response = array(
                'status' => 201,
                'message' => 'Invalid user token'
            );
        }

        return $this->respond($response);
   }
}
