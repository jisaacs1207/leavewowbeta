<?php
declare(strict_types=1);

/**
 * Lightweight character-name abuse filter (word list + basic patterns).
 * Intended to block obvious profanity/slurs/spam without rejecting normal fantasy names.
 */

/**
 * @return list<string>
 */
function petition_blocked_name_terms(): array
{
    // Lowercase terms matched as whole words after normalization.
    // Keep this list short and obvious; avoid substring traps for fantasy names.
    return [
        // Common profanity
        'fuck', 'fucker', 'fucking', 'motherfucker', 'shit', 'bullshit', 'asshole',
        'bastard', 'bitch', 'cunt', 'dick', 'cock', 'pussy', 'twat', 'wank', 'wanker',
        'piss', 'crap', 'slut', 'whore', 'faggot', 'fag', 'dyke', 'tranny',
        // Sexual / pornographic / exploitative
        'porn', 'porno', 'xxx', 'onlyfans', 'sex', 'sexy', 'nude', 'nudes', 'hentai',
        'cum', 'jizz', 'penis', 'vagina', 'boob', 'boobs', 'tits', 'titty',
        'pedo', 'pedophile', 'paedo', 'paedophile', 'childporn', 'loli', 'lolicon',
        'shotacon', 'rape', 'rapist',
        // Race / ethnicity / antisemitic slurs and obvious hate (non-exhaustive)
        'nigger', 'nigga', 'kike', 'spic', 'chink', 'gook', 'wetback', 'coon',
        'towelhead', 'raghead', 'sandnigger', 'sheeny', 'heeb', 'kyke',
        'retard', 'retarded', 'nazi', 'hitler', 'heil',
        // Religion / identity attack tokens (whole-word; keep fantasy names working)
        'christkiller', 'killjews', 'gasjews', 'gasjew', 'jewhater', 'muslimhater',
        'islamhater', 'burnquran', 'burnkoran', 'whitepower', 'heilhitler',
        // Spam-ish handles that show up as "names"
        'admin', 'moderator', 'blizzard', 'support', 'http', 'https', 'www',
    ];
}

function petition_normalize_name_for_filter(string $name): string
{
    $name = mb_strtolower($name, 'UTF-8');
    // Map common leetspeak so f0ck / sh1t still match.
    $map = [
        '0' => 'o',
        '1' => 'i',
        '3' => 'e',
        '4' => 'a',
        '5' => 's',
        '7' => 't',
        '@' => 'a',
        '$' => 's',
    ];
    $name = strtr($name, $map);
    // Collapse separators so "f.u.c.k" / "f-u-c-k" still match as a token stream.
    $name = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $name) ?? '';
    $name = preg_replace('/\s+/u', ' ', trim($name)) ?? '';
    return $name;
}

/**
 * @return array{ok:bool,error?:string}
 */
function petition_character_name_allowed(string $name): array
{
    $raw = trim($name);
    if ($raw === '') {
        return ['ok' => false, 'error' => 'Use a Forever character name.'];
    }

    // Pattern spam even if letter-only regex later fails: URLs, emails, junk runs.
    if (preg_match('/https?:\/\/|www\.|@|\.(com|net|org|gg|io|tv|xyz)\b/i', $raw)) {
        return ['ok' => false, 'error' => 'Use a Forever character name.'];
    }
    if (preg_match('/(.)\1{5,}/u', $raw)) {
        return ['ok' => false, 'error' => 'Use a Forever character name.'];
    }

    $normalized = petition_normalize_name_for_filter($raw);
    if ($normalized === '') {
        return ['ok' => false, 'error' => 'Use a Forever character name.'];
    }

    // Compact form catches spaced/punctuated evasion (e.g. "f u c k") without
    // substring traps like "cock" inside "cockatrice".
    $compact = preg_replace('/\s+/u', '', $normalized) ?? '';
    $blocked = petition_blocked_name_terms();
    $blockedLookup = array_fill_keys($blocked, true);

    if (isset($blockedLookup[$compact])) {
        return ['ok' => false, 'error' => 'Use a Forever character name.'];
    }

    foreach ($blocked as $term) {
        if ($term === '') {
            continue;
        }
        if (preg_match('/\b' . preg_quote($term, '/') . '\b/u', $normalized)) {
            return ['ok' => false, 'error' => 'Use a Forever character name.'];
        }
    }

    // Catch glued evasion (e.g. "fuckyou") without substring traps like "cock" in "cockatrice".
    $compactDenylist = [
        'fuck', 'shit', 'cunt', 'nigger', 'faggot', 'retard', 'porn', 'nazi', 'hitler', 'onlyfans',
        'kike', 'pedo', 'childporn', 'heilhitler', 'killjews', 'gasjew', 'whitepower',
        'towelhead', 'raghead', 'christkiller',
    ];
    foreach ($compactDenylist as $term) {
        if (str_contains($compact, $term)) {
            return ['ok' => false, 'error' => 'Use a Forever character name.'];
        }
    }

    return ['ok' => true];
}
