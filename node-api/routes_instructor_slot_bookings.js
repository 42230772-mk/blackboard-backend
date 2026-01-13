const express = require("express");
const router = express.Router();
const pool = require("./db");

// ==================================================
// GET bookings for a slot (instructor)
// GET /instructor/office-hours/:instructor_id/slot/:slot_id/bookings
// ==================================================
router.get("/instructor/office-hours/:instructor_id/slot/:slot_id/bookings", async (req, res) => {
  const instructor_id = parseInt(req.params.instructor_id, 10);
  const slot_id = parseInt(req.params.slot_id, 10);

  if (!instructor_id || instructor_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid instructor_id" });
  }
  if (!slot_id || slot_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid slot_id" });
  }

  try {
    // ✅ Ensure slot belongs to this instructor
    const [slotRows] = await pool.query(
      "SELECT slot_id FROM office_hour_slot WHERE slot_id = ? AND instructor_id = ? LIMIT 1",
      [slot_id, instructor_id]
    );

    if (slotRows.length === 0) {
      return res.status(404).json({
        success: false,
        error: "Slot not found or not owned by this instructor",
      });
    }

    // ✅ Fetch bookings + student info (confirmed + cancelled etc.)
    const sql = `
      SELECT
        b.booking_id,
        b.student_id,
        b.slot_id,
        b.status,
        b.booked_at,

        u.first_name AS student_first_name,
        u.last_name  AS student_last_name,
        u.email      AS student_email
      FROM office_hour_booking b
      JOIN users u ON u.user_id = b.student_id
      WHERE b.slot_id = ?
      ORDER BY b.booked_at DESC
    `;

    const [rows] = await pool.query(sql, [slot_id]);

    return res.json({
      success: true,
      slot_id,
      bookings: rows,
    });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});

// ==================================================
// POST cancel a booking (instructor)
// POST /instructor/bookings/:booking_id/cancel
// body: { instructor_id }
// ==================================================
router.post("/instructor/bookings/:booking_id/cancel", async (req, res) => {
  const booking_id = parseInt(req.params.booking_id, 10);
  const instructor_id = parseInt(req.body?.instructor_id, 10);

  if (!booking_id || booking_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid booking_id" });
  }
  if (!instructor_id || instructor_id <= 0) {
    return res.status(400).json({ success: false, error: "Invalid instructor_id" });
  }

  try {
    // ✅ Make sure booking exists + belongs to this instructor (via slot)
    const [rows] = await pool.query(
      `
      SELECT
        b.booking_id,
        b.status,
        b.student_id,
        s.instructor_id,
        s.slot_id
      FROM office_hour_booking b
      JOIN office_hour_slot s ON s.slot_id = b.slot_id
      WHERE b.booking_id = ?
      LIMIT 1
      `,
      [booking_id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ success: false, error: "Booking not found" });
    }

    const booking = rows[0];

    if (parseInt(booking.instructor_id) !== instructor_id) {
      return res.status(403).json({ success: false, error: "Not your slot / booking" });
    }

    if (booking.status !== "confirmed") {
      return res.status(409).json({
        success: false,
        error: `Cannot cancel booking with status '${booking.status}'`,
      });
    }

    // ✅ Cancel it
    await pool.query(
      "UPDATE office_hour_booking SET status = 'cancelled' WHERE booking_id = ?",
      [booking_id]
    );

    return res.json({ success: true, message: "Booking cancelled by instructor" });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});


module.exports = router;
