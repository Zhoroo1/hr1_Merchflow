<?php
declare(strict_types=1);

/* ---- SAFE BOOT (single session_start only) ---- */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$u = $_SESSION['user'];

/* Brand */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg'; // use exact filename/case

/* Active-link helper */
function isActive(string $page): string {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}

/* DB */
require_once __DIR__ . '/includes/db.php';

/* Timezone (PH) + force MySQL connection to +08:00 */
date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Throwable $e) {}


  /* ---------- Helpers ---------- */
  function pick(array $r, array $keys, $def=''){
    foreach ($keys as $k) {
      if (array_key_exists($k, $r)) {
        $v = $r[$k];
        if ($v !== null && !(is_string($v) && trim($v) === '')) {
          return $v;
        }
      }
    }
    return $def;
  }

  function fmtLocalDate($s){
    if(!$s) return '—';
    try{
      // Treat DB value as UTC then convert to current PHP TZ (set to Asia/Manila)
      $dt = new DateTime($s, new DateTimeZone('UTC'));
      $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
      return $dt->format('M d, Y');
    }catch(Throwable $e){
      $ts = strtotime($s);
      return $ts ? date('M d, Y', $ts) : '—';
    }
  }

  /* ---------- Load from DB (defensive mapping) ---------- */
  try {
    $rows = $pdo->query("SELECT * FROM applicants ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $apps = array_map(function($a){
      $resume = pick($a, ['resume_path','resume','cv_path','cv_file','cv','resume_file']);
      // ----- tolerant status mapping (label ↔ code) -----
      $statusRaw = pick($a, ['status','stage','status_code'], 'New');
      $map = [
        'new'        => 'New',
        'pending'    => 'Pending',
        'screening'  => 'Screening',
        'interview'  => 'Interview',
        'offered'    => 'Offered',
        'rejected'   => 'Rejected',
        'hired'      => 'Hired',
        'archived'   => 'Archived',
      ];
      $lc = strtolower((string)$statusRaw);
      $status = $map[$lc] ?? $statusRaw;   

      $archCol = (int) pick($a, ['archived','is_archived'], 0);
      $isArchived = $archCol ?: (strcasecmp($status,'Archived')===0 ? 1 : 0);
      return [
        'id'          => pick($a, ['id']),
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
        'date'        => pick($a, ['applied_at','created_at','date_applied']),
        'score'       => pick($a, ['score','rating']),
        'shortlisted' => (int) pick($a, ['shortlisted','is_shortlisted'], 0),
        'resume'      => $resume,
        'archived'    => $isArchived,
      ];
    }, $rows);
  } catch (Throwable $e) {
    $apps = [];
  }

  /* roles for filter dropdown */
  $roleOptions = array_values(array_unique(array_filter(array_map(fn($a)=>$a['role']??'', $apps))));
  sort($roleOptions);
  ?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Applicants | HR1 <?= htmlspecialchars($brandName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
      #sidebar{width:16rem}
      #sidebar.collapsed{width:4rem}
      #sidebar .nav-item{padding:.6rem .85rem}
      #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
      #sidebar.collapsed .item-label,#sidebar.collapsed .section-title{display:none}
      #contentWrap{padding-left:16rem;transition:padding .25s ease}
      #contentWrap.collapsed{padding-left:4rem}
      #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
      .dot-badge{position:absolute;top:-2px;right:-2px;width:16px;height:16px;border-radius:9999px;background:#ef4444;color:#fff;font-size:10px;line-height:16px;text-align:center}
      .iconbtn{width:32px;height:32px;display:grid;place-items:center;border-radius:.5rem;border:1px solid #e5e7eb;color:#475569}
      .iconbtn:hover{background:#f8fafc}
      .tooltip{position:relative}
      .tooltip:hover:after{content:attr(data-tip);position:absolute;bottom:110%;left:50%;transform:translateX(-50%);background:#0f172a;color:#fff;padding:.25rem .4rem;border-radius:.35rem;font-size:.7rem;white-space:nowrap}

      #sidebar{ z-index:40; }
      @media (max-width:1024px){
        #sidebar{
          position: fixed; left: 0; top: 0; bottom: 0;
          width: 16rem; transform: translateX(-100%);
          transition: transform .25s ease;
        }
        #sidebar.show{ transform: translateX(0); }
        #contentWrap{ padding-left: 0 !important; }
        #topbarPad{ margin-left: 0 !important; }
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

  <!-- TOP BAR -->
  <header class="sticky top-0 z-40">
    <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="min-h-14 px-3 md:px-4 flex flex-wrap items-center gap-3">
        <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
          <i class="fa-solid fa-bars"></i>
        </button>

        <!-- removed the global search bar -->
        <div class="flex-1"></div>

        <div class="flex items-center gap-2">
          <a id="bell" href="#" class="relative w-10 h-10 grid place-items-center rounded-xl ring-1 ring-rose-300/70 text-rose-600 hover:bg-rose-50">
            <i class="fa-regular fa-bell"></i><span id="bellDot" class="dot-badge">•</span>
          </a>
          <a href="apply.php" target="_blank" class="bg-rose-500 hover:bg-rose-600 text-white px-3 py-2 rounded-lg text-sm">Public Apply</a>
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
            <div id="userMenu"
                class="menu hidden">
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
    <!-- SIDEBAR -->
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
        <?php if (!empty($u['role']) && strtolower($u['role']) === 'admin'): ?>
          <a href="users.php"
            class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('users.php'); ?>">
            <i class="fa-solid fa-user-gear"></i>
            <span class="item-label">Users</span>
          </a>
        <?php endif; ?>

      </nav>
    </aside>

    <!-- Scrim for mobile drawer -->
    <div id="sbScrim" class="fixed inset-0 bg-black/30 z-30 hidden"></div>

    <!-- CONTENT -->
    <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
      <div class="px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
          <div>
            <h1 class="text-2xl font-semibold text-rose-600">Applicants</h1>
            <div class="text-xs text-slate-500">Applicant Management • HR1 MerchFlow</div>
          </div>

          <div class="flex items-center gap-2">
            <!-- Show/Hide Archived toggle -->
            <button id="btnToggleArchived"
                    class="ring-1 ring-slate-300 px-3 py-2 rounded-lg bg-white text-slate-700 hover:bg-slate-50 flex items-center gap-2">
              <i class="fa-solid fa-box-archive"></i>
              <span class="label">Show Archived</span>
            </button>

            <a href="apply.php" target="_blank"
              class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg shadow">
              + Add Applicant
            </a>
          </div>
        </div>

        <!-- Filters -->
        <div class="mb-4 flex flex-wrap items-center gap-3">
          <div class="relative w-full sm:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input id="fQuery" type="text" placeholder="Search name / position…"
                  class="w-full pl-8 pr-3 py-2 rounded-lg bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-300">
          </div>
          <select id="fStatus" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
            <option value="">All statuses</option>
            <option>New</option>
            <option>Screening</option>
            <option>Interview</option>   <!-- unhidden -->
            <option class="hidden">Pending</option>
            <option class="hidden">Offered</option>
            <option>Rejected</option>
            <option>Hired</option>
          </select>

          <select id="fRole" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
            <option value="">All roles</option>
            <?php foreach($roleOptions as $r): ?>
              <option><?php echo htmlspecialchars($r); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Table -->
        <div class="bg-white shadow rounded-xl overflow-hidden ring-1 ring-slate-200">
          <div class="overflow-x-auto">
            <table class="min-w-[900px] sm:min-w-0 w-full text-sm text-left">
              <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                <tr>
                  <th class="px-6 py-3">#</th>
                  <th class="px-6 py-3">Name</th>
                  <th class="px-6 py-3">Role</th>
                  <th class="px-6 py-3">Status</th>
                  <th class="px-6 py-3">Date Applied</th>
                  <th class="px-6 py-3">Actions</th>
                </tr>
              </thead>
              <tbody id="rows">
                <?php if(empty($apps)): ?>
                  <tr><td colspan="6" class="px-6 py-6 text-slate-500">No applicants found.</td></tr>
                <?php else: $i=1; foreach ($apps as $a): ?>
                  <?php
                    $st = strtolower($a['status']);
                    $color = ($a['archived'] ? 'text-slate-400' :
                            ($st==='interview' ? 'text-orange-500' :
                            ($st==='offered'   ? 'text-green-600'  :
                            ($st==='screening' ? 'text-rose-500'   :
                            ($st==='hired'     ? 'text-emerald-700': 'text-slate-600')))));
                    $cv = $a['resume'];
                    $cvAttr = htmlspecialchars($cv ?: '', ENT_QUOTES, 'UTF-8');
                    $siteDisp = $a['site'] ?: 'HQ';
                  ?>
                  <tr class="border-t app-row"
                      data-id="<?php echo (int)$a['id']; ?>"
                      data-name="<?php echo htmlspecialchars($a['name'] ?? ''); ?>"
                      data-role="<?php echo htmlspecialchars(strtolower($a['role'] ?? '')); ?>"
                      data-role_raw="<?php echo htmlspecialchars($a['role'] ?? ''); ?>"
                      data-site="<?php echo htmlspecialchars($siteDisp); ?>"
                      data-status="<?php echo htmlspecialchars(strtolower($a['status'] ?? '')); ?>"
                      data-email="<?php echo htmlspecialchars(strtolower($a['email'] ?? '')); ?>"
                      data-mobile="<?php echo htmlspecialchars(strtolower($a['mobile'] ?? '')); ?>"
                      data-address="<?php echo htmlspecialchars($a['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                      data-education="<?php echo htmlspecialchars($a['education'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                      data-yoe="<?php echo htmlspecialchars((string)($a['yoe'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                      data-start="<?php echo htmlspecialchars($a['start_date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                      data-cv="<?php echo $cvAttr; ?>"
                      data-archived="<?php echo (int)$a['archived']; ?>">
                    <td class="px-6 py-3 idx"><?php echo $i++; ?></td>
                    <td class="px-6 py-3 font-medium text-slate-800"><?php echo htmlspecialchars($a['name'] ?? ''); ?></td>
                    <td class="px-6 py-3"><?php echo htmlspecialchars($a['role'] ?? ''); ?></td>
                    <td class="px-6 py-3 font-medium <?php echo $color; ?>"><span class="status-label"><?php echo $a['archived'] ? 'Archived' : htmlspecialchars($a['status'] ?? ''); ?></span></td>
                    <td class="px-6 py-3"><?php echo fmtLocalDate($a['date']); ?></td>
                    <td class="px-6 py-3">
                      <div class="flex flex-wrap gap-2">
                        <button class="iconbtn tooltip act-view"      data-tip="View details"><i class="fa-regular fa-eye"></i></button>
                        <button class="iconbtn tooltip act-open-cv"   data-tip="Open CV"><i class="fa-regular fa-file-lines"></i></button>

                        <!-- Hidden in archived view -->
                        <button class="iconbtn tooltip act-notify"    data-tip="Notify"><i class="fa-regular fa-bell"></i></button>
                        <button class="iconbtn tooltip act-interview" data-tip="Schedule"><i class="fa-regular fa-calendar"></i></button>
                        <button class="iconbtn tooltip act-status"    data-tip="Tag status"><i class="fa-solid fa-tags"></i></button>
                        <button class="iconbtn tooltip act-forward"   data-tip="Forward"><i class="fa-solid fa-share-nodes"></i></button>

                        <!-- Archive / Unarchive / Delete -->
                        <button class="iconbtn tooltip act-archive"   data-tip="Archive" <?php echo $a['archived']?'style="display:none"':''; ?>><i class="fa-solid fa-box-archive"></i></button>
                        <button class="iconbtn tooltip act-unarchive" data-tip="Unarchive" <?php echo $a['archived']?'':'style="display:none"'; ?>><i class="fa-solid fa-box-open"></i></button>
                        <button class="iconbtn tooltip act-delete"    data-tip="Delete permanently" <?php echo $a['archived']?'':'style="display:none"'; ?>><i class="fa-solid fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal -->
  <div id="modal" class="fixed inset-0 hidden flex items-center justify-center bg-black/40 z-50 p-4 overflow-y-auto">
    <div class="w-[680px] max-w-[95vw] bg-white rounded-2xl ring-1 ring-slate-200 shadow-xl p-5">
      <div class="flex items-center justify-between mb-3">
        <h3 id="modalTitle" class="text-lg font-semibold text-slate-800">Modal</h3>
        <button id="modalClose" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div id="modalBody" class="space-y-3"></div>
      <div id="modalFooter" class="mt-5 text-right">
        <button id="modalOk" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg">Save</button>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast"
      class="fixed top-16 left-1/2 -translate-x-1/2 hidden z-[60]
              bg-slate-900 text-white text-base md:text-lg font-medium
              px-5 md:px-6 py-3 md:py-3.5 rounded-xl shadow-lg ring-1 ring-black/10
              tracking-wide">
  </div>


  <script>
  /* ================== Sidebar & layout ================== */
  const btn = document.getElementById('btnSidebar');
  const sb = document.getElementById('sidebar');
  const scrim = document.getElementById('sbScrim');
  const main = document.getElementById('contentWrap');
  const topbarPad = document.getElementById('topbarPad');

  function isMobile(){ return window.matchMedia('(max-width:1024px)').matches; }
  function applyDesktopShift(){
    if (sb.classList.contains('collapsed')) {
      topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16');
      main.classList.add('collapsed');
    } else {
      topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64');
      main.classList.remove('collapsed');
    }
  }
  function syncLayout(){
    if (isMobile()){
      sb.classList.remove('collapsed'); sb.classList.remove('show');
      scrim.classList.add('hidden'); topbarPad.classList.remove('ml-64','ml-16'); main.classList.remove('collapsed');
    } else {
      scrim.classList.add('hidden'); sb.classList.remove('show');
      if (localStorage.getItem('sb-collapsed') === '1') sb.classList.add('collapsed'); else sb.classList.remove('collapsed');
      if (!topbarPad.classList.contains('ml-64') && !topbarPad.classList.contains('ml-16')) topbarPad.classList.add('ml-64');
      applyDesktopShift();
    }
  }
  btn?.addEventListener('click', ()=>{
    if (isMobile()){
      const open = !sb.classList.contains('show'); sb.classList.toggle('show', open); scrim.classList.toggle('hidden', !open);
    } else {
      sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed') ? '1' : '0'); applyDesktopShift();
    }
  });
  scrim?.addEventListener('click', ()=>{ sb.classList.remove('show'); scrim.classList.add('hidden'); });
  window.addEventListener('resize', ()=>{ clearTimeout(window.__rsz); window.__rsz = setTimeout(syncLayout,120); });
  syncLayout();

  /* ===== User menu toggle ===== */
(function(){
  const root = document.getElementById('userMenuRoot');
  const btn  = document.getElementById('userMenuBtn');
  const menu = document.getElementById('userMenu');
  if (!root || !btn || !menu) return;

  function close(){ menu.classList.add('hidden'); }
  function toggle(e){ e.stopPropagation(); menu.classList.toggle('hidden'); }

  btn.addEventListener('click', toggle);
  document.addEventListener('click', (e)=>{
    if (!root.contains(e.target)) close();
  });
  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape') close();
  });
})();


  /* ================== Filters ================== */
  const fQuery=document.getElementById('fQuery');
  const fStatus=document.getElementById('fStatus');
  const fRole=document.getElementById('fRole');
  const rows=[...document.querySelectorAll('.app-row')];

  let showArchived=false;
  const btnToggleArchived=document.getElementById('btnToggleArchived');
  btnToggleArchived.addEventListener('click', ()=>{ showArchived=!showArchived; updateArchBtn(); filter(); });

  function updateArchBtn(){
    const lbl=btnToggleArchived.querySelector('.label');
    lbl.textContent = showArchived ? 'Hide Archived' : 'Show Archived';
    btnToggleArchived.classList.toggle('bg-rose-50', showArchived);
    btnToggleArchived.classList.toggle('text-rose-700', showArchived);
    btnToggleArchived.classList.toggle('ring-rose-300', showArchived);
  }
  updateArchBtn();

  function renumber(){
    let n=1;
    rows.forEach(r=>{
      if (r.style.display !== 'none') {
        const cell = r.querySelector('.idx'); if (cell) cell.textContent = n++;
      }
    });
  }

  function applyActionVisibility(tr){
    const isArch = tr.dataset.archived === '1';
    // buttons
    const btnNotify    = tr.querySelector('.act-notify');
    const btnSched     = tr.querySelector('.act-interview');
    const btnStatus    = tr.querySelector('.act-status');
    const btnForward   = tr.querySelector('.act-forward');
    const btnArchive   = tr.querySelector('.act-archive');
    const btnUnarchive = tr.querySelector('.act-unarchive');
    const btnDelete    = tr.querySelector('.act-delete');

    // Archived page: hide middle actions; show unarchive + delete; hide archive
    const hideMids = showArchived && isArch;

    [btnNotify,btnSched,btnStatus,btnForward].forEach(b=>{ if(b) b.style.display = hideMids?'none':''; });

    if (isArch){
      if (btnArchive)   btnArchive.style.display   = 'none';
      if (btnUnarchive) btnUnarchive.style.display = '';
      if (btnDelete)    btnDelete.style.display    = '';
    } else {
      if (btnArchive)   btnArchive.style.display   = '';
      if (btnUnarchive) btnUnarchive.style.display = 'none';
      if (btnDelete)    btnDelete.style.display    = 'none';
    }
  }

  function filter(){
    const q=(fQuery.value||'').toLowerCase();
    const st=(fStatus.value||'').toLowerCase();
    const rl=(fRole.value||'').toLowerCase();
    let any=false;

    rows.forEach(r=>{
      const hay = (r.dataset.name+' '+r.dataset.role+' '+(r.dataset.email||'')+' '+(r.dataset.mobile||'')).toLowerCase();
      const okQ = !q || hay.includes(q);
      const okS = !st || r.dataset.status===st;
      const okR = !rl || r.dataset.role===rl;
      const isArch = r.dataset.archived === '1';
      const okArch = showArchived ? isArch : !isArch;

      const show = okQ && okS && okR && okArch;
      r.style.display = show ? '' : 'none';
      if (show){ applyActionVisibility(r); any=true; }
    });

    if(!any){
      if(!document.getElementById('emptyRow')){
        const tr=document.createElement('tr'); tr.id='emptyRow';
        tr.innerHTML=`<td colspan="6" class="px-6 py-6 text-slate-500">No matching applicants.</td>`;
        document.getElementById('rows').appendChild(tr);
      }
    } else {
      const er=document.getElementById('emptyRow'); if(er) er.remove();
    }

    renumber();
  }
  [fQuery,fStatus,fRole].forEach(el=>el.addEventListener('input',filter));
  filter();

  /* ================== Toast & Modal ================== */
  const toast=(m)=>{ const t=document.getElementById('toast'); t.textContent=m; t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),2600); };
  const modal=document.getElementById('modal'), mTitle=document.getElementById('modalTitle'), mBody=document.getElementById('modalBody'), mOk=document.getElementById('modalOk'), mClose=document.getElementById('modalClose'), mFooter=document.getElementById('modalFooter');
  const openModal=(title,bodyHTML,okText=null,onOk=()=>{})=>{
    mTitle.textContent=title; mBody.innerHTML=bodyHTML;
    if(okText){ mOk.textContent=okText; mFooter.classList.remove('hidden'); }
    else { mFooter.classList.add('hidden'); }
    modal.classList.remove('hidden');
    const off=()=>{ modal.classList.add('hidden'); mOk.onclick=null; };
    mOk.onclick=()=>{ onOk(); off(); }; mClose.onclick=off; modal.onclick=(e)=>{ if(e.target===modal) off(); };
  };

  /* ================== API helpers ================== */
  async function api(payload){
    try{
      const r = await fetch('api/applicants_api.php',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      });
      return await r.json();
    }catch(err){ return {ok:false,error: String(err)}; }
  }
  /* ➕ Onboarding API (for auto-add) */
  async function apiOnboarding(payload){
    try{
      const r = await fetch('api/onboarding_api.php',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      });
      return await r.json();
    }catch(err){ return {ok:false,error:String(err)}; }
  }

  /* ➕ Employees API (auto-add when Hired) */
  async function apiEmployees(payload){
    try{
      const r = await fetch('api/employees_api.php',{
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      });
      return await r.json();
    }catch(err){ return {ok:false,error:String(err)}; }
  }

  async function apiApplicants(action, data) {
    const res = await fetch('api/applicants_api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
      body: new URLSearchParams({ action, ...data })
    });
    const j = await res.json();
    if (!j.ok) throw new Error(j.error || 'API error');
    return j.data;
  }

  // CHANGE STATUS → tatawag sa applicants_api.php
  async function changeApplicantStatus(id, status) {
    const data = await apiApplicants('change_status', { id, status });
    console.log('changed:', data);
    // TODO: refresh table / toast
  }

  // NOTIFY → tatawag sa applicants_api.php
  async function notifyApplicant(id, message, status='update', viaEmail=1, viaSMS=0) {
    const data = await apiApplicants('notify', { id, message, status, via_email: viaEmail, via_sms: viaSMS });
    console.log('notify results:', data);
    // TODO: toast
  }


  /* ================== Bell dot (demo) ================== */
  const bellDot=document.getElementById('bellDot');
  document.getElementById('bell').addEventListener('click', e=>{ e.preventDefault(); bellDot.classList.add('hidden'); toast('No new applicants.'); });

  /* ---------- Notify modal helper ---------- */
  function openNotifyModal(tr, statusHint=null){
    const id = tr.dataset.id;
    const name = tr.querySelector('td:nth-child(2)').textContent.trim();
    const email = tr.dataset.email || '';
    const status = (statusHint || tr.dataset.status || 'update');
    const msgDefault =
  `Hi ${name},
  This is to inform you that your application status is now: ${status.toUpperCase()}.
  Thank you for applying to HR1 MerchFlow.`;

    openModal('Notify Applicant', `
      <div class="text-sm text-slate-600">To</div>
      <div class="px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium">${email || '— no email on file —'}</div>
      <label class="text-sm text-slate-600 mt-3 block">Message</label>
      <textarea id="msg" rows="4" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200"
        placeholder="Write your message...">${msgDefault}</textarea>
      <div class="flex items-center gap-3 mt-2">
        <label class="text-sm"><input type="checkbox" id="viaEmail" checked class="mr-2">Email</label>
        <label class="text-sm"><input type="checkbox" id="viaSMS" class="mr-2">SMS</label>
      </div>
    `,'Send', async ()=>{
      const msg = document.getElementById('msg').value.trim();
      const viaE = document.getElementById('viaEmail').checked ? 1 : 0;
      const viaS = document.getElementById('viaSMS').checked   ? 1 : 0;
      if (!msg) { toast('Message is empty'); return; }
     const res = await api({
        action:'notify', applicant_id: id, to_email: email,
        status: status, message: msg, via_email: viaE, via_sms: viaS
      });
      toast(res.ok ? 'Notification sent' : (res.error || 'Notify failed'));
    });
  }

  // === Status helpers ===
  function normStatusCode(s){
    s = String(s||'').toLowerCase();
    const ok = ['new','pending','screening','interview','offered','rejected','hired','archived'];
    return ok.includes(s) ? s : 'new';
  }
  function statusLabelFromCode(code){
    const m = {
      new:'New', pending:'Pending', screening:'Screening', interview:'Interview',
      offered:'Offered', rejected:'Rejected', hired:'Hired', archived:'Archived'
    };
    return m[String(code||'').toLowerCase()] || 'New';
  }


  /* ================== Row actions ================== */
  document.getElementById('rows').addEventListener('click', async (e)=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const tr = btn.closest('tr');
    const id = tr.dataset.id;
    const name = tr.querySelector('td:nth-child(2)').textContent.trim();

    /* ---- View Details ---- */
    if(btn.classList.contains('act-view')){
      const html = `
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div><div class="text-slate-500">Full name</div><div class="font-medium">${escapeHtml(name)}</div></div>
          <div><div class="text-slate-500">Position applying for</div><div class="font-medium">${escapeHtml(cap(tr.dataset.role_raw||tr.dataset.role||''))}</div></div>
          <div><div class="text-slate-500">Email</div><div class="font-medium">${escapeHtml(tr.dataset.email||'—')}</div></div>
          <div><div class="text-slate-500">Mobile</div><div class="font-medium">${escapeHtml(tr.dataset.mobile||'—')}</div></div>
          <div class="sm:col-span-2"><div class="text-slate-500">Address</div><div class="font-medium">${escapeHtml(tr.dataset.address||'—')}</div></div>
          <div><div class="text-slate-500">Highest Education</div><div class="font-medium">${escapeHtml(tr.dataset.education||'—')}</div></div>
          <div><div class="text-slate-500">Years of Work Experience</div><div class="font-medium">${escapeHtml(tr.dataset.yoe||'0')}</div></div>
          <div><div class="text-slate-500">Preferred Start Date</div><div class="font-medium">${prettyDate(tr.dataset.start)||'—'}</div></div>
          <div><div class="text-slate-500">Status</div><div class="font-medium">${cap(tr.dataset.status)}</div></div>
        </div>
        <div class="mt-4 flex items-center justify-between bg-slate-50 rounded-xl p-3 text-sm">
          <div class="text-slate-600"><i class="fa-regular fa-file-lines mr-2"></i>Resume / CV</div>
          ${tr.dataset.cv ? `<a href="${tr.dataset.cv}" target="_blank" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700">Open CV</a>` : `<span class="text-slate-400">No file</span>`}
        </div>
      `;
      openModal('Application Details', html, null);
      return;
    }

    /* ---- Open CV ---- */
    if(btn.classList.contains('act-open-cv')){
      const link = tr.dataset.cv || '';
      if(!link){ toast('No CV uploaded'); return; }
      window.open(link, '_blank');
      return;
    }

 /* ---- Change Status (VISIBLE ang Interview) ---- */
if (btn.classList.contains('act-status')) {
  const current = (tr.dataset.status || '').toLowerCase(); // code
  openModal('Change Status', `
    <label class="text-sm text-slate-600">Status</label>
    <select id="newStatus" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
      ${['New','Screening','Interview','Rejected','Hired']
        .map(s => `<option value="${s}" ${s.toLowerCase()===current?'selected':''}>${s}</option>`).join('')}
    </select>
    <label class="inline-flex items-center gap-2 mt-3 text-sm">
      <input type="checkbox" id="chkNotifyAfter" checked>
      <span>Send email notification after updating</span>
    </label>
  `, 'Update', async () => {
    const nsLabel = document.getElementById('newStatus').value;       // e.g. "Interview"
    const nsCode  = (nsLabel || '').toLowerCase();                    // "interview"
    const notify  = document.getElementById('chkNotifyAfter').checked;

    // ✅ Gumamit ng form-urlencoded sa legacy route para siguradong tatama
    try {
      const res = await fetch('api/applicants_api.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({ action:'change_status', id: tr.dataset.id, status: nsCode })
      }).then(r=>r.json());

      if (!res.ok) throw new Error(res.error || 'API error');

      // update UI
      tr.dataset.status = nsCode;
      tr.querySelector('.status-label').textContent = nsLabel;
      toast('Status updated to ' + nsLabel);
      filter();

      if (notify) openNotifyModal(tr, nsLabel);
    } catch (e) {
      toast(e.message || 'Failed to update');
    }
  });
  return;
}




    /* ---- Archive ---- */
    if(btn.classList.contains('act-archive')){
      openModal('Archive Applicant', `<p class="text-sm text-slate-700">Move <b>${escapeHtml(name)}</b> to archive?</p>`,'Archive', async ()=>{
        const res = await api({action:'applicant.archive', applicant_id:id});
        if(res.ok){
          tr.dataset.archived='1';
          tr.dataset.status='archived';
          tr.querySelector('.status-label').textContent='Archived';
          applyActionVisibility(tr);
          toast('Archived');
          filter();
        } else {
          toast(res.error || 'Archive failed');
        }
      });
      return;
    }

    /* ---- Unarchive ---- */
    if(btn.classList.contains('act-unarchive')){
      openModal('Unarchive Applicant', `<p class="text-sm text-slate-700">Restore <b>${escapeHtml(name)}</b> to active applicants?</p>`,'Unarchive', async ()=>{
        const res = await api({action:'applicant.unarchive', applicant_id:id});
        if(res.ok){
          tr.dataset.archived='0';
          tr.dataset.status = (tr.dataset.status==='archived'?'pending':tr.dataset.status);
          tr.querySelector('.status-label').textContent = cap(tr.dataset.status);
          applyActionVisibility(tr);
          toast('Unarchived');
          filter();
        } else {
          toast(res.error || 'Unarchive failed');
        }
      });
      return;
    }

    /* ---- Delete (only for archived) ---- */
    if (btn.classList.contains('act-delete')) {
      openModal('Delete Applicant', `
        <p class="text-sm text-slate-700">Permanently delete <b>${escapeHtml(name)}</b>?</p>
      `, 'Delete', async ()=>{
        const res = await api({action:'applicant.delete', applicant_id:id});
        if (res.ok) { tr.remove(); toast('Deleted'); renumber(); }
        else { toast(res.error || 'Delete failed'); }
      });
      return;
    }

  /* ---- Schedule Interview ---- */
  if (btn.classList.contains('act-interview')) {
    openModal('Schedule Interview', `
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="text-sm text-slate-600">Date</label>
          <input type="date" id="ivDate" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
        </div>
        <div>
          <label class="text-sm text-slate-600">Time</label>
          <input type="time" id="ivTime" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
        </div>
      </div>
      <div>
        <label class="text-sm text-slate-600">Mode</label>
        <select id="ivMode" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
          <option>On-site</option><option>Video</option><option>Phone</option>
        </select>
      </div>
      <div>
        <label class="text-sm text-slate-600">Notes</label>
        <textarea id="ivNotes" rows="3" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="Panel, location, reminders…"></textarea>
      </div>
    `, 'Schedule', async () => {
      const d  = (document.getElementById('ivDate').value || '').trim();
      const t  = (document.getElementById('ivTime').value || '').trim();
      const md = (document.getElementById('ivMode').value || 'On-site').trim();
      const nt = (document.getElementById('ivNotes').value || '').trim();

      if (!d || !t) { toast('Please pick a date and time.'); return; }

          // 1) Save schedule (backend also sends email)
    const rSched = await api({
      action: 'applicant.schedule',
      applicant_id: id,
      date: d,
      time: t,
      mode: md,
      notes: nt
    });
    if (!rSched.ok) { toast(rSched.error || 'Failed to save schedule'); return; }

    // 2) Update UI to Screening
    tr.dataset.status = 'screening';
    tr.querySelector('.status-label').textContent = 'Screening';
    filter();

   // 3) Toast (neutral kapag walang email)
  if (rSched.emailed === true) {
    toast('Interview scheduled • Email sent');
  } else {
    toast('Interview scheduled');
  }



      const msg =
  `Hi ${name},

  This is to confirm your interview schedule:

  Date/Time: ${whenText}
  Mode: ${md}
  ${nt ? `Notes: ${nt}\n` : ''}

  Please reply to confirm your availability. Thank you.`;

      await api({
        action: 'applicant.notify',
        applicant_id: id,
        to_email: tr.dataset.email || '',
        status: 'Interview',
        message: msg,
        via_email: 1,
        via_sms: 0
      });
    });
    return;
  }


    /* ---- Notify ---- */
    if(btn.classList.contains('act-notify')){
      openNotifyModal(tr, tr.dataset.status || 'update');
      return;
    }

    /* ---- Forward ---- */
    if(btn.classList.contains('act-forward')){
      openModal('Forward Resume', `
        <label class="text-sm text-slate-600">Forward to Branch / Site</label>
        <select id="site" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
          <option>O!Save – Biñan</option><option>O!Save – Calamba</option>
          <option>O!Save – Sta. Rosa</option><option>DC – Laguna</option><option>HQ</option>
        </select>
        <label class="text-sm text-slate-600 mt-3 block">Note (optional)</label>
        <textarea id="note" rows="3" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="Why they’re a fit…"></textarea>
      `,'Forward', async ()=>{
        const site = document.getElementById('site').value;
        const note = document.getElementById('note').value;
        const res = await api({action:'applicant.forward', applicant_id:id, site, note});
        toast(res.ok ? ('Forwarded to '+site) : (res.error || 'Forward failed'));
      });
      return;
    }
  });

  /* Utilities */
  function prettyDate(s){
    if(!s) return '';
    const d = new Date(s); if(isNaN(d)) return s;
    return d.toLocaleDateString(undefined,{month:'short',day:'2-digit',year:'numeric'});
  }
  function cap(s){ s=s||''; return s.charAt(0).toUpperCase()+s.slice(1); }
  function escapeHtml(str){
    return (str||'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m]));
  }

  /* initial filter sync */
  filter();
  </script>
  </body>
  </html>
