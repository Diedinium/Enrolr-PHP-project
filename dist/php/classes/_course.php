<?php

class Course {
    public static function getUpcomingCourses(int $userId) : array 
    {
        global $connection;

        $result = [];
        $query = $connection->query("SELECT t_courses.*, COUNT(t_enroll.iduser) AS 'enrolled', IFNULL(MAX(CASE WHEN t_enroll.iduser = $userId then true end), false) as 'isUserEnrolled' FROM t_courses LEFT JOIN t_enroll ON t_enroll.idcourse = t_courses.id WHERE t_courses.date >= CURRENT_DATE GROUP BY t_enroll.idcourse");
        while ($row = $query->fetch_assoc()) {
            array_push($result, $row);
        }
        return $result;
    }
}