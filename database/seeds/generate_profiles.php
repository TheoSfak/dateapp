<?php
/**
 * Generate 100 polished Greek profiles seed SQL.
 * Run: php database/seeds/generate_profiles.php > database/seeds/greek_profiles.sql
 */

mt_srand(42); // deterministic

// ── Greek cities with coordinates ──────────────────────
$cities = [
    ['Athens',          'Greece', 37.9838, 23.7275],
    ['Thessaloniki',    'Greece', 40.6401, 22.9444],
    ['Heraklion',       'Greece', 35.3387, 25.1442],
    ['Patras',          'Greece', 38.2466, 21.7346],
    ['Larissa',         'Greece', 39.6370, 22.4200],
    ['Volos',           'Greece', 39.3666, 22.9420],
    ['Ioannina',        'Greece', 39.6650, 20.8537],
    ['Chania',          'Greece', 35.5138, 24.0180],
    ['Rhodes',          'Greece', 36.4341, 28.2176],
    ['Corfu',           'Greece', 39.6243, 19.9217],
    ['Kavala',          'Greece', 40.9397, 24.4014],
    ['Kalamata',        'Greece', 37.0387, 22.1143],
    ['Alexandroupoli',  'Greece', 40.8468, 25.8743],
    ['Serres',          'Greece', 41.0856, 23.5500],
    ['Trikala',         'Greece', 39.5556, 21.7677],
    ['Glyfada',         'Greece', 37.8660, 23.7530],
    ['Marousi',         'Greece', 38.0504, 23.8058],
    ['Piraeus',         'Greece', 37.9485, 23.6432],
    ['Kifisia',         'Greece', 38.0744, 23.8108],
    ['Rethymno',        'Greece', 35.3693, 24.4737],
    ['Mykonos',         'Greece', 37.4467, 25.3289],
    ['Santorini',       'Greece', 36.3932, 25.4615],
    ['Nafplio',         'Greece', 37.5673, 22.8017],
    ['Chalkida',        'Greece', 38.4633, 23.5985],
    ['Kos',             'Greece', 36.8939, 26.9869],
    ['Zakynthos',       'Greece', 37.7870, 20.8979],
    ['Drama',           'Greece', 41.1499, 24.1475],
    ['Veria',           'Greece', 40.5240, 22.2040],
    ['Kastoria',        'Greece', 40.5193, 21.2685],
    ['Xanthi',          'Greece', 41.1350, 24.8880],
];

// ── Greek names ────────────────────────────────────────
$femaleNames = [
    'Αθηνά' => 'Athina',     'Μαρία' => 'Maria',       'Ελένη' => 'Eleni',
    'Κατερίνα' => 'Katerina', 'Σοφία' => 'Sofia',       'Αναστασία' => 'Anastasia',
    'Δήμητρα' => 'Dimitra',   'Ειρήνη' => 'Eirini',     'Γεωργία' => 'Georgia',
    'Χριστίνα' => 'Christina','Παναγιώτα' => 'Panagiota','Βασιλική' => 'Vasiliki',
    'Αγγελική' => 'Angeliki', 'Ελευθερία' => 'Eleftheria','Σταυρούλα' => 'Stavroula',
    'Νίκη' => 'Niki',        'Θεοδώρα' => 'Theodora',  'Μαριάννα' => 'Marianna',
    'Ιωάννα' => 'Ioanna',    'Ευαγγελία' => 'Evangelia','Ξένια' => 'Xenia',
    'Δανάη' => 'Danai',      'Φωτεινή' => 'Foteini',   'Αλεξάνδρα' => 'Alexandra',
    'Ραφαέλα' => 'Rafaela',  'Μυρτώ' => 'Myrto',       'Λυδία' => 'Lydia',
    'Άρτεμις' => 'Artemis',  'Σελήνη' => 'Selini',     'Μελίνα' => 'Melina',
    'Ρούλα' => 'Roula',      'Πηνελόπη' => 'Pinelopi', 'Ζωή' => 'Zoi',
    'Θάλεια' => 'Thalia',    'Δέσποινα' => 'Despoina', 'Μαρίνα' => 'Marina',
    'Αντωνία' => 'Antonia',  'Κλεοπάτρα' => 'Kleopatra','Ρέα' => 'Rea',
    'Σμαράγδα' => 'Smaragda','Ολυμπία' => 'Olympia',   'Ευγενία' => 'Eugenia',
    'Περσεφόνη' => 'Persefoni','Αριάδνη' => 'Ariadni',  'Νεφέλη' => 'Nefeli',
    'Μάρθα' => 'Martha',     'Πόπη' => 'Popi',         'Τζένη' => 'Tzeni',
    'Βίκυ' => 'Vicky',       'Λίλα' => 'Lila',         'Στέλλα' => 'Stella',
    'Κωνσταντίνα' => 'Konstantina', 'Ρεβέκκα' => 'Rebekka', 'Αλίκη' => 'Aliki',
    'Ηλέκτρα' => 'Ilektra',  'Φαίδρα' => 'Faidra',
];

