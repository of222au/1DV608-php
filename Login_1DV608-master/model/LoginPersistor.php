<?php

namespace model;

class LoginPersistor {

    private static $sessionIsAuthenticated = 'LoginPersistor::IsAuthenthicated';
    private static $sessionUserName = 'LoginPersistor::UserName';
    private static $sessionUserAgent = 'LoginPersistor::UserAgent';

    private static $filename = '../../data/Logins.txt';
    private static $timeInSecondsToRememberLogins = 2592000; // 60*60*24*30 = 2592000 seconds = 30 days;

    /**
     * Retrieves the static $timeInSecondsToRememberLogins
     * @return int
     */
    public function getTimeInSecondsToRememberLogins() {
        return self::$timeInSecondsToRememberLogins;
    }

    /**
     * Retrieves any user login from session
     * @param $userAgent, string
     * @return UserModel|null
     */
    public function getSessionLogin($userAgent) {

        if (isset($_SESSION[self::$sessionIsAuthenticated])) {
            if ($_SESSION[self::$sessionIsAuthenticated] === true && isset($_SESSION[self::$sessionUserName])) {

                //make sure correct user agent (to somewhat prevent session hijacking)
                if (isset($_SESSION[self::$sessionUserAgent]) && $_SESSION[self::$sessionUserAgent] == $userAgent) {

                    $userName = $_SESSION[self::$sessionUserName];
                    $user = new UserModel($userName);
                    return $user;
                }
            }
        }
        return null;
    }

    /**
     * Logins the supplied user (to session)
     * @param UserModel $user
     * @param $userAgent, string
     */
    public function logInUser(UserModel $user, $userAgent) {
        $_SESSION[self::$sessionIsAuthenticated] = true;
        $_SESSION[self::$sessionUserName] = $user->getUserName();
        $_SESSION[self::$sessionUserAgent] = $userAgent;
    }

    /**
     * Logs out the user (clears session from login)
     */
    public function logOutUser() {
        unset($_SESSION[self::$sessionIsAuthenticated]);
        unset($_SESSION[self::$sessionUserName]);
        unset($_SESSION[self::$sessionUserAgent]);
    }



    /**
     * Retrieves stored login if the correct username and matching temp password is supplied
     * @param $username, String
     * @param $tempPassword, String
     * @return UserModel|null
     * @throws \Exception
     */
    public function getSavedLogin($username, $tempPassword, $userAgent) {
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
            if (count($info) == 3 &&
                is_numeric($info[2])) {

                //check if not expired
                $currentTime = time();
                $loginExpires = $info[2];

                //check if correct username and temp password was supplied, and that the saved login hasn't expired
                if (trim($info[0]) == $username &&
                    trim($info[1]) == $tempPassword &&
                    $loginExpires > $currentTime) {

                    //all okay, return a UserModel
                    $user = new UserModel($username);

                    //login the user
                    $this->logInUser($user, $userAgent);

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
     * @param $loginExpiresAt, Number (in seconds)
     * @return bool
     * @throws \Exception
     */
    public function saveLoginFile($userName, $tempPassword) {
        assert(is_string($userName) && is_string($tempPassword));

        $expires = time() + self::$timeInSecondsToRememberLogins;
        $contents = $userName . '::' . $tempPassword . '::' . $expires; //(OBS: same separation characters in getSavedLogin())
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