<?php
namespace App\Models;

use App\Core\Model;

class DateIdea extends Model
{
    /**
     * Date idea templates keyed by interest category.
     * Each has a title, description template, and emoji.
     */
    private static array $templates = [
        'food_drink' => [
            ['emoji' => '☕', 'title' => 'Cozy Café Date', 'desc' => 'You both love {interests} — find a cozy indie café to explore together.'],
            ['emoji' => '🍳', 'title' => 'Cooking Class', 'desc' => 'Take a cooking class together and bond over making something delicious.'],
            ['emoji' => '🍷', 'title' => 'Wine & Dine Tasting', 'desc' => 'Try a wine or food tasting event — perfect for adventurous foodies.'],
        ],
        'outdoors' => [
            ['emoji' => '🥾', 'title' => 'Scenic Hike', 'desc' => 'Hit a local trail together — you both enjoy {interests}.'],
            ['emoji' => '🚴', 'title' => 'Bike & Picnic', 'desc' => 'Rent bikes and pack a picnic in a nearby park.'],
            ['emoji' => '🌅', 'title' => 'Sunset Spot', 'desc' => 'Find a scenic viewpoint and watch the sunset together.'],
        ],
        'arts_culture' => [
            ['emoji' => '🎨', 'title' => 'Gallery Hop', 'desc' => 'Explore local art galleries — you both appreciate {interests}.'],
            ['emoji' => '🎭', 'title' => 'Live Performance', 'desc' => 'Catch a local theatre show, comedy night, or live music gig.'],
            ['emoji' => '📚', 'title' => 'Bookstore Café', 'desc' => 'Browse an indie bookstore, then grab coffee and share your picks.'],
        ],
        'music' => [
            ['emoji' => '🎵', 'title' => 'Live Music Night', 'desc' => 'You both love music — check out a local live music venue or open mic.'],
            ['emoji' => '🎧', 'title' => 'Vinyl & Coffee', 'desc' => 'Browse a record shop and share your favorite albums over coffee.'],
            ['emoji' => '🎤', 'title' => 'Karaoke Night', 'desc' => 'Belt out your favorites at a karaoke bar — no judgment!'],
        ],
        'fitness' => [
            ['emoji' => '🧘', 'title' => 'Yoga in the Park', 'desc' => 'Join a free outdoor yoga session or work out together.'],
            ['emoji' => '🏃', 'title' => 'Fun Run', 'desc' => 'Sign up for a local fun run or do a fitness challenge together.'],
            ['emoji' => '🧗', 'title' => 'Climbing Wall', 'desc' => 'Try indoor rock climbing — great for building trust and teamwork!'],
        ],
        'entertainment' => [
            ['emoji' => '🎮', 'title' => 'Arcade / Board Game Café', 'desc' => 'Challenge each other at a retro arcade or board game café.'],
            ['emoji' => '🎬', 'title' => 'Indie Film Night', 'desc' => 'Watch an indie film at a local cinema, then discuss over snacks.'],
            ['emoji' => '🎳', 'title' => 'Bowling Night', 'desc' => 'Friendly competition at the bowling alley — loser buys drinks!'],
        ],
        'default' => [
            ['emoji' => '🌮', 'title' => 'Street Food Walk', 'desc' => 'Explore the local food scene together — try something new!'],
            ['emoji' => '🛍️', 'title' => 'Market Stroll', 'desc' => 'Visit a farmers market or flea market and people-watch.'],
            ['emoji' => '🧋', 'title' => 'Coffee & Conversation', 'desc' => 'Keep it simple — a great coffee shop and real conversation.'],
        ],
    ];

    /**
     * Map interest names to categories for matching.
     */
    private static array $interestCategoryMap = [
        'Coffee' => 'food_drink', 'Cooking' => 'food_drink', 'Foodie' => 'food_drink',
        'Wine' => 'food_drink', 'Beer' => 'food_drink', 'Brunch' => 'food_drink',
        'Tea' => 'food_drink', 'Baking' => 'food_drink', 'Vegan' => 'food_drink',
        'Hiking' => 'outdoors', 'Camping' => 'outdoors', 'Nature' => 'outdoors',
        'Travel' => 'outdoors', 'Cycling' => 'outdoors', 'Surfing' => 'outdoors',
        'Skiing' => 'outdoors', 'Beach' => 'outdoors', 'Gardening' => 'outdoors',
        'Art' => 'arts_culture', 'Photography' => 'arts_culture', 'Reading' => 'arts_culture',
        'Writing' => 'arts_culture', 'Theatre' => 'arts_culture', 'Museums' => 'arts_culture',
        'Poetry' => 'arts_culture', 'Design' => 'arts_culture', 'History' => 'arts_culture',
        'Music' => 'music', 'Concerts' => 'music', 'Singing' => 'music',
        'Guitar' => 'music', 'Piano' => 'music', 'Dancing' => 'music', 'DJ' => 'music',
        'Yoga' => 'fitness', 'Gym' => 'fitness', 'Running' => 'fitness',
        'Swimming' => 'fitness', 'Climbing' => 'fitness', 'CrossFit' => 'fitness',
        'Martial Arts' => 'fitness', 'Tennis' => 'fitness', 'Basketball' => 'fitness',
        'Gaming' => 'entertainment', 'Movies' => 'entertainment', 'Anime' => 'entertainment',
        'Board Games' => 'entertainment', 'Netflix' => 'entertainment', 'Comedy' => 'entertainment',
    ];

