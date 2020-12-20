<?php
require __DIR__ . '/../classes/_connect.php';
require __DIR__ . '/../classes/_course.php';

echo json_encode(Course::getUpcomingCourses(55));