const express = require("express");
const router = express.Router();
const pool = require("./db");

// GET /student/petitions/:student_id
router.get("/student/petitions/:student_id", async (req, res) => {
  const student_id = parseInt(req.params.student_id, 10);

  if (!student_id || student_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid student_id" });
  }

  try {
    const sql = `
      SELECT
        p.petition_id,
        p.exam_id,
        p.student_id,
        p.reason,
        p.status,
        p.created_at,
        p.admin_comment,

        e.title AS exam_title,
        e.type AS exam_type,
        e.start_time,
        e.end_time,

        c.code AS course_code,
        c.title AS course_title
      FROM exam_petition p
      JOIN exam e ON e.exam_id = p.exam_id
      JOIN course c ON c.course_id = e.course_id
      WHERE p.student_id = ?
      ORDER BY p.created_at DESC
    `;

    const [rows] = await pool.query(sql, [student_id]);

    return res.json({ success: true, petitions: rows });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});

module.exports = router;
