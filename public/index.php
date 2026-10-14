<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$config = petition_config();
$siteKey = $config['turnstile']['site_key'] ?? '';
$appUrl = $config['app_url'];

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>leavewowbeta.live — Keep Forever Beta Until Launch</title>
  <meta name="description" content="Petition Blizzard to leave World of Warcraft: Forever beta online until the November 4th launch — no two-week gap.">
  <meta property="og:title" content="Keep WoW Forever beta live until November 4">
  <meta property="og:description" content="Players are asking Blizzard not to shut down Forever beta on October 21. Sign at leavewowbeta.live.">
  <meta property="og:url" content="<?= e($appUrl) ?>">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="canonical" href="<?= e($appUrl) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <?php if ($siteKey !== ''): ?>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <?php endif; ?>
</head>
<body>
  <div class="atmosphere" aria-hidden="true">
    <div class="atmosphere__sky"></div>
    <div class="atmosphere__ember"></div>
    <div class="atmosphere__ridge"></div>
  </div>

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
      <div class="hero__content">
        <p class="hero__brand" id="hero-brand">leavewowbeta.live</p>
        <h1 class="hero__title">Keep <em>Forever</em> beta live until launch</h1>
        <p class="hero__lede">
          Blizzard plans to take the Forever beta down on October&nbsp;21 — nearly two weeks before the November&nbsp;4 launch. We’re asking them to leave it up so players can keep testing through release week.
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
      <div class="section__inner">
        <h2 id="why-title">A clean launch needs more nights in Azeroth</h2>
        <p class="section__lede">
          Forever’s beta isn’t just fun — it’s live feedback. Pulling servers offline on October&nbsp;21 creates a quiet gap right when polish, scale, and launch-week bugs still need eyes on them.
        </p>
        <ul class="reasons">
          <li>
            <strong>Keep momentum</strong>
            <span>Players are already in the world. A two-week blackout breaks that energy before day one.</span>
          </li>
          <li>
            <strong>More testing data</strong>
            <span>Continued play surfaces economy, queue, and systems issues that only show under real load.</span>
          </li>
          <li>
            <strong>A smoother November&nbsp;4</strong>
            <span>Leave the beta standing until launch so Forever arrives with fewer surprises.</span>
          </li>
        </ul>
      </div>
    </section>

    <section class="section section--sign" id="sign" aria-labelledby="sign-title">
      <div class="section__inner section__inner--narrow">
        <h2 id="sign-title">Add your name</h2>
        <p class="section__lede">
          One email, one signature. Your character name is shown publicly; your email is only for petition verification and delivery.
        </p>

        <?php if ($dbError !== null): ?>
          <p class="form-banner form-banner--warn" role="status"><?= e($dbError) ?></p>
        <?php endif; ?>

        <form class="petition-form" id="petition-form" action="/api/sign.php" method="post" novalidate>
          <!-- Honeypot -->
          <div class="hp" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>

          <div class="field">
            <label for="email">Email <span class="req">required</span></label>
            <input type="email" id="email" name="email" required maxlength="255" autocomplete="email" placeholder="you@example.com">
            <p class="field__error" data-error-for="email" hidden></p>
          </div>

          <div class="field">
            <label for="character_name">Character name <span class="req">required</span></label>
            <input type="text" id="character_name" name="character_name" required maxlength="32" autocomplete="nickname" placeholder="Your toon">
            <p class="field__error" data-error-for="character_name" hidden></p>
          </div>

          <div class="field">
            <label for="realm">Realm <span class="opt">optional</span></label>
            <input type="text" id="realm" name="realm" maxlength="64" autocomplete="off" placeholder="e.g. Area 52">
            <p class="field__error" data-error-for="realm" hidden></p>
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

          <p class="privacy-note">
            Emails are used only to verify and deliver this petition. They are never sold or shown publicly. Character names appear in the recent signatures list.
          </p>
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
          Pass the link — every signature helps show Blizzard the community wants continuous testing through November&nbsp;4.
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

  <footer class="site-footer">
    <p>Independent community petition — not affiliated with Blizzard Entertainment.</p>
    <p>World of Warcraft® and Blizzard Entertainment® are trademarks of their respective owners.</p>
  </footer>

  <script src="/assets/js/petition.js" defer></script>
</body>
</html>
