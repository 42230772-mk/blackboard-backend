<?php
/**
 * Shared chat permission checker
 * Rules:
 * 1) Student ↔ Instructor if they share a course
 * 2) Student ↔ Admin if same department
 * 3) Instructor ↔ Admin if same department
 * 4) Admin ↔ Admin is NOT allowed
 */

function canChat(PDO $pdo, int $user1_id, int $user2_id): array
{
    // Same user guard
    if ($user1_id === $user2_id) {
        return [
            "allowed" => false,
            "reason" => "Cannot chat with self"
        ];
    }

    // Fetch both users
    $userSql = "
        SELECT user_id, role, department_id
        FROM users
        WHERE user_id IN (?, ?)
    ";
    $stmt = $pdo->prepare($userSql);
    $stmt->execute([$user1_id, $user2_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) !== 2) {
        return [
            "allowed" => false,
            "reason" => "One or both users not found"
        ];
    }

    // Normalize
    $u1 = $users[0];
    $u2 = $users[1];

    // Helper flags
    $roles = [$u1["role"], $u2["role"]];

    $isStudentInstructor =
        in_array("student", $roles) && in_array("instructor", $roles);

    $isStudentAdmin =
        in_array("student", $roles) && in_array("admin", $roles);

    $isInstructorAdmin =
        in_array("instructor", $roles) && in_array("admin", $roles);

    $isAdminAdmin =
        $u1["role"] === "admin" && $u2["role"] === "admin";

    // ❌ Admin ↔ Admin explicitly forbidden
    if ($isAdminAdmin) {
        return [
            "allowed" => false,
            "reason" => "Admin to admin chat is not allowed"
        ];
    }

    // ✅ Rule 1: Student ↔ Instructor (shared course)
    if ($isStudentInstructor) {
        $student_id = ($u1["role"] === "student") ? $u1["user_id"] : $u2["user_id"];
        $instructor_id = ($u1["role"] === "instructor") ? $u1["user_id"] : $u2["user_id"];

        $courseSql = "
            SELECT 1
            FROM course c
            INNER JOIN enrollment e ON e.course_id = c.course_id
            WHERE c.created_by = ?
              AND e.student_id = ?
            LIMIT 1
        ";
        $courseStmt = $pdo->prepare($courseSql);
        $courseStmt->execute([$instructor_id, $student_id]);

        if ($courseStmt->fetch()) {
            return [
                "allowed" => true,
                "reason" => "Student and instructor share a course"
            ];
        }

        return [
            "allowed" => false,
            "reason" => "Student and instructor do not share a course"
        ];
    }

    // ✅ Rule 2 & 3: Student ↔ Admin OR Instructor ↔ Admin (same department)
    if ($isStudentAdmin || $isInstructorAdmin) {
        if ($u1["department_id"] === $u2["department_id"]) {
            return [
                "allowed" => true,
                "reason" => "Same department"
            ];
        }

        return [
            "allowed" => false,
            "reason" => "Different departments"
        ];
    }

    // ❌ Everything else
    return [
        "allowed" => false,
        "reason" => "Chat not permitted for these roles"
    ];
}
