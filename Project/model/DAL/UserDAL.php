<?php

namespace model;

/**
 * MYSQL table structure:

CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) NOT NULL,
`username` varchar(50) NOT NULL,
`password_hash` varchar(255) NOT NULL,
`created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `users`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

 */

class UserDAL {

    private $database;
    private $users = null;

    public function __construct(\mysqli $db) {
        $this->database = $db;
    }

    /**
     * returns the requested user (if found)
     * @param string $userName
     * @return \model\User | null
     * @throws \Exception if unexpected error in database
     */
    public function getUser($userName) {
        if ($userName != null && $userName != '') {
            $users = $this->doGetUsers($userName);
            if ($users != null && count($users) === 1) {
                return $users[0];
            }
        }
        return null;
    }
    public function getUserById($userId) {
        $users = $this->doGetUsers(null, $userId);
        if ($users != null && count($users) === 1) {
            return $users[0];
        }
        return null;
    }
    /**
     * returns all users in the database
     * @return array|null
     * @throws \Exception if unexpected error in database
     */
    public function getUsers() {
        $this->users = $this->doGetUsers();
        return $this->users;
    }
    private function doGetUsers($onlyWithUserName = null, $onlyWithUserId = null) {
        assert($onlyWithUserName == null || is_string($onlyWithUserName));
        assert($onlyWithUserId == null || is_numeric($onlyWithUserId));

        $users = array();

        //select all rows from users table
        $whereClause = "";
        if ($onlyWithUserName != null || $onlyWithUserId != null) {
            if ($onlyWithUserName != null) {
                $whereClause = "WHERE username = ?";
            }
            else if ($onlyWithUserId != null) {
                $whereClause = "WHERE id = ?";
            }
        }
        $stmt = $this->database->prepare("SELECT * FROM " . \Settings::DATABASE_TABLE_USERS . "
                                          " . $whereClause);
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        if ($onlyWithUserName != null) {
            $stmt->bind_param('s', $onlyWithUserName);
        }
        else if ($onlyWithUserId != null) {
            $stmt->bind_param('i', $onlyWithUserId);
        }

        $stmt->execute();
        $stmt->bind_result($id, $username, $passwordHash, $created_at);

        while ($stmt->fetch()) {
            $user = new User($id, $username, $passwordHash, $created_at);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param RegisterCredentials $credentials
     * @return bool
     * @throws \Exception if unexpected error in database
     */
    public function saveNewUser(RegisterCredentials $credentials) {

        //prepare the database insert
        $stmt = $this->database->prepare("INSERT INTO " . \Settings::DATABASE_TABLE_USERS . "
                                            (username, password_hash, created_at)
                                            VALUES (?, ?, NOW())");
        if ($stmt === FALSE) {
            throw new \Exception($this->database->error);
        }

        //set values
        $userName = $credentials->getUserName();
        $passwordHash = $this->createHashedPassword($credentials->getPassword());
        $stmt->bind_param('ss', $userName, $passwordHash);

        //execute the statement
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