-- Interest Tags Seed Data (~35 tags across 7 categories)

USE `dateapp`;

INSERT IGNORE INTO `interests` (`name`, `emoji`, `category`) VALUES
-- Lifestyle
('Travel',      '✈️', 'Lifestyle'),
('Fitness',     '💪', 'Lifestyle'),
('Yoga',        '🧘', 'Lifestyle'),
('Outdoors',    '🏕️', 'Lifestyle'),
('Cooking',     '👨‍🍳', 'Lifestyle'),
('Gardening',   '🌱', 'Lifestyle'),

-- Sports
('Football',    '⚽', 'Sports'),
('Basketball',  '🏀', 'Sports'),
('Tennis',      '🎾', 'Sports'),
('Swimming',    '🏊', 'Sports'),
('Running',     '🏃', 'Sports'),
('Cycling',     '🚴', 'Sports'),

-- Creative
('Photography', '📸', 'Creative'),
('Art',         '🎨', 'Creative'),
('Music',       '🎵', 'Creative'),
('Writing',     '✍️', 'Creative'),
('Design',      '🎯', 'Creative'),
('Film',        '🎬', 'Creative'),

-- Social
('Nightlife',   '🌃', 'Social'),
('Brunch',      '🥂', 'Social'),
('Board Games', '🎲', 'Social'),
('Volunteering','🤝', 'Social'),
('Networking',  '💼', 'Social'),

-- Food & Drink
('Coffee',      '☕', 'Food & Drink'),
('Wine',        '🍷', 'Food & Drink'),
('Craft Beer',  '🍺', 'Food & Drink'),
('Vegan',       '🥗', 'Food & Drink'),
('Foodie',      '🍕', 'Food & Drink'),

-- Entertainment
('Movies',      '🎥', 'Entertainment'),
('Netflix',     '📺', 'Entertainment'),
('Gaming',      '🎮', 'Entertainment'),
('Reading',     '📚', 'Entertainment'),
('Podcasts',    '🎧', 'Entertainment'),
('Anime',       '🌸', 'Entertainment'),

-- Mindset
('Meditation',       '🧠', 'Mindset'),
('Sustainability',   '♻️', 'Mindset'),
('Entrepreneurship', '🚀', 'Mindset'),
('Science',          '🔬', 'Mindset');
