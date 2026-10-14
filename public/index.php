<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$config = petition_config();
$siteKey = $config['turnstile']['site_key'] ?? '';
$appUrl = $config['app_url'];
$privacyContact = $config['privacy_contact'] ?? 'privacy@leavewowbeta.live';

$count = 0;
$recent = [];
$dbError = null;

try {
    $count = petition_signature_count();
    $recent = petition_recent_signatures(12);
} catch (Throwable $e) {
    $dbError = $config['debug'] ? $e->getMessage() : 'Signatures will appear once the database is connected.';
    error_log('petition: page load db error: ' . $e->getMessage());
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$shareLine = 'Leave Wow Beta Live: keep WoW Forever beta online until the November 4 launch instead of shutting it down October 21. Sign here:';
$shareTextX = rawurlencode($shareLine . ' @Warcraft');
$shareUrl = rawurlencode($appUrl);
$shareTextBluesky = rawurlencode($shareLine . ' ' . $appUrl . ' @Warcraft');
$redditTitle = rawurlencode('Leave Wow Beta Live: keep Forever beta online until November 4');
$mailtoSubject = rawurlencode('Leave Wow Beta Live: keep Forever beta until launch');
$mailtoBody = rawurlencode(
    "I signed Leave Wow Beta Live, the community petition asking Blizzard to keep WoW Forever beta online until the November 4 launch.\n\n"
    . $appUrl . "\n\n"
    . "Suggested post:\n"
    . $shareLine . ' ' . $appUrl . " @Warcraft\n"
);
$suggestedCopy = $shareLine . ' ' . $appUrl . ' @Warcraft';
$ogImage = rtrim($appUrl, '/') . '/assets/img/og-cinematic.jpg';
$pageTitle = 'Leave Wow Beta Live | Keep WoW Forever Beta Online Until Nov 4';
$pageDescription = 'Leave Wow Beta Live asks Blizzard to keep WoW Forever beta online after the October 21 take-down through the November 4 launch. Sign the petition at leavewowbeta.live.';
$jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => rtrim($appUrl, '/') . '/#organization',
            'name' => 'Leave Wow Beta Live',
            'url' => $appUrl,
            'description' => $pageDescription,
            'logo' => rtrim($appUrl, '/') . '/assets/img/icon-192.png',
        ],
        [
            '@type' => 'WebSite',
            '@id' => rtrim($appUrl, '/') . '/#website',
            'url' => $appUrl,
            'name' => 'Leave Wow Beta Live',
            'description' => $pageDescription,
            'inLanguage' => 'en',
            'publisher' => ['@id' => rtrim($appUrl, '/') . '/#organization'],
        ],
        [
            '@type' => 'WebPage',
            '@id' => rtrim($appUrl, '/') . '/#webpage',
            'url' => $appUrl,
            'name' => $pageTitle,
            'isPartOf' => ['@id' => rtrim($appUrl, '/') . '/#website'],
            'description' => $pageDescription,
            'primaryImageOfPage' => ['@id' => rtrim($appUrl, '/') . '/#primaryimage'],
            'dateModified' => gmdate('c'),
            'inLanguage' => 'en',
            'about' => [
                '@type' => 'Thing',
                'name' => 'Keep World of Warcraft Forever beta online until November 4 launch',
            ],
        ],
        [
            '@type' => 'ImageObject',
            '@id' => rtrim($appUrl, '/') . '/#primaryimage',
            'url' => $ogImage,
            'width' => 1200,
            'height' => 500,
        ],
        [
            '@type' => 'FAQPage',
            '@id' => rtrim($appUrl, '/') . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'When does the WoW Forever beta end?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Blizzard has said October 21 is the last full day of Forever beta testing before final launch preparations. Leave Wow Beta Live asks them to keep the beta servers online until the November 4 launch instead.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Is there a gap between Forever beta and launch?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. If the beta comes down on October 21 and Forever launches on November 4, players face almost two weeks offline with nowhere to keep testing. This petition asks Blizzard to close that gap by keeping beta up.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'What is Leave Wow Beta Live?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Leave Wow Beta Live (leavewowbeta.live) is an independent community petition asking Blizzard to keep World of Warcraft: Forever beta online until launch. It is not affiliated with Blizzard. Emails stay private; character names may appear in the public signature list.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How do I ask Blizzard to keep Forever beta online?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Sign at leavewowbeta.live with your email and Forever character name, then share the link on X, Reddit, Bluesky, or the WoW forums. Tag @Warcraft when it fits and keep the ask specific: keep beta live from October 21 through November 4.',
                    ],
                ],
            ],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="theme-color" content="#05070b">
  <meta name="color-scheme" content="dark">
  <meta property="og:site_name" content="Leave Wow Beta Live">
  <meta property="og:locale" content="en_US">
  <meta property="og:title" content="Leave Wow Beta Live | Keep WoW Forever beta online until Nov 4">
  <meta property="og:description" content="October 21 beta take-down, November 4 launch. Ask Blizzard to keep Forever beta live through that gap. Sign at leavewowbeta.live.">
  <meta property="og:url" content="<?= e($appUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:image:alt" content="World of Warcraft Forever cinematic still for the Leave Wow Beta Live petition">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="500">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Leave Wow Beta Live | Keep Forever beta online until Nov 4">
  <meta name="twitter:description" content="Petition Blizzard to keep WoW Forever beta up after October 21 through the November 4 launch.">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
  <meta name="twitter:image:alt" content="World of Warcraft Forever cinematic still for the Leave Wow Beta Live petition">
  <link rel="canonical" href="<?= e($appUrl) ?>">
  <link rel="sitemap" type="application/xml" href="<?= e(rtrim($appUrl, '/') . '/sitemap.xml') ?>">
  <link rel="icon" href="/assets/img/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon-32.png">
  <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css?v=13">
  <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <?php if ($siteKey !== ''): ?>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <?php endif; ?>
