CREATE TABLE admins (id INTEGER PRIMARY KEY, username TEXT NOT NULL UNIQUE, password_hash TEXT NOT NULL);
CREATE TABLE sessions (token_hash TEXT PRIMARY KEY, admin_id INTEGER NOT NULL REFERENCES admins(id) ON DELETE CASCADE, expires_at INTEGER NOT NULL);
CREATE INDEX sessions_expiry ON sessions(expires_at);
CREATE TABLE students (id INTEGER PRIMARY KEY, name TEXT NOT NULL CHECK(length(name) BETWEEN 1 AND 100), class TEXT NOT NULL CHECK(length(class) BETWEEN 1 AND 30), major TEXT NOT NULL CHECK(length(major) BETWEEN 1 AND 50));
CREATE INDEX students_class ON students(class);
CREATE TABLE attendance (id INTEGER PRIMARY KEY, student_id INTEGER NOT NULL REFERENCES students(id) ON DELETE RESTRICT, date TEXT NOT NULL, time TEXT NOT NULL, status TEXT NOT NULL CHECK(status IN ('Hadir','Izin','Sakit','Alpa')), UNIQUE(student_id, date));
CREATE INDEX attendance_date ON attendance(date);
CREATE TABLE login_attempts (key TEXT PRIMARY KEY, attempts INTEGER NOT NULL, reset_at INTEGER NOT NULL);
