<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg'; // exact filename/case


function isActive($page){
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}

/* ===== DB & Helpers (same mapping logic as applicants.php) ===== */
require_once __DIR__ . '/includes/db.php';
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}

function pick(array $r, array $keys, $def=''){
  foreach ($keys as $k) {
    if (array_key_exists($k, $r)) {
      $v = $r[$k];
      if ($v !== null && !(is_string($v) && trim($v) === '')) return $v;
    }
  }
  return $def;
}

/* Load applicants once and embed to JS */
try {
  $rows = $pdo->query("SELECT * FROM applicants ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
  $apps = array_map(function($a){
    $resume  = pick($a, ['resume_path','resume','cv_path','cv_file','cv','resume_file']);
    $status  = pick($a, ['status','stage'],'New');
    $archCol = (int) pick($a, ['archived','is_archived'], 0);
    $isArchived = $archCol ?: (strcasecmp($status,'Archived')===0 ? 1 : 0);
    $score = pick($a, ['score','rating']);
    return [
      'id'          => (int) pick($a, ['id']),
      'name'        => pick($a, ['name','full_name','applicant_name']),
      'email'       => pick($a, ['email','mail']),
      'mobile'      => pick($a, ['mobile','phone','contact_no','contact']),
      'address'     => pick($a, ['address','addr','home_address']),
      'education'   => pick($a, ['education','highest_education','educ']),
      'yoe'         => pick($a, ['yoe','years_experience','experience_years','years_of_experience']),
      'role'        => pick($a, ['role','position','job_applied','position_applied']),
      'site'        => pick($a, ['site','location','branch','office']),
      'start_date'  => pick($a, ['start_date','preferred_start_date']),
      'req_no'      => pick($a, ['req_no','requisition_no','reqid']),
      'status'      => $status,
      'date_applied'=> pick($a, ['applied_at','created_at','date_applied']),
      'score'       => is_numeric($score) ? 0 + $score : null,
      'shortlisted' => (int) pick($a, ['shortlisted','is_shortlisted'], 0),
      'resume'      => $resume,
      'archived'    => $isArchived,
    ];
  }, $rows);
} catch (Throwable $e) { $apps = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recruitment | HR1 <?= htmlspecialchars($brandName) ?></title>
<link rel="icon" type="image/png" href="assets/logo3.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
  #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
  #sidebar .nav-item{padding:.6rem .85rem}
  #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
  #sidebar.collapsed .item-label,#sidebar.collapsed .section-title{display:none}
  #contentWrap{padding-left:16rem;transition:padding .25s ease}
  #contentWrap.collapsed{padding-left:4rem}
  #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
  .dot-badge{position:absolute;top:-2px;right:-2px;width:16px;height:16px;border-radius:9999px;background:#ef4444;color:#fff;font-size:10px;line-height:16px;text-align:center}
  .iconbtn{width:30px;height:30px;display:grid;place-items:center;border-radius:.5rem;border:1px solid #e5e7eb;color:#475569}
  .iconbtn:hover{background:#f8fafc}
  .tooltip{position:relative}
  .tooltip:hover:after{content:attr(data-tip);position:absolute;bottom:110%;left:50%;transform:translateX(-50%);
    background:#0f172a;color:#fff;padding:.25rem .4rem;border-radius:.35rem;font-size:.7rem;white-space:nowrap}
  .tooltip::after{ pointer-events:none; }
  .tbl th, .tbl td { vertical-align: middle; }
  .tbl th { font-weight: 600; letter-spacing:.02em; }

  /* ===== Applicants table alignment ===== */
  .app-tbl { table-layout: fixed; }
  .app-tbl col.id      { width: 80px; }
  .app-tbl col.name    { width: 260px; }
  .app-tbl col.role    { width: 260px; }
  .app-tbl col.status  { width: 120px; }
  .app-tbl col.score   { width: 100px; }
  .app-tbl col.short   { width: 120px; }
  .app-tbl col.actions { width: 280px; }
  .ellipsis { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .kpi-card{
  display:flex; align-items:center; gap:.75rem;
  background:#fff; border-radius:.75rem;
  padding:1rem; box-shadow: 0 0 0 1px rgba(226,232,240,1); /* ring-slate-200 */
  }
  .kpi-icon{
    width:42px; height:42px; display:grid; place-items:center;
    border-radius:.75rem; font-size:1.1rem;
  }
  .kpi-label{
  font-size: .9rem;      /* mas laki at readable */
  color: #475569;        /* mas contrast */
  font-weight: 600;
}
.kpi-value{
  font-size: 1.75rem;    /* mas laki ang numbers */
  font-weight: 700;
  letter-spacing: .005em;
}

/* -- User menu (top-right) -- */
.user-menu{ position:relative }
.user-menu .menu{
  position:absolute; right:0; margin-top:.5rem; width:11rem;
  background:#fff; border:1px solid #e5e7eb; border-radius:.75rem;
  box-shadow:0 12px 28px rgba(0,0,0,.08);
}
.user-menu a{
  display:flex; align-items:center; gap:.5rem;
  padding:.5rem .75rem; font-size:.9rem; color:#0f172a;
}
.user-menu a:hover{ background:#f8fafc }


</style>
</head>
<body class="bg-slate-50">

<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="flex-1 min-w-[220px]">
        <div class="relative max-w-2xl">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input id="searchTop" type="text" placeholder="Search requisitions or applicants…"
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-white border border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400 placeholder:text-slate-400">
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="#" class="relative w-10 h-10 grid place-items-center rounded-xl ring-1 ring-rose-300/70 text-rose-600 hover:bg-rose-50">
          <i class="fa-regular fa-bell"></i><span class="dot-badge">•</span>
        </a>
       <!-- User menu -->
        <div class="user-menu" id="userMenuRoot">
          <button id="userMenuBtn"
                  class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow hover:bg-slate-50">
            <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold">
              <?php echo strtoupper(substr($u['name'],0,2)); ?>
            </div>
            <div class="leading-tight pr-1 text-left">
              <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]">
                <?php echo htmlspecialchars($u['name']); ?>
              </div>
              <div class="text-[11px] text-slate-500 capitalize">
                <?php echo htmlspecialchars($u['role']); ?>
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
  </div>
</header>

<div class="relative">
  <aside id="sidebar" class="fixed top-0 bottom-0 left-0 text-slate-100 overflow-y-auto transition-all duration-200"
         style="background:linear-gradient(to bottom,#121214 0%,#121214 70%,#e11d48 100%)">
   <div class="h-14 bg-rose-600 flex items-center justify-center gap-2">
      <div class="w-10 h-10 overflow-hidden rounded-md">
        <img src="<?= $brandLogo ?>" alt="Logo" class="w-full h-full object-cover">
      </div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>
    <nav class="py-4">
      <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>
      <a href="index.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('index.php'); ?>">
        <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
      </a>
      <a href="applicants.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('applicants.php'); ?>">
        <i class="fa-solid fa-user"></i><span class="item-label">Applicants</span>
      </a>
      <a href="recruitment.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('recruitment.php'); ?>">
        <i class="fa-solid fa-briefcase"></i><span class="item-label">Recruitment</span>
      </a>
      <a href="onboarding.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('onboarding.php'); ?>">
        <i class="fa-solid fa-square-check"></i><span class="item-label">Onboarding</span>
      </a>
      <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">MANAGEMENT</div>
      <a href="employees.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('employees.php'); ?>">
        <i class="fa-solid fa-users"></i><span class="item-label">Employees</span>
      </a>
      <a href="evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('evaluations.php'); ?>">
        <i class="fa-solid fa-chart-line"></i><span class="item-label">Evaluations</span>
      </a>
      <a href="recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?php echo isActive('recognition.php'); ?>">
        <i class="fa-solid fa-award"></i><span class="item-label">Recognition</span>
      </a>
      <?php if (!empty($u['role']) && in_array(strtolower($u['role']), ['admin','superadmin'])): ?>
        <a href="users.php"
          class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('users.php'); ?>">
          <i class="fa-solid fa-user-gear"></i>
          <span class="item-label">Users</span>
        </a>
      <?php endif; ?>

    </nav>
  </aside>

  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">

      <!-- KPIs -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-6" id="kpiRow">
        <!-- Open Reqs -->
        <div class="kpi-card">
          <div class="kpi-icon bg-rose-100 text-rose-600">
            <i class="fa-solid fa-briefcase"></i>
          </div>
          <div>
            <div class="kpi-label">Open Positions</div>
            <div class="kpi-value" id="kOpen">0</div>
          </div>
        </div>

        <!-- Applicants in pipeline -->
        <div class="kpi-card">
          <div class="kpi-icon bg-sky-100 text-sky-600">
            <i class="fa-solid fa-user-group"></i>
          </div>
          <div>
            <div class="kpi-label">Applicants Under Review</div>
            <div class="kpi-value" id="kPipe">0</div>
          </div>
        </div>

        <!-- Interviews this week -->
        <div class="kpi-card">
          <div class="kpi-icon bg-emerald-100 text-emerald-600">
            <i class="fa-solid fa-calendar-check"></i>
          </div>
          <div>
            <div class="kpi-label">Interviews this week</div>
            <div class="kpi-value" id="kInt">0</div>
          </div>
        </div>
      </div>


      <!-- Hiring Request -->
      <div class="rounded-xl bg-white ring-1 ring-slate-200 mb-6">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">Hiring Request</h2>
          <button id="btnNewReq" type="button" class="iconbtn tooltip" data-tip="Add requisition">
            <i class="fa-solid fa-circle-plus"></i>
          </button>
        </div>

        <table class="tbl w-full text-sm table-auto">
          <thead class="bg-slate-100 text-slate-600 uppercase text-[11px]">
            <tr>
              <th class="px-4 py-2 text-left">Req Id</th>
              <th class="px-4 py-2 text-left">Role</th>
              <th class="px-4 py-2 text-center">Stage</th>
              <th class="px-4 py-2 text-right">Needed</th>
              <th class="px-4 py-2 text-center">Action</th>
            </tr>
          </thead>
          <tbody id="reqBody"></tbody>
        </table>
      </div>

      <!-- Applicants -->
      <div class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">Applicants</h2>
          <div class="flex items-center gap-3">
            <div class="relative">
              <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
              <input id="q" placeholder="Search name / role…" class="pl-8 pr-3 py-2 rounded-lg border border-slate-200">
            </div>
            <select id="qStatus" class="h-10 rounded-lg border border-slate-200 px-3 text-sm">
              <option value="">All statuses</option>
              <option>New</option><option>Pending</option><option>Screening</option><option>Interview</option>
              <option>Offered</option><option>Rejected</option><option>Hired</option><option>Archived</option>
            </select>
            <button id="btnShowArchived"
               class="h-10 px-3 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">
                Show Archived
            </button>
          </div>
        </div>
        <table class="tbl app-tbl w-full text-sm" id="tbl">
          <colgroup>
            <col class="id"><col class="name"><col class="role">
            <col class="status"><col class="score"><col class="short"><col class="actions">
          </colgroup>
        <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
          <tr>
            <th class="px-6 py-3 text-right">ID</th>
            <th class="px-6 py-3 text-left">Name</th>
            <th class="px-6 py-3 text-left">Role</th>
            <th class="px-6 py-3 text-center">Status</th>
            <th class="px-6 py-3 text-center">Score</th>
            <th class="px-6 py-3 text-center">Shortlist</th>
            <th class="px-6 py-3 text-center">Actions</th>
          </tr>
        </thead>
          <tbody id="appBody"></tbody>
        </table>
      </div>

      <!-- Interview Schedules -->
      <div class="rounded-xl bg-white ring-1 ring-slate-200 overflow-hidden mt-6">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">Interview Schedules</h2>
        </div>
        <table class="w-full text-sm table-auto">
          <thead class="bg-slate-100 text-slate-600 uppercase text-[11px]">
            <tr>
              <th class="px-4 py-2 text-left">Date</th>
              <th class="px-4 py-2 text-left">Time</th>
              <th class="px-4 py-2 text-left" title="Internal job request ID">Req ID</th>
              <th class="px-4 py-2 text-left">Role</th>
              <th class="px-4 py-2 text-left">Panel</th>
              <th class="px-4 py-2 text-center">Action</th>
            </tr>
          </thead>
          <tbody id="schBody"></tbody>
        </table>
        <div id="schEmpty" class="px-4 py-6 text-sm text-slate-500">No scheduled interviews.</div>
      </div>

      <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 mt-6">
        <div class="grid sm:grid-cols-5 gap-4" id="bars"></div>
      </div>

    </div>
  </main>
</div>

<!-- Modal -->
<div id="modal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
  <div class="w-[640px] max-w-[94vw] bg-white rounded-2xl ring-1 ring-slate-200 shadow-xl p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 id="mTitle" class="text-lg font-semibold text-slate-800">Modal</h3>
      <button id="mClose" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div id="mBody" class="space-y-3"></div>
    <div class="mt-5 text-right">
      <button id="mOk" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg">Save</button>
    </div>
  </div>
</div>

<!-- Drawer -->
<div id="drawer" class="fixed right-0 top-0 bottom-0 w-[520px] max-w-[95vw] translate-x-full bg-white ring-1 ring-slate-200 shadow-2xl z-50 transition-transform">
  <div class="h-14 px-4 border-b border-slate-200 flex items-center justify-between">
    <div class="font-semibold">Compare Applicants</div>
    <button id="dClose" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div id="dBody" class="p-4"></div>
</div>

<!-- Toast -->
<div id="toast"
     class="fixed top-16 left-1/2 -translate-x-1/2 hidden z-[60]
            bg-slate-900 text-white text-base md:text-lg font-medium
            px-5 md:px-6 py-3 md:py-3.5 rounded-xl shadow-lg ring-1 ring-black/10
            tracking-wide">
</div>

<script>
/* ===== Boot guard ===== */
if (!window.__recruitmentBooted) {
window.__recruitmentBooted = true;
(() => {
  'use strict';

  /* ================= Sidebar ================= */
  const btn=document.getElementById('btnSidebar');
  const sb=document.getElementById('sidebar');
  const main=document.getElementById('contentWrap');
  const topbarPad=document.getElementById('topbarPad');
  function applyShift(){
    if(sb.classList.contains('collapsed')){
      topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16'); main.classList.add('collapsed');
    } else {
      topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64'); main.classList.remove('collapsed');
    }
  }
  btn?.addEventListener('click',()=>{
    sb.classList.toggle('collapsed');
    localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0');
    applyShift();
  });
  if(localStorage.getItem('sb-collapsed')==='1'){ sb.classList.add('collapsed'); }
  applyShift();

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


  /* ================= Toast / Modal / Drawer ================= */
  const toast=(m)=>{ const t=document.getElementById('toast'); t.textContent=m; t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),2200); };
  const modal=document.getElementById('modal'), mT=document.getElementById('mTitle'), mB=document.getElementById('mBody'), mOk=document.getElementById('mOk'), mClose=document.getElementById('mClose');
  const openModal = (title, body, ok='Save', onOk=()=>{}) => {
    mT.textContent = title; mB.innerHTML = body; mOk.textContent = ok;
    modal.classList.remove('hidden'); modal.classList.add('flex');
    const off = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); mOk.onclick = null; };
    mOk.onclick = () => { onOk(); off(); }; mClose.onclick = off; modal.onclick = (e)=>{ if(e.target===modal) off(); };
  };
  const drawer=document.getElementById('drawer'), dBody=document.getElementById('dBody');
  const openDrawer=(html)=>{ dBody.innerHTML=html; drawer.style.transform='translateX(0%)'; };
  const closeDrawer=()=>{ drawer.style.transform='translateX(100%)'; };
  document.getElementById('dClose').onclick=closeDrawer;

  /* ================= Server data (Applicants) ================= */
  const APPS = <?php echo json_encode($apps, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
  const CURRENT_USER_ROLE = <?php echo json_encode($u['role'] ?? ''); ?>;

  /* ================= Minimal API ================= */
  const API_URL = (function () {
    const base = <?php echo json_encode(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/'); ?>;
    return base + 'api/recruitment_api.php';
  })();
  async function api(action, payload = {}) {
    try{
      const res = await fetch(API_URL, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action, ...payload })
      });
      const raw = await res.text();
      let j; try { j = JSON.parse(raw); } catch(e){ throw new Error('Invalid JSON from API'); }
      if (!j.ok) throw new Error(j.error||'API error');
      return j.data ?? j;
    }catch(err){
      toast(err.message || 'Network error');
      throw err;
    }
  }

  /* ================= DOM targets ================= */
  const reqBody=document.getElementById('reqBody');
  const appBody=document.getElementById('appBody');
  const bars=document.getElementById('bars');
  const schBody=document.getElementById('schBody');
  const schEmpty=document.getElementById('schEmpty');

  /* ================= Utils ================= */
  const cap = s => (s||'').toString().replace(/\b\w/g, c=>c.toUpperCase());
  function fmtDate(d){
    if(!d) return '—';
    const dt = new Date(d);
    if (!isNaN(dt)) return dt.toLocaleDateString(undefined,{month:'short',day:'2-digit',year:'numeric'});
    const [y,m,day] = (d+'').split('-');
    const dt2 = new Date(+y,(+m||1)-1,+day||1);
    return isNaN(dt2)?d:dt2.toLocaleDateString(undefined,{month:'short',day:'2-digit',year:'numeric'});
  }
  function fmtTime(t){
    if(!t) return '';
    const [H,M] = (t+'').split(':').map(Number);
    const d = new Date(); d.setHours(H||0, M||0, 0, 0);
    return d.toLocaleTimeString(undefined,{hour:'numeric', minute:'2-digit'});
  }

  /* ===== Roles (for dropdowns) ===== */
const ROLE_OPTIONS = [
  'Store Part Timer',
  'Cashier',
  'Merchandiser / Promodiser',
  'Inventory Clerk / Stockman',
  'Order Processor',
  'Deputy Store Manager',
  'Store Manager'
];
function renderRoleOptions(selected='') {
  const esc = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  const opts = ['<option value="" disabled selected>Select role</option>']
    .concat(ROLE_OPTIONS.map(r => `<option value="${esc(r)}"${r===selected?' selected':''}>${esc(r)}</option>`));
  return opts.join('');
}


  /* ========== Week helpers (for KPI: Interviews this week) ========== */
  function startOfWeek(d=new Date()){
    const x = new Date(d); const day = (x.getDay()+6)%7; // Mon=0
    x.setHours(0,0,0,0); x.setDate(x.getDate()-day); return x;
  }
  function endOfWeek(d=new Date()){
    const s = startOfWeek(d); const e = new Date(s);
    e.setDate(s.getDate()+7); e.setMilliseconds(-1); return e;
  }

  /* ========== Stage normalization (hiring requests) ========== */
  function normStage(s){
  s = (s || '').toLowerCase().trim();
  if (['new','screening','interview','offer','closed'].includes(s)) return s;
  if (['screen','screened','in-screen','in_screening'].includes(s)) return 'screening';
  if (['accept','accepted','done','complete','closed'].includes(s)) return 'closed';
  if (['reject','rejected','fail','failed'].includes(s)) return 'closed';
  return 'new';
}

  function stageLabel(s){
    switch (normStage(s)) {
      case 'new':        return 'New';
      case 'screening':  return 'Screening';
      case 'interview':  return 'Interview';
      case 'offer':      return 'Offer';
      case 'closed':     return 'Closed';
      default:           return 'New';
    }
  }
  function toBackendStage(code){
    switch (normStage(code)) {
      case 'screening': return 'screen';
      case 'closed':    return 'accepted';
      default:          return normStage(code);
    }
  }

  /* ================= Renderers ================= */
  let REQS_BY_NO = {};
  function renderReqs(reqs){
    REQS_BY_NO = {};
    reqs.forEach(r=>{ if (r && r.req_no) REQS_BY_NO[r.req_no]=r; });
    reqBody.innerHTML = '';
    if(!reqs.length){
      reqBody.innerHTML = `<tr><td colspan="5" class="px-6 py-6 text-sm text-slate-500">No requisitions.</td></tr>`;
      document.getElementById('kOpen').textContent = '0';
      return;
    }
    reqs.forEach(r=>{
      const tr=document.createElement('tr'); tr.className='border-t';
      tr.innerHTML = `
        <td class="px-4 py-3 font-medium text-slate-800 truncate">${r.req_no||'—'}</td>
        <td class="px-4 py-3 truncate">${r.role || '—'}</td>
        <td class="px-4 py-3 text-center">${stageLabel(r.stage)}</td>
        <td class="px-4 py-3 text-right tabular-nums">${(r.needed??0)}</td>
        <td class="px-4 py-3 text-center">
          <div class="inline-flex gap-2">
            <button class="iconbtn tooltip btn-view" data-tip="View" data-req="${r.req_no}"><i class="fa-regular fa-eye"></i></button>
            <button class="iconbtn tooltip btn-edit" data-tip="Edit" data-req="${r.req_no}"><i class="fa-regular fa-pen-to-square"></i></button>
            <button class="iconbtn tooltip btn-del text-rose-600" data-tip="Delete" data-req="${r.req_no}"><i class="fa-regular fa-trash-can"></i></button>
            <button class="iconbtn tooltip btn-sched" data-tip="Schedule" data-req="${r.req_no}"><i class="fa-solid fa-calendar-days"></i></button>
          </div>
        </td>`;
      reqBody.appendChild(tr);
    });

    const openReqs = reqs.filter(r => String(r.stage||'').toLowerCase() !== 'closed').length;
    document.getElementById('kOpen').textContent = openReqs;
  }

  function allowedActionsFor(a, userRole){
    const st = String(a.status||'').toLowerCase();
    const role = String(userRole||'').toLowerCase();
    const can = new Set(['shortlist','notify']);
    if (['new','pending','screening'].includes(st)) { can.add('assign'); can.add('score'); }
    if (st === 'interview') { can.add('assign'); can.add('score'); }
    if (st === 'offered') { can.add('score'); can.add('approve'); } // still supported data-wise
    if (!['admin','hr manager'].includes(role)) can.delete('approve');
    return can;
  }
  function statusColor(st){
    st = (st||'').toLowerCase();
    if (st==='hired') return 'text-emerald-700';
    if (st==='interview') return 'text-orange-500';
    if (st==='offered') return 'text-green-600';
    if (st==='screening') return 'text-rose-500';
    if (st==='rejected') return 'text-slate-500';
    if (st==='archived') return 'text-slate-400';
    return 'text-slate-700';
  }

  let SHOW_ARCHIVED = false;
  const btnShowArchived = document.getElementById('btnShowArchived');
  function refreshArchivedBtn() {
    btnShowArchived.textContent = SHOW_ARCHIVED ? 'Hide Archived' : 'Show Archived';
    btnShowArchived.classList.toggle('bg-rose-50', SHOW_ARCHIVED);
    btnShowArchived.classList.toggle('text-rose-700', SHOW_ARCHIVED);
    btnShowArchived.classList.toggle('border-rose-300', SHOW_ARCHIVED);
  }

  function renderApps(apps){
    const userRole = <?php echo json_encode(strtolower($u['role'])); ?>;
    appBody.innerHTML='';

    if(!apps.length){
      appBody.innerHTML = `<tr><td colspan="7" class="px-6 py-6 text-sm text-slate-500">No applicants.</td></tr>`;
      document.getElementById('kPipe').textContent = '0';
      return;
    }

    apps.forEach(a=>{
      const allow = allowedActionsFor(a, userRole);
      const st = String(a.status||'').toLowerCase();
      const isArchView = SHOW_ARCHIVED === true;
      const canArchive = ['hired','rejected'].includes(st); // ✅ show Archive for rejected + hired

      const tr = document.createElement('tr');
      tr.className = 'border-t';
      tr.dataset.id = a.id;
      tr.innerHTML = `
        <td class="px-6 py-3 text-right tabular-nums">${a.id}</td>
        <td class="px-6 py-3 font-medium text-slate-800 ellipsis" title="${a.name||''}">${a.name||'—'}</td>
        <td class="px-6 py-3 ellipsis" title="${a.role||''}">${(a.role||'—')}</td>
        <td class="px-6 py-3 font-medium ${statusColor(a.status)} text-center"><span class="status">${a.status||''}</span></td>
        <td class="px-6 py-3 text-center"><span class="score">${a.score==null?'—':a.score}</span></td>
        <td class="px-6 py-3 text-center"><span class="short">${a.shortlisted?'Yes':'No'}</span></td>
        <td class="px-6 py-3">
          <div class="flex flex-wrap gap-2 justify-center">
            ${!isArchView ? `
              <button class="iconbtn tooltip act-invite" data-tip="Invite" style="${allow.has('assign')?'':'display:none'}"><i class="fa-solid fa-paper-plane"></i></button>
              <button class="iconbtn tooltip act-eform" data-tip="Eval form" style="${allow.has('score')?'':'display:none'}"><i class="fa-regular fa-clipboard"></i></button>
              <button class="iconbtn tooltip act-score" data-tip="Score/Status" style="${allow.has('score')?'':'display:none'}"><i class="fa-solid fa-check-to-slot"></i></button>
              <button class="iconbtn tooltip act-compare" data-tip="Compare" style="${allow.has('score')?'':'display:none'}"><i class="fa-solid fa-chart-bar"></i></button>
              <button class="iconbtn tooltip act-shortlist" data-tip="Shortlist toggle"><i class="fa-regular fa-star"></i></button>
              <button class="iconbtn tooltip act-approve text-emerald-700" data-tip="Approve" style="${allow.has('approve')?'':'display:none'}"><i class="fa-solid fa-thumbs-up"></i></button>
              <button class="iconbtn tooltip act-archive" data-tip="Archive" style="${canArchive ? '' : 'display:none'}"><i class="fa-solid fa-box-archive"></i></button>
            ` : `
              <button class="iconbtn tooltip act-unarchive" data-tip="Unarchive"><i class="fa-solid fa-box-open"></i></button>
              <button class="iconbtn tooltip act-delete text-rose-600" data-tip="Delete permanently"><i class="fa-regular fa-trash-can"></i></button>
            `}
          </div>
        </td>`;
      appBody.appendChild(tr);
    });

    const stageCounts = apps.reduce((m,a)=>{const k=(a.status||'').toLowerCase(); m[k]=(m[k]||0)+1; return m;}, {});
    document.getElementById('kPipe').textContent  = apps.length;

    // tiny bars
    bars.innerHTML = '';
    Object.keys(stageCounts).forEach(key=>{
      const val = stageCounts[key], w = Math.min(100, Number(val)*8);
      bars.innerHTML += `<div>
        <div class="flex items-center justify-between text-sm"><span>${cap(key)}</span><span class="tabular-nums">${val}</span></div>
        <div class="h-2 rounded-full bg-slate-200 overflow-hidden mt-1"><div class="h-full bg-rose-500" style="width:${w}%"></div></div>
      </div>`;
    });
  }

  /* =========== Archived toggle (Applicants) =========== */
  btnShowArchived?.addEventListener('click', () => {
    SHOW_ARCHIVED = !SHOW_ARCHIVED; refreshArchivedBtn(); loadApplicants();
  });
  refreshArchivedBtn();

  /* ================= Loaders ================= */
  async function loadReqs(){
    try {
      const { requisitions } = await api('list', { q: document.getElementById('searchTop').value||'' });
      renderReqs(requisitions||[]);
    } catch(e){ renderReqs([]); }
  }

  function loadApplicants(){
    const q  = (document.getElementById('q').value || document.getElementById('searchTop').value || '').toLowerCase();
    const st = (document.getElementById('qStatus').value || '').toLowerCase();

    const base = APPS.filter(a => {
      if (SHOW_ARCHIVED) { if (!a.archived) return false; }
      else { if (a.archived) return false; }
      if (!SHOW_ARCHIVED) { if (st && (String(a.status||'').toLowerCase() !== st)) return false; }
      const hay = [(a.name||''),(a.role||''),(a.email||''),(a.mobile||''),(a.req_no||'')].join(' ').toLowerCase();
      return !q || hay.includes(q);
    });

    renderApps(base);

    // bars use the set currently shown, or fallback to all visible set
    const kpiSet = base.length ? base : (SHOW_ARCHIVED ? APPS.filter(a=>a.archived) : APPS.filter(a=>!a.archived));
    const counts = kpiSet.reduce((m,a)=>{const k=(a.status||'').toLowerCase(); m[k]=(m[k]||0)+1; return m;}, {});
    // (bars already updated by renderApps)
  }

  /* ====== Interviews this week KPI via schedules ====== */
  let SCHEDULES = [];
  async function loadSchedules(showAll = false){
    try{
      const rows = await api('schedule.list', showAll ? { show: 'all' } : {});
      SCHEDULES = rows || [];

      if (!SCHEDULES.length){
        schBody.innerHTML = '';
        schEmpty.classList.remove('hidden');
        document.getElementById('kInt').textContent = '0';
        return;
      }
      schEmpty.classList.add('hidden');

      const timeRange = (s, e) => {
        const pick = (t)=> (t||'').slice(0,5);
        return `${fmtTime(pick(s))} – ${fmtTime(pick(e))}`;
      };
      schBody.innerHTML = SCHEDULES.map(r => `
        <tr class="border-t" data-id="${r.id}">
          <td class="px-4 py-3">${fmtDate(r.date)}</td>
          <td class="px-4 py-3">${timeRange(r.start || r.start_time, r.end || r.end_time)}</td>
          <td class="px-4 py-3">${r.req_no || '—'}</td>
          <td class="px-4 py-3">${r.role || '—'}</td>
          <td class="px-4 py-3">${r.panel||'—'}</td>
          <td class="px-4 py-3 text-center">
            <div class="inline-flex gap-2">
              <button class="iconbtn tooltip btn-done-sched" data-tip="${r.done ? 'Undo' : 'Done'}" data-id="${r.id}">
                <i class="fa-solid ${r.done ? 'fa-rotate-left' : 'fa-check'}"></i>
              </button>
              <button class="iconbtn tooltip btn-del-sched text-rose-600" data-tip="Delete" data-id="${r.id}">
                <i class="fa-regular fa-trash-can"></i>
              </button>
            </div>
          </td>
        </tr>
      `).join('');

      const s = startOfWeek(), e = endOfWeek();
      const weekly = SCHEDULES.filter(r=>{
        if (r.done) return false;
        const d = new Date(r.date || r.start || r.start_time || r.when);
        return !isNaN(d) && d >= s && d <= e;
      }).length;
      document.getElementById('kInt').textContent = weekly;

    }catch(e){
      schBody.innerHTML=''; schEmpty.classList.remove('hidden');
      document.getElementById('kInt').textContent = '0';
    }
  }

  /* ================= Filters & Events ================= */
  function debounce(fn,ms){let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms);} }
  document.getElementById('q').addEventListener('input', debounce(loadApplicants, 200));
  document.getElementById('qStatus').addEventListener('change', loadApplicants);
  document.getElementById('searchTop').addEventListener('input', ()=>{ loadApplicants(); loadReqs(); });

  /* ================= Requisition actions (modal handlers) ================= */
  reqBody.addEventListener('click', async (e)=>{
    const b = e.target.closest('button'); if(!b) return;
    const reqNo = b.dataset.req || '';
    const r = REQS_BY_NO[reqNo] || {};

    if (b.classList.contains('btn-sched')) {
      openModal('Schedule Interviews', `
        <div class="grid grid-cols-2 gap-3">
          <div class="col-span-2"><label class="text-sm text-slate-600">Req Id</label><input disabled value="${reqNo}" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200 bg-slate-50"></div>
          <div><label class="text-sm text-slate-600">Date</label><input id="d" type="date" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"></div>
          <div><label class="text-sm text-slate-600">Start</label><input id="t1" type="time" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"></div>
          <div><label class="text-sm text-slate-600">End</label><input id="t2" type="time" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"></div>
          <div class="col-span-2"><label class="text-sm text-slate-600">Panel</label><input id="panel" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="HR – Anna, Ops – Kyle"></div>
        </div>
      `,'Schedule', async ()=>{
        await api('schedule.save', {
          req_no: reqNo,
          date: document.getElementById('d').value,
          start: document.getElementById('t1').value,
          end: document.getElementById('t2').value,
          panel: document.getElementById('panel').value
        });
        toast('Interviews scheduled for req '+reqNo);
        await loadSchedules();
      });
      return;
    }

    if (b.classList.contains('btn-view')) {
      const html = `
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div><div class="text-slate-500">Req Id</div><div class="font-medium">${r.req_no||'—'}</div></div>
          <div><div class="text-slate-500">Stage</div><div class="font-medium">${stageLabel(r.stage)||'—'}</div></div>
          <div class="col-span-2"><div class="text-slate-500">Role / Site</div><div class="font-medium">${[r.role,r.site].filter(Boolean).join(' • ')||'—'}</div></div>
          <div><div class="text-slate-500">Needed</div><div class="font-medium">${r.needed??0}</div></div>
        </div>`;
      openModal('Requisition details', html, 'Close', ()=>{});
      return;
    }

  if (b.classList.contains('btn-edit')) {
  openModal('Edit Hiring Request', `
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="sm:col-span-2">
        <label class="text-sm text-slate-600">Role</label>
        <select id="erRole" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
          ${renderRoleOptions(r.role || '')}
        </select>
      </div>
      <div>
        <label class="text-sm text-slate-600">Needed</label>
        <input id="erNeeded" type="number" min="1" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" value="${r.needed??1}">
      </div>
      <div class="sm:col-span-2">
        <label class="text-sm text-slate-600">Stage</label>
        <select id="erStage" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
          <option value="new">New</option>
          <option value="screening">Screening</option>
          <option value="interview">Interview</option>
          <option value="closed">Closed</option>
        </select>
      </div>
    </div>
  `, 'Save', async ()=>{
    const role = document.getElementById('erRole').value;
    const needed = Math.max(1, Number(document.getElementById('erNeeded').value||1));
    const stageCode = normStage(document.getElementById('erStage').value||'new');
    await api('update_req', { req_no: reqNo, role, needed, stage: toBackendStage(stageCode) });
    toast('Hiring Request updated'); loadReqs();
  });

  // stage preselect
  setTimeout(()=>{
    const sel = document.getElementById('erStage');
    const cur = normStage(r.stage || 'new');
    sel.value = ['new','screening','interview','closed'].includes(cur) ? cur : 'new';
  },0);
  return;
}



    if (b.classList.contains('btn-del')) {
      openModal('Delete Requisition', `<p class="text-sm">Delete <span class="font-semibold">${reqNo}</span>? This cannot be undone.</p>`, 'Delete', async ()=>{
        await api('delete_req', { req_no: reqNo });
        toast('Hiring Request deleted'); loadReqs();
      });
      return;
    }
  });

  // === Normalization helpers for applicant status (hide "Offered" in UI only) ===
  function normApplicantStatusForApi(s){
    s = String(s||'').toLowerCase();
    if (['interview','rejected','hired','pending','screening','archived'].includes(s)) return s; // "offered" intentionally omitted from UI forms
    return 'screening';
  }
  function labelFromStatus(code){
    const m = { interview:'Interview', rejected:'Rejected', hired:'Hired', pending:'Pending', screening:'Screening', archived:'Archived' };
    return m[String(code||'').toLowerCase()] || 'Screening';
  }

  /* ================= Applicant actions ================= */
  appBody.addEventListener('click', async (e)=>{
    const b = e.target.closest('button'); if(!b) return;
    const tr = b.closest('tr'); const id = Number(tr.dataset.id);
    const row = APPS.find(x=>x.id===id) || {};

    // ----- Invite -----
    if (b.classList.contains('act-invite')) {
      const defDate = new Date().toISOString().slice(0,10);
      openModal('Send Interview Invite', `
        <div class="space-y-3">
          <div class="text-sm text-slate-600">To</div>
          <div class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium">${row.email||'— no email —'}</div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="text-sm text-slate-600">Date</label><input id="ivD" type="date" value="${defDate}" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"></div>
            <div><label class="text-sm text-slate-600">Time</label><input id="ivT" type="time" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"></div>
          </div>
          <div><label class="text-sm text-slate-600">Mode</label>
            <select id="ivM" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
              <option>On-site</option><option>Video</option><option>Phone</option>
            </select>
          </div>
          <div><label class="text-sm text-slate-600">Message</label>
            <textarea id="ivMsg" rows="4" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="Optional note..."></textarea>
          </div>
        </div>
      `,'Send', async ()=>{
        const d = document.getElementById('ivD').value, t = document.getElementById('ivT').value, m = document.getElementById('ivM').value;
        const extra = (document.getElementById('ivMsg').value||'').trim();
        const sched = (d && t) ? `${d} ${t}` : '(to be confirmed)';
        const msg = `Hi ${row.name||'Applicant'},\n\nWe’d like to invite you to an interview for the ${row.role||'position'}.\n\nWhen: ${sched}\nMode: ${m}\n${extra?`\n${extra}\n`:''}\nPlease reply to confirm. Thank you!\n— HR1 MerchFlow`;
        try{ await api('notify', { applicant_id:id, message: msg }); toast('Invite sent.'); }catch(_){}
      });
      return;
    }

    // ----- Evaluation Form (Offered hidden) -----
    if (b.classList.contains('act-eform')) {
      openModal('Evaluation Form', `
        <div class="space-y-3">
          <div class="grid grid-cols-3 gap-3">
            <div><label class="text-sm text-slate-600">Communication</label><input id="evC" type="number" min="0" max="100" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="0-100"></div>
            <div><label class="text-sm text-slate-600">Experience</label><input id="evE" type="number" min="0" max="100" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="0-100"></div>
            <div><label class="text-sm text-slate-600">Culture Fit</label><input id="evF" type="number" min="0" max="100" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="0-100"></div>
          </div>
          <div><label class="text-sm text-slate-600">Set Status</label>
            <select id="evS" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
              <option>Interview</option>
              <option>Rejected</option>
              <option>Hired</option>
            </select>
          </div>
        </div>
      `,'Save', async ()=>{
        const c = +document.getElementById('evC').value||0;
        const e2= +document.getElementById('evE').value||0;
        const f = +document.getElementById('evF').value||0;
        const avg = Math.round((c+e2+f)/3);
        const stLabel = document.getElementById('evS').value;
        const stCode  = normApplicantStatusForApi(stLabel);
        try{
          await api('submit_score',{ applicant_id:id, score:avg });
          await api('set_status',   { applicant_id:id, status:stCode });
          tr.querySelector('.score').textContent = avg || '—';
          tr.querySelector('.status').textContent = labelFromStatus(stCode);
          tr.querySelector('.status').className   = 'status ' + statusColor(stCode);
          // also reflect in APPS array
          const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS[idx].score = avg; APPS[idx].status = stCode; }
          toast('Evaluation saved.');
        }catch(_){}
      });
      return;
    }

    // ----- Quick score/status (Offered hidden) -----
    if (b.classList.contains('act-score')) {
      openModal('Submit Interview Score', `
        <div class="grid grid-cols-2 gap-3">
          <div><label class="text-sm text-slate-600">Score (0–100)</label><input id="sc" type="number" min="0" max="100" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"></div>
          <div><label class="text-sm text-slate-600">Status</label>
            <select id="st" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
              <option>Interview</option>
              <option>Rejected</option>
              <option>Hired</option>
            </select>
          </div>
        </div>
      `,'Submit', async ()=>{
        const sc = Number(document.getElementById('sc').value||0);
        const stLabel = document.getElementById('st').value;
        const st = normApplicantStatusForApi(stLabel);
        try{
          await api('submit_score',{ applicant_id:id, score:sc });
          await api('set_status',   { applicant_id:id, status:st });
          tr.querySelector('.score').textContent = sc||'—';
          tr.querySelector('.status').textContent = labelFromStatus(st);
          tr.querySelector('.status').className = 'status ' + statusColor(st);
          const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS[idx].score = sc; APPS[idx].status = st; }
          toast('Score submitted.');
        }catch(_){}
      });
      return;
    }

    // ----- Compare -----
    if (b.classList.contains('act-compare')) {
      const sameRole = APPS.filter(x=>!x.archived && (x.role||'').toLowerCase()===(row.role||'').toLowerCase())
                           .sort((a,b)=>(b.score||0)-(a.score||0));
      const top = sameRole.slice(0,5);
      const card = (a)=>`
        <div class="rounded-xl ring-1 ring-slate-200 p-3">
          <div class="font-semibold text-slate-800">${a.name||'—'}</div>
          <div class="text-sm text-slate-600">${a.role||'—'} • ${a.site||'HQ'}</div>
          <div class="text-sm mt-1"><span class="font-medium">Status:</span> ${a.status||'—'}</div>
          <div class="text-sm"><span class="font-medium">Score:</span> ${a.score==null?'—':a.score}</div>
          <div class="text-xs text-slate-500 mt-1">Applied: ${a.date_applied?fmtDate(a.date_applied):'—'}</div>
        </div>`;
      openDrawer(`<div class="space-y-3"><div class="text-slate-700 text-sm mb-2">Role: <b>${row.role||'—'}</b></div>${top.map(card).join('')}</div>`);
      return;
    }

    // ----- Shortlist toggle -----
    if (b.classList.contains('act-shortlist')) {
      try{
        const { shortlisted } = await api('toggle_shortlist', { applicant_id:id });
        tr.querySelector('.short').textContent = shortlisted ? 'Yes' : 'No';
        const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS[idx].shortlisted = shortlisted ? 1 : 0; }
        toast('Shortlist updated.');
      }catch(_){}
      return;
    }

    // ----- Approve -> Hired (kept) -----
    if (b.classList.contains('act-approve')) {
      try{
        await api('approve',{ applicant_id:id, with_offer:1 });
        tr.querySelector('.status').textContent='Hired';
        tr.querySelector('.status').className='status '+statusColor('Hired');
        const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS[idx].status = 'hired'; }
        toast('Approved.');
      }catch(_){}
      return;
    }

    // ----- Archive (active list) -----
    if (b.classList.contains('act-archive')) {
      openModal('Archive Applicant', `<p class="text-sm">Move <b>${row.name||'this applicant'}</b> to archive?</p>`, 'Archive', async ()=>{
        try{
          await api('applicant.archive', { applicant_id: id });
          // reflect locally
          const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS[idx].archived = 1; }
          toast('Archived'); loadApplicants();
        }catch(_){}
      });
      return;
    }

    // ----- Unarchive (archived list) -----
    if (b.classList.contains('act-unarchive')) {
      openModal('Unarchive Applicant', `<p class="text-sm">Restore <b>${row.name||'this applicant'}</b> to active applicants?</p>`, 'Unarchive', async ()=>{
        try{
          await api('applicant.unarchive', { applicant_id: id });
          const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS[idx].archived = 0; }
          toast('Unarchived'); loadApplicants();
        }catch(_){}
      });
      return;
    }

    // ----- Delete (archived only) -----
    if (b.classList.contains('act-delete')) {
      openModal('Delete Applicant', `<p class="text-sm">Permanently delete <b>${row.name||'this applicant'}</b>? This cannot be undone.</p>`, 'Delete', async ()=>{
        try{
          await api('applicant.delete', { applicant_id: id });
          const idx = APPS.findIndex(x=>x.id===id); if (idx>-1){ APPS.splice(idx,1); }
          toast('Deleted'); loadApplicants();
        }catch(_){}
      });
      return;
    }
  });

  /* ================= Schedules actions ================= */
  schBody.addEventListener('click', async (e)=>{
    const btnDel  = e.target.closest('button.btn-del-sched');
    const btnDone = e.target.closest('button.btn-done-sched');
    if (!btnDel && !btnDone) return;

    if (btnDel) {
      const id = btnDel.dataset.id;
      openModal('Delete Schedule', `<p class="text-sm">Delete this interview schedule?</p>`, 'Delete', async ()=>{
        try{ await api('schedule.delete', { id }); toast('Schedule deleted'); await loadSchedules(); }catch(_){}
      });
      return;
    }
    if (btnDone) {
      const id = btnDone.dataset.id;
      try{ await api('schedule.done', { id }); toast('Marked as done'); await loadSchedules(); }catch(_){}
      return;
    }
  });

  /* ================= Create Requisition ================= */
 document.getElementById('btnNewReq')?.addEventListener('click', () => {
  openModal('New Hiring Request', `
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="sm:col-span-2">
        <label class="text-sm text-slate-600">Role <span class="text-rose-600">*</span></label>
        <select id="rRole" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
          ${renderRoleOptions('')}
        </select>
      </div>
      <div>
        <label class="text-sm text-slate-600">Needed <span class="text-rose-600">*</span></label>
        <input id="rNeeded" type="number" min="1" value="1" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
      </div>
      <div>
        <label class="text-sm text-slate-600">Stage</label>
        <select id="rStage" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
          <option value="new">New</option>
          <option value="screening">Screening</option>
          <option value="interview">Interview</option>
          <option value="closed">Closed</option>
        </select>
      </div>
    </div>
    <p class="text-xs text-slate-500 mt-2">Req Id will be auto-generated (e.g., REQ-<?php echo date('Y'); ?>-001).</p>
  `, 'Create', async () => {
    const role   = document.getElementById('rRole').value;
    const needed = Math.max(1, Number(document.getElementById('rNeeded').value || 0));
    const stageCode = normStage(document.getElementById('rStage').value || 'new');
    if (!role) return toast('Please select a role');
    const data = await api('add_req', { role, needed, stage: toBackendStage(stageCode) });
    toast('Requisition created: ' + (data.req_no || ''));
    loadReqs();
  });
});



  

  /* ================= Initial load ================= */
  loadReqs().catch(()=>renderReqs([]));
  loadApplicants();
  loadSchedules().catch(()=>{});

})(); }
</script>
</body>
</html>
