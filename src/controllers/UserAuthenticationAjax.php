<?php


/**
 * UserAuthenticationAjax
 *
 * @category    Erdiko
 * @package     User
 * @copyright   Copyright (c) 2016, Arroyo Labs, http://www.arroyolabs.com
 * @author      Julian Diaz, julian@arroyolabs.com
 */

namespace erdiko\users\controllers;

use erdiko\authenticate\BasicAuth;
use erdiko\authenticate\iErdikoUser;

use erdiko\users\models\User;

class UserAuthenticationAjax extends \erdiko\core\AjaxController
{

    /**
     * @param null $var
     *
     * @return mixed
     */
    public function get($var = null)
    {
        $this->id = 0;
        if (!empty($var)) {
            $routing = explode('/', $var);
            if(is_array($routing)) {
                $var = array_shift($routing);
                $this->id = empty($routing)
                    ? 0
                    : array_shift($routing);
            } else {
                $var = $routing;
            }

            header('Content-Type: application/json');
            return $this->_autoaction($var, 'get');
        } else {
            return $this->getNoop();
        }
    }

    /**
     * @param null $var
     *
     * @return mixed
     */
    public function post($var = null)
    {
        $this->id = 0;
        if (!empty($var)) {
            $routing = explode('/', $var);
            if(is_array($routing)) {
                $var = array_shift($routing);
                $this->id = empty($routing)
                    ? 0
                    : array_shift($routing);
            } else {
                $var = $routing;
            }

            // load action based off of naming conventions
            header('Content-Type: application/json');
            return $this->_autoaction($var, 'post');
        } else {
            return $this->getNoop();
        }
    }

    /**
     * Default response for no action requests
     */
    protected function getNoop()
    {
        $response = array(
            "action" => "None",
            "success" => false,
            "error_code" => 404,
            "error_message" => 'Sorry, you need to specify a valid action'
        );

        $this->setContent($response);
    }


    public function postLogin()
    {
        $response = array(
            "method" => "login",
            "success" => false,
            "error_code" => 0,
            "error_message" => ""
        );

        try {
            $data = json_decode(file_get_contents("php://input"));
            // Check required fields
            $requiredParams = array('email','password');
            $params = (array) $data;
            foreach ($requiredParams as $param){
                if(empty($params[$param])){
                    throw new \Exception(ucfirst($param) .' is required.');
                }
            }

            $authenticator = new BasicAuth(new User());
            if($authenticator->login(array('username'=>$data->email, 'password'=>$data->password),'erdiko_user')){
                $response['success'] = true;
            }
            else{
                throw new \Exception("Username or password are wrong. Please try again.");
            }
            $this->setStatusCode(200);
        } catch (\Exception $e) {
            $response['error_message'] = $e->getMessage();
            $response['error_code'] = $e->getCode();
        }

        $this->setContent($response);
    }

    public function getLogout()
    {
        $response = array(
            "method" => "logout",
            "success" => false,
            "error_code" => 0,
            "error_message" => ""
        );

        try {
            $authenticator = new BasicAuth(new User());
            $authenticator->logout();
            $response['success'] = true;
            $this->setStatusCode(200);
        } catch (\Exception $e) {
            $response['error_message'] = $e->getMessage();
            $response['error_code'] = $e->getCode();
        }

        $this->setContent($response);
    }

    public function postForgotPass(){
        $response = array(
            "method" => "forgotpass",
            "success" => false,
            "error_code" => 0,
            "error_message" => ""
        );

        try {
            $data = json_decode(file_get_contents("php://input"));
            // Check required fields
            $requiredParams = array('email');
            $params = (array) $data;
            foreach ($requiredParams as $param){
                if(empty($params[$param])){
                    throw new \Exception(ucfirst($param) .' is required.');
                }
            }

            $authenticator = new BasicAuth(new User());
            /**
             * ask what to do here
             */
            $this->setStatusCode(200);
        } catch (\Exception $e) {
            $response['error_message'] = $e->getMessage();
            $response['error_code'] = $e->getCode();
        }

        $this->setContent($response);
    }

    public function postChangePass(){
        $response = array(
            "method" => "changepass",
            "success" => false,
            "error_code" => 0,
            "error_message" => ""
        );

        try {
            $data = json_decode(file_get_contents("php://input"));
            // Check required fields
            $requiredParams = array('email', 'currentpass', 'newpass');
            $params = (array) $data;
            foreach ($requiredParams as $param){
                if(empty($params[$param])){
                    throw new \Exception(ucfirst($param) .' is required.');
                }
            }

            if($data->currentpass == $data->newpass){
                throw new \Exception('Current pass and new pass should be different.');
            }

            $authenticator = new BasicAuth(new User());

            if($authenticator->login(array('username'=>$data->email, 'password'=>$data->currentpass),'erdiko_user')){
                $usermodel = new \erdiko\users\models\User();
                $currentUser = $authenticator->current_user();
                $currentUser->save(array('id' => $currentUser->getUserId(), 'password' => $data->newpass));

                $response['success'] = true;
            }
            else{
                throw new \Exception("Username or password are wrong. Please try again.");
            }
            $this->setStatusCode(200);
        } catch (\Exception $e) {
            $response['error_message'] = $e->getMessage();
            $response['error_code'] = $e->getCode();
        }

        $this->setContent($response);
    }
}