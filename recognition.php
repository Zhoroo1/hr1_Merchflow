<?php 
/* =======================================================
   HR1 MerchFlow • Recognition (custom delete modal + toast)
   - Feed safe even if users/employees mismatch
   - Give Kudos modal resolves recipient name -> employee_id
   ======================================================= */
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

require_once __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/idle_logout.php';

date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';
$role = strtolower($u['role'] ?? '');
$isAdminHr = in_array($role, ['admin','superadmin','hr','hr manager','human resources'], true);

/* ---------- Helpers ---------- */
function isActive($page) {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}

/* ---------- FEED (users or employees name) ---------- */
$sql = "SELECT r.*,
               uf.name AS from_name,
               COALESCE(ut.name, e.name) AS to_name
        FROM recognitions r
        LEFT JOIN users uf ON uf.id = r.from_user_id
        LEFT JOIN users ut ON ut.id = r.employee_id
        LEFT JOIN employees e ON e.id = r.employee_id
        ORDER BY r.created_at DESC";
$feed = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$feed = $feed ?: []; // avoid 'undefined variable' warnings

/* ---------- Leaderboard ---------- */
$lbTitle = 'Top Senders (MTD)';
$sqlLB = "SELECT COALESCE(u.name, CONCAT('User #', r.from_user_id)) AS name, COUNT(*) AS count
          FROM recognitions r
          LEFT JOIN users u ON u.id = r.from_user_id
          WHERE r.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
          GROUP BY name ORDER BY count DESC LIMIT 5";
$leaderboard = $pdo->query($sqlLB)->fetchAll(PDO::FETCH_ASSOC);

if (!$leaderboard) {
  $lbTitle = 'Top Senders (Last 60 days)';
  $sqlLB2 = "SELECT COALESCE(u.name, CONCAT('User #', r.from_user_id)) AS name, COUNT(*) AS count
             FROM recognitions r
             LEFT JOIN users u ON u.id = r.from_user_id
             WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
             GROUP BY name ORDER BY count DESC LIMIT 5";
  $leaderboard = $pdo->query($sqlLB2)->fetchAll(PDO::FETCH_ASSOC);
}

