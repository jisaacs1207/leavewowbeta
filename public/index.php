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

$shareLine = 'Keep WoW Forever beta live until the November 4 launch. Players do not want a nearly two-week gap after October 21. Sign and amplify:';
$shareTextX = rawurlencode($shareLine . ' @Warcraft');
$shareUrl = rawurlencode($appUrl);
$shareTextBluesky = rawurlencode($shareLine . ' ' . $appUrl . ' @Warcraft');
$redditTitle = rawurlencode('Keep WoW Forever beta live until the November 4 launch');
$mailtoSubject = rawurlencode('Keep Forever beta live until launch');
$mailtoBody = rawurlencode(
    "I signed the community petition asking Blizzard to leave WoW Forever beta online until the November 4 launch.\n\n"
    . $appUrl . "\n\n"
    . "Suggested post:\n"
    . $shareLine . ' ' . $appUrl . " @Warcraft\n"
);
$suggestedCopy = $shareLine . ' ' . $appUrl . ' @Warcraft';
$ogImage = rtrim($appUrl, '/') . '/assets/img/og-cinematic.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>leavewowbeta.live · Keep Forever Beta Until Launch</title>
  <meta name="description" content="Petition Blizzard to leave World of Warcraft: Forever beta online until the November 4th launch. No two-week gap.">
  <meta property="og:title" content="Keep WoW Forever beta live until November 4">
  <meta property="og:description" content="Players are asking Blizzard not to shut down Forever beta on October 21. Sign at leavewowbeta.live.">
  <meta property="og:url" content="<?= e($appUrl) ?>">
  <meta property="og:type" content="website">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
  <link rel="canonical" href="<?= e($appUrl) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css?v=4">
  <?php if ($siteKey !== ''): ?>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <?php endif; ?>