$maleNames = [
    'Νίκος' => 'Nikos',       'Δημήτρης' => 'Dimitris',  'Γιώργος' => 'Giorgos',
    'Κώστας' => 'Kostas',     'Αλέξανδρος' => 'Alexandros','Θανάσης' => 'Thanasis',
    'Παναγιώτης' => 'Panagiotis','Μιχάλης' => 'Michalis', 'Βασίλης' => 'Vasilis',
    'Χρήστος' => 'Christos',  'Ανδρέας' => 'Andreas',    'Σπύρος' => 'Spyros',
    'Μάνος' => 'Manos',       'Λευτέρης' => 'Lefteris',  'Σταύρος' => 'Stavros',
    'Ηλίας' => 'Ilias',       'Αντώνης' => 'Antonis',    'Πέτρος' => 'Petros',
    'Γιάννης' => 'Giannis',   'Στέφανος' => 'Stefanos',  'Φίλιππος' => 'Filippos',
    'Λάμπρος' => 'Lambros',   'Χάρης' => 'Charis',       'Παύλος' => 'Pavlos',
    'Θοδωρής' => 'Theodoris', 'Ξενοφών' => 'Xenofon',   'Μάριος' => 'Marios',
    'Ορέστης' => 'Orestis',   'Άρης' => 'Aris',          'Σάκης' => 'Sakis',
    'Κυριάκος' => 'Kyriakos', 'Ρένος' => 'Renos',       'Τάσος' => 'Tasos',
    'Μάκης' => 'Makis',       'Φώτης' => 'Fotis',       'Αγγελος' => 'Angelos',
    'Ζήσης' => 'Zisis',       'Πάνος' => 'Panos',       'Ράφαελ' => 'Rafael',
    'Αχιλλέας' => 'Achilleas','Εμμανουήλ' => 'Emmanouil','Λάζαρος' => 'Lazaros',
    'Δαμιανός' => 'Damianos', 'Νεκτάριος' => 'Nektarios','Σωκράτης' => 'Sokratis',
    'Θωμάς' => 'Thomas',
];

