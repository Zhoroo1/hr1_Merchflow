<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

$role = strtolower($u['role'] ?? '');
$isAdminHr = in_array($role, ['admin','superadmin','hr','hr manager','human resources'], true);


/* ---------- BRAND (edit here anytime) ---------- */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg'; // exact filename/case

/* --- Helper: return Tailwind classes for active/hover states --- */
function isActive($page) {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is
    ? 'bg-rose-900/60 text-rose-500'
    : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}

/* ================= HARD-CODED DATA (HR1 • Recognition) ================= */
$kpi = [
  'kudos_mtd'    => 24,
  'badges_mtd'   => 17,
  'teams_covered'=> 6,
  'top_sender'   => 'Ops Lead',
];

$feed = [
  ['from'=>'Ops Lead','to'=>'Nina Cruz','badge'=>'On-Time Hero','reason'=>'Exceeded delivery SLA for Cabuyao','site'=>'DC – Laguna','date'=>'2025-09-20'],
  ['from'=>'HR1','to'=>'Team Biñan','badge'=>'Onboarding Champ','reason'=>'100% completion of onboarding tasks','site'=>'O!Save – Biñan','date'=>'2025-09-19'],
  ['from'=>'Admin','to'=>'Jay Luna','badge'=>'Customer Hero','reason'=>'Great customer recovery on POS outage','site'=>'O!Save – Sta. Rosa','date'=>'2025-09-18'],
  ['from'=>'QA Lead','to'=>'Aira Santos','badge'=>'Quality Star','reason'=>'Zero critical defects this sprint','site'=>'HQ – QA','date'=>'2025-09-17'],
];

