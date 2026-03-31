<?php
/**
 * Database Seeder – creates test users for development.
 * Run: php database/seed.php
 */

require_once __DIR__ . '/../config/database.php';

$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "DateApp Seeder\n";
echo "==============\n\n";

// Create admin user
$adminHash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
$pdo->prepare("INSERT IGNORE INTO users (email, password_hash, role, status, email_verified_at) VALUES (?, ?, 'admin', 'active', NOW())")
    ->execute(['admin@dateapp.com', $adminHash]);
$adminId = $pdo->lastInsertId();
if ($adminId) {
    $pdo->prepare("INSERT IGNORE INTO profiles (user_id, name, bio, gender, looking_for, city, country) VALUES (?, 'Admin', 'App administrator', 'male', 'everyone', 'Athens', 'Greece')")
        ->execute([$adminId]);
    echo "✓ Admin: admin@dateapp.com / admin123\n";
}

// Test users data
$users = [
    ['name' => 'Sofia', 'email' => 'sofia@test.com', 'gender' => 'female', 'looking_for' => 'male', 'dob' => '1996-05-12', 'bio' => 'Love hiking and sunset photography 🌅', 'city' => 'Athens', 'lat' => 37.9838, 'lng' => 23.7275],
    ['name' => 'Nikos', 'email' => 'nikos@test.com', 'gender' => 'male', 'looking_for' => 'female', 'dob' => '1994-08-22', 'bio' => 'Coffee addict ☕ and amateur chef', 'city' => 'Athens', 'lat' => 37.9755, 'lng' => 23.7348],
    ['name' => 'Maria', 'email' => 'maria@test.com', 'gender' => 'female', 'looking_for' => 'male', 'dob' => '1998-01-30', 'bio' => 'Bookworm and cat mom 📚🐱', 'city' => 'Thessaloniki', 'lat' => 40.6401, 'lng' => 22.9444],
    ['name' => 'Dimitris', 'email' => 'dimitris@test.com', 'gender' => 'male', 'looking_for' => 'female', 'dob' => '1993-11-15', 'bio' => 'Musician and beach lover 🎸🏖️', 'city' => 'Athens', 'lat' => 37.9690, 'lng' => 23.7507],
    ['name' => 'Elena', 'email' => 'elena@test.com', 'gender' => 'female', 'looking_for' => 'everyone', 'dob' => '1997-03-08', 'bio' => 'Yoga instructor & travel enthusiast ✈️', 'city' => 'Athens', 'lat' => 37.9842, 'lng' => 23.7281],
    ['name' => 'Kostas', 'email' => 'kostas@test.com', 'gender' => 'male', 'looking_for' => 'female', 'dob' => '1995-07-20', 'bio' => 'Startup founder, coffee runs, late nights 💻', 'city' => 'Athens', 'lat' => 37.9780, 'lng' => 23.7150],
    ['name' => 'Anna', 'email' => 'anna@test.com', 'gender' => 'female', 'looking_for' => 'male', 'dob' => '1999-09-11', 'bio' => 'Med student who loves dancing 💃', 'city' => 'Athens', 'lat' => 37.9900, 'lng' => 23.7400],
    ['name' => 'George', 'email' => 'george@test.com', 'gender' => 'male', 'looking_for' => 'female', 'dob' => '1992-12-05', 'bio' => 'Photographer & world traveler 📷', 'city' => 'Athens', 'lat' => 37.9720, 'lng' => 23.7260],
    ['name' => 'Katerina', 'email' => 'katerina@test.com', 'gender' => 'female', 'looking_for' => 'male', 'dob' => '1996-06-18', 'bio' => 'Foodie exploring every restaurant in town 🍕', 'city' => 'Athens', 'lat' => 37.9810, 'lng' => 23.7320],
    ['name' => 'Alex', 'email' => 'alex@test.com', 'gender' => 'non-binary', 'looking_for' => 'everyone', 'dob' => '1997-04-25', 'bio' => 'Artist, gamer, dog person 🎨🐕', 'city' => 'Athens', 'lat' => 37.9860, 'lng' => 23.7190],
];

$goals = ['long-term', 'short-term', 'friendship', 'casual', 'undecided'];
$smoking = ['never', 'sometimes', 'regularly'];
$drinking = ['never', 'sometimes', 'regularly'];
$password = password_hash('test123', PASSWORD_BCRYPT, ['cost' => 12]);

$insertUser = $pdo->prepare("INSERT IGNORE INTO users (email, password_hash, status, email_verified_at, is_premium) VALUES (?, ?, 'active', NOW(), ?)");
$insertProfile = $pdo->prepare("INSERT IGNORE INTO profiles (user_id, name, bio, date_of_birth, gender, looking_for, relationship_goal, height_cm, smoking, drinking, latitude, longitude, city, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Greece')");

$count = 0;
foreach ($users as $i => $u) {
    $isPremium = ($i % 3 === 0) ? 1 : 0;
    $insertUser->execute([$u['email'], $password, $isPremium]);
    $userId = $pdo->lastInsertId();
    if ($userId) {
        $insertProfile->execute([
            $userId, $u['name'], $u['bio'], $u['dob'], $u['gender'], $u['looking_for'],
            $goals[array_rand($goals)],
            rand(155, 195),
            $smoking[array_rand($smoking)],
            $drinking[array_rand($drinking)],
            $u['lat'], $u['lng'], $u['city']
        ]);
        $count++;
        echo "✓ {$u['name']}: {$u['email']} / test123" . ($isPremium ? " ⭐ Premium" : "") . "\n";
    }
}

echo "\n{$count} test users created.\n";
echo "All test passwords: test123\n";
echo "Admin: admin@dateapp.com / admin123\n";
