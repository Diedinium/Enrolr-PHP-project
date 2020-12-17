<?php

class Account
{

    private int $id;
    private $email;
    private $password;
    private bool $isAuthenticated;
    private $firstName;
    private $lastName;
    private $created;
    private $isAdmin;

    public function __construct()
    {
        $this->id = 0;
        $this->email = null;
        $this->password = null;
        $this->isAuthenticated = false;
        $this->firstName = null;
        $this->lastName = null;
        $this->created = null;
        $this->isAdmin = null;
    }

    public function __destruct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getAuthenticated(): bool
    {
        return $this->isAuthenticated;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getFullName()
    {
        return $this->firstName . " " . $this->lastName;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    public function addAccount(string $email, string $password, string $firstName, string $lastName, string $jobTitle, bool $isAdmin = false)
    {
        global $connection;

        if (session_status() == PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            $email = trim($email);
            $password = trim($password);

            if (!is_null($this->getIdFromName($email))) {
                throw new Exception("An account with this email already exists");
            }

            $addAccountQuery = $connection->prepare("INSERT INTO t_users (firstName, lastName, email, password, jobTitle, isAdmin) VALUES (?, ?, ?, ?, ?, ?)");
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $addAccountQuery->bind_param("ssssss", $firstName, $lastName, $email, $hash, $jobTitle, $isAdmin);
            $addAccountQuery->execute();

            if (!empty($addAccountQuery->error)) {
                throw new Exception("Failed to add user.");
            }
        }
    }

    // Delete currently logged in account, or delete account by manually setting id on new instance of account class (admin only).
    public function deleteAccount()
    {
        global $connection;

        if (is_null($this->id)) {
            throw new Exception("Cannot delete, no user id.");
        }

        $success = $connection->query("DELETE FROM t_users WHERE id = $this->id");

        if (!$success) {
            throw new Exception("Deleting account failed.");
        }
    }

    // Login using session id as token, or on post, via email/password. Creates new session in t_persist if logging in for first time.
    public function login(string $email = '', string $password = '')
    {
        global $connection;

        if (session_status() == PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            $tokenQuery = $connection->prepare("SELECT iduser FROM t_persist WHERE token = ?");

            $tokenQuery->bind_param("s", $sessionId);
            $tokenQuery->execute();

            $tokenQuery->bind_result($userID);
            $tokenQuery->store_result();

            if ($tokenQuery->num_rows > 0) {
                $tokenQuery->fetch();

                $userQueryResults = $connection->query("SELECT id, firstName, lastName, email, password, jobTitle, isAdmin, created FROM t_users WHERE id = {$userID}");
                $row = $userQueryResults->fetch_assoc();

                $this->id = $row['id'];
                $this->firstName = $row['firstName'];
                $this->lastName = $row['lastName'];
                $this->email = $row['email'];
                $this->password = $row['password'];
                $this->jobTitle = $row['jobTitle'];
                $this->isAdmin = $row['isAdmin'];
                $this->created = $row['created'];
                $this->isAuthenticated = true;
                return;
            }

            if (!empty($email) && !empty($password)) {
                $userQuery = $connection->prepare("SELECT id, firstName, lastName, email, password, jobTitle, isAdmin, created FROM t_users WHERE email = ?");

                $userQuery->bind_param("s", $email);
                $userQuery->execute();

                $userQuery->bind_result($resultId, $resultFirstName, $resultLastName, $resultEmail, $resultPassword, $resultJobTitle, $resultIsAdmin, $resultCreated);
                $userQuery->store_result();

                if ($userQuery->num_rows > 0) {
                    $row = $userQuery->fetch();

                    if (password_verify($password, $resultPassword)) {
                        $this->id = $resultId;
                        $this->firstName = $resultFirstName;
                        $this->lastName = $resultLastName;
                        $this->email = $resultEmail;
                        $this->password = $resultPassword;
                        $this->jobTitle = $resultJobTitle;
                        $this->isAdmin = $resultIsAdmin;
                        $this->created = $resultCreated;
                        $this->isAuthenticated = true;

                        $connection->query("INSERT INTO t_persist (iduser, token) VALUES ($this->id, '$sessionId')");
                        return;
                    } else {
                        throw new Exception('Password was incorrect');
                    }
                } else {
                    throw new Exception('User with this email not found.');
                }
            }
        }
    }

    public static function getAllStaff(): array
    {
        global $connection;

        $result = [];
        $query = $connection->query("SELECT id, firstName, lastName, email, password, jobTitle, isAdmin, created FROM t_users");
        while ($row = $query->fetch_assoc()) {
            array_push($result, $row);
        }
        return $result;
    }

    public static function fitlerAdministrators(array $arrayToFilter): array
    {
        $result = [];
        foreach ($arrayToFilter as $userAccount) {
            if ($userAccount['isAdmin']) {
                array_push($result, $userAccount);
            }
        }
        
        return $result;
    }

    public static function filterStaff(array $arrayToFilter): array
    {
        $result = [];
        foreach ($arrayToFilter as $userAccount) {
            if (!$userAccount['isAdmin']) {
                array_push($result, $userAccount);
            }
        }
        
        return $result;
    }

    // Logout by removing session id from t_persist
    public function logout()
    {
        global $connection;

        if (session_status() == PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            $delete = $connection->query("DELETE FROM t_persist WHERE token = '$sessionId'");

            if (!$delete) {
                throw new Exception("Logout failed");
            }

            return;
        }
        return;
    }

    // Update users names
    public function updateNames(string $firstName, string $lastName)
    {
        global $connection;

        $updateNames = $connection->prepare("UPDATE t_users SET firstName=?, lastName=? WHERE id = {$this->id}");
        $updateNames->bind_param("ss", $firstName, $lastName);
        $success = $updateNames->execute();

        if (!$success) {
            throw new Exception("Updating names failed.");
        }
    }

    public function editUser(string $firstName, string $lastName, string $email, string $jobTitle) 
    {
        global $connection;

        $editUser = $connection->prepare("UPDATE t_users SET firstName=?, lastName=?, email=?, jobTitle=? WHERE id = {$this->id}");
        $editUser->bind_param("ssss", $firstName, $lastName, $email, $jobTitle);
        $success = $editUser->execute();

        if (!$success) {
            throw new Exception("Edit user operation failed; most likely because you tried to set a users email to an already existing email");
        }
    }

    // Update user password
    public function changePassword($newPassword)
    {
        global $connection;

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updatePasswordQuery = $connection->prepare("UPDATE t_users SET password=? WHERE id = {$this->id}");
        $updatePasswordQuery->bind_param("s", $passwordHash);
        $success = $updatePasswordQuery->execute();

        if (!$success) {
            throw new Exception("Updating password failed.");
        }
    }

    // Delete all enrollments
    public function deleteAllEnrollments()
    {
        global $connection;

        if (is_null($this->id)) {
            throw new Exception("Cannot delete, no user id.");
        }

        $success = $connection->query("DELETE FROM t_enroll WHERE iduser = $this->id");

        if (!$success) {
            throw new Exception("Deleting enrollments failed.");
        }
    }

    // Attempts to find existing accounts with email, used to validate that email is not already signed up.
    public function getIdFromName(string $email)
    {
        global $connection;
        $userQuery = $connection->prepare("SELECT id FROM t_users WHERE email = ?");

        $userQuery->bind_param("s", $email);
        $userQuery->execute();

        $userQuery->bind_result($userID);
        $userQuery->store_result();
        $userQuery->fetch();

        return $userQuery->num_rows() > 0 ? $userID : null;
    }
}
