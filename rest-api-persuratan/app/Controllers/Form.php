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

   public function getpermohonan($user_token, $permohonan_id) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $user_model = new UserModel();
                $login_user_data = $user_model->find($user_id);

                $data = array();
                
                if($permohonan_data['created_by'] == $user_id || ($login_user_data['is_superadmin'] == 1 && strtolower($permohonan_data['status']) != 'draft')) {

                    $form_model = new FormModel();
                    $form_model = $form_model->find($permohonan_data['form_id']);

                    $jenis_peminjaman = '';
                    if($permohonan_data['jenis_peminjaman_id'] != '' && $permohonan_data['jenis_peminjaman_id'] > 0) {
                        $jenis_peminjaman_model = new JenisPeminjamanModel();
                        $jenis_peminjaman_model = $jenis_peminjaman_model->find($permohonan_data['jenis_peminjaman_id']);

                        if($jenis_peminjaman_model) {
                            $jenis_peminjaman = $jenis_peminjaman_model['jenis_peminjaman'];
                        }
                    }

                    $response_by = '-';
                    if($permohonan_data['response_by'] != '' && $permohonan_data['response_by'] > 0) {
                        $user_response = $user_model->find($permohonan_data['response_by']);

                        if($user_response) {
                            $response_by = $user_response['fullname'];
                        }
                    }

                    $user_create = $user_model->find($permohonan_data['created_by']);

                    $user_update = $user_model->find($permohonan_data['updated_by']);

                    $data[] = array(
                        'permohonan_id' => $permohonan_data['permohonan_id'],
                        'form'          => $form_model['form'],
                        'jenis_peminjaman' => $jenis_peminjaman,
                        'pdf_filename' => $permohonan_data['pdf_filename'],
                        'perihal'       => ucwords(strtolower($permohonan_data['perihal'])),
                        'nrp'       => $permohonan_data['nrp'],
                        'nama'      => ucwords(strtolower($permohonan_data['nama'])),
                        'universitas'   => strtoupper(strtolower($permohonan_data['universitas'])),
                        'keterangan'    => ucwords(strtolower($permohonan_data['keterangan'])),
                        'date_start'    => date('d M Y', strtotime($permohonan_data['date_start'])),
                        'date_end'      => date('d M Y', strtotime($permohonan_data['date_end'])),
                        'status'        => ucwords(strtolower($permohonan_data['status'])),
                        'is_open_for_notif'     => $permohonan_data['is_open_for_notif'],
                        'response_by'   => $response_by,
                        'alasan'        => $permohonan_data['alasan'],
                        'created_on'    => date('d M Y', strtotime($permohonan_data['created_on'])),
                        'created_by'    => $user_create['fullname'],
                        'updated_on'    => date('d M Y', strtotime($permohonan_data['updated_on'])),
                        'updated_by'    => $user_update['fullname'],
                        'has_edit_access' => $permohonan_data['created_by'] == $user_id ? "1" : "0",
                    );
                }


                $response = array(
                    'status' => 200,
                    'data' => $data
                );
            } else {
                $response = array(
                    'status' => 201,
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

   public function getpermohonanforedit($user_token, $permohonan_id) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $user_model = new UserModel();
                $login_user_data = $user_model->find($user_id);

                $data = array();
                
                if($permohonan_data['created_by'] == $user_id || ($login_user_data['is_superadmin'] == 1 && strtolower($permohonan_data['status']) != 'draft')) {

                    $form_model = new FormModel();
                    $form_model = $form_model->find($permohonan_data['form_id']);

                    $jenis_peminjaman = '';
                    if($permohonan_data['jenis_peminjaman_id'] != '' && $permohonan_data['jenis_peminjaman_id'] > 0) {
                        $jenis_peminjaman_model = new JenisPeminjamanModel();
                        $jenis_peminjaman_model = $jenis_peminjaman_model->find($permohonan_data['jenis_peminjaman_id']);

                        if($jenis_peminjaman_model) {
                            $jenis_peminjaman = $jenis_peminjaman_model['jenis_peminjaman'];
                        }
                    }

                    $response_by = '-';
                    if($permohonan_data['response_by'] != '' && $permohonan_data['response_by'] > 0) {
                        $user_response = $user_model->find($permohonan_data['response_by']);

                        if($user_response) {
                            $response_by = $user_response['fullname'];
                        }
                    }

                    $user_create = $user_model->find($permohonan_data['created_by']);

                    $user_update = $user_model->find($permohonan_data['updated_by']);

                    $data = array(
                        'permohonan_id' => $permohonan_data['permohonan_id'],
                        'form'          => $form_model['form'],
                        'jenis_peminjaman' => $jenis_peminjaman,
                        'pdf_filename' => $permohonan_data['pdf_filename'],
                        'perihal'       => ucwords(strtolower($permohonan_data['perihal'])),
                        'nrp'       => $permohonan_data['nrp'],
                        'nama'      => ucwords(strtolower($permohonan_data['nama'])),
                        'universitas'   => strtoupper(strtolower($permohonan_data['universitas'])),
                        'keterangan'    => ucwords(strtolower($permohonan_data['keterangan'])),
                        'date_start'    => date('d M Y', strtotime($permohonan_data['date_start'])),
                        'date_end'      => date('d M Y', strtotime($permohonan_data['date_end'])),
                        'status'        => ucwords(strtolower($permohonan_data['status'])),
                        'is_open_for_notif'     => $permohonan_data['is_open_for_notif'],
                        'response_by'   => $response_by,
                        'alasan'        => $permohonan_data['alasan'],
                        'created_on'    => date('d M Y', strtotime($permohonan_data['created_on'])),
                        'created_by'    => $user_create['fullname'],
                        'updated_on'    => date('d M Y', strtotime($permohonan_data['updated_on'])),
                        'updated_by'    => $user_update['fullname'],
                        'has_edit_access' => $permohonan_data['created_by'] == $user_id ? "1" : "0",
                    );
                }


                $response = array(
                    'status' => 200,
                    'data' => $data
                );
            } else {
                $response = array(
                    'status' => 201,
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

   public function updatestatus($user_token) {
        $permohonan_id = $this->request->getVar('permohonan_id');
        $status = $this->request->getVar('status');

        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            
            $user_model = new UserModel();

            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $isUpdated = $permohonan_model->update($permohonan_id, ['status' => $status, 'response_by', $user_id]); 

                if($isUpdated) {
                    $permohonan_data = $permohonan_model->find($permohonan_id);

                    $response_by = '-';
                    $user_response = $user_model->find($user_id);

                    if($user_response) {
                        $response_by = $user_response['fullname'];
                    }

                    $response = array(
                        'status' => 200,
                        'data'  => [
                            'permohonan_id' => $permohonan_data['permohonan_id'],
                            'status'        => $permohonan_data['status'],
                            'pdf_filename'  => $permohonan_data['pdf_filename'],
                            'nrp'   => $permohonan_data['nrp'],
                            'nama' => $permohonan_data['nama'],
                            'universitas'   => $permohonan_data['universitas'],
                            'perihal'       => $permohonan_data['perihal'],
                            'date_start'    => date('d M Y', strtotime($permohonan_data['date_start'])),
                            'date_end'      => date('d M Y', strtotime($permohonan_data['date_end'])),
                            'response_by'   => $response_by,
                        ]
                    );

                    $response = array(
                        'status' => 200,
                        'message' => $response
                    );
                } else {
                    $response = array(
                        'status' => 404,
                        'message' => 'Failed to update'
                    );
                }
               
            } else {
                $response = array(
                    'status' => 201,
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
}
