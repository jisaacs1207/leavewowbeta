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

$shareText = rawurlencode('Sign the petition to keep WoW Forever beta live until launch on November 4th:');
$shareUrl = rawurlencode($appUrl);
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
  <link rel="stylesheet" href="/assets/css/styles.css?v=3">
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
      <div class="section__backdrop" aria-hidden="true">
        <picture>
          <source srcset="/assets/img/atmosphere-ashenvale.webp" type="image/webp">
          <img src="/assets/img/atmosphere-ashenvale.jpg" alt="" width="1600" height="900" loading="lazy" decoding="async">
        </picture>
      </div>
      <div class="section__inner">
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
    </section>

    <section class="section section--sign" id="sign" aria-labelledby="sign-title">
      <div class="section__inner section__inner--narrow">
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

    <section class="section section--share" id="share" aria-labelledby="share-title">
      <div class="section__inner section__inner--narrow">
        <h2 id="share-title">Share the ask</h2>
        <p class="section__lede">
          Pass the link. Every signature helps show Blizzard the community wants continuous testing through November&nbsp;4.
        </p>
        <div class="share-row">
          <input type="text" class="share-row__url" id="share-url" value="<?= e($appUrl) ?>" readonly aria-label="Petition URL">
          <button type="button" class="btn btn--ghost" id="copy-link">Copy link</button>
        </div>
        <p class="share-links">
          <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank" rel="noopener noreferrer">Post on X</a>
          ·
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" rel="noopener noreferrer">Share on Facebook</a>
        </p>
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

  <script src="/assets/js/petition.js?v=3" defer></script>
</body>
</html>
