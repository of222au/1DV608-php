<?php

namespace model;

class TempCredentialsDAL {


    /**
     * @param $userName
     * @return TempCredentials|null
     */
    public function loadLogin($userName) {

        $fileName = $this->getFilename($userName);
        if (file_exists($fileName)) {
            $fileContents = file_get_contents($fileName);
            if ($fileContents !== FALSE) {

                return unserialize($fileContents);
            }
        }

        return null;
    }

    /**
     * @param LoggedInUser $user
     * @param TempCredentials $tempCredentials
     * @return bool
     */
    public function saveLogin(LoggedInUser $user, TempCredentials $tempCredentials) {

        $result = file_put_contents($this::getFilename($user->getUserName()), serialize($tempCredentials));
        if ($result !== false) {
            return true;
        }
        return false;
    }


    private function getFilename($userName) {
        //TODO: replace the addslashes with something that makes username safe for use in filesystem
        return \Settings::DATA_PATH . addslashes($userName);
    }


}