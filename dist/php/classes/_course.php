<?php

class Course {
    public static function getUpcomingCourses(int $userId) : array 
    {
        global $connection;

        $result = [];
        $query = $connection->query("SELECT t_courses.*, COUNT(t_enroll.iduser) AS 'enrolled', IFNULL(MAX(CASE WHEN t_enroll.iduser = $userId then true end), false) as 'isUserEnrolled' FROM t_courses LEFT JOIN t_enroll ON t_enroll.idcourse = t_courses.id WHERE t_courses.date > CURRENT_TIMESTAMP GROUP BY t_courses.id ORDER BY t_courses.date");
        while ($row = $query->fetch_assoc()) {
            array_push($result, $row);
        }
        return $result;
    }

    public static function deleteCourse(int $courseId)
    {
        global $connection;

        $delete = $connection->query("DELETE FROM t_courses WHERE id = $courseId");

        if (!$delete) {
            throw new Exception("Deleting course failed for an unknown reason.");
        }
    }

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