// ── Bios ───────────────────────────────────────────────
$femaleBios = [
    "Αρχιτέκτονας με πάθος για τα ελληνικά νησιά. Ψάχνω κάποιον να μοιραστώ freddo espresso & ηλιοβασιλέματα 🌅",
    "Coffee addict & bookworm. Let's grab a freddo ☕ If you can make me laugh, you're already winning",
    "Yoga instructor by morning, foodie by night. Looking for someone who appreciates a good souvlaki 🥙",
    "Med student surviving on caffeine and determination. Will steal your hoodies 💃",
    "Marketing girl who loves hiking on weekends. If you can keep up on Hymettus, we'll get along fine 🏔️",
    "Photographer capturing life one frame at a time. Beach sunsets are my therapy 📸",
    "Software engineer who actually goes outside 🌿 Let's explore hidden beaches!",
    "Pastry chef in the making 🧁 I'll bake for you if you deserve it",
    "Αγαπώ τη θάλασσα, τα βιβλία και τις μακρινές βόλτες. Ψάχνω genuine connection",
    "Interior designer. I will rearrange your apartment on the first date 🛋️ Fair warning!",
    "Dancer & dreamer. Salsa nights and lazy Sunday mornings are my love language 💃",
    "Teacher by day, traveller by passion. 23 countries and counting ✈️",
    "Gym rat who also loves pizza. It's called balance 🏋️‍♀️🍕",
    "PhD in overthinking. Fluent in sarcasm and Greek. Wine fixes everything 🍷",
    "Music lover — if you play guitar you already have my attention 🎵",
    "Environmental scientist. I care about the planet and I need you to care too ♻️",
    "Pilot ✈️ Yes, really. I can take you places — literally",
    "Journalist with a weakness for good coffee and better conversations",
    "Graphic designer. I notice fonts on restaurant menus. It's a curse 🎨",
    "Veterinarian 🐾 My cat already approves of you. Just kidding, she hates everyone",
    "Wine sommelier. Will judge your wine choices (lovingly) 🍇",
    "Lawyer by profession, beach bum on vacation. Dual personality 😎",
    "Digital nomad currently based in Greece. Coffee shop hopper ☕",
    "Fitness coach & healthy food enthusiast. But I cheat on weekends 🍩",
    "Archaeologist 🏛️ I dig the past but I'm looking for a future with someone",
    "Marine biologist. The sea is my office and I wouldn't have it any other way 🐠",
    "TV presenter. Yes, you've probably seen me. No, I won't tell you where 📺",
    "Bookshop owner with too many cats and not enough shelf space 📚",
    "Speech therapist who talks too much. The irony is not lost on me 🗣️",
    "Startup founder hustling by day, Netflix binging by night 🚀",
    "Classical musician. I play violin but I also appreciate good rap 🎻",
    "Physiotherapist. I'll fix your back and your attitude 💆",
    "Fashion designer. Style is everything. First impressions matter ✨",
    "Biologist researching coral reefs. Half mermaid, half scientist 🧜‍♀️",
    "Civil engineer building bridges — literally and metaphorically 🌉",
    "Hotel manager in a 5-star resort. I know all the best spots 🏨",
    "Elementary school teacher. Patience is my superpower 🦸‍♀️",
    "Art curator. I can talk about Picasso for hours. You've been warned 🖼️",
    "Personal trainer. I'll motivate you to be your best self 💪",
    "Psychologist. No, I won't analyze you. Okay, maybe a little 🧠",
    "Dentist with a killer smile. Get it? 😬 Sorry, occupational humor",
    "Tattoo artist. Every piece tells a story. What's yours? 🎨",
    "Nutritionist who believes in cheat meals and good vibes 🥗",
    "Αισιόδοξη, ταξιδιάρα, λάτρης του θεάτρου. Looking for my co-star 🎭",
    "Florist 🌸 I speak the language of flowers. Let me teach you",
    "Data scientist by day, amateur astronomer by night 🔭",
    "Kindergarten teacher. I can handle 20 kids so I can definitely handle you 😄",
    "Surfer girl 🏄‍♀️ Catch waves with me in Crete",
    "Αγαπώ τις περιπέτειες. Paragliding, diving, bungee — name it, I've done it",
    "Makeup artist. I can transform anyone — including your boring weekends 💄",
    "Pharmacist who believes laughter is the best medicine 💊",
    "Economist with expensive taste and cheap humor 📊",
    "Podcast host. I'll interview you on the first date 🎙️",
    "Ceramic artist. Getting my hands dirty is therapy 🏺",
    "Astrophysicist dreaming about stars and also about someone to stargaze with 🌟",
];

