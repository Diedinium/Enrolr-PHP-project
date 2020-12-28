<?php

class Course
{
    // Returns array of upcoming courses.
    public static function getUpcomingCourses(int $userId, int $pageIndex, string $searchMinDate, string $searchMaxDate, string $searchTitle): array
    {
        global $connection;

        // Idea here is to return one more result than is dispayed.
        // Then, in the front end, if there are 13 results rather than 12, paginate controls know to allow navigation to next page.
        $limitEnd = (12 * $pageIndex) + 1;
        $limitStart = $limitEnd - 13;
        $upcomingQuery = null;

        // Build where clause
        if (!empty($searchMinDate)) {
            $where = "WHERE DATE(t_courses.date) >= '$searchMinDate'";
        } else {
            $where = "WHERE DATE(t_courses.date) >= CURRENT_DATE";
        }

        if (!empty($searchMaxDate)) {
            $where .= " AND DATE(t_courses.date) <= '$searchMaxDate'";
        }

        // Since title is not validated at _getUpcoming, should be prepared via prepared statement.
        if (!empty($searchTitle)) {
            $where .= " AND t_courses.title LIKE CONCAT('%', ?, '%')";
        }

        $upcomingQueryString = "SELECT t_courses.*, COUNT(t_enroll.iduser) AS 'enrolled', IFNULL(MAX(CASE WHEN t_enroll.iduser = ? then true end), false) as 'isUserEnrolled' FROM t_courses LEFT JOIN t_enroll ON t_enroll.idcourse = t_courses.id $where GROUP BY t_courses.id ORDER BY t_courses.date LIMIT ?, ?";

        // Prepare statement differently depending on if title is populated or not.
        if (!empty($searchTitle)) {
            $upcomingQuery = $connection->prepare($upcomingQueryString);
            $upcomingQuery->bind_param("isii", $userId, $searchTitle, $limitStart, $limitEnd);
        } else {
            $upcomingQuery = $connection->prepare($upcomingQueryString);
            $upcomingQuery->bind_param("iii", $userId, $limitStart, $limitEnd);
        }

        $upcomingQuery->execute();
        $queryResult = $upcomingQuery->get_result();
        $result = [];
        while ($row = $queryResult->fetch_assoc()) {
            array_push($result, $row);
        }
        return $result;
    }

    public static function getUsersEnrolledOnCourse(int $courseId, int $pageIndex): array
    {
        global $connection;

        $limitEnd = (5 * $pageIndex) + 1;
        $limitStart = $limitEnd - 6;

        $usersEnrolledQuery = $connection->prepare("SELECT t_users.id, t_users.firstName, t_users.lastName, t_users.email, t_users.jobTitle, t_enroll.dateCreated FROM t_enroll LEFT JOIN t_users ON t_enroll.iduser = t_users.id WHERE t_enroll.idcourse = ? ORDER BY dateCreated LIMIT $limitStart, $limitEnd");
        $usersEnrolledQuery->bind_param("i", $courseId);

        $success = $usersEnrolledQuery->execute();

        if (!$success) {
            throw new Exception("Fetching users enrolled on course failed.");
        }

        $queryResult = $usersEnrolledQuery->get_result();
        $result = [];
        while ($row = $queryResult->fetch_assoc()) {
            array_push($result, $row);
        }
        return $result;
    }

    // Deletes course by course Id
    public static function deleteCourse(int $courseId)
    {
        global $connection;

        $delete = $connection->query("DELETE FROM t_courses WHERE id = $courseId");

        if (!$delete) {
            throw new Exception("Deleting course failed for an unknown reason.");
        }
    }

    // Admin based user unenrollment, takes course id and user id and removes matching record.
    public static function unenrolUser(int $courseId, int $userId)
    {
        global $connection;

        $unenrolUserQuery = $connection->prepare("DELETE FROM t_enroll WHERE iduser = ? AND idcourse = ?");
        $unenrolUserQuery->bind_param("ii", $userId, $courseId);
        $delete = $unenrolUserQuery->execute();

        if (!$delete) {
            throw new Exception("Deleting enrollment failed for an unknown reason.");
        }
    }

    // Edits course by course Id and by passing all values.
    public static function editCourse(int $courseId, string $title, string $date, float $duration, int $maxAttendees, string $description, string $link, string $location)
    {
        global $connection;

        $editCourse = $connection->prepare("UPDATE t_courses SET title=?, date=?, duration=?, maxAttendees=?, description=?, link=?, location=? WHERE id = ?");
        $editCourse->bind_param("ssdisssi", $title, $date, $duration, $maxAttendees, $description, $link, $location, $courseId);
        $success = $editCourse->execute();

        if (!$success) {
            throw new Exception("Edit course operation failed");
        }
    }

    public static function createCourse(string $title, string $date, float $duration, int $maxAttendees, string $description, string $link, string $location): int
    {
        global $connection;

        $createCourse = $connection->prepare("INSERT INTO t_courses (title, date, duration, maxAttendees, description, link, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $createCourse->bind_param("ssdisss", $title, $date, $duration, $maxAttendees, $description, $link, $location);
        $createCourse->execute();

        if (!empty($createCourse->error)) {
            throw new Exception("Create course operation failed");
        }

        return $createCourse->insert_id;
    }
}