</head>
<body>
  <a class="skip-link" href="#sign">Skip to petition form</a>
  <header class="site-nav">
    <a class="site-nav__brand" href="#top">
      <span class="site-nav__name">Leave Wow Beta Live</span>
      <span class="site-nav__domain">leavewowbeta.live</span>
    </a>
    <nav class="site-nav__links" aria-label="Page">
      <a href="#why">Why</a>
      <a href="#sign">Sign</a>
      <a href="#voices">Voices</a>
      <a href="#reach">Reach</a>
      <a href="#faq">FAQ</a>
    </nav>
  </header>

  <main id="top">
    <section class="hero" aria-labelledby="hero-brand">
      <div class="hero__media">
        <picture>
          <source srcset="/assets/img/hero-key-art-alliance.webp" type="image/webp">
          <img
            src="/assets/img/hero-key-art-alliance.jpg"
            alt="World of Warcraft Forever Alliance key art from the official press kit"
            width="1920"
            height="1317"
            fetchpriority="high"
            decoding="async"
          >
        </picture>
        <div class="hero__veil" aria-hidden="true"></div>
      </div>
      <div class="hero__content">
        <p class="hero__brand" id="hero-brand">
          <img
            class="hero__logo"
            src="/assets/img/wow-forever-logo-white.png"
            alt="World of Warcraft Forever"
            width="720"
            height="405"
            decoding="async"
          >
          <span class="hero__site">Leave Wow Beta Live</span>
        </p>
        <h1 class="hero__title">Keep WoW Forever beta live until launch</h1>
        <p class="hero__lede">
          Forever beta has been worth the time. Blizzard plans to take it down on October&nbsp;21, with launch on November&nbsp;4. That is almost two weeks with nowhere to play or test. Leave Wow Beta Live asks them to keep the servers up.
        </p>
        <p class="hero__dates" aria-label="Key dates">
          <span>Oct&nbsp;21 take-down</span>
          <span class="hero__dates-sep" aria-hidden="true"></span>
          <span>Nov&nbsp;4 launch</span>
        </p>
        <div class="hero__actions">
          <a class="btn btn--primary" href="#sign">Sign the petition</a>
          <a class="btn btn--ghost" href="#why">Why it matters</a>
        </div>
        <p class="hero__meta">
          <span class="hero__count" data-count="<?= (int) $count ?>"><?= number_format($count) ?></span>
          <?php if ((int) $count === 0): ?>
            signatures so far. Be among the first Forever players to ask.
          <?php else: ?>
            signatures so far
          <?php endif; ?>
        </p>
      </div>
    </section>

    <section class="band band--why" id="why" aria-labelledby="why-title">
      <div class="band__media">
        <picture>
          <source srcset="/assets/img/atmosphere-ashenvale.webp" type="image/webp">
          <img src="/assets/img/atmosphere-ashenvale.jpg" alt="Quiet Ashenvale scenic from the WoW Forever press kit" width="1600" height="900" loading="lazy" decoding="async">
        </picture>
        <div class="band__veil band__veil--why" aria-hidden="true"></div>
      </div>
      <div class="band__inner">
        <div class="band__copy">
          <p class="section__eyebrow">Why we’re asking</p>
          <h2 id="why-title">Why keep Forever beta online after October&nbsp;21?</h2>
          <p class="section__lede">
            Players are already in Forever beta logging bugs and learning the new systems. Shutting the servers down before the November&nbsp;4 launch cuts that short and leaves a dead week-plus offline.
          </p>
          <div class="why-note">
            <p>
              Keep the servers up and the final stretch stays useful: people keep playing, groups stay together, and issues that only show under real load still have a chance to get caught before November&nbsp;4.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="section section--sign" id="sign" aria-labelledby="sign-title">
      <div class="section__inner sign-split">
        <figure class="sign-split__visual">
          <picture>
            <source srcset="/assets/img/scenic-camping.webp" type="image/webp">
            <img
              src="/assets/img/scenic-camping.jpg"
              alt="Players gathered at a camp from the official Forever press kit"
              width="1600"
              height="900"
              loading="lazy"
              decoding="async"
            >
          </picture>
        </figure>
        <div class="sign-split__form">
          <p class="section__eyebrow">Sign</p>
          <h2 id="sign-title">Sign the petition to keep Forever beta online</h2>
          <p class="section__lede">
            Email stays private. Your character name appears with the other signatures. This list is how we show Blizzard that players want Forever beta access through launch.
          </p>

          <?php if ($dbError !== null): ?>
            <p class="form-banner form-banner--warn" role="status"><?= e($dbError) ?></p>
          <?php endif; ?>

          <form class="petition-form" id="petition-form" action="/api/sign.php" method="post" novalidate>
            <div class="hp" aria-hidden="true">
              <label for="website">Website</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="field">
              <label for="email">Email <span class="req">required</span></label>
              <input type="email" id="email" name="email" required maxlength="255" autocomplete="email" placeholder="name@example.com">
              <p class="field__hint">Only for verifying and delivering this petition. Never shown publicly.</p>
              <p class="field__error" data-error-for="email" hidden></p>
            </div>

            <div class="field">
              <label for="character_name">Character name <span class="req">required</span></label>
              <input type="text" id="character_name" name="character_name" required maxlength="32" autocomplete="nickname" placeholder="Character name" spellcheck="false">
              <p class="field__hint">As it appears in Forever.</p>
              <p class="field__error" data-error-for="character_name" hidden></p>
            </div>

            <?php if ($siteKey !== ''): ?>
              <div class="turnstile-wrap">
                <div class="cf-turnstile" data-sitekey="<?= e($siteKey) ?>" data-theme="dark"></div>
              </div>
            <?php else: ?>
              <p class="form-banner form-banner--warn" role="status">
                Turnstile site key is not configured yet. Set <code>TURNSTILE_SITE_KEY</code> before going live.
              </p>
            <?php endif; ?>

            <button type="submit" class="btn btn--primary btn--block" id="submit-btn">
              Sign the petition
            </button>

            <p class="form-status" id="form-status" role="status" aria-live="polite" hidden></p>
          </form>

          <p class="sign-split__count">
            <span class="sign-split__count-num" data-sign-count="<?= (int) $count ?>"><?= number_format($count) ?></span>
            on the petition
          </p>
        </div>
      </div>
    </section>

    <section class="band band--voices" id="voices" aria-labelledby="voices-title">
      <div class="band__media">
        <picture>
          <source srcset="/assets/img/scenic-camping-alt.webp" type="image/webp">
          <img src="/assets/img/scenic-camping-alt.jpg" alt="Players camping together in Forever from the official press kit" width="1600" height="900" loading="lazy" decoding="async">
        </picture>
        <div class="band__veil band__veil--voices" aria-hidden="true"></div>
      </div>
      <div class="band__inner band__inner--voices">
        <div class="band__copy">
          <p class="section__eyebrow">Signatures</p>
          <h2 id="voices-title">Players who signed Leave Wow Beta Live</h2>
          <p class="section__lede">
            <span id="live-count"><?= number_format($count) ?></span> character names so far. Newest first.
          </p>
          <ol class="sig-list" id="sig-list">
            <?php if ($recent === []): ?>
              <li class="sig-list__empty" id="sig-empty">
                No names yet.
                <a href="#sign">Sign with your Forever character</a>
                and open the list.
              </li>
            <?php else: ?>
              <?php foreach ($recent as $row): ?>
                <li>
                  <span class="sig-list__name"><?= e($row['character_name']) ?></span>
                  <time datetime="<?= e($row['created_at']) ?>"><?= e(petition_format_relative_time($row['created_at'])) ?></time>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ol>
        </div>
      </div>
    </section>

    <section class="band band--reach" id="reach" aria-labelledby="reach-title">
      <div class="band__media">
        <picture>
          <source srcset="/assets/img/cinematic-still-2.webp" type="image/webp">
          <img src="/assets/img/cinematic-still-2.jpg" alt="Forever cinematic still suggesting launch urgency from the press kit" width="1600" height="666" loading="lazy" decoding="async">
        </picture>
        <div class="band__veil band__veil--reach" aria-hidden="true"></div>
      </div>
      <div class="band__inner">
        <header class="reach-header">
          <p class="section__eyebrow">Share</p>
          <h2 id="reach-title">Share the ask to keep Forever beta online</h2>
          <p class="section__lede section__lede--wide">
            A petition page alone rarely moves a publisher. Share Leave Wow Beta Live, keep the tone straight, and name the dates: October&nbsp;21 beta take-down, November&nbsp;4 launch. This site is independent and unaffiliated.
          </p>
          <p class="reach-hint">
            Lead with the link, the Oct&nbsp;21 / Nov&nbsp;4 ask, and <a href="https://x.com/Warcraft" target="_blank" rel="noopener noreferrer">@Warcraft</a> when it fits. On X, replies under Forever posts matter more than tags. Skip tags on Reddit.
          </p>
        </header>

        <div class="reach-panel">
          <div class="share-row">
            <input type="text" class="share-row__url" id="share-url" value="<?= e($appUrl) ?>" readonly aria-label="Petition URL">
            <button type="button" class="btn btn--ghost" id="copy-link">Copy link</button>
          </div>

          <div class="share-actions" role="group" aria-label="Share this petition">
            <a
              class="share-btn"
              href="https://x.com/intent/post?text=<?= $shareTextX ?>&url=<?= $shareUrl ?>"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg class="share-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.727-8.828L1.99 2.25H8.08l4.253 5.622L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
              <span>X</span>
            </a>
            <a
              class="share-btn"
              href="https://www.reddit.com/submit?url=<?= $shareUrl ?>&title=<?= $redditTitle ?>"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg class="share-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.48 2 2 6.24 2 11.5c0 3.17 1.9 5.93 4.68 7.36-.2-.7-.38-1.79.08-2.56.42-.7 2.72-1.76 2.72-1.76s-.16-.7-.16-1.72c0-1.61.94-2.81 2.1-2.81 1 0 1.48.74 1.48 1.63 0 1-.64 2.49-.97 3.87-.28 1.16.59 2.11 1.75 2.11 2.1 0 3.52-2.69 3.52-5.86 0-2.42-1.63-4.23-4.6-4.23-3.35 0-5.43 2.49-5.43 5.28 0 .96.28 1.64.73 2.17.08.1.09.18.07.28l-.27 1.1c-.04.18-.14.22-.32.13-1.2-.55-1.76-2.02-1.76-3.67 0-2.73 2.3-6 6.87-6 3.7 0 6.15 2.68 6.15 5.56 0 3.8-2.11 6.64-5.23 6.64-1.05 0-2.03-.57-2.37-1.22l-.64 2.45c-.23.9-.86 2.03-1.28 2.72.96.3 1.98.46 3.04.46 5.52 0 10-4.24 10-9.5S17.52 2 12 2z"/></svg>
              <span>Reddit</span>
            </a>
            <a
              class="share-btn"
              href="https://bsky.app/intent/compose?text=<?= $shareTextBluesky ?>"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg class="share-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 10.8c-1.09-2.12-4.05-6.07-6.8-8.01C3.34.98 2.5 1.51 2.5 3.12c0 .72.38 6.02.62 6.9.8 2.9 3.42 3.88 5.86 3.57-4.2.62-5.6 2.66-3.15 4.72 2.63 2.22 6.68-.22 6.68-.22s4.05 2.44 6.68.22c2.45-2.06 1.05-4.1-3.15-4.72 2.44.31 5.06-.67 5.86-3.57.24-.88.62-6.18.62-6.9 0-1.61-.84-2.14-2.7-.33-2.75 1.94-5.71 5.89-6.8 8.01z"/></svg>
              <span>Bluesky</span>
            </a>
          </div>
        </div>

        <div class="reach-grid">
          <div class="reach-block">
            <h3>Suggested post</h3>
            <p class="suggested-copy" id="suggested-copy"><?= e($suggestedCopy) ?></p>
            <button type="button" class="btn btn--ghost btn--compact" id="copy-suggested">Copy text</button>
          </div>
          <div class="reach-block">
            <h3>Where else to send it</h3>
            <ul class="reach-list">
              <li>
                Post and tag
                <a href="https://x.com/Warcraft" target="_blank" rel="noopener noreferrer">@Warcraft</a>
                when it fits. Stay specific about the October&nbsp;21 / November&nbsp;4 gap.
              </li>
              <li>
                Send polite product feedback through
                <a href="https://us.battle.net/support/en/" target="_blank" rel="noopener noreferrer">Blizzard Support</a>
                or in-game (ESC, Support, submit feedback). Not a spam channel.
              </li>
              <li>
                Start or join a thread on the
                <a href="https://us.forums.blizzard.com/en/wow/" target="_blank" rel="noopener noreferrer">WoW forums</a>
                or community megathreads. Link the petition. Do not claim official status.
              </li>
              <li>
                <a href="mailto:?subject=<?= $mailtoSubject ?>&body=<?= $mailtoBody ?>">Email yourself</a>
                a draft to forward to friends.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="section section--faq" id="faq" aria-labelledby="faq-title">
      <div class="section__inner faq-inner">
        <p class="section__eyebrow">Common questions</p>
        <h2 id="faq-title">WoW Forever beta dates and this petition</h2>
        <p class="section__lede section__lede--wide">
          Straight answers for people searching when Forever beta ends, whether there is a gap before launch, and what Leave Wow Beta Live is asking for.
        </p>
        <div class="faq-list">
          <details class="faq-item" open>
            <summary>When does the WoW Forever beta end?</summary>
            <p>
              Blizzard has called October&nbsp;21 the last full day of Forever beta testing before final launch preparations. Exact shutdown hour can vary by region. Leave Wow Beta Live asks Blizzard to keep the beta servers online until the November&nbsp;4 launch instead of going dark after October&nbsp;21.
            </p>
          </details>
          <details class="faq-item">
            <summary>Is there a gap between Forever beta and the November&nbsp;4 launch?</summary>
            <p>
              Yes. October&nbsp;21 to November&nbsp;4 is almost two weeks. If beta comes down on schedule, players have nowhere to keep testing through that stretch. That gap is the whole reason for this petition.
            </p>
          </details>
          <details class="faq-item">
            <summary>What is Leave Wow Beta Live?</summary>
            <p>
              Leave Wow Beta Live (leavewowbeta.live) means keep Forever beta live. It is an independent community petition, not a Blizzard site. Sign with email and your Forever character name. Emails stay private.
            </p>
          </details>
          <details class="faq-item">
            <summary>How do I ask Blizzard to keep Forever beta online?</summary>
            <p>
              <a href="#sign">Sign the petition</a>, then share the link on X, Reddit, Bluesky, or the
              <a href="https://us.forums.blizzard.com/en/wow/" target="_blank" rel="noopener noreferrer">WoW forums</a>.
              Tag <a href="https://x.com/Warcraft" target="_blank" rel="noopener noreferrer">@Warcraft</a> when it fits. Stay specific about keeping beta up from October&nbsp;21 through November&nbsp;4. Plain language beats hashtag piles.
            </p>
          </details>
        </div>
      </div>
    </section>

    <section class="section section--about" id="legal" aria-labelledby="about-title">
      <div class="section__inner about-split">
        <figure class="about-split__visual">
          <picture>
            <source srcset="/assets/img/zone-zephras.webp" type="image/webp">
            <img src="/assets/img/zone-zephras.jpg" alt="Zephras settlement scenic from the official Forever press kit" width="1600" height="900" loading="lazy" decoding="async">
          </picture>
        </figure>
        <div class="about-split__copy site-footer">
          <h2 class="site-footer__title" id="about-title">About this petition</h2>
          <p>
            <strong>Not affiliated.</strong>
            Leave Wow Beta Live (leavewowbeta.live) is an independent, fan-run community petition asking Blizzard to keep Forever beta online until launch. It is not affiliated with Blizzard Entertainment, World of Warcraft, or Forever.
          </p>
          <p>
            <strong>Privacy.</strong>
            Emails are never sold and never shown publicly. Character names may appear in the public list.
            Questions or deletion requests:
            <a href="mailto:<?= e($privacyContact) ?>"><?= e($privacyContact) ?></a>.
          </p>

          <details class="legal-more">
            <summary>More legal and privacy</summary>
            <div class="legal-more__body">
              <p>
                World of Warcraft®, Blizzard Entertainment®, and related marks are trademarks of their respective owners.
                Press imagery on this page comes from Blizzard’s official
                <a href="https://blizzard.gamespress.com/wowforeverreveal" rel="noopener noreferrer">Forever press / media kit</a>
                where applicable.
              </p>
              <p>
                Emails are used only to run, verify, and deliver this petition (authenticity, counting, and possible later delivery to Blizzard).
                Uniqueness is enforced with a server-side email hash; the email itself is stored only on the server.
              </p>
              <p>
                If you are in the EEA, UK, California, or a similar jurisdiction, you may request access to or deletion of your signature data by emailing
                <a href="mailto:<?= e($privacyContact) ?>"><?= e($privacyContact) ?></a>.
              </p>
            </div>
          </details>
        </div>
      </div>
    </section>
  </main>

  <a class="mobile-sign" href="#sign">Sign the petition</a>

  <script src="/assets/js/petition.js?v=13" defer></script>
</body>
</html>