$maleBios = [
    "Software engineer by day, amateur chef by night 👨‍🍳 I make a mean moussaka",
    "Architect designing spaces and looking for someone to fill mine 🏗️",
    "Ψάχνω κάποια για freddo espresso & long walks στην παραλιακή",
    "Gym + basketball + souvlaki = my personality. Simple but effective 🏀",
    "Photographer. I'll take photos of you until you get annoyed 📷",
    "Mechanical engineer who fixes everything except his love life 🔧",
    "Doctor in the ER. Yes, the hours are crazy. Yes, it's worth it 🏥",
    "Musician — I play bouzouki and guitar. Serenading is included 🎸",
    "Startup founder running on espresso and ambition ☕ Looking for my co-pilot",
    "Marine officer ⚓ I've seen every port but I'm looking to anchor somewhere",
    "Chef specializing in Mediterranean cuisine. Your taste buds will thank me 🍽️",
    "Civil engineer. I build things — including relationships 🌉",
    "Lawyer who argues for a living but just wants peace at home ⚖️",
    "Football fanatic. Ολυμπιακός/ΠΑΟ/ΑΕΚ fan. Don't @ me ⚽",
    "Teacher who loves hiking and exploring abandoned places 🏚️",
    "DJ spinning tracks and looking for someone to dance with 🎧",
    "Pilot ✈️ I travel for work but I'd rather travel with someone special",
    "Personal trainer. I'll make you stronger in every way 💪",
    "Graphic designer & coffee snob. I judge latte art ☕",
    "Marine biologist diving into the deep blue — and into dating 🤿",
    "Filmmaker. Life is a movie and I'm looking for my leading lady 🎬",
    "Farmer growing olives & dreams in the Greek countryside 🫒",
    "Dentist. Floss daily and we'll get along just fine 🦷",
    "Real estate developer. I specialize in building connections 🏢",
    "Bartender who knows 200 cocktails and zero pickup lines 🍸",
    "Electrical engineer. I bring the spark — literally ⚡",
    "Journalist chasing stories and sometimes chasing sunsets 📝",
    "Psychology student trying to understand people, starting with myself 🧠",
    "Αρχαιολόγος. I spend my days digging up the past, looking for someone to build a future with",
    "Fisherman from a long line of fishermen. I know every hidden cove 🎣",
    "PhD researcher in physics. I can explain the universe but not why I'm still single 🔬",
    "Yoga teacher. Inner peace is real, I promise 🧘‍♂️",
    "Tour guide showing people the real Greece. Let me show you too 🏛️",
    "Wine maker from Nemea 🍷 Third generation. The grapes don't lie",
    "Veterinarian. Animals trust me, so should you 🐕",
    "App developer building the next big thing. Or at least trying 📱",
    "Basketball player. 1.95m tall — yes, the weather up here is nice 🏀",
    "Sailing instructor. The Aegean is my office 🚤",
    "Αγαπώ τη φύση, τα ταξίδια και τα καλά φαγητά. Είμαι απλός αλλά γνήσιος",
    "Coffee shop owner in the heart of the city. First cup is on me ☕",
    "Firefighter 🚒 I save lives for work and I cook for fun",
    "Olive oil producer. My family has 500 trees. Yes, I'm that Greek 🫒",
    "History teacher making the past interesting (hopefully) 📜",
    "Tattoo artist with a story behind every design ✒️",
    "Scuba dive instructor. I'll show you a world you've never seen 🐙",
];

// ── Relationship goals + weights ───────────────────────
$goals = ['long-term', 'short-term', 'friendship', 'casual', 'undecided'];
$goalWeights = [40, 20, 10, 15, 15];

$smokingOpts  = ['never', 'sometimes', 'regularly'];
$smokingW     = [60, 25, 15];
$drinkingOpts = ['never', 'sometimes', 'regularly'];
$drinkingW    = [20, 55, 25];

function weightedRandom(array $items, array $weights): string {
    $total = array_sum($weights);
    $r = mt_rand(1, $total);
    $cumulative = 0;
    foreach ($items as $i => $item) {
        $cumulative += $weights[$i];
        if ($r <= $cumulative) return $item;
    }
    return end($items);
}

function randomDate(string $min, string $max): string {
    $ts = mt_rand(strtotime($min), strtotime($max));
    return date('Y-m-d', $ts);
}

function randomOffset(): float {
    return (mt_rand(-500, 500) / 10000); // ±0.05 degrees (~5km)
}

// ── Build output ───────────────────────────────────────
$sql = "-- Auto-generated: 100 Greek profiles seed data\n";
$sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

$femaleNamesList = array_values($femaleNames);
$femaleEmailNames = array_map(fn($n) => strtolower($n), $femaleNamesList);
$maleNamesList = array_values($maleNames);
$maleEmailNames = array_map(fn($n) => strtolower($n), $maleNamesList);

