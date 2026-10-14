<?php
declare(strict_types=1);

/**
 * Character-name abuse filter (word list + basic patterns).
 *
 * Tiers:
 * - hard: hate / slurs / sexual-exploitative / nazi → reject, do not store
 * - soft: impersonation / spam tokens → may store with is_public=0 (never shown)
 * - allow: normal fantasy names → store public (list still gated by signature threshold)
 *
 * Curated English additions were reviewed against classic-wow-name-filter's
 * namesprofanity.csv (MIT). We do not load the full CSV. See THIRD_PARTY_NOTICES.md.
 */

/**
 * Hard-block terms (whole-word / compact). Never store these names.
 *
 * @return list<string>
 */
function petition_hard_blocked_name_terms(): array
{
    return [
        // Common profanity
        'fuck', 'fucker', 'fucking', 'motherfucker', 'shit', 'bullshit', 'asshole',
        'bastard', 'bitch', 'cunt', 'dick', 'cock', 'pussy', 'twat', 'wank', 'wanker',
        'wankoff', 'piss', 'crap', 'slut', 'whore', 'faggot', 'fag', 'fags', 'dyke', 'tranny',
        // Sexual / pornographic / exploitative
        'porn', 'porno', 'pornstar', 'xxx', 'onlyfans', 'sex', 'sexy', 'nude', 'nudes', 'hentai',
        'cum', 'jizz', 'jizzing', 'penis', 'vagina', 'boob', 'boobs', 'tits', 'titty',
        'pedo', 'pedophile', 'paedo', 'paedophile', 'childporn', 'loli', 'lolicon',
        'shotacon', 'rape', 'rapist', 'raped', 'rapes', 'gangrape',
        // Race / ethnicity / antisemitic slurs and obvious hate (non-exhaustive)
        'nigger', 'nigga', 'niggr', 'niggs', 'kike', 'spic', 'chink', 'chinks', 'gook',
        'wetback', 'coon', 'beaner', 'japs',
        'towelhead', 'raghead', 'sandnigger', 'sheeny', 'heeb', 'kyke',
        'retard', 'retarded', 'nazi', 'hitler', 'heil', 'holocaust',
        // Religion / identity attack tokens (whole-word; keep fantasy names working)
        'christkiller', 'killjews', 'gasjews', 'gasjew', 'jewhater', 'muslimhater',
        'islamhater', 'burnquran', 'burnkoran', 'whitepower', 'heilhitler',
        // Compound abuse handles seen in public name lists (classic-wow curated)
        'annaslut', 'warslut', 'darkwanker', 'chokeoncocks',
    ];
}

/**
 * Soft-quarantine terms: store signature but never show the name publicly.
 *
 * @return list<string>
 */
function petition_soft_quarantine_name_terms(): array
{
    return [
        'admin', 'moderator', 'blizzard', 'support', 'http', 'https', 'www',
    ];
}

/**
 * @deprecated Use petition_hard_blocked_name_terms(); kept for any older callers.
 * @return list<string>
 */
function petition_blocked_name_terms(): array
{
    return array_values(array_unique(array_merge(
        petition_hard_blocked_name_terms(),
        petition_soft_quarantine_name_terms()
    )));
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
 * @param list<string> $terms
 */
function petition_name_matches_terms(string $normalized, string $compact, array $terms): bool
{
    $lookup = array_fill_keys($terms, true);
    if (isset($lookup[$compact])) {
        return true;
    }
    foreach ($terms as $term) {
        if ($term === '') {
            continue;
        }
        if (preg_match('/\b' . preg_quote($term, '/') . '\b/u', $normalized)) {
            return true;
        }
    }
    return false;
}

/**
 * @return array{ok:bool,error?:string,visibility?:string}
 *   visibility: public | hidden (only when ok)
 */
function petition_character_name_check(string $name): array
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

    $compact = preg_replace('/\s+/u', '', $normalized) ?? '';

    // Hard compact denylist catches glued evasion without substring traps like "cock" in "cockatrice".
    $hardCompact = [
        'fuck', 'shit', 'cunt', 'nigger', 'faggot', 'retard', 'porn', 'nazi', 'hitler', 'onlyfans',
        'kike', 'pedo', 'childporn', 'heilhitler', 'killjews', 'gasjew', 'whitepower',
        'towelhead', 'raghead', 'christkiller', 'gangrape', 'beaner', 'holocaust',
        'chokeoncocks', 'warslut', 'darkwanker',
    ];
    foreach ($hardCompact as $term) {
        if (str_contains($compact, $term)) {
            return ['ok' => false, 'error' => 'Use a Forever character name.'];
        }
    }

    if (petition_name_matches_terms($normalized, $compact, petition_hard_blocked_name_terms())) {
        return ['ok' => false, 'error' => 'Use a Forever character name.'];
    }

    // Soft quarantine: looks like a handle, but we still accept the signature privately.
    if (petition_name_matches_terms($normalized, $compact, petition_soft_quarantine_name_terms())) {
        return ['ok' => true, 'visibility' => 'hidden'];
    }

    return ['ok' => true, 'visibility' => 'public'];
}

/**
 * Backward-compatible boolean-style check (treats soft quarantine as allowed).
 *
 * @return array{ok:bool,error?:string}
 */
function petition_character_name_allowed(string $name): array
{
    $check = petition_character_name_check($name);
    if (!$check['ok']) {
        return ['ok' => false, 'error' => $check['error'] ?? 'Use a Forever character name.'];
    }
    return ['ok' => true];
}
