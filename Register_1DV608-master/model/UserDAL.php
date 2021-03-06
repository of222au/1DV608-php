<?php

namespace model;

/**
 * CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password_hash` varchar(255) NOT NULL
    ) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1;
 */

class UserDAL {

    private static $table_users = "users";

    private $database;
    private $users = null;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    /**
     * returns all users in the database
     * @return array|null
     * @throws \Exception if unexpected error in database
     */
    public function getUsers() {

        if ($this->users == null) {

            $this->users = [];

            //select all rows from users table
            $stmt = $this->database->prepare("SELECT * FROM " . self::$table_users);
            if ($stmt === FALSE) {
                throw new \Exception($this->database->error);
            }

            $stmt->execute();
            $stmt->bind_result($id, $username, $passwordHash);

            while ($stmt->fetch()) {
                $user = new User($username, $passwordHash);
                $this->users[] = $user;
            }
        }

        return $this->users;
    }

    /**
     * returns the requested user (if found)
     * @param string $userName
     * @return \model\User | null
     * @throws \Exception if unexpected error in database
     */
    public function getUser($userName) {
        $users = $this->getUsers();
        foreach($users as $user) {
            if ($user->getUserName() == $userName) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @param RegisterCredentials $credentials
     * @return bool
     * @throws \Exception if unexpected error in database
     */
    public function registerNewUser(RegisterCredentials $credentials) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . self::$table_users . "
                                            (username, password_hash)
                                            VALUES (?, ?)");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $userName = $credentials->getUserName();
        $passwordHash = $this->createHashedPassword($credentials->getPassword());
        $stmt->bind_param('ss', $userName, $passwordHash);

        //execute the insert
        $result = $stmt->execute();
        if ($result === FALSE) {
            throw new \Exception($this->database->error);
        }

        return true;
    }

    /**
     * Checks for an existing user with the supplied user name
     * @param string $userName
     * @return bool
     * @throws \Exception if unexpected error in database
     */
    public function checkIfUserNameAlreadyExists($userName) {
        foreach ($this->getUsers() as $user) {
            if ($user->getUsername() == $userName) {
                return true;
            }
        }

        return false;
    }

    private function createHashedPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

}