// 55 female + 45 male = 100
$profiles = [];

// Female profiles
for ($i = 0; $i < 55; $i++) {
    $name = $femaleNamesList[$i % count($femaleNamesList)];
    $emailName = $femaleEmailNames[$i % count($femaleEmailNames)];
    $suffix = $i >= count($femaleNamesList) ? ($i - count($femaleNamesList) + 2) : '';
    $city = $cities[mt_rand(0, count($cities) - 1)];
    $dob = randomDate('1993-01-01', '2004-12-31');
    $goal = weightedRandom($goals, $goalWeights);
    $height = mt_rand(155, 178);
    $smoking = weightedRandom($smokingOpts, $smokingW);
    $drinking = weightedRandom($drinkingOpts, $drinkingW);
    $lookingFor = mt_rand(1, 100) <= 90 ? 'male' : 'everyone';
    $bio = $femaleBios[$i % count($femaleBios)];
    $photoIdx = $i + 16; // seed_woman_16..70
    $isPremium = mt_rand(1, 100) <= 15 ? 1 : 0;
    $createdDaysAgo = mt_rand(1, 60);
    $activeMinsAgo = mt_rand(1, 4320); // up to 3 days

    $profiles[] = [
        'email'     => "{$emailName}{$suffix}" . mt_rand(10, 99) . "@example.gr",
        'name'      => $name,
        'bio'       => $bio,
        'dob'       => $dob,
        'gender'    => 'female',
        'looking_for' => $lookingFor,
        'goal'      => $goal,
        'height'    => $height,
        'smoking'   => $smoking,
        'drinking'  => $drinking,
        'city'      => $city[0],
        'country'   => $city[1],
        'lat'       => round($city[2] + randomOffset(), 7),
        'lng'       => round($city[3] + randomOffset(), 7),
        'photo'     => "uploads/photos/seed_woman_{$photoIdx}.jpg",
        'is_premium'=> $isPremium,
        'created_days_ago' => $createdDaysAgo,
        'active_mins_ago'  => $activeMinsAgo,
    ];
}

// Male profiles
for ($i = 0; $i < 45; $i++) {
    $name = $maleNamesList[$i % count($maleNamesList)];
    $emailName = $maleEmailNames[$i % count($maleEmailNames)];
    $suffix = $i >= count($maleNamesList) ? ($i - count($maleNamesList) + 2) : '';
    $city = $cities[mt_rand(0, count($cities) - 1)];
    $dob = randomDate('1991-01-01', '2003-06-30');
    $goal = weightedRandom($goals, $goalWeights);
    $height = mt_rand(170, 195);
    $smoking = weightedRandom($smokingOpts, $smokingW);
    $drinking = weightedRandom($drinkingOpts, $drinkingW);
    $lookingFor = mt_rand(1, 100) <= 90 ? 'female' : 'everyone';
    $bio = $maleBios[$i % count($maleBios)];
    $photoIdx = $i + 1; // seed_man_1..45
    $isPremium = mt_rand(1, 100) <= 15 ? 1 : 0;
    $createdDaysAgo = mt_rand(1, 60);
    $activeMinsAgo = mt_rand(1, 4320);

    $profiles[] = [
        'email'     => "{$emailName}{$suffix}" . mt_rand(10, 99) . "@example.gr",
        'name'      => $name,
        'bio'       => $bio,
        'dob'       => $dob,
        'gender'    => 'male',
        'looking_for' => $lookingFor,
        'goal'      => $goal,
        'height'    => $height,
        'smoking'   => $smoking,
        'drinking'  => $drinking,
        'city'      => $city[0],
        'country'   => $city[1],
        'lat'       => round($city[2] + randomOffset(), 7),
        'lng'       => round($city[3] + randomOffset(), 7),
        'photo'     => "uploads/photos/seed_man_{$photoIdx}.jpg",
        'is_premium'=> $isPremium,
        'created_days_ago' => $createdDaysAgo,
        'active_mins_ago'  => $activeMinsAgo,
    ];
}

