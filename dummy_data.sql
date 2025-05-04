-- Disable foreign key checks to allow clean inserts in any order
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------
-- Dummy data for Course
-- ------------------------------------------------------------------
INSERT INTO `course` (`id`, `title`, `description`, `code`, `created_at`, `updated_at`) VALUES
  (1, 'Introduction to Computer Science', 'An overview of fundamental programming concepts and computer systems.', 'CS101',  '2024-09-01 08:00:00', '2024-09-01 08:00:00'),
  (2, 'Calculus I',                       'Differential and integral calculus of one variable.',                          'MATH201','2024-09-01 08:00:00', '2024-09-01 08:00:00'),
  (3, 'World History',                    'A survey of world history from ancient to modern times.',                      'HIST210','2024-09-01 08:00:00', '2024-09-01 08:00:00'),
  (4, 'Physics I',                        'Mechanics and thermodynamics of classical physics.',                           'PHYS101','2024-09-01 08:00:00', '2024-09-01 08:00:00');

-- ------------------------------------------------------------------
-- Dummy data for User
-- ------------------------------------------------------------------
-- Note: passwords hashed with bcrypt (Symfony-compatible)
INSERT INTO `user` (
  `id`, `username`, `email`, `password`, `first_name`, `last_name`,
  `password_auto_generated`, `phone_number`, `address`, `created_at`, `updated_at`
) VALUES
  (1, 'asmith',    'alice.smith@example.com',    '$2b$12$tf5Addu/9/vAdR18aFinWeDizswlBoNGgOTXWW32l8vctctKPMJw6', 'Alice',   'Smith',   FALSE, '555-1234', '123 Maple St, Springfield', '2025-01-10 09:00:00', '2025-01-10 09:00:00'),
  (2, 'bjohnson',  'bob.johnson@example.com',    '$2b$12$EsYGFCyRIv670i.enPMSQebQWwAjlP1FvnCj2hFIpYgre5gYJjsCm', 'Bob',     'Johnson', FALSE, '555-5678', '456 Oak Ave, Riverside',    '2025-01-15 10:30:00', '2025-01-15 10:30:00'),
  (3, 'cwilliams', 'carol.williams@example.com', '$2b$12$iVvQDGd2UgLevmbye.tnDe8yCXgusLP3v9uauyrF4lpCbsK/tUY/a','Carol',  'Williams',TRUE, '555-9012', '789 Pine Rd, Centerville',   '2025-02-01 14:45:00', '2025-02-01 14:45:00'),
  (4, 'dbrown',    'david.brown@example.com',    '$2b$12$F3l30xznIqdMxieECe4aVeD5Rh1MzPvL2ew4dXnexLqV7ehR5f4Ni','David',  'Brown',   FALSE, '555-3456', '321 Birch Ln, Lakeside',     '2025-02-10 08:20:00', '2025-02-10 08:20:00'),
  (5, 'edavis',    'emily.davis@example.com',    '$2b$12$6DlIrmAibSVXz7Bh8RFReOAR/VMNKCYavY0BX3xXQmCQxawGVP9xy','Emily',  'Davis',   TRUE, NULL,       '654 Cedar Blvd, Hillcrest',  '2025-02-20 11:10:00', '2025-02-20 11:10:00');

-- ------------------------------------------------------------------
-- Dummy data for User–Role join table
-- ------------------------------------------------------------------
-- (Assuming roles already exist in `role` table: 1=Administrator, 2=Instructor, 3=Student)
INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
  (1, 1),  -- Alice → Administrator
  (2, 2),  -- Bob   → Instructor
  (3, 2),  -- Carol → Instructor
  (4, 3),  -- David → Student
  (5, 3);  -- Emily → Student