/* ---------- Unified recipient list (users + employees) ---------- */
$recipients = $pdo->query("
  SELECT id, name, 'user' AS src FROM users
  UNION ALL
  SELECT id, name, 'employee' AS src FROM employees
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recognition | <?= htmlspecialchars($brandName) ?></title>
  <link rel="icon" type="image/png" href="assets/logo3.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    #sidebar{width:16rem}
    #sidebar.collapsed{width:4rem}
    #sidebar .nav-item{padding:.6rem .85rem}
    #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
    #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}
    #contentWrap{padding-left:16rem;transition:padding .25s ease}
    #contentWrap.collapsed{padding-left:4rem}
    #sidebar{scrollbar-width:none;-ms-overflow-style:none}
    #sidebar::-webkit-scrollbar{display:none}
    /* Toast (top-center) */
    #toast{position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:70;display:none}
    #toast.show{display:block}
    /* Custom modals */
    .cmask{position:fixed;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:60}
    .cmask.show{display:flex}
    .card{width:100%;max-width:520px;border-radius:1rem;background:#fff;box-shadow:0 20px 40px rgba(0,0,0,.18)}
    .iconbtn{inline-size:34px; block-size:34px}
    /* --- User menu (top-right) --- */
    .user-menu{ position:relative }
    .user-menu .menu{
      position:absolute; right:0; margin-top:.5rem; width:11rem;
      background:#fff; border:1px solid #e5e7eb; border-radius:.75rem;
      box-shadow:0 12px 28px rgba(0,0,0,.08); z-index:70;
    }
    .user-menu a{
      display:flex; align-items:center; gap:.5rem;
      padding:.5rem .75rem; font-size:.9rem; color:#0f172a;
    }
    .user-menu a:hover{ background:#f8fafc }

  </style>
</head>
<body class="bg-slate-50">

<!-- Toast -->
<div id="toast" class="px-4 py-2 rounded-lg bg-emerald-600 text-white shadow text-sm"></div>

<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="flex-1 min-w-[220px]">
        <div class="relative max-w-2xl">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input id="searchBox" type="text" placeholder="Search name or badge…"
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-white border border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400 placeholder:text-slate-400">
        </div>
      </div>
      <!-- User menu -->
      <div class="user-menu" id="userMenuRoot">
        <button id="userMenuBtn"
                class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow hover:bg-slate-50">
          <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold">
            <?= strtoupper(substr($u['name'],0,2)); ?>
          </div>
          <div class="leading-tight pr-1 text-left">
            <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]">
              <?= htmlspecialchars($u['name']); ?>
            </div>
            <div class="text-[11px] text-slate-500 capitalize">
              <?= htmlspecialchars($u['role']); ?>
            </div>
          </div>
          <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
        </button>

        <!-- Dropdown -->
        <div id="userMenu" class="menu hidden">
          <a href="profile.php">
            <i class="fa-regular fa-user text-rose-600 w-5 text-center"></i>
            <span>View Profile</span>
          </a>
          <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket text-rose-600 w-5 text-center"></i>
            <span>Log Out</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="relative">
  <aside id="sidebar" class="fixed top-0 bottom-0 left-0 text-slate-100 overflow-y-auto transition-all duration-200"
         style="background:linear-gradient(to bottom,#121214 0%,#121214 70%,#e11d48 100%)">
    <div class="h-14 bg-rose-600 flex items-center justify-center gap-2">
      <div class="w-10 h-10 overflow-hidden rounded-md bg-white grid place-items-center">
        <img src="<?= htmlspecialchars($brandLogo) ?>" alt="Logo" class="w-full h-full object-cover">
      </div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>

    <nav class="py-4">
      <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>
      <a href="index.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('index.php'); ?>">
        <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
      </a>
      <a href="applicants.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('applicants.php'); ?>">
        <i class="fa-solid fa-user"></i><span class="item-label">Applicants</span>
      </a>
      <a href="recruitment.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('recruitment.php'); ?>">
        <i class="fa-solid fa-briefcase"></i><span class="item-label">Recruitment</span>
      </a>
      <a href="onboarding.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('onboarding.php'); ?>">
        <i class="fa-solid fa-square-check"></i><span class="item-label">Onboarding</span>
      </a>
      <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">MANAGEMENT</div>
      <a href="employees.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('employees.php'); ?>">
        <i class="fa-solid fa-users"></i><span class="item-label">Employees</span>
      </a>
      <a href="evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('evaluations.php'); ?>">
        <i class="fa-solid fa-chart-line"></i><span class="item-label">Evaluations</span>
      </a>
      <a href="recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('recognition.php'); ?>">
        <i class="fa-solid fa-award"></i><span class="item-label">Recognition</span>
      </a>
      <?php if ($isAdminHr): ?>
        <a href="users.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('users.php'); ?>">
          <i class="fa-solid fa-user-gear"></i><span class="item-label">Users</span>
        </a>
      <?php endif; ?>
    </nav>
  </aside>

  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-rose-600">Recognition</h1>
        <button id="btnAdd" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg shadow">+ Give Kudos</button>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Kudos Feed</h2>
          </div>
          <div id="feedBox" class="divide-y divide-slate-200">
            <?php if (!$feed): ?>
              <div class="p-4 text-sm text-slate-500">No recognition records yet.</div>
            <?php else: foreach ($feed as $r): 
              $canDelete = $isAdminHr || ((int)$u['id'] === (int)$r['from_user_id']);
              $desc = trim(($r['from_name'] ?? 'Someone').' → '.($r['to_name'] ?? ('Employee #'.$r['employee_id'])).' ('.$r['badge'].')');
            ?>
              <div class="px-4 py-3 feed-row" data-id="<?= (int)$r['id'] ?>">
                <div class="flex items-center justify-between gap-3">
                  <div class="min-w-0 flex-1">
                    <div class="text-sm">
                      <span class="font-medium text-slate-800"><?= htmlspecialchars($r['from_name'] ?? 'Unknown') ?></span>
                      <span class="text-slate-600"> gave </span>
                      <span class="font-medium text-rose-600"><?= htmlspecialchars($r['to_name'] ?? ('Employee #'.$r['employee_id'])) ?></span>
                      <span class="text-slate-600"> the </span>
                      <span class="font-medium"><?= htmlspecialchars($r['badge']) ?></span>
                      <span class="text-slate-600"> badge</span>
                    </div>
                    <?php if (!empty($r['note'])): ?>
                      <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($r['note']) ?></div>
                    <?php endif; ?>
                  </div>

                  <div class="flex items-center gap-3 shrink-0">
                    <div class="text-xs text-slate-500"><?= date('M d, Y', strtotime($r['created_at'])) ?></div>
                    <?php if ($canDelete): ?>
                      <button class="iconbtn grid place-items-center rounded-lg ring-1 ring-slate-200 hover:bg-rose-50 text-slate-500 hover:text-rose-600 del-open"
                              title="Delete"
                              data-id="<?= (int)$r['id'] ?>"
                              data-desc="<?= htmlspecialchars($desc, ENT_QUOTES) ?>">
                        <i class="fa-solid fa-trash"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800"><?= htmlspecialchars($lbTitle) ?></h2>
          </div>
          <div id="lbBox" class="p-4 space-y-3">
            <?php if (!$leaderboard): ?>
              <p class="text-sm text-slate-500">No data yet.</p>
            <?php else:
              $max = max(array_column($leaderboard,'count')) ?: 1;
              foreach ($leaderboard as $i=>$l):
                $pct = round(($l['count']/$max)*100);
            ?>
              <div class="lb-row" data-name="<?= htmlspecialchars($l['name']) ?>" data-count="<?= (int)$l['count'] ?>">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-700"><?= ($i+1).'. '.htmlspecialchars($l['name']); ?></span>
                  <span class="font-medium text-slate-800 count"><?= (int)$l['count']; ?></span>
                </div>
                <div class="h-2 rounded-full bg-slate-200 overflow-hidden mt-1">
                  <div class="h-full bg-rose-500" style="width: <?= $pct ?>%"></div>
                </div>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Give Kudos Modal -->
<div id="modalGive" class="cmask">
  <div class="card p-6">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold text-rose-600">Give Kudos</h2>
      <button type="button" class="text-slate-400 hover:text-slate-600" id="closeGive"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="kudosForm" class="space-y-3">
      <div>
        <label class="text-sm font-medium text-slate-600">Recipient</label>
        <!-- visible text input with datalist -->
        <input type="text" id="recipientName" placeholder="Type recipient name…" list="recipientOptions"
               autocomplete="off" required
               class="w-full mt-1 border rounded-lg px-3 py-2 focus:ring-2 focus:ring-rose-300">
        <!-- hidden field that will hold the resolved ID -->
        <input type="hidden" id="recipientId">
        <!-- datalist options -->
        <datalist id="recipientOptions">
          <?php foreach ($recipients as $p): ?>
            <option value="<?= htmlspecialchars($p['name']) ?>"></option>
          <?php endforeach; ?>
        </datalist>
      </div>
      <div>
        <label class="text-sm font-medium text-slate-600">Badge</label>
        <select name="badge" required class="w-full mt-1 border rounded-lg px-3 py-2">
          <option value="">Select badge</option>
          <option value="helpful">Helpful</option>
          <option value="teamwork">Teamwork</option>
          <option value="customer_hero">Customer Hero</option>
          <option value="innovation">Innovation</option>
        </select>
      </div>
      <div>
        <label class="text-sm font-medium text-slate-600">Message</label>
        <textarea name="note" rows="3" required class="w-full mt-1 border rounded-lg px-3 py-2" placeholder="Why are you giving this kudos?"></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-1">
        <button type="button" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300" id="cancelGive">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded-lg bg-rose-500 text-white hover:bg-rose-600">Submit</button>
      </div>
    </form>
  </div>
</div>

<!-- Custom DELETE Modal -->
<div id="modalDel" class="cmask">
  <div class="card">
    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
      <h3 class="text-base font-semibold text-slate-800">Delete Kudos</h3>
      <button class="text-slate-400 hover:text-slate-600" id="closeDelTop"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="px-6 py-4 text-sm text-slate-600">
      Permanently delete <span class="font-semibold text-slate-900" id="delDesc">this kudos</span>?
    </div>
    <div class="px-6 pb-6 flex justify-end gap-2">
      <button class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300" id="cancelDel">Cancel</button>
      <button class="px-4 py-2 rounded-lg bg-rose-500 text-white hover:bg-rose-600" id="confirmDel">Delete</button>
    </div>
  </div>
</div>

<script>
/* ====== Sidebar behavior ====== */
const btn=document.getElementById('btnSidebar'), sb=document.getElementById('sidebar'), main=document.getElementById('contentWrap'), topbarPad=document.getElementById('topbarPad');
function applyShift(){
  if(sb.classList.contains('collapsed')){ topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16'); main.classList.add('collapsed'); }
  else { topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64'); main.classList.remove('collapsed'); }
}
btn?.addEventListener('click',()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
if(localStorage.getItem('sb-collapsed')==='1'){ sb.classList.add('collapsed'); }
applyShift();

/* ====== Toast ====== */
const toastEl=document.getElementById('toast');
function showToast(msg, ok=true){
  toastEl.textContent = msg;
  toastEl.className = ok ? 'px-4 py-2 rounded-lg bg-emerald-600 text-white shadow text-sm show'
                         : 'px-4 py-2 rounded-lg bg-rose-600 text-white shadow text-sm show';
  setTimeout(()=>toastEl.classList.remove('show'), 1800);
}

/* ====== Recipients map (name -> id) ====== */
const RECIPIENTS = <?php echo json_encode($recipients, JSON_UNESCAPED_UNICODE); ?>;
const nameToId = new Map();
RECIPIENTS.forEach(p => { nameToId.set((p.name || '').trim().toLowerCase(), p.id); });

const inpName = document.getElementById('recipientName');
const inpId   = document.getElementById('recipientId');

function resolveRecipientId() {
  const key = (inpName.value || '').trim().toLowerCase();
  const id = nameToId.get(key) || null;
  inpId.value = id ? String(id) : '';
  return !!id;
}
inpName.addEventListener('change', resolveRecipientId);
inpName.addEventListener('blur', resolveRecipientId);

/* ====== Give Kudos Modal ====== */
const modalGive = document.getElementById('modalGive');
document.getElementById('btnAdd').onclick = ()=> modalGive.classList.add('show');
document.getElementById('closeGive').onclick = ()=> modalGive.classList.remove('show');
document.getElementById('cancelGive').onclick = ()=> modalGive.classList.remove('show');

document.getElementById('kudosForm').addEventListener('submit', async (e)=>{
  e.preventDefault();

  if (!resolveRecipientId()) {
    showToast('Recipient not found', false);
    inpName.focus();
    return;
  }

  const fd=new FormData(e.target);
  fd.append('from_user_id', <?= (int)$u['id'] ?>);
  fd.append('employee_id', document.getElementById('recipientId').value);
  fd.append('recipient_name', document.getElementById('recipientName').value.trim()); // optional

  try{
    const res = await fetch('api/recognition_api.php', { method:'POST', body: fd });
    const j = await res.json();
    if(!j.ok) throw new Error(j.error||'Server error');
    modalGive.classList.remove('show');
    e.target.reset();
    inpId.value = '';
    showToast('Kudos sent!');
    location.reload();
  }catch(err){
    showToast(err.message||'Failed to submit', false);
  }
});

/* ====== Custom DELETE Modal ====== */
const modalDel = document.getElementById('modalDel');
const delDesc  = document.getElementById('delDesc');
let delId = null;

function openDel(id, desc){
  delId = id;
  delDesc.textContent = desc || ('Kudos #'+id);
  modalDel.classList.add('show');
}
function closeDel(){
  modalDel.classList.remove('show');
  delId = null;
}
document.getElementById('closeDelTop').onclick = closeDel;
document.getElementById('cancelDel').onclick   = closeDel;

document.getElementById('confirmDel').addEventListener('click', async ()=>{
  if (!delId) return;
  try {
    const fd = new FormData();
    fd.append('action','delete');
    fd.append('id', delId);
    const res = await fetch('api/recognition_api.php', { method:'POST', body: fd });
    const j = await res.json();
    if (!j.ok) throw new Error(j.error || 'Delete failed');

    document.querySelector(`.feed-row[data-id="${delId}"]`)?.remove();
    closeDel();
    showToast('Kudos deleted');
  } catch (err) {
    showToast(err.message || 'Delete failed', false);
  }
});

/* Open delete modal when trash is clicked */
document.getElementById('feedBox')?.addEventListener('click', (e)=>{
  const b = e.target.closest('.del-open');
  if (!b) return;
  openDel(b.dataset.id, b.dataset.desc);
});

/* ====== Search filter ====== */
const searchBox=document.getElementById('searchBox');
function norm(s){ return (s||'').toLowerCase(); }
searchBox?.addEventListener('input', ()=>{
  const term = norm(searchBox.value);
  document.querySelectorAll('#feedBox .feed-row').forEach(row=>{
    row.classList.toggle('hidden', term && !norm(row.textContent).includes(term));
  });
});
/* ===== User menu toggle (top-right) ===== */
(function(){
  const root = document.getElementById('userMenuRoot');
  const btn  = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userMenu');
  if (!root || !btn || !menu) return;

  function close(){ menu.classList.add('hidden'); }
  function toggle(e){ e.stopPropagation(); menu.classList.toggle('hidden'); }

  btn.addEventListener('click', toggle);
  document.addEventListener('click', (e)=>{ if (!root.contains(e.target)) close(); });
  document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') close(); });
})();

</script>
</body>
</html>
