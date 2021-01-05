<?php

class Account
{
    // Member variables
    private int $id;
    private string $email;
    private string $password;
    private bool $isAuthenticated;
    private string $firstName;
    private string $lastName;
    private string $jobRole;
    private string $created;
    private bool $isAdmin;

    public function __construct()
    {
        $this->id = 0;
        $this->email = "";
        $this->password = "";
        $this->isAuthenticated = false;
        $this->firstName = "";
        $this->lastName = "";
        $this->jobRole = "";
        $this->created = "";
        $this->isAdmin = false;
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

    public function getJobRole()
    {
        return $this->jobRole;
    }

    // Combine first name and last name to get full name
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

    // Add an account to the DB, password passed should be plain string
    public function addAccount(string $email, string $password, string $firstName, string $lastName, string $jobTitle, bool $isAdmin = false): int
    {
        global $connection;

        $email = trim($email);
        $password = trim($password);

        // If function returns value, email already exists in DB, throw error.
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

        return $addAccountQuery->insert_id;
    }

    // Delete currently logged in account, or delete account by manually setting id on new instance of account class (admin only).
    public function deleteAccount()
    {
        global $connection;

        // Throw error if deletion is attempted against unitialised blank account class
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

        // Only attempt this login if the session is active
        if (session_status() == PHP_SESSION_ACTIVE) {
            // Get the session Id
            $sessionId = session_id();
            $tokenQuery = $connection->prepare("SELECT iduser FROM t_persist WHERE token = ?");

            $tokenQuery->bind_param("s", $sessionId);
            $tokenQuery->execute();

            $tokenQuery->bind_result($userID);
            $tokenQuery->store_result();

            // If result is more than 0, this means a persisted session has been found.
            if ($tokenQuery->num_rows > 0) {
                $tokenQuery->fetch();

                // Fetch user based on associated Id of user from the persist table.
                $userQueryResults = $connection->query("SELECT id, firstName, lastName, email, password, jobTitle, isAdmin, created FROM t_users WHERE id = {$userID}");
                $row = $userQueryResults->fetch_assoc();

                $this->id = $row['id'];
                $this->firstName = $row['firstName'];
                $this->lastName = $row['lastName'];
                $this->email = $row['email'];
                $this->password = $row['password'];
                $this->jobRole = $row['jobTitle'];
                $this->isAdmin = $row['isAdmin'];
                $this->created = $row['created'];
                $this->isAuthenticated = true;
                return;
            }

            // If passed values are not empty, attempt to login using username/password instead.
            if (!empty($email) && !empty($password)) {
                $userQuery = $connection->prepare("SELECT id, firstName, lastName, email, password, jobTitle, isAdmin, created FROM t_users WHERE email = ?");

                $userQuery->bind_param("s", $email);
                $userQuery->execute();

                $userQuery->bind_result($resultId, $resultFirstName, $resultLastName, $resultEmail, $resultPassword, $resultJobTitle, $resultIsAdmin, $resultCreated);
                $userQuery->store_result();

                // If result is found, this means user with provided email was found.
                if ($userQuery->num_rows > 0) {
                    $row = $userQuery->fetch();

                    // Verify that password matches
                    if (password_verify($password, $resultPassword)) {
                        $this->id = $resultId;
                        $this->firstName = $resultFirstName;
                        $this->lastName = $resultLastName;
                        $this->email = $resultEmail;
                        $this->password = $resultPassword;
                        $this->jobRole = $resultJobTitle;
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

    // Returns all staff.
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

    // Takes array of staff and only returns adminstrators from these
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

    // Takes an array of staff and only returns staff who are not admins from these
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

    // When re-creating or creating default admin account, ensure that old admin account is deleted.
    public static function ensureAdminAccountDeleted() {
        global $connection;

        $delete = $connection->query("DELETE FROM t_users WHERE email = 'Admin.McAdmin@enrolr.co.uk'");

        if (!$delete) {
            throw new Exception("Deleting exisitng admin account failed for some unknown reason.");
        }
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

    // Update user details, but from non-admin pages such as settings page.
    public function updateDetails(string $firstName, string $lastName, string $jobRole)
    {
        global $connection;

        $updateNames = $connection->prepare("UPDATE t_users SET firstName=?, lastName=?, jobTitle=? WHERE id = {$this->id}");
        $updateNames->bind_param("sss", $firstName, $lastName, $jobRole);
        $success = $updateNames->execute();

        if (!$success) {
            throw new Exception("Updating details failed for an unknown reason.");
        }
    }

    // Used to update user details on user management page
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

    // Update user password (non-admin, intended to be used by users when they self change their own password)
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

    // Delete all enrollments for current user
    public function deleteAllEnrollments()
    {
        global $connection;

        if (is_null($this->id)) {
            throw new Exception("Cannot delete, no user id.");
        }

        $success = $connection->query("DELETE t_enroll FROM t_enroll LEFT JOIN t_courses ON t_enroll.idcourse = t_courses.id WHERE t_enroll.iduser = {$this->id} AND DATE(t_courses.date) >= CURRENT_DATE()");

        if (!$success) {
            throw new Exception("Deleting enrollments failed.");
        }
    }

    // Enroll current user on course.
    public function enroll(int $courseId)
    {
        global $connection;

        $canUserEnroll = $this->canUserEnrol($courseId);

        if (!$canUserEnroll) {
            throw new Exception("Could not enrol on this course, either you are already enrolled or it is fully booked.");
        }

        $enrollOnCourseQuery = $connection->prepare("INSERT INTO t_enroll (iduser, idcourse) VALUES ({$this->id}, ?)");
        $enrollOnCourseQuery->bind_param("i", $courseId);
        $success = $enrollOnCourseQuery->execute();

        if (!$success) {
            throw new Exception("Enrolling on course failed for unknown reason, please try again.");
        }
    }

    // Unenroll current user from course
    public function unenroll(int $courseId)
    {
        global $connection;

        $unenrolQuery = $connection->prepare("DELETE FROM t_enroll WHERE iduser = {$this->id} AND idcourse = ?");
        $unenrolQuery->bind_param("i", $courseId);
        $delete = $unenrolQuery->execute();

        if (!$delete) {
            throw new Exception("Unenrolling failed, an unknown error occured.");
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

    // Checks if user can enrol
    private function canUserEnrol(int $courseId): bool
    {
        global $connection;

        // Return false when user is admin
        if ($this->getIsAdmin()) {
            return false;
        }

        $checkCourseQuery = $connection->prepare("SELECT t_courses.maxAttendees, COUNT(t_enroll.iduser) AS 'enrolled', IFNULL(MAX(CASE WHEN t_enroll.iduser = {$this->id} then true end), false) as 'isUserEnrolled' FROM t_courses LEFT JOIN t_enroll ON t_enroll.idcourse = t_courses.id WHERE t_courses.id = ? GROUP BY t_courses.id");
        $checkCourseQuery->bind_param("i", $courseId);
        $success = $checkCourseQuery->execute();

        if (!$success) {
            throw new Exception("Something went wrong while checking enrollment status");
        }

        $checkCourseQuery->bind_result($maxAttendees, $currentAttendees, $isUserEnrolled);
        $checkCourseQuery->store_result();
        $checkCourseQuery->fetch();

        // Return false if user is already enrolled.
        if ($isUserEnrolled) {
            return false;
        }

        // Return true if space is available, false if not.
        if ($maxAttendees > $currentAttendees) {
            return true;
        }
        else {
            return false;
        }
    }
}
