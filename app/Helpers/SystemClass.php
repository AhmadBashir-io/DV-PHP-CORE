<?php

use App\Helpers\Helper;
use App\Helpers\DataStore as DS;
use App\Helpers\DevlessHelper as DVH;
use App\Http\Controllers\ServiceController as service;

/**
 * Created by Devless.
 * User: eddymens
 * Date Created: 23rd of September 2016 09:01:20 AM.
 *
 * @Service: event
 * @Version: 1.0
 */

//Action method for serviceName
class devless
{
    public $serviceName = 'dvauth';
    private $auth;

    public function __construct()
    {
        $this->auth = new DVH();
    }

    /**
     * @ACL public
     **/
    public function hello()
    {
        return 'Hello World';
    }
    /**
     * method for handling user signup.
     *
     * @ACL public
     */
    public function signUp(
        $email = null,
        $password = null,
        $username = null,
        $phone_number = null,
        $first_name = null,
        $last_name = null,
        $remember_token = null,
        $role = null
    ) {
        $payload = get_defined_vars();

        $payload = self::getSetParams($payload);

        $auth = $this->auth;

        $output = $auth->signup($payload);

        return $output;
    }

    /**
     * method for handling user login.
     *
     * @ACL public
     */
    public function login($username = null, $email = null, $phone_number = null, $password = null)
    {
        $payload = get_defined_vars();

        $payload = self::getSetParams($payload);

        $auth = $this->auth;

        $output = $auth->login($payload);

        return $output;
    }

    /**
     * get user profile.
     *
     * @ACL public
     */
    public function profile()
    {
        $auth = $this->auth;

        $profile = $auth->get_profile();

        return $profile;
    }

    /**
     * logout.
     *
     * @ACL public
     */
    public function logout()
    {
        $auth = $this->auth;
        $logState = $auth->logOut();

        return $logState;
    }

    /**
     * method for handling user login.
     *
     * @ACL public
     */
    public function updateProfile(
        $email = null,
        $password = null,
        $username = null,
        $phone_number = null,
        $first_name = null,
        $last_name = null,
        $remember_token = null
    ) {
        $payload = get_defined_vars();

        foreach ($payload as $key => $value) {
            if ($value == null) {
                unset($payload[$key]);
            }
        }
        $auth = $this->auth;

        $output = $auth->update_profile($payload);

        return $output;
    }

    private static function getSetParams($payload)
    {
        foreach ($payload as $key => $value) {
            if ($value == null) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }

    /**
     * @param $serviceName
     * @param $table
     * @param $fields
     *
     * @return mixed
     * @ACL private
     */
    public function addData($serviceName, $table, $data)
    {
        $service = new service();
        $output = DS::service($serviceName, $table, $service)->addData([$data]);

        return $output;
    }

    /**
     * @param $serviceName
     * @param $table
     *
     * @return mixed
     * @ACL private
     */
    public function queryData($serviceName, $table, $whereKey = null, $whereValue = null)
    {
        $service = new service();

        $queryBuilder = ($whereKey && $whereValue) ? DS::service($serviceName, $table, $service)
            ->where($whereKey, $whereValue) : DS::service($serviceName, $table, $service);

        $output = $queryBuilder->related('*')->queryData();

        return $output['payload']['results'];
    }

    /**
     * @param $serviceName
     * @param $table
     * @param $id
     *
     * @return mixed
     * @ACL private
     */
    public function updateData($serviceName, $table, $whereKey, $whereValue, $data)
    {
        $service = new service();
        $output = DS::service($serviceName, $table, $service)->where($whereKey, $whereValue)->update($data);

        return $output;
    }

    /**
     * @param $serviceName
     * @param $table
     * @param $id
     *
     * @return mixed
     * @ACL private
     */
    public function deleteData($serviceName, $table, $id)
    {
        $service = new service();
        $output = DS::service($serviceName, $table, $service)->where('id', $id)->delete();
        return $output;
    }

    
    /**
     * Delete a users profile
     * @return bool
     * @ACL private
     */
    public function deleteUserProfile($id)
    {
        return (\DB::table('users')->where('id', $id)->delete())?true:false; 
    }


    /**
     * Update a users profile
     * @param $email
     * @param $password
     * @param $username
     * @param $phone_number
     * @param $first_name
     * @param $last_name
     * @param $remember_token
     * @param $role
     * @return bool
     * @ACL private
     */
    public function updateUserProfile(
        $id,
        $email = null,
        $password = null,
        $username = null,
        $phone_number = null,
        $first_name = null,
        $last_name = null,
        $remember_token = null,
        $status = null
    ) {
        $profileUpdate =array_filter(
            get_defined_vars(), function ($value) {
                return $value !== ''; 
            }
        );
        unset($profileUpdate['id']);
        
        return (DB::table('users')->where('id', $id)->update($profileUpdate))?true:false;
    }

    /**
     * @param $serviceName
     * @param $table
     * @param $id
     *
     * @return array
     * @ACL private
     */
    public function getUserProfile($input)
    {
        (empty($input)) ? Helper::interrupt(628) : false;
        if (is_array($input)) {
            $id = $input['user_id'];
        } else {
            $id = $input;
        }
        $profile = DB::table('users')->where('id', $id)->first();
        if ($profile) {
            return (array) $profile;
        }

        return [];
    }
    /**
     * Get all users within the system 
     * @return array
     * @ACL private
     */
    public function getAllUsers()
    {
        return DB::table('users')->select(
            [

                "id", "username", "email", "first_name", "last_name", "status", "phone_number",
            ])->get();
    }
}