-- ------------------------------------------------------------------
-- Dummy data for Post
-- ------------------------------------------------------------------
-- (Assuming message categories 1=General, 2=Homework, 3=Exams already exist)
INSERT INTO `post` (
  `id`, `title`, `content`, `type`, `created_at`, `updated_at`,
  `is_pinned`, `position`, `category_id`, `course_id`, `author_id`
) VALUES
  (1, 'Welcome to CS101',
      'This is the first post for CS101. Please introduce yourselves.',
      'general', '2024-09-02 10:00:00', '2024-09-02 10:00:00',
      FALSE, 1, 1, 1, 2),
  (2, 'Homework 1 Released',
      'Homework 1 is now available and due next Friday.',
      'homework', '2024-09-05 09:00:00', '2024-09-05 09:00:00',
      FALSE, 2, 2, 1, 2),
  (3, 'Exam Schedule',
      'The midterm exam will be on October 15th.',
      'exam',    '2024-10-01 08:30:00', '2024-10-01 08:30:00',
      FALSE, 3, 3, 2, 3),
  (4, 'Welcome to Physics I',
      'This is the introductory forum for Physics I. Feel free to ask questions about mechanics and thermodynamics.',
      'general', '2024-09-03 11:15:00', '2024-09-03 11:15:00',
      FALSE, 1, 1, 4, 2);

-- ------------------------------------------------------------------
-- Dummy data for Enrollment
-- ------------------------------------------------------------------
INSERT INTO `enrollments` (`id`, `user_id`, `course_id`) VALUES
  (1, 4, 1),  -- David enrolled in CS101
  (2, 4, 2),  -- David enrolled in MATH201
  (3, 5, 1),  -- Emily enrolled in CS101
  (4, 5, 3),  -- Emily enrolled in HIST210
  (5, 2, 1),  -- Bob (Instructor) assigned to CS101
  (6, 2, 4),  -- Bob (Instructor) assigned to Physics I
  (7, 3, 3),  -- Carol (Instructor) assigned to World History
  (8, 3, 2);  -- Carol (Instructor) assigned to Calculus I

-- ------------------------------------------------------------------
-- Dummy data for UserCourseAccess
-- ------------------------------------------------------------------
INSERT INTO `user_course_access` (`id`, `user_id`, `course_id`, `accessed_at`) VALUES
  (1, 4, 1, '2025-02-15 14:23:00'),
  (2, 5, 1, '2025-02-16 10:05:00'),
  (3, 4, 2, '2025-02-17 11:45:00'),
  (4, 5, 3, '2025-02-20 09:30:00'),
  (5, 2, 1, '2025-03-01 09:00:00'),  -- Bob accessed CS101
  (6, 3, 2, '2025-03-02 10:30:00');  -- Carol accessed Calculus I

-- ------------------------------------------------------------------
-- Dummy data for AdminAlert
-- ------------------------------------------------------------------
INSERT INTO `admin_alert` (`id`, `course_id`, `post_id`, `admin_id`, `action`, `created_at`) VALUES
  (1, 1, 1, 1, 'create', '2025-03-01 12:00:00'),
  (2, 1, 2, 1, 'update', '2025-03-02 15:30:00'),
  (3, 2, NULL, 1, 'delete', '2025-03-03 09:45:00');

-- ------------------------------------------------------------------
-- Dummy data for AlertAcknowledgement
-- ------------------------------------------------------------------
INSERT INTO `alert_acknowledgement` (`id`, `alert_id`, `user_id`, `acknowledged_at`) VALUES
  (1, 1, 4, '2025-03-01 12:15:00'),
  (2, 2, 4, '2025-03-02 16:00:00'),
  (3, 1, 5, '2025-03-01 13:00:00');

-- ------------------------------------------------------------------
-- Dummy data for Announcement
-- ------------------------------------------------------------------
INSERT INTO `announcement` (`id`, `title`, `content`, `created_at`, `updated_at`, `author_id`) VALUES
  (1, 'Site Maintenance',
      'The site will be down for maintenance on March 20 from 01:00 to 03:00.',
      '2025-03-10 08:00:00', '2025-03-10 08:00:00', 1),
  (2, 'New Course Added',
      'We have added PHYS101 Introduction to Physics to next semester’s catalog.',
      '2025-04-05 09:30:00', '2025-04-05 09:30:00', 1),
  (3, 'Holiday Notice',
      'Campus will be closed on April 15 for the public holiday.',
      '2025-04-10 10:00:00', '2025-04-10 10:00:00', 1);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
