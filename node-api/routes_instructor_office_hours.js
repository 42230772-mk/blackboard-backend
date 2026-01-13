const express = require("express");
const router = express.Router();
const pool = require("./db");

// ================================
// GET instructor slots
// GET /instructor/office-hours/:instructor_id
// ================================
router.get("/instructor/office-hours/:instructor_id", async (req, res) => {
  const instructor_id = parseInt(req.params.instructor_id, 10);

  if (!instructor_id || instructor_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid instructor_id" });
  }

  try {
    const sql = `
      SELECT
        s.slot_id,
        s.course_id,
        s.instructor_id,
        s.start_time,
        s.end_time,
        s.location,
        s.capacity,
        s.created_at,

        c.code AS course_code,
        c.title AS course_title,

        (
          SELECT COUNT(*)
          FROM office_hour_booking b
          WHERE b.slot_id = s.slot_id AND b.status = 'confirmed'
        ) AS booked_count,

        (s.capacity - (
          SELECT COUNT(*)
          FROM office_hour_booking b
          WHERE b.slot_id = s.slot_id AND b.status = 'confirmed'
        )) AS remaining

      FROM office_hour_slot s
      JOIN course c ON c.course_id = s.course_id
      WHERE s.instructor_id = ?
      ORDER BY s.start_time ASC
    `;

    const [rows] = await pool.query(sql, [instructor_id]);

    return res.json({ success: true, slots: rows });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});

// =======================================
// POST create instructor slot
// POST /instructor/office-hours/create
// body: { instructor_id, course_id, start_time, end_time, location, capacity }
// =======================================
router.post("/instructor/office-hours/create", async (req, res) => {
  const instructor_id = parseInt(req.body.instructor_id, 10);
  const course_id = parseInt(req.body.course_id, 10);
  const start_time = req.body.start_time;
  const end_time = req.body.end_time;
  const location = (req.body.location || "").trim();
  const capacity = parseInt(req.body.capacity, 10);

  if (!instructor_id || instructor_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid instructor_id" });
  }
  if (!course_id || course_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid course_id" });
  }
  if (!start_time || !end_time) {
    return res.status(400).json({ success: false, error: "Missing start_time or end_time" });
  }
  if (!location) {
    return res.status(400).json({ success: false, error: "Location is required" });
  }
  if (!capacity || capacity <= 0) {
    return res.status(400).json({ success: false, error: "Invalid capacity" });
  }

  try {
    // ✅ Ensure course exists
    const [courseRows] = await pool.query(
      "SELECT course_id FROM course WHERE course_id = ? LIMIT 1",
      [course_id]
    );
    if (courseRows.length === 0) {
      return res.status(404).json({ success: false, error: "Course not found" });
    }

    // ✅ Insert slot
    const sql = `
      INSERT INTO office_hour_slot (course_id, instructor_id, start_time, end_time, location, capacity, created_at)
      VALUES (?, ?, ?, ?, ?, ?, NOW())
    `;
    const [result] = await pool.query(sql, [
      course_id,
      instructor_id,
      start_time,
      end_time,
      location,
      capacity,
    ]);

    return res.json({
      success: true,
      message: "Slot created successfully",
      slot_id: result.insertId,
    });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});


module.exports = router;