</head>
<body>
  <header class="site-nav">
    <a class="site-nav__brand" href="#top">leavewowbeta.live</a>
    <nav class="site-nav__links" aria-label="Page">
      <a href="#why">Why</a>
      <a href="#sign">Sign</a>
      <a href="#voices">Voices</a>
      <a href="#reach">Reach</a>
    </nav>
  </header>

  <main id="top">
    <section class="hero" aria-labelledby="hero-brand">
      <div class="hero__media" aria-hidden="true">
        <picture>
          <source srcset="/assets/img/hero-key-art-alliance.webp" type="image/webp">
          <img
            src="/assets/img/hero-key-art-alliance.jpg"
            alt=""
            width="1920"
            height="1317"
            fetchpriority="high"
            decoding="async"
          >
        </picture>
        <div class="hero__veil"></div>
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
          <span class="hero__site">leavewowbeta.live</span>
        </p>
        <h1 class="hero__title">Keep Forever beta live until launch</h1>
        <p class="hero__lede">
          Players are enjoying the beta and do not want a nearly two-week gap from the October&nbsp;21 take-down to the November&nbsp;4 launch. We are asking Blizzard to leave Forever beta online so the community can keep testing through release week.
        </p>
        <div class="hero__actions">
          <a class="btn btn--primary" href="#sign">Sign the petition</a>
          <a class="btn btn--ghost" href="#why">Why it matters</a>
        </div>
        <p class="hero__meta">
          <span class="hero__count" data-count="<?= (int) $count ?>"><?= number_format($count) ?></span>
          signatures and counting
        </p>
      </div>
    </section>

    <section class="section section--why" id="why" aria-labelledby="why-title">
      <div class="section__inner why-split">
        <div class="why-split__copy">
          <p class="section__eyebrow">Why it matters</p>
          <h2 id="why-title">Do not let the gap break the momentum</h2>
          <p class="section__lede">
            Forever’s beta is live feedback and a world players already care about. Pulling servers offline on October&nbsp;21 creates a quiet stretch right when polish, scale, and launch-week bugs still need eyes on them.
          </p>
          <ul class="reasons">
            <li>
              <strong>Players already showed up</strong>
              <span>Beta testers gave their time. A forced pause before November&nbsp;4 is frustrating for people who have been helping shape the launch.</span>
            </li>
            <li>
              <strong>Keep the momentum</strong>
              <span>That nearly two-week gap lets excitement fade. Leave the servers up so day one does not start from a cold stop.</span>
            </li>
            <li>
              <strong>A smoother November&nbsp;4</strong>
              <span>Continued testing can still surface economy, queue, and systems issues under real load, and help smooth release week.</span>
            </li>
          </ul>
        </div>
        <figure class="why-split__visual">
          <picture>
            <source srcset="/assets/img/atmosphere-ashenvale.webp" type="image/webp">
            <img
              src="/assets/img/atmosphere-ashenvale.jpg"
              alt="Scenic view of Ashenvale from the official World of Warcraft Forever press kit"
              width="1600"
              height="900"
              loading="lazy"
              decoding="async"
            >
          </picture>
        </figure>
      </div>
    </section>

    <section class="section section--sign" id="sign" aria-labelledby="sign-title">
      <div class="section__inner section__inner--form">
        <p class="section__eyebrow">The petition</p>
        <h2 id="sign-title">Add your signature</h2>
        <p class="section__lede">
          Provide an email and character name. Emails are never shown publicly. Character names appear in the recent signatures list.
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
            <p class="field__hint">Used only to verify and deliver this petition.</p>
            <p class="field__error" data-error-for="email" hidden></p>
          </div>

          <div class="field">
            <label for="character_name">Character name <span class="req">required</span></label>
            <input type="text" id="character_name" name="character_name" required maxlength="32" autocomplete="nickname" placeholder="Character name">
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
      </div>
    </section>

    <section class="section section--voices" id="voices" aria-labelledby="voices-title">
      <div class="section__inner">
        <p class="section__eyebrow">Community</p>
        <h2 id="voices-title">Recent signatures</h2>
        <p class="section__lede">
          <span id="live-count"><?= number_format($count) ?></span> adventurers standing with Forever beta through launch.
        </p>
        <ol class="sig-list" id="sig-list">
          <?php if ($recent === []): ?>
            <li class="sig-list__empty" id="sig-empty">Be the first to sign.</li>
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
    </section>

    <section class="section section--reach" id="reach" aria-labelledby="reach-title">
      <div class="section__inner">
        <p class="section__eyebrow">Amplify</p>
        <h2 id="reach-title">Help it reach Blizzard</h2>
        <p class="section__lede section__lede--wide">
          Successful community asks travel beyond a single page: clear share posts, tagged official accounts, forum threads, and polite support feedback. This site is independent and unaffiliated. Use the tools below to send the ask further.
        </p>

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
            <a
              class="share-btn"
              href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>"
              target="_blank"
              rel="noopener noreferrer"
            >
              <svg class="share-btn__icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M13.5 22v-8h2.7l.4-3.1h-3.1V8.9c0-.9.25-1.5 1.55-1.5H16.8V4.6c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.2H8.2V14h2.5v8h2.8z"/></svg>
              <span>Facebook</span>
            </a>
          </div>
        </div>

        <div class="reach-grid">
          <div class="reach-block">
            <h3>Suggested share copy</h3>
            <p class="suggested-copy" id="suggested-copy"><?= e($suggestedCopy) ?></p>
            <button type="button" class="btn btn--ghost btn--compact" id="copy-suggested">Copy suggested text</button>
          </div>
          <div class="reach-block">
            <h3>Send it further</h3>
            <ul class="reach-list">
              <li>
                Post on X, Reddit, or Bluesky and mention
                <a href="https://x.com/Warcraft" target="_blank" rel="noopener noreferrer">@Warcraft</a>
                where appropriate. Keep it professional and on-message.
              </li>
              <li>
                Open a polite note via
                <a href="https://us.battle.net/support/en/" target="_blank" rel="noopener noreferrer">Blizzard Support</a>
                or share feedback in-game (ESC, then Support, then submit feedback). Support tickets are for legitimate product feedback, not mass spam.
              </li>
              <li>
                Start or join a discussion on the
                <a href="https://us.forums.blizzard.com/en/wow/" target="_blank" rel="noopener noreferrer">World of Warcraft forums</a>
                or community megathreads. Link the petition; do not claim official affiliation.
              </li>
              <li>
                <a href="mailto:?subject=<?= $mailtoSubject ?>&body=<?= $mailtoBody ?>">Email yourself</a>
                a ready draft to forward to friends.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer" id="legal">
    <div class="site-footer__inner">
      <h2 class="site-footer__title">About this petition</h2>
      <p>
        <strong>Not affiliated.</strong>
        leavewowbeta.live is an independent, fan-run community petition. It is not affiliated with Blizzard Entertainment, World of Warcraft, or Forever.
        World of Warcraft®, Blizzard Entertainment®, and related marks are trademarks of their respective owners.
        Press imagery on this page is used from Blizzard’s official
        <a href="https://blizzard.gamespress.com/wowforeverreveal" rel="noopener noreferrer">Forever press / media kit</a>
        where applicable.
      </p>
      <p>
        <strong>Privacy.</strong>
        Emails are used only to run, verify, and deliver this petition (for example authenticity, counting, and possible later delivery to Blizzard).
        Emails are never sold and never shown publicly. Character names may appear in the public signature list.
        Uniqueness is enforced with a server-side email hash; the email itself is stored only on the server.
      </p>
      <p>
        <strong>Your rights.</strong>
        If you are in the EEA, UK, California, or a similar jurisdiction, you may request access to or deletion of your signature data by emailing
        <a href="mailto:<?= e($privacyContact) ?>"><?= e($privacyContact) ?></a>.
      </p>
    </div>
  </footer>

  <script src="/assets/js/petition.js?v=4" defer></script>
</body>
</html>
