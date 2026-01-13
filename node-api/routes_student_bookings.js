const express = require("express");
const router = express.Router();
const pool = require("./db");

/**
 * GET /student/bookings/:studentId
 * Lists the student's office hour bookings (confirmed + cancelled)
 */
router.get("/student/bookings/:studentId", async (req, res) => {
  const studentId = parseInt(req.params.studentId, 10);

  if (!studentId || studentId <= 0) {
    return res.status(400).json({ success: false, error: "Invalid studentId" });
  }

  try {
    const sql = `
      SELECT
        b.booking_id,
        b.student_id,
        b.slot_id,
        b.status,
        b.booked_at,

        s.start_time,
        s.end_time,
        s.location,
        s.capacity,

        u.user_id AS instructor_id,
        u.first_name AS instructor_first_name,
        u.last_name AS instructor_last_name,

        c.course_id,
        c.code AS course_code,
        c.title AS course_title
      FROM office_hour_booking b
      INNER JOIN office_hour_slot s ON s.slot_id = b.slot_id
      LEFT JOIN users u ON u.user_id = s.instructor_id
      LEFT JOIN course c ON c.course_id = s.course_id
      WHERE b.student_id = ?
      ORDER BY b.booked_at DESC
    `;

    const [rows] = await pool.query(sql, [studentId]);

    return res.json({
      success: true,
      bookings: rows,
    });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});

/**
 * POST /student/bookings/:bookingId/cancel
 * Cancels a booking by setting status = 'cancelled'
 * Requires studentId in body to prevent cancelling someone else’s booking.
 */
router.post("/student/bookings/:bookingId/cancel", async (req, res) => {
  const bookingId = parseInt(req.params.bookingId, 10);
  const studentId = parseInt(req.body?.student_id, 10);

  if (!bookingId || bookingId <= 0) {
    return res.status(400).json({ success: false, error: "Invalid bookingId" });
  }
  if (!studentId || studentId <= 0) {
    return res.status(400).json({ success: false, error: "Missing/invalid student_id" });
  }

  try {
    // 1) verify booking belongs to this student
    const [found] = await pool.query(
      "SELECT booking_id, status FROM office_hour_booking WHERE booking_id = ? AND student_id = ? LIMIT 1",
      [bookingId, studentId]
    );

    if (found.length === 0) {
      return res.status(404).json({ success: false, error: "Booking not found for this student" });
    }

    // 2) prevent cancelling twice
    if (found[0].status === "cancelled") {
      return res.status(409).json({ success: false, error: "Booking already cancelled" });
    }

    // 3) update status
    await pool.query(
      "UPDATE office_hour_booking SET status = 'cancelled' WHERE booking_id = ?",
      [bookingId]
    );

    return res.json({ success: true, message: "Booking cancelled successfully" });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});

module.exports = router;