    /**
     * Generate 3 date ideas for a match based on shared interests and locations.
     */
    public static function generate(int $userId1, int $userId2): array
    {
        // Get shared interests
        $stmt = static::db()->query(
            "SELECT i.name, i.category FROM user_interests ui1
             JOIN user_interests ui2 ON ui1.interest_id = ui2.interest_id AND ui2.user_id = ?
             JOIN interests i ON i.id = ui1.interest_id
             WHERE ui1.user_id = ?",
            [$userId2, $userId1]
        );
        $sharedInterests = $stmt->fetchAll();
        $sharedNames = array_column($sharedInterests, 'name');

        // Get both users' locations
        $stmt1 = static::db()->query(
            "SELECT latitude, longitude, city FROM profiles WHERE user_id = ?", [$userId1]
        );
        $loc1 = $stmt1->fetch();

        $stmt2 = static::db()->query(
            "SELECT latitude, longitude, city FROM profiles WHERE user_id = ?", [$userId2]
        );
        $loc2 = $stmt2->fetch();

        // Calculate midpoint
        $midpoint = null;
        if ($loc1 && $loc2 && $loc1['latitude'] && $loc2['latitude']) {
            $midpoint = [
                'lat' => ($loc1['latitude'] + $loc2['latitude']) / 2,
                'lng' => ($loc1['longitude'] + $loc2['longitude']) / 2,
            ];
        }

        $locationHint = '';
        if ($loc1 && $loc2 && $loc1['city'] && $loc2['city']) {
            if ($loc1['city'] === $loc2['city']) {
                $locationHint = 'in ' . $loc1['city'];
            } else {
                $locationHint = 'halfway between ' . $loc1['city'] . ' and ' . $loc2['city'];
            }
        } elseif ($loc1 && $loc1['city']) {
            $locationHint = 'near ' . $loc1['city'];
        }

        // Determine relevant categories from shared interests
        $cats = [];
        foreach ($sharedNames as $name) {
            $cat = self::$interestCategoryMap[$name] ?? null;
            if ($cat && !in_array($cat, $cats, true)) {
                $cats[] = $cat;
            }
        }

        // Build ideas: pick from matched categories, pad with defaults
        $ideas = [];
        $usedTemplates = [];

        foreach ($cats as $cat) {
            if (count($ideas) >= 3) break;
            $pool = self::$templates[$cat] ?? [];
            foreach ($pool as $tpl) {
                if (in_array($tpl['title'], $usedTemplates, true)) continue;
                $idea = self::buildIdea($tpl, $sharedNames, $locationHint);
                $ideas[] = $idea;
                $usedTemplates[] = $tpl['title'];
                break;
            }
        }

        // Fill remaining with defaults
        if (count($ideas) < 3) {
            foreach (self::$templates['default'] as $tpl) {
                if (count($ideas) >= 3) break;
                if (in_array($tpl['title'], $usedTemplates, true)) continue;
                $idea = self::buildIdea($tpl, $sharedNames, $locationHint);
                $ideas[] = $idea;
                $usedTemplates[] = $tpl['title'];
            }
        }

        return [
            'ideas' => $ideas,
            'shared_interests' => $sharedNames,
            'location_hint' => $locationHint,
            'midpoint' => $midpoint,
        ];
    }

    private static function buildIdea(array $tpl, array $sharedNames, string $locationHint): array
    {
        $interestStr = !empty($sharedNames) ? implode(', ', array_slice($sharedNames, 0, 3)) : 'trying new things';
        $desc = str_replace('{interests}', $interestStr, $tpl['desc']);
        if ($locationHint) {
            $desc .= ' Look for spots ' . $locationHint . '.';
        }

        return [
            'emoji' => $tpl['emoji'],
            'title' => $tpl['title'],
            'description' => $desc,
        ];
    }
}
