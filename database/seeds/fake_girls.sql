-- Seed 15 fake female profiles for development/testing
-- Run: mysql -u root dateapp < database/seeds/fake_girls.sql

INSERT INTO users (email, password_hash, status, is_premium, created_at, last_active_at) VALUES
('emma.s@fake.test',    '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 2 DAY,  NOW() - INTERVAL 10 MINUTE),
('sophia.r@fake.test',  '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 5 DAY,  NOW() - INTERVAL 30 MINUTE),
('olivia.m@fake.test',  '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 1 DAY,  NOW() - INTERVAL 5 MINUTE),
('ava.w@fake.test',     '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 3 DAY,  NOW() - INTERVAL 2 HOUR),
('isabella.j@fake.test','$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 7 DAY,  NOW() - INTERVAL 1 HOUR),
('mia.b@fake.test',     '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 1, NOW() - INTERVAL 4 DAY,  NOW() - INTERVAL 15 MINUTE),
('charlotte.d@fake.test','$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012','active', 0, NOW() - INTERVAL 6 DAY,  NOW() - INTERVAL 45 MINUTE),
('amelia.h@fake.test',  '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 1 DAY,  NOW() - INTERVAL 3 MINUTE),
('harper.g@fake.test',  '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 8 DAY,  NOW() - INTERVAL 4 HOUR),
('ella.c@fake.test',    '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 1, NOW() - INTERVAL 2 DAY,  NOW() - INTERVAL 20 MINUTE),
('luna.p@fake.test',    '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 10 DAY, NOW() - INTERVAL 6 HOUR),
('chloe.l@fake.test',   '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 3 DAY,  NOW() - INTERVAL 8 MINUTE),
('grace.k@fake.test',   '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 12 DAY, NOW() - INTERVAL 2 DAY),
('zoe.n@fake.test',     '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 1 DAY,  NOW() - INTERVAL 1 MINUTE),
('lily.f@fake.test',    '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ012', 'active', 0, NOW() - INTERVAL 5 DAY,  NOW() - INTERVAL 50 MINUTE);

-- Grab the IDs (assumes auto-increment, last 15 rows)
SET @id1  = (SELECT id FROM users WHERE email = 'emma.s@fake.test');
SET @id2  = (SELECT id FROM users WHERE email = 'sophia.r@fake.test');
SET @id3  = (SELECT id FROM users WHERE email = 'olivia.m@fake.test');
SET @id4  = (SELECT id FROM users WHERE email = 'ava.w@fake.test');
SET @id5  = (SELECT id FROM users WHERE email = 'isabella.j@fake.test');
SET @id6  = (SELECT id FROM users WHERE email = 'mia.b@fake.test');
SET @id7  = (SELECT id FROM users WHERE email = 'charlotte.d@fake.test');
SET @id8  = (SELECT id FROM users WHERE email = 'amelia.h@fake.test');
SET @id9  = (SELECT id FROM users WHERE email = 'harper.g@fake.test');
SET @id10 = (SELECT id FROM users WHERE email = 'ella.c@fake.test');
SET @id11 = (SELECT id FROM users WHERE email = 'luna.p@fake.test');
SET @id12 = (SELECT id FROM users WHERE email = 'chloe.l@fake.test');
SET @id13 = (SELECT id FROM users WHERE email = 'grace.k@fake.test');
SET @id14 = (SELECT id FROM users WHERE email = 'zoe.n@fake.test');
SET @id15 = (SELECT id FROM users WHERE email = 'lily.f@fake.test');

-- Profiles (Athens-area coords with slight offsets, all female, looking_for men/everyone)
INSERT INTO profiles (user_id, name, bio, date_of_birth, gender, looking_for, relationship_goal, height_cm, smoking, drinking, city, country, latitude, longitude) VALUES
(@id1,  'Emma',      'Coffee addict & bookworm. Let''s grab a freddo ☕',                    '1998-03-15', 'female', 'male',     'relationship', 168, 'never',      'socially',   'Athens',       'Greece', 37.9838 + 0.005,  23.7275 + 0.008),
(@id2,  'Sophia',    'Adventure seeker. Hiking, diving, and everything outdoors 🏔️',         '1996-07-22', 'female', 'male',     'relationship', 172, 'never',      'never',      'Glyfada',      'Greece', 37.8660 + 0.003,  23.7530 + 0.002),
(@id3,  'Olivia',    'Med student by day, salsa dancer by night 💃',                          '2000-11-08', 'female', 'male',     'dating',       165, 'never',      'socially',   'Athens',       'Greece', 37.9750 + 0.002,  23.7350 + 0.005),
(@id4,  'Ava',       'Foodie who will judge you by your restaurant picks 🍕',                 '1999-01-30', 'female', 'male',     'dating',       160, 'sometimes',  'regularly',  'Piraeus',      'Greece', 37.9485 + 0.004,  23.6432 + 0.003),
(@id5,  'Isabella',  'Photographer & cat mom. Will send you sunsets pics 🌅',                 '1997-05-19', 'female', 'male',     'relationship', 170, 'never',      'socially',   'Kifisia',      'Greece', 38.0744 + 0.001,  23.8108 + 0.004),
(@id6,  'Mia',       'Yoga instructor. Looking for good vibes only ✨',                       '1995-09-12', 'female', 'everyone', 'relationship', 175, 'never',      'never',      'Marousi',      'Greece', 38.0504 + 0.003,  23.8058 + 0.001),
(@id7,  'Charlotte', 'Architect with a weakness for street art and vinyl records 🎨',         '1998-12-01', 'female', 'male',     'something_casual', 163, 'sometimes','socially',  'Kolonaki',     'Greece', 37.9790 + 0.001,  23.7430 + 0.002),
(@id8,  'Amelia',    'PhD in overthinking. Fluent in sarcasm and Greek 🇬🇷',                  '2001-04-25', 'female', 'male',     'dating',       167, 'never',      'socially',   'Athens',       'Greece', 37.9920 + 0.004,  23.7320 + 0.006),
(@id9,  'Harper',    'Gym rat 🏋️ Love cooking healthy meals and long walks on the beach',     '1996-08-14', 'female', 'male',     'relationship', 171, 'never',      'socially',   'Vouliagmeni',  'Greece', 37.8120 + 0.002,  23.7780 + 0.003),
(@id10, 'Ella',      'Marketing manager. Wine lover. Dog person 🐕',                          '1999-06-03', 'female', 'male',     'relationship', 166, 'never',      'regularly',  'Chalandri',    'Greece', 38.0211 + 0.005,  23.7988 + 0.002),
(@id11, 'Luna',      'Musician 🎵 If you can play guitar, you already have my attention',     '2000-02-18', 'female', 'male',     'dating',       158, 'sometimes',  'socially',   'Pagrati',      'Greece', 37.9635 + 0.003,  23.7510 + 0.004),
(@id12, 'Chloe',     'Interior designer. I will rearrange your apartment on the first date 🛋️','1997-10-27','female', 'male',     'relationship', 169, 'never',      'socially',   'Neo Psychiko', 'Greece', 37.9970 + 0.002,  23.7780 + 0.001),
(@id13, 'Grace',     'Teacher by profession, traveller by passion ✈️',                        '1994-03-09', 'female', 'male',     'relationship', 164, 'never',      'never',      'Nea Smyrni',   'Greece', 37.9380 + 0.004,  23.7130 + 0.005),
(@id14, 'Zoe',       'Software dev who actually touches grass 🌿 Let''s go hiking!',          '2001-08-21', 'female', 'male',     'dating',       173, 'never',      'socially',   'Athens',       'Greece', 37.9860 + 0.001,  23.7260 + 0.003),
(@id15, 'Lily',      'Pastry chef 🧁 Will bake you something if you''re lucky',               '1998-11-16', 'female', 'everyone', 'something_casual', 162, 'never',  'socially',   'Kallithea',    'Greece', 37.9560 + 0.003,  23.6990 + 0.002);

-- Primary photos
INSERT INTO photos (user_id, file_path, is_primary) VALUES
(@id1,  'uploads/photos/seed_woman_1.jpg',  1),
(@id2,  'uploads/photos/seed_woman_2.jpg',  1),
(@id3,  'uploads/photos/seed_woman_3.jpg',  1),
(@id4,  'uploads/photos/seed_woman_4.jpg',  1),
(@id5,  'uploads/photos/seed_woman_5.jpg',  1),
(@id6,  'uploads/photos/seed_woman_6.jpg',  1),
(@id7,  'uploads/photos/seed_woman_7.jpg',  1),
(@id8,  'uploads/photos/seed_woman_8.jpg',  1),
(@id9,  'uploads/photos/seed_woman_9.jpg',  1),
(@id10, 'uploads/photos/seed_woman_10.jpg', 1),
(@id11, 'uploads/photos/seed_woman_11.jpg', 1),
(@id12, 'uploads/photos/seed_woman_12.jpg', 1),
(@id13, 'uploads/photos/seed_woman_13.jpg', 1),
(@id14, 'uploads/photos/seed_woman_14.jpg', 1),
(@id15, 'uploads/photos/seed_woman_15.jpg', 1);

-- User scores (ELO + profile completeness)
INSERT INTO user_scores (user_id, elo_score, profile_completeness, photo_count) VALUES
(@id1,  1050, 85, 1),
(@id2,  1120, 90, 1),
(@id3,  980,  75, 1),
(@id4,  1010, 80, 1),
(@id5,  1080, 88, 1),
(@id6,  1200, 95, 1),
(@id7,  950,  70, 1),
(@id8,  1030, 82, 1),
(@id9,  1100, 87, 1),
(@id10, 1150, 92, 1),
(@id11, 900,  65, 1),
(@id12, 1060, 86, 1),
(@id13, 1000, 78, 1),
(@id14, 1090, 89, 1),
(@id15, 970,  72, 1);

-- Sprinkle some interests (IDs reference the interests seed table)
INSERT INTO user_interests (user_id, interest_id) VALUES
(@id1, 1), (@id1, 5), (@id1, 12), (@id1, 20),
(@id2, 3), (@id2, 7), (@id2, 15), (@id2, 22), (@id2, 30),
(@id3, 2), (@id3, 8), (@id3, 18),
(@id4, 4), (@id4, 9), (@id4, 14), (@id4, 25),
(@id5, 6), (@id5, 11), (@id5, 19), (@id5, 28),
(@id6, 1), (@id6, 10), (@id6, 16), (@id6, 23), (@id6, 31),
(@id7, 5), (@id7, 13), (@id7, 21),
(@id8, 2), (@id8, 7), (@id8, 17), (@id8, 26),
(@id9, 3), (@id9, 8), (@id9, 15), (@id9, 29),
(@id10, 4), (@id10, 12), (@id10, 20), (@id10, 24),
(@id11, 6), (@id11, 11), (@id11, 18),
(@id12, 1), (@id12, 9), (@id12, 16), (@id12, 27),
(@id13, 5), (@id13, 10), (@id13, 22),
(@id14, 2), (@id14, 7), (@id14, 14), (@id14, 30), (@id14, 35),
(@id15, 3), (@id15, 13), (@id15, 19);
