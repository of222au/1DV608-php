<?php

namespace model;

class LoginPersistor {

    private static $sessionIsAuthenticated = 'UserModel::IsAuthenthicated';
    private static $sessionUserName = 'UserModel::UserName';

    private static $filename = '../../data/Logins.txt';

    /**
     * Retrieves any user login from session
     * @return UserModel|null
     */
    public function getSessionLogin() {
        if (isset($_SESSION[self::$sessionIsAuthenticated])) {
            if ($_SESSION[self::$sessionIsAuthenticated] === true && isset($_SESSION[self::$sessionUserName])) {
                $userName = $_SESSION[self::$sessionUserName];
                $user = new UserModel($userName);
                return $user;
            }
        }
        return null;
    }

    /**
     * Logins the supplied user (to session)
     * @param UserModel $user
     */
    public function logInUser(UserModel $user) {
        $_SESSION[self::$sessionUserName] = $user->getUserName();
        $_SESSION[self::$sessionIsAuthenticated] = true;
    }

    /**
     * Logs out the user (clears session from login)
     */
    public function logOutUser() {
        unset($_SESSION[self::$sessionUserName]);
        unset($_SESSION[self::$sessionIsAuthenticated]);
    }



    /**
     * Retrieves stored login if the correct username and matching temp password is supplied
     * @param $username, String
     * @param $tempPassword, String
     * @return UserModel|null
     * @throws \Exception
     */
    public function getSavedLogin($username, $tempPassword) {
        assert(is_string($username) && is_string($tempPassword));

        //read the file
        try {
            $data = file_get_contents(self::$filename);
        }
        catch (\Exception $e) {
            throw new \Exception("Could not read the file");
        }

        if ($data !== false) {

            //create array separated by :: (OBS: same separation characters in saveLoginFile())
            $info = explode("::", $data);

            //make sure correct amount of data in the file
            if (count($info) == 2) {

                //check if correct username and temp password was supplied
                if (trim($info[0]) == $username &&
                    trim($info[1]) == $tempPassword) {

                    //all okay, return a UserModel
                    $user = new UserModel($username);

                    //login the user
                    $this->logInUser($user);

                    return $user;
                }
            }
            else {
                throw new \Exception("Incorrect data in the file");
            }
        }

        return null;
    }

    /**
     * Saves the login storage file with supplied credentials
     * @param $userName, String
     * @param $tempPassword, String
     * @return bool
     * @throws \Exception
     */
    public function saveLoginFile($userName, $tempPassword) {
        assert(is_string($userName) && is_string($tempPassword));

        $contents = $userName . '::' . $tempPassword; //(OBS: same separation characters in getSavedLogin())
        return $this->writeToLoginFile($contents);
    }

    /**
     * Clears the login storage file
     * @return bool
     * @throws \Exception
     */
    public function clearLoginFile() {
        return $this->writeToLoginFile('');
    }

    /**
     * @param $contents
     * @return bool
     * @throws \Exception
     */
    private function writeToLoginFile($contents) {

        $result = file_put_contents(self::$filename, $contents);
        if ($result !== false) {
            return true;
        }
        return false;
    }

}