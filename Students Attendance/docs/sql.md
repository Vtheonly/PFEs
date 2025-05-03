Okay, here are the SQL `CREATE TABLE` statements for the tables shown in your image: `AttendanceSessions`, `Messages`, `QRCodes`, `StudentAttendanceRecords`, `TeacherAnnouncements`, and `Users`. Explanations for each table and column are included.

**Important Notes Before You Start:**

*   **Password Security:** The `Users` table below includes a `password` column storing plain text. **This is extremely insecure and should NEVER be done in a real application.** Always hash passwords using a strong algorithm (like bcrypt or Argon2) before storing them. This example uses plain text only for simplicity based on the likely initial setup implied.
*   **Foreign Keys:** Foreign key constraints (`FOREIGN KEY ... REFERENCES ...`) are included. These enforce data integrity (e.g., ensuring a `teacher_id` in `AttendanceSessions` actually exists in the `Users` table). While technically optional to *create* the tables, they are highly recommended for a robust database.
*   **Database System:** SQL syntax can have minor variations between database systems (MySQL, PostgreSQL, SQL Server, SQLite). These scripts use common syntax likely compatible with MySQL/MariaDB.

---

**1. Users Table**

```sql
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- WARNING: Storing plain text passwords is very insecure! Hash passwords in production.
    role ENUM('teacher', 'student') NOT NULL,
    `group` INT NULL -- Assign student to a group, or teacher to the group they teach. NULL if not applicable.
);
```

**Explanation:**

*   `user_id`: Unique identifier for each user (student or teacher). Automatically generated integer and the primary key.
*   `name`: The full name of the user. Cannot be empty.
*   `email`: The user's email address. Must be unique for each user and cannot be empty. Often used for login.
*   `password`: The user's password. **(Security Warning)** Stored as plain text here for simplicity only. Should be securely hashed in a real system. Cannot be empty.
*   `role`: Defines if the user is a 'teacher' or a 'student'. Restricts values to only these two options. Cannot be empty.
*   `group`: An integer representing the group number. For students, it's their assigned group. For teachers, it might be the group they primarily teach (assuming one teacher per group for simplicity). Can be `NULL` if a user isn't assigned to a specific group.

---

**2. AttendanceSessions Table**

```sql
CREATE TABLE AttendanceSessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,          -- Foreign key linking to the teacher who created the session
    date DATE NOT NULL,               -- The date the session is for
    start_time TIME NOT NULL,         -- The time the session starts
    end_time TIME NOT NULL,           -- The time the session ends
    status ENUM('open', 'closed') DEFAULT 'open', -- Status of the session (e.g., accepting attendance or not)
    FOREIGN KEY (teacher_id) REFERENCES Users(user_id) ON DELETE CASCADE ON UPDATE CASCADE -- Link to Users table
);
```

**Explanation:**

*   `session_id`: Unique identifier for each attendance session. Automatically generated integer and the primary key.
*   `teacher_id`: Links to the `user_id` of the teacher who initiated this session. Cannot be empty.
*   `date`: The specific date for which this attendance session is valid. Cannot be empty.
*   `start_time`: The time the attendance session begins. Cannot be empty.
*   `end_time`: The time the attendance session ends. Cannot be empty.
*   `status`: Indicates whether the session is currently 'open' (accepting attendance) or 'closed'. Defaults to 'open' when a new session is created.
*   `FOREIGN KEY (teacher_id) ...`: Ensures that the `teacher_id` must exist in the `Users` table. `ON DELETE CASCADE` means if a teacher user is deleted, their sessions are also deleted. `ON UPDATE CASCADE` means if a teacher's `user_id` changes (unlikely but possible), it's updated here too.

---

**3. QRCodes Table**

```sql
CREATE TABLE QRCodes (
    qr_code_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT UNIQUE NOT NULL,   -- Foreign key linking to the specific session (ensures one QR per session)
    code_value VARCHAR(255) UNIQUE NOT NULL, -- The unique value/string embedded in the QR code (e.g., a random token)
    is_used BOOLEAN DEFAULT FALSE,     -- Flag to indicate if the code has been successfully used (might not be needed if regenerated)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the QR code record was created
    FOREIGN KEY (session_id) REFERENCES AttendanceSessions(session_id) ON DELETE CASCADE ON UPDATE CASCADE -- Link to AttendanceSessions table
);
```

**Explanation:**

*   `qr_code_id`: Unique identifier for the QR code record. Automatically generated integer and the primary key.
*   `session_id`: Links to the `session_id` this QR code is associated with. `UNIQUE` constraint ensures only one QR code record exists per session. Cannot be empty.
*   `code_value`: The actual data stored within the QR code (e.g., a unique random string or token). Must be unique across all QR codes and cannot be empty. `VARCHAR(255)` allows flexibility in code length.
*   `is_used`: A boolean flag (`TRUE`/`FALSE` or `1`/`0`) indicating if this specific code instance has been scanned/processed. Defaults to `FALSE`. Your application logic might regenerate the `code_value` instead of using this flag.
*   `created_at`: Records the date and time when this QR code entry was created. Defaults to the time of insertion.
*   `FOREIGN KEY (session_id) ...`: Ensures that the `session_id` must exist in the `AttendanceSessions` table. Cascading rules apply similarly to the previous table.