$leaderboard = [
  ['name'=>'Ops Lead','count'=>8],
  ['name'=>'HR1','count'=>6],
  ['name'=>'Admin','count'=>5],
  ['name'=>'QA Lead','count'=>3],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Recognition | <?php echo htmlspecialchars($brandName); ?></title>
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

    .dot-badge{position:absolute;top:-2px;right:-2px;width:16px;height:16px;border-radius:9999px;background:#ef4444;color:#fff;font-size:10px;line-height:16px;text-align:center}

    #sidebar{scrollbar-width:none;-ms-overflow-style:none}
    #sidebar::-webkit-scrollbar{display:none}
  </style>
</head>
<body class="bg-slate-50">

<!-- TOP BAR -->
<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <!-- Search -->
      <div class="flex-1 min-w-[220px]">
        <div class="relative max-w-2xl">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input id="q" type="text" placeholder="Search by name, badge, site…"
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-white border border-rose-300 focus:outline-none focus:ring-2 focus:ring-rose-400 placeholder:text-slate-400">
        </div>
      </div>
      <!-- User -->
      <div class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow">
        <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold">
          <?php echo strtoupper(substr($u['name'],0,2)); ?>
        </div>
        <div class="leading-tight pr-1">
          <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]"><?php echo htmlspecialchars($u['name']); ?></div>
          <div class="text-[11px] text-slate-500 capitalize"><?php echo htmlspecialchars($u['role']); ?></div>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="relative">
  <!-- SIDEBAR -->
  <aside id="sidebar" class="fixed top-0 bottom-0 left-0 text-slate-100 overflow-y-auto transition-all duration-200"
         style="background:linear-gradient(to bottom,#121214 0%,#121214 70%,#e11d48 100%)">
    <!-- Brand header -->
    <div class="h-14 bg-rose-600 flex items-center justify-center gap-2">
      <div class="w-10 h-10 overflow-hidden rounded-md bg-white grid place-items-center">
        <img src="<?php echo htmlspecialchars($brandLogo); ?>" alt="Logo" class="w-full h-full object-cover">
      </div>
      <span class="item-label font-semibold text-white"><?php echo htmlspecialchars($brandName); ?></span>
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

  <!-- CONTENT -->
  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-rose-600">Recognition</h1>
        <button class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg shadow">+ Give Kudos</button>
      </div>

      <!-- KPI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-hand-holding-heart"></i></div>
          <div><div class="text-xs text-slate-500">Kudos (MTD)</div><div class="text-lg font-semibold"><?= $kpi['kudos_mtd'] ?></div></div>
        </div>
        <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-medal"></i></div>
          <div><div class="text-xs text-slate-500">Badges Awarded</div><div class="text-lg font-semibold"><?= $kpi['badges_mtd'] ?></div></div>
        </div>
        <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-store"></i></div>
          <div><div class="text-xs text-slate-500">Teams Covered</div><div class="text-lg font-semibold"><?= $kpi['teams_covered'] ?></div></div>
        </div>
        <div class="rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-user-tie"></i></div>
          <div><div class="text-xs text-slate-500">Top Sender</div><div class="text-lg font-semibold"><?= htmlspecialchars($kpi['top_sender']) ?></div></div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input id="fQuery" type="text" placeholder="Search name / badge / site…"
                 class="w-full pl-8 pr-3 py-2 rounded-lg bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-300">
        </div>
        <select id="fBadge" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
          <option value="">All badges</option>
          <option>Customer Hero</option>
          <option>Quality Star</option>
          <option>On-Time Hero</option>
          <option>Onboarding Champ</option>
        </select>
        <select id="fSite" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
          <option value="">All sites</option>
          <option>O!Save</option>
          <option>HQ</option>
          <option>DC</option>
        </select>
      </div>

      <!-- GRID -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Feed -->
        <div class="xl:col-span-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Kudos Feed</h2>
            <span class="text-xs text-slate-500">Hardcoded demo</span>
          </div>
          <div id="feedBox" class="divide-y divide-slate-200">
            <?php foreach ($feed as $it): ?>
              <div class="px-4 py-3 feed-row">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <div class="text-sm">
                      <span class="font-medium text-slate-800"><?= htmlspecialchars($it['from']) ?></span>
                      <span class="text-slate-600"> gave </span>
                      <span class="font-medium text-rose-600"><?= htmlspecialchars($it['to']) ?></span>
                      <span class="text-slate-600"> the </span>
                      <span class="font-medium"><?= htmlspecialchars($it['badge']) ?></span>
                      <span class="text-slate-600"> badge</span>
                    </div>
                    <div class="text-xs text-slate-500 truncate"><?= htmlspecialchars($it['reason']) ?> • <?= htmlspecialchars($it['site']) ?></div>
                  </div>
                  <div class="text-xs text-slate-500 shrink-0"><?= date('M d, Y', strtotime($it['date'])) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Leaderboard -->
        <div class="rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">Top Senders (MTD)</h2>
          </div>
          <div class="p-4 space-y-3">
            <?php foreach ($leaderboard as $i=>$l): ?>
              <div>
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-700"><?= ($i+1).'. '.htmlspecialchars($l['name']); ?></span>
                  <span class="font-medium text-slate-800"><?= (int)$l['count']; ?></span>
                </div>
                <div class="h-2 rounded-full bg-slate-200 overflow-hidden mt-1">
                  <div class="h-full bg-rose-500" style="width: <?= min(100,$l['count']*10) ?>%"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
  // Sidebar toggle
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
  btn?.addEventListener('click',()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
  if(localStorage.getItem('sb-collapsed')==='1'){ sb.classList.add('collapsed'); }
  applyShift();

  // Filters for feed
  const q=document.getElementById('fQuery');
  const fBadge=document.getElementById('fBadge');
  const fSite=document.getElementById('fSite');
  const rows=Array.from(document.querySelectorAll('#feedBox .feed-row'));

  function norm(s){return (s||'').toLowerCase();}
  function applyFilters(){
    const term=norm(q.value), badge=norm(fBadge.value), sitePick=norm(fSite.value);
    rows.forEach(row=>{
      const txt=norm(row.textContent);
      const okText=!term||txt.includes(term);
      const okBadge=!badge||txt.includes(badge);
      const okSite =!sitePick||txt.includes(sitePick); // matches HQ/DC/O!Save keywords
      row.classList.toggle('hidden', !(okText && okBadge && okSite));
    });
  }
  q.addEventListener('input',applyFilters);
  fBadge.addEventListener('change',applyFilters);
  fSite.addEventListener('change',applyFilters);
</script>
</body>
</html>
