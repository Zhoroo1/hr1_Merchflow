<?php


if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }


$IDLE_LIMIT  = 5 * 60; 
$WARN_BEFORE = 30;       // warn 30s before logout

/* ===== Server-side guard ===== */
$now = time();
if (!isset($_SESSION['__last_activity'])) {
  $_SESSION['__last_activity'] = $now;
} else {
  $idle = $now - (int)$_SESSION['__last_activity'];
  if ($idle > $IDLE_LIMIT) {
    // hard kill the session, then bounce to login with reason
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', $now - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
  }
  // rolling window
  $_SESSION['__last_activity'] = $now;
}

/* ===== Client-side warning & auto-logout =====
 * Inserts minimal HTML+JS. No dependencies.
 */
?>
<!-- Idle Logout (auto-injected by includes/idle_logout.php) -->
<style>
  #idleMask{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.45);backdrop-filter:blur(2px);z-index:1000}
  #idleCard{width:min(92vw,520px);background:#fff;border:1px solid #e5e7eb;border-radius:1rem;box-shadow:0 20px 40px rgba(0,0,0,.18);padding:1.25rem}
</style>
<div id="idleMask" role="dialog" aria-modal="true">
  <div id="idleCard">
    <div class="flex items-center justify-between mb-2">
      <h3 class="text-lg font-semibold text-slate-800">You’re inactive</h3>
      <button id="idleStayBtn" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">I’m still here</button>
    </div>
    <p class="text-slate-600 text-sm">
      For security, you’ll be logged out after inactivity.
      Logging out in <span id="idleCountdown" class="font-semibold text-rose-600">—</span>…
    </p>
  </div>
</div>
<script>
(function(){
  const LIMIT   = <?= json_encode((int)$IDLE_LIMIT) ?>;      // total idle seconds (120)
  const WARN    = <?= json_encode((int)$WARN_BEFORE) ?>;     // seconds before logout to warn (30)
  const TO_LOGO = 'logout.php?auto=1&reason=idle';

  let last = Date.now();
  let warnTimer = null, logoutTimer = null, ticker = null;
  const mask = document.getElementById('idleMask');
  const cnt  = document.getElementById('idleCountdown');
  const stay = document.getElementById('idleStayBtn');

  // Activity resets
  const bump = () => {
    last = Date.now();
    hideWarn();
    schedule();
  };

  ['click','mousemove','keydown','scroll','touchstart','focus'].forEach(ev => {
    window.addEventListener(ev, bump, {passive:true});
  });

  function secondsSinceLast(){ return Math.floor((Date.now() - last)/1000); }

  function showWarn(remaining){
    if (!mask) return;
    mask.style.display = 'flex';
    updateCountdown(remaining);
    if (ticker) clearInterval(ticker);
    ticker = setInterval(()=>{
      const secLeft = LIMIT - secondsSinceLast();
      updateCountdown(secLeft);
      if (secLeft <= 0) { goLogout(); }
    }, 250);
  }
  function hideWarn(){
    if (!mask) return;
    mask.style.display = 'none';
    if (ticker) { clearInterval(ticker); ticker = null; }
  }
  function updateCountdown(s){
    if (!cnt) return;
    const v = Math.max(0, Math.floor(s));
    cnt.textContent = v + 's';
  }

  function goLogout(){ window.location.href = TO_LOGO; }

  function schedule(){
    if (warnTimer) clearTimeout(warnTimer);
    if (logoutTimer) clearTimeout(logoutTimer);

    const since = secondsSinceLast();
    const untilWarn   = Math.max(0, LIMIT - WARN - since) * 1000;
    const untilLogout = Math.max(0, LIMIT - since) * 1000;

    warnTimer   = setTimeout(()=> showWarn(LIMIT - secondsSinceLast()), untilWarn);
    logoutTimer = setTimeout(goLogout, untilLogout);
  }

  stay?.addEventListener('click', ()=> {
    // Simply counts as activity; server has rolling window guard too.
    bump();
  });

  // initial arm
  schedule();
})();
</script>
