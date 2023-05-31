<?php namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\UserApiLoginModel;
use App\Models\PermohonanModel;
use App\Models\FormModel;
use App\Models\JenisPeminjamanModel;
use App\ThirdParty\fpdf\FPDF;

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

   public function getalljenispeminjaman($user_token, $form_id) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $jenis_peminjaman_model = new JenisPeminjamanModel();
            $jenis_peminjaman_data = $jenis_peminjaman_model->where('is_deleted', 0)
                                                            ->where('form_id', $form_id)
                                                            ->findAll();

            if($jenis_peminjaman_data) {
                $response = array(
                    'status' => 200,
                    'data' => $jenis_peminjaman_data
                );
            } else {
                $response = array(
                    'status' => 200,
                    'data' => null
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

   public function getjenispeminjaman($user_token, $jenis_peminjaman_id) {
        $response = array();
        if(UserModel::isUserTokenValid($user_token)) {
            $jenis_peminjaman_model = new JenisPeminjamanModel();
            $jenis_peminjaman_data = $jenis_peminjaman_model->find($jenis_peminjaman_id);

            if($jenis_peminjaman_data) {
                $response = array(
                    'status' => 200,
                    'data'  => $jenis_peminjaman_data['jenis_peminjaman']
                );
               
            } else {
                $response = array(
                    'status' => 201,
                    'data' => []
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
                $where = 'is_deleted = 0 AND (perihal LIKE "%'.$keyword.'%" OR nrp LIKE "%'.$keyword.'%" OR nama LIKE "%'.$keyword.'%" OR universitas LIKE "%'.$keyword.'%" OR status LIKE "%'.$keyword.'%")';
            }
            
            $permohonan_data = $permohonan_model->where($where)
                                                ->orderBy('updated_on', 'DESC')
                                                ->findAll();

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
                            'alasan'        => $permohonan['alasan'] == null || $permohonan['alasan'] == '' ? '' : $permohonan['alasan'],
                            'lampiran'        => $permohonan['lampiran'] == null || $permohonan['lampiran'] == '' ? '-' : $permohonan['lampiran'],
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
                    'data' => array()
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

   public function getpermohonan($user_token, $permohonan_id, $markasread) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $user_model = new UserModel();
                $login_user_data = $user_model->find($user_id);

                if($markasread == "1" || $login_user_data['is_superadmin'] == 1) {
                    $permohonan_model->update($permohonan_id, ['is_open_for_notif' => 1]);
                }

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
                        'alasan'        => $permohonan_data['alasan'] == null || $permohonan_data['alasan'] == '' ? '' : $permohonan_data['alasan'],
                        'lampiran'        => $permohonan_data['lampiran'] == null || $permohonan_data['lampiran'] == '' ? '-' : $permohonan_data['lampiran'],
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
                        'form_id'          => $form_model['form_id'],
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
                        'alasan'        => $permohonan_data['alasan'] == null || $permohonan_data['alasan'] == '' ? '' : $permohonan_data['alasan'],
                        'lampiran'        => $permohonan_data['lampiran'] == null || $permohonan_data['lampiran'] == '' ? '-' : $permohonan_data['lampiran'],
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
            $user_model = new UserModel();
            $user_data = $user_model->find($user_id);
            $numOfUnreadNotif = 0;

            if($user_data['is_superadmin'] == 1) {
                $permohonan_model = new PermohonanModel();
                $permohonan_data = $permohonan_model
                                    ->where('is_deleted', 0)
                                    ->where('is_open_for_notif', 0)
                                    ->where('status', 'pending')
                                    // ->where('created_by', $user_id)
                                    ->findAll();
                if($permohonan_data) {
                    $numOfUnreadNotif = count($permohonan_data);
                }
                
            }

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
        $alasan = $this->request->getVar('alasan');

        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            
            $user_model = new UserModel();

            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                // $isUpdated = $permohonan_model->update($permohonan_id, ['status' => $status, 'response_by'=> $user_id, 'alasan' => $alasan]);
                if(strtolower($status) == 'pending') {
                    $isUpdated = $permohonan_model->update($permohonan_id, ['status' => $status]); 
                } else {
                    $isUpdated = $permohonan_model->update($permohonan_id, ['status' => $status, 'response_by'=> $user_id, 'alasan' => $alasan]); 
                }
                

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
                } else {
                    $response = array(
                        'status' => 404,
                        'message' => 'Failed to update'
                    );
                }
               
            } else {
                $response = array(
                    'status' => 201,
                    'data' => []
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

   public function deletepermohonan($user_token) {
        $permohonan_id = $this->request->getVar('permohonan_id');
        
        $response = array();
        if(UserModel::isUserTokenValid($user_token)) {
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $isUpdated = $permohonan_model->update($permohonan_id, ['is_deleted' => 1]); 

                if($isUpdated) {
                    $response = array(
                        'status' => 200
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

   public function getpdffilename($user_token, $permohonan_id) {
        $response = array();
        if(UserModel::isUserTokenValid($user_token)) {
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $response = array(
                    'status' => 200,
                    'data'  => $permohonan_data['pdf_filename']
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

   public function createpermohonan($user_token) {
        $response = array();
        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            $permohonan_id = $this->request->getVar('permohonan_id');
            $form_id = $this->request->getVar('form_id');
            $jenis_peminjaman_id = $this->request->getVar('jenis_peminjaman_id');
            $perihal = $this->request->getVar('perihal');

            if($jenis_peminjaman_id > 0) {
                $jenis_peminjaman_model = new JenisPeminjamanModel();
                $jenis_peminjaman_data = $jenis_peminjaman_model->find($jenis_peminjaman_id);
                if($jenis_peminjaman_data) {
                    $perihal .= ' '.$jenis_peminjaman_data['jenis_peminjaman'];
                }
            }

            $data = [
                'form_id' => $form_id,
                'jenis_peminjaman_id' => $jenis_peminjaman_id,
                'perihal' => $perihal,
                'nrp' => $this->request->getVar('nrp'),
                'nama' => $this->request->getVar('nama'),
                'universitas' => $this->request->getVar('universitas'),
                'keterangan' => $this->request->getVar('keterangan'),
                'date_start' => date('Y-m-d H:i:s', strtotime($this->request->getVar('date_start'))),
                'date_end' => date('Y-m-d H:i:s', strtotime($this->request->getVar('date_end'))),
                'status' => $this->request->getVar('status'),
                'is_open_for_notif' => 0,
                'alasan' => $this->request->getVar('alasan'),
                'created_by' => $user_id,
                'created_on' => date('Y-m-d H:i:s'),
                'updated_by' => $user_id,
                'updated_on' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ];

            $permohonan_model = new PermohonanModel();
            $form_model = new FormModel();
            $query_result = 0;

            if($permohonan_id > 0) {
                // for update
                $query_result = $permohonan_model->update($permohonan_id, $data);
            } else {
                // for create new
                $query_result = $permohonan_model->insert($data);
                $permohonan_id = $permohonan_model->insertID;
            }

            if($query_result) {
                $permohonan_data = $permohonan_model->find($permohonan_id);

                $form_data = $form_model->find($form_id);
                $pdf_filename = $permohonan_data['nrp'] . '_' . $permohonan_id . '_' . preg_replace('/\s+/', '', $form_data['form']) . '.pdf';

                $permohonan_model->update($permohonan_id, ['pdf_filename' => $pdf_filename]);

                if(isset($_FILES['file']['tmp_name'])) {
                    $tempFile = $_FILES['file']['tmp_name'];

                    $temp = explode(".", $_FILES["file"]["name"]);
                    $newfilename = round(microtime(true)) . '.' . end($temp);

                    $targetPath = 'documents/';
                    $targetFile =  $targetPath. $newfilename;
                    if(move_uploaded_file($tempFile,$targetFile)) {
                        $permohonan_model->update($permohonan_id, ['lampiran' => $newfilename]);  
                    }
                }
                
                $response = array(
                    'status' => 200,
                    'data'  => [
                        'permohonan_id' => $permohonan_data['permohonan_id'],
                        'status'        => ucwords(strtolower($permohonan_data['status'])),
                        'pdf_filename'  => $pdf_filename,
                        'has_edit_access' => $permohonan_data['created_by'] == $user_id ? "1" : "0",
                    ]
                );
            } else {
                $response = array(
                    'status' => 404,
                    'message' => 'Failed to create/update data'
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

   public function markasread($user_token) {
        $permohonan_id = $this->request->getVar('permohonan_id');
        
        $response = array();
        if(UserModel::isUserTokenValid($user_token)) {
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $isUpdated = $permohonan_model->update($permohonan_id, ['is_open_for_notif' => 1]); 

                if($isUpdated) {
                    $response = array(
                        'status' => 200
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

   public function getunreadpermohonan($user_token) {
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $user_id = UserApiLoginModel::getUserID($user_token);
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->where('is_deleted', 0)
                                                ->where('is_open_for_notif', 0)
                                                ->where('status', 'pending')
                                                ->orderBy('created_on', 'DESC')
                                                ->findAll();

            if($permohonan_data) {
                $user_model = new UserModel();
                $login_user_data = $user_model->find($user_id);

                $data = array();
                foreach($permohonan_data as $permohonan) {
                    if($login_user_data['is_superadmin'] == 1) {

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
                            'alasan'        => $permohonan['alasan'] == null || $permohonan['alasan'] == '' ? '' : $permohonan['alasan'],
                            'lampiran'        => $permohonan['lampiran'] == null || $permohonan['lampiran'] == '' ? '-' : $permohonan['lampiran'],
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

   public function generatepdf($user_token, $permohonan_id) {
        $response = array();
        $filename = 'default.pdf';
        if(UserModel::isUserTokenValid($user_token)) {
            $user_model = new UserModel();
            $permohonan_model = new PermohonanModel();
            $permohonan_data = $permohonan_model->find($permohonan_id);

            if($permohonan_data) {
                $form_model = new FormModel();
                $form_data = $form_model->find($permohonan_data['form_id']);

                $filename = $permohonan_data['nrp'].'_'.$permohonan_data['permohonan_id'].'_'.preg_replace('/\s+/', '', $form_data['form']).'.pdf';

                $pdf = new FPDF();
                $pdf->AddPage();
                // $pdf->SetFont('Arial', 'B', 18);
                 // Logo
                $pdf->Image('documents/logo2.png',10,6,40);
                // Arial bold 15
                $pdf->SetFont('Arial','B',14);
                // Move to the right
                $pdf->Cell(10);
                // Title
                $pdf->Cell(200,10,'PT. INTEGRA TEKNOLOGI SOLUSI', 0, 0, 'C');
                $pdf->Ln(5);
                $pdf->SetFont('Arial','B',9);
                $pdf->Cell(10);
                $pdf->Cell(200,10,'Wisma Medokan Asri, Jl. Medokan Asri Utara XV No.10, Medokan Ayu', 0, 0, 'C');
                $pdf->Ln(4);
                $pdf->Cell(10);
                $pdf->Cell(200,10,'Rungkut, Surabaya City, East Java 60295', 0, 1, 'C');
                // Line break
                $pdf->Ln(5);
                $pdf->Line(10, 30, 210-10, 30); // 20mm from each edge
                

                $pdf->SetFont('Arial','B',14);
                $pdf->Cell(200,10,strtoupper($permohonan_data['perihal']), 0, 0, 'C');

                $pdf->SetFont('Arial','B',9);
                $pdf->Ln(10);

                $pdf->SetFont('');
                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'NRP');
                $pdf->Cell(200, 20, ': '.$permohonan_data['nrp']);

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Nama');
                $pdf->Cell(200, 20, ucwords(strtolower(': '.$permohonan_data['nama'])));

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Universitas');
                $pdf->Cell(200, 20, strtoupper(strtolower(': '.$permohonan_data['universitas'])));

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Perihal');
                $pdf->Cell(200, 20, ucwords(strtolower(': '.$permohonan_data['perihal'])));

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Tanggal Mulai');
                $pdf->Cell(200, 20, ': '.date('d M Y', strtotime($permohonan_data['date_start'])));

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Tanggal Berakhir');
                $pdf->Cell(200, 20, ': '.date('d M Y', strtotime($permohonan_data['date_end'])));

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Status');
                $pdf->Cell(200, 20, strtoupper(strtolower(': '.$permohonan_data['status'])));

                $pdf->Ln(5);

                $pdf->Cell(15);
                $pdf->Cell(35, 20, 'Keterangan');
                $pdf->Cell(200, 20, ': '.$permohonan_data['keterangan']);


                $pdf->Ln(20);
                $pdf->Cell(5);
                $pdf->MultiCell(180, 5, 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.');

                $response_by = '';
                if($permohonan_data['response_by'] != '' && $permohonan_data['response_by'] > 0) {
                    $user_response = $user_model->find($permohonan_data['response_by']);

                    if($user_response) {
                        $response_by = $user_response['fullname'];
                    }
                }


                $pdf->Ln(30);
                $pdf->Cell(150);
                $pdf->Cell(50, 10, 'Menyetujui');

                $pdf->Ln(10);
                $pdf->Cell(150);

                // $pdf->Cell(50, 10, strtoupper(strtolower($permohonan_data['status'])));
                if(strtolower($permohonan_data['status']) == 'approved') {
                    $pdf->Image('documents/ttd.png',150,152,40);
                } else if(strtolower($permohonan_data['status']) == 'rejected') {
                    $pdf->Image('documents/rejected-stamp.png',150,152,40);
                }
                

                $pdf->Ln(20);
                
                if($response_by == '') {
                    $pdf->Cell(145);
                    $pdf->Cell(50, 10, '(............................)');
                } else {
                    // $response_by = 'Jason Immanuel, S. Kom, M. Kom';
                    $len = strlen($response_by);
                    $space = 145;
                    if($len < 14) {
                        $space = $space + (14 - $len - 1);
                    } elseif($len > 15) {
                        $space = $space - ($len - 16);
                    }

                    $pdf->Cell($space);
                    $pdf->Cell(50, 10, '('.strtoupper(strtolower($response_by)).')');
                }
                

                $pdf->Output('F', 'documents/'.$filename); 
                
                $permohonan_model->update($permohonan_data['permohonan_id'], ['pdf_filename' => $filename]);
                $response = array(
                    'status' => 200,
                    'link' => 'http://34.101.208.151/agutask/persuratan/persuratan-api/rest-api-persuratan/public/documents/'.$filename

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

}
