-- Existing administrators retain admin access; no seed accounts or student data.
ALTER TABLE admins ADD COLUMN role TEXT NOT NULL DEFAULT 'admin' CHECK (role IN ('admin', 'petugas'));
ALTER TABLE admins ADD COLUMN assigned_class TEXT;
-- Last editor only, not a complete change history.
ALTER TABLE attendance ADD COLUMN recorded_by INTEGER REFERENCES admins(id) ON DELETE SET NULL;
