<?php

class Course {
    // Returns array of upcoming courses.
    public static function getUpcomingCourses(int $userId, int $pageIndex) : array 
    {
        global $connection;

        // Idea here is to return one more result than is dispayed.
        // Then, in the front end, if there are 13 results rather than 12, paginate controls know to allow navigation to next page.
        $limitEnd = (12 * $pageIndex) + 1;
        $limitStart = $limitEnd - 13;

        $result = [];
        $query = $connection->query("SELECT t_courses.*, COUNT(t_enroll.iduser) AS 'enrolled', IFNULL(MAX(CASE WHEN t_enroll.iduser = $userId then true end), false) as 'isUserEnrolled' FROM t_courses LEFT JOIN t_enroll ON t_enroll.idcourse = t_courses.id WHERE t_courses.date >= CURRENT_DATE GROUP BY t_courses.id ORDER BY t_courses.date LIMIT $limitStart, $limitEnd");
        while ($row = $query->fetch_assoc()) {
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

    // Edits course by course Id and by passing all values.
    public static function editCourse(int $courseId, string $title, string $date, float $duration, int $maxAttendees, string $description, string $link, string $location) {
        global $connection;

        $editCourse = $connection->prepare("UPDATE t_courses SET title=?, date=?, duration=?, maxAttendees=?, description=?, link=?, location=? WHERE id = ?");
        $editCourse->bind_param("ssdisssi", $title, $date, $duration, $maxAttendees, $description, $link, $location, $courseId);
        $success = $editCourse->execute();

        if (!$success) {
            throw new Exception("Edit course operation failed");
        }
    }

    public static function createCourse(string $title, string $date, float $duration, int $maxAttendees, string $description, string $link, string $location): int {
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