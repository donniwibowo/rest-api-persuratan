<?php namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\UserApiLoginModel;

class User extends ResourceController
{
    use ResponseTrait;

    public function index()
    {
        return view('welcome_message');
    }
    
    public function login(){
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $model = new UserModel();
        $data = $model->where('email', $email)
                        ->where('status', 1)->first();

        if ($data) {
            if(UserModel::decrypt($data['password'], $data['encryption_key'], $data['encryption_iv']) == $password) {
                // get latest api login information
                $api_login_model = new UserApiLoginModel();
                $api_login_data = $api_login_model->where('user_id', $data['user_id'])
                                ->orderBy('api_login_id', 'DESC')->first();

                // force to logout
                if($api_login_data) {
                    if(is_null($api_login_data['clock_out'])) {
                        $api_data = [
                            'clock_out' => date('Y-m-d H:i:s'),
                        ];

                        $api_login_model->update($api_login_data['api_login_id'], $api_data);  
                    }
                }

                // create new api login
                $api_login_model = new UserApiLoginModel();
                $api_data = [
                    'user_id' => $data['user_id'],
                    'clock_in'  => date('Y-m-d H:i:s'),
                ];

                $api_login_model->insert($api_data);

                $result = array(
                    'status' => 200,
                    'data'   => [
                        'user_token'    => UserModel::encrypt($api_login_model->insertID),
                        'user_id'       => $data['user_id'],
                        'is_superadmin' => $data['is_superadmin'],
                        'email'         => $data['email'],
                        'phone'         => $data['phone'],
                        'fullname'      => ucwords(strtolower($data['fullname'])),
                    ]
                );
            } else {
                $result = array(
                    'status' => 401,
                    'message' => 'Invalid email or password'
                );
            }

            return $this->respond($result);
        } else {
            return $this->failNotFound('No data found');
        }

    }

    public function logout() {
        $user_token = $this->request->getVar('user_token');
        $response = array();

        if(UserModel::isUserTokenValid($user_token)) {
            $api_login_id = UserModel::decrypt($user_token);
            $model = UserApiLoginModel::doClockOut($api_login_id);

            if($model) {
                $response = array(
                    'status' => 200,
                    'message' => 'Log out success'
                );
            } else {
                $response = array(
                    'status' => 500,
                    'message' => $model->errors()
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
