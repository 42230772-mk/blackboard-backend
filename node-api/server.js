const express = require("express");
const cors = require("cors");
require("dotenv").config();

const pool = require("./db");

const studentPetitionsRoutes = require("./routes_student_petitions");
const studentBookingsRoutes = require("./routes_student_bookings");//1
const instructorOfficeHoursRoutes = require("./routes_instructor_office_hours");
const instructorSlotBookingsRoutes = require("./routes_instructor_slot_bookings");

const app = express();

app.use(
  cors({
    origin: "http://localhost:3000",
    credentials: true,
  })
);

app.use(express.json());
app.use(studentPetitionsRoutes);
app.use(instructorSlotBookingsRoutes);
app.use(instructorOfficeHoursRoutes);
app.use(studentBookingsRoutes);//2


// ✅ quick DB test route
app.get("/health", async (req, res) => {
  try {
    const [rows] = await pool.query("SELECT DATABASE() AS dbname");
    res.json({ success: true, db: rows[0].dbname });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

app.listen(process.env.PORT || 5050, () => {
  console.log(`Node API running on http://localhost:${process.env.PORT || 5050}`);
});