---

**4. StudentAttendanceRecords Table**

```sql
CREATE TABLE StudentAttendanceRecords (
    record_id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,          -- Foreign key linking to the session
    student_id INT NOT NULL,          -- Foreign key linking to the student
    attendance_status ENUM('present', 'absent', 'late', 'excused') NOT NULL, -- Status of the student for this session
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, -- When the record was created or last updated
    notes TEXT NULL,                  -- Optional notes (e.g., reason for absence/lateness)
    UNIQUE KEY unique_student_session (session_id, student_id), -- Prevent duplicate records for the same student in the same session
    FOREIGN KEY (session_id) REFERENCES AttendanceSessions(session_id) ON DELETE CASCADE ON UPDATE CASCADE, -- Link to AttendanceSessions
    FOREIGN KEY (student_id) REFERENCES Users(user_id) ON DELETE CASCADE ON UPDATE CASCADE -- Link to Users (students)
);
```

**Explanation:**

*   `record_id`: Unique identifier for each individual attendance entry. Automatically generated integer and the primary key.
*   `session_id`: Links to the specific `session_id` this record belongs to. Cannot be empty.
*   `student_id`: Links to the `user_id` of the student whose attendance is being recorded. Cannot be empty.
*   `attendance_status`: The recorded status for the student in this session (e.g., 'present', 'absent'). Added 'late' and 'excused' as common possibilities. Cannot be empty.
*   `timestamp`: Records when this attendance record was created or last modified. Defaults to the current date and time.
*   `notes`: An optional field to add any relevant notes about this specific attendance record (e.g., reason for absence). Can be `NULL`.
*   `UNIQUE KEY unique_student_session ...`: This crucial constraint prevents inserting more than one attendance record for the same student within the same session.
*   `FOREIGN KEY (session_id) ...` and `FOREIGN KEY (student_id) ...`: Ensure `session_id` and `student_id` exist in their respective tables. Cascading rules apply.

---

**5. Messages Table**

```sql
CREATE TABLE Messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,           -- Foreign key linking to the user who sent the message
    receiver_id INT NOT NULL,         -- Foreign key linking to the user who received the message
    message_content TEXT NOT NULL,    -- The actual content of the message
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, -- When the message was sent
    is_read BOOLEAN DEFAULT FALSE,     -- Flag to indicate if the receiver has read the message
    FOREIGN KEY (sender_id) REFERENCES Users(user_id) ON DELETE CASCADE ON UPDATE CASCADE, -- Link to Users (sender)
    FOREIGN KEY (receiver_id) REFERENCES Users(user_id) ON DELETE CASCADE ON UPDATE CASCADE -- Link to Users (receiver)
);
```

**Explanation:**

*   `message_id`: Unique identifier for each message. Automatically generated integer and the primary key.
*   `sender_id`: Links to the `user_id` of the user who sent the message. Cannot be empty.
*   `receiver_id`: Links to the `user_id` of the user who is the intended recipient. Cannot be empty.
*   `message_content`: The text of the message itself. `TEXT` allows for longer messages. Cannot be empty.
*   `timestamp`: Records when the message was sent. Defaults to the current date and time.
*   `is_read`: A boolean flag indicating whether the recipient has marked the message as read. Defaults to `FALSE`.
*   `FOREIGN KEY (sender_id) ...` and `FOREIGN KEY (receiver_id) ...`: Ensure sender and receiver IDs exist in the `Users` table. Cascading rules apply.

---

**6. TeacherAnnouncements Table**

```sql
CREATE TABLE TeacherAnnouncements (
    announcement_id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,          -- Foreign key linking to the teacher who made the announcement
    content TEXT NOT NULL,            -- The content of the announcement
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the announcement was posted
    -- updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Optional: uncomment if announcements can be edited
    FOREIGN KEY (teacher_id) REFERENCES Users(user_id) ON DELETE CASCADE ON UPDATE CASCADE -- Link to Users (teacher)
);
```

**Explanation:**

*   `announcement_id`: Unique identifier for each announcement. Automatically generated integer and the primary key.
*   `teacher_id`: Links to the `user_id` of the teacher who posted the announcement. Cannot be empty. (Application logic should ensure this user has the 'teacher' role).
*   `content`: The text content of the announcement. Cannot be empty.
*   `created_at`: Records when the announcement was initially posted. Defaults to the current timestamp.
*   `updated_at`: (Optional, currently commented out) If announcements can be edited, this timestamp would automatically update whenever the announcement record is modified.
*   `FOREIGN KEY (teacher_id) ...`: Ensures the `teacher_id` exists in the `Users` table. Cascading rules apply.

---

Execute these `CREATE TABLE` statements in your SQL client connected to the `pfe_std_att` database. Remember the password security warning for the `Users` table!