// Shuffle so it's not all females then all males
shuffle($profiles);

$fakePasswordHash = '$2y$10$FAKE.HASH.DO.NOT.USE.FOR.REAL.ACCOUNTS.PLACEHOLDER000';

$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- USERS\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "INSERT INTO users (email, password_hash, status, is_premium, email_verified_at, created_at, last_active_at) VALUES\n";

$userRows = [];
foreach ($profiles as $p) {
    $email = addslashes($p['email']);
    $hash  = addslashes($fakePasswordHash);
    $userRows[] = "('{$email}', '{$hash}', 'active', {$p['is_premium']}, "
        . "NOW() - INTERVAL {$p['created_days_ago']} DAY, "
        . "NOW() - INTERVAL {$p['created_days_ago']} DAY, "
        . "NOW() - INTERVAL {$p['active_mins_ago']} MINUTE)";
}
$sql .= implode(",\n", $userRows) . ";\n\n";

// Variable declarations for IDs
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- FETCH USER IDS\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
foreach ($profiles as $i => $p) {
    $email = addslashes($p['email']);
    $sql .= "SET @u{$i} = (SELECT id FROM users WHERE email = '{$email}');\n";
}
$sql .= "\n";

// Profiles
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- PROFILES\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "INSERT INTO profiles (user_id, name, bio, date_of_birth, gender, looking_for, relationship_goal, height_cm, smoking, drinking, city, country, latitude, longitude) VALUES\n";

$profileRows = [];
foreach ($profiles as $i => $p) {
    $name = addslashes($p['name']);
    $bio  = addslashes($p['bio']);
    $profileRows[] = "(@u{$i}, '{$name}', '{$bio}', '{$p['dob']}', '{$p['gender']}', "
        . "'{$p['looking_for']}', '{$p['goal']}', {$p['height']}, '{$p['smoking']}', "
        . "'{$p['drinking']}', '{$p['city']}', '{$p['country']}', {$p['lat']}, {$p['lng']})";
}
$sql .= implode(",\n", $profileRows) . ";\n\n";

// Photos
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- PHOTOS\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "INSERT INTO photos (user_id, file_path, is_primary) VALUES\n";
$photoRows = [];
foreach ($profiles as $i => $p) {
    $photoRows[] = "(@u{$i}, '{$p['photo']}', 1)";
}
$sql .= implode(",\n", $photoRows) . ";\n\n";

// User scores
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- USER SCORES\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "INSERT INTO user_scores (user_id, elo_score, profile_completeness, photo_count) VALUES\n";
$scoreRows = [];
foreach ($profiles as $i => $p) {
    $elo = mt_rand(850, 1300);
    $completeness = mt_rand(70, 98);
    $scoreRows[] = "(@u{$i}, {$elo}, {$completeness}, 1)";
}
$sql .= implode(",\n", $scoreRows) . ";\n\n";

// User interests (3-7 random interests per user, from IDs 1-38)
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- INTERESTS\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "INSERT INTO user_interests (user_id, interest_id) VALUES\n";
$interestRows = [];
foreach ($profiles as $i => $p) {
    $count = mt_rand(3, 7);
    $picked = [];
    while (count($picked) < $count) {
        $id = mt_rand(1, 38);
        if (!in_array($id, $picked)) $picked[] = $id;
    }
    sort($picked);
    foreach ($picked as $intId) {
        $interestRows[] = "(@u{$i}, {$intId})";
    }
}
$sql .= implode(",\n", $interestRows) . ";\n\n";

// Dealbreakers (only some users, random)
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$sql .= "-- DEALBREAKERS (subset of users)\n";
$sql .= "-- ═══════════════════════════════════════════════════════\n";
$dealRows = [];
foreach ($profiles as $i => $p) {
    if (mt_rand(1, 100) <= 30) { // 30% have a smoking dealbreaker
        $dealRows[] = "(@u{$i}, 'smoking', 'never')";
    }
}
if (!empty($dealRows)) {
    $sql .= "INSERT INTO user_dealbreakers (user_id, field, value) VALUES\n";
    $sql .= implode(",\n", $dealRows) . ";\n";
}

echo $sql;
