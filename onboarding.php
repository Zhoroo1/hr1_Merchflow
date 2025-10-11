<?php
session_start();
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$u = $_SESSION['user'];

$role = strtolower($u['role'] ?? '');
$isAdminHr = in_array($role, ['admin','hr manager','hr','human resources'], true);


$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg'; // case-sensitive

require_once __DIR__ . '/includes/config.php'; // STORE_NAME / STORE_SITE

function isActive($page) {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Onboarding | HR1 <?= htmlspecialchars($brandName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    /* --- Base sizing for desktop --- */
    #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
    #sidebar .nav-item{padding:.6rem .85rem}
    #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
    #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}
    #contentWrap{padding-left:16rem;transition:padding .25s ease}
    #contentWrap.collapsed{padding-left:4rem}
    .dot-badge{position:absolute;top:-2px;right:-2px;width:16px;height:16px;border-radius:9999px;background:#ef4444;color:#fff;font-size:10px;line-height:16px;text-align:center}
    #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
    #drawerBackdrop{backdrop-filter: blur(2px)}

    /* ---------- Row alignment (compact + icons) ---------- */
    .row-grid{
      display:grid;
      grid-template-columns: 1fr 6rem 12rem 4rem 2.25rem 2.25rem;
      align-items:center;
      gap:.25rem;
    }
    .row-header{ font-size:.75rem; color:#64748b; }

    /* Icon buttons */
    .icon-btn{
      width:2rem; height:2rem;
      display:inline-flex; align-items:center; justify-content:center;
      border-radius:.5rem;
      color:#475569;
    }
    .icon-btn:hover{ background:#f1f5f9; }
    .icon-btn.danger{ color:#dc2626; }
    .icon-btn.danger:hover{ background:#fee2e2; }
    .icon-btn { cursor: pointer; }
    .task-editor { display: inline-flex; gap: .4rem; align-items: center; }
    .task-editor button { cursor: pointer; }


    /* ==================== MOBILE DRAWER SIDEBAR ==================== */
    #sidebar{ z-index:40; }
    @media (max-width:1024px){
      #sidebar{
        position: fixed;
        left: 0; top: 0; bottom: 0;
        width: 16rem;
        transform: translateX(-100%);
        transition: transform .25s ease;
      }
      #sidebar.show{ transform: translateX(0); }

      #contentWrap{ padding-left: 0 !important; }
      #topbarPad{ margin-left: 0 !important; }
    }
  </style>
</head>
<body class="bg-slate-50">

<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>

      <!-- removed global search bar -->
      <div class="flex-1"></div>

      <div class="flex items-center gap-2">
        <a href="#" class="relative w-10 h-10 grid place-items-center rounded-xl ring-1 ring-rose-300/70 text-rose-600 hover:bg-rose-50">
          <i class="fa-regular fa-bell"></i><span class="dot-badge">•</span>
        </a>
        <a href="#" class="relative w-10 h-10 grid place-items-center rounded-xl ring-1 ring-rose-300/70 text-rose-600 hover:bg-rose-50">
          <i class="fa-regular fa-envelope"></i><span class="dot-badge">•</span>
        </a>
        <div class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow-[0_8px_20px_rgba(0,0,0,.06)]">
          <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold">
            <?php echo strtoupper(substr($u['name'],0,2)); ?>
          </div>
          <div class="leading-tight pr-1">
            <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]"><?php echo htmlspecialchars($u['name']); ?></div>
            <div class="text-[11px] text-slate-500 capitalize"><?php echo htmlspecialchars($u['role']); ?></div>
          </div>
          <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
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

  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-8 py-8">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-rose-600">New Hire Onboarding</h1>
          <p class="text-xs text-slate-500">HR1 • Task tracking and start dates (<?php echo htmlspecialchars(STORE_NAME); ?>)</p>
        </div>
        <button id="btnAddHire" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg shadow">
          + Add New Hire
        </button>
      </div>

      <!-- KPIs -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="group rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-people-arrows"></i></div>
          <div><div class="text-xs text-slate-500">In Onboarding</div><div class="text-lg font-semibold" id="kpiIn">0</div></div>
        </div>
        <div class="group rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-calendar-day"></i></div>
          <div><div class="text-xs text-slate-500">Tasks Due Today</div><div class="text-lg font-semibold" id="kpiDue">0</div></div>
        </div>
        <div class="group rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 grid place-items-center"><i class="fa-solid fa-clock-rotate-left"></i></div>
          <div><div class="text-xs text-slate-500">Overdue Tasks</div><div class="text-lg font-semibold" id="kpiOverdue">0</div></div>
        </div>
        <div class="group rounded-xl bg-white ring-1 ring-slate-200 p-4 flex items-center gap-3">
          <div><i class="fa-solid fa-signal w-10 h-10 grid place-items-center rounded-lg bg-rose-100 text-rose-600"></i></div>
          <div><div class="text-xs text-slate-500">Avg Completion</div><div class="text-lg font-semibold"><span id="kpiAvg">0</span>%</div></div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative w-full sm:w-80">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
          <input id="fQuery" type="text" placeholder="Search name…"
            class="w-full pl-8 pr-3 py-2 rounded-lg bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-300">
        </div>
        <select id="fStatus" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-300">
          <option value="">All statuses</option>
          <option>Pending</option>
          <option>In Progress</option>
          <option>Completed</option>
        </select>
      </div>

      <!-- Data Panels -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-xl bg-white ring-1 ring-slate-200">
          <div class="px-4 py-3 border-b border-slate-200">
            <div class="flex items-center justify-between">
              <h2 class="font-semibold text-slate-800">Active Onboarding</h2>
            </div>
            <div class="pt-2 row-grid row-header">
              <div></div>
              <div></div>
              <div></div>
              <div>Live data</div>
              <div class="col-span-2 text-center">Actions</div>
            </div>
          </div>

          <div class="divide-y divide-slate-200" id="hireList"></div>
        </div>

        <div class="space-y-6">
          <div class="rounded-xl bg-white ring-1 ring-slate-200">
            <div class="px-4 py-3 border-b border-slate-200"><h2 class="font-semibold text-slate-800">Upcoming Start Dates</h2></div>
            <div class="divide-y divide-slate-200" id="upcomingList"></div>
          </div>
          <div class="rounded-xl bg-white ring-1 ring-slate-200">
            <div class="px-4 py-3 border-b border-slate-200"><h2 class="font-semibold text-slate-800">Overdue Tasks</h2></div>
            <div class="divide-y divide-slate-200" id="overdueList"></div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Add Hire Modal -->
<div id="addHireModal" class="fixed inset-0 z-50 hidden">
  <div id="addHireBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center px-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-800">Add New Hire</h3>
        <button id="btnAddHireClose" class="w-9 h-9 grid place-items-center rounded-lg hover:bg-slate-100">
          <i class="fa-solid fa-xmark text-slate-500"></i>
        </button>
      </div>
      <form id="addHireForm" class="px-5 py-4 space-y-4">
        <div>
          <label class="text-sm text-slate-600">Full name <span class="text-rose-600">*</span></label>
          <input name="hire_name" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-300" placeholder="e.g., Ana Villanueva">
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
         <div>
            <label class="text-sm text-slate-600">Role <span class="text-rose-600">*</span></label>
            <select name="role" required
              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-300">
              <option value="" disabled selected>Select role</option>
              <option value="Store Part Timer">Store Part Timer</option>
              <option value="Cashier">Cashier</option>
              <option value="Merchandiser / Promodiser">Merchandiser / Promodiser</option>
              <option value="Inventory Clerk / Stockman">Inventory Clerk / Stockman</option>
              <option value="Order Processor">Order Processor</option>
              <option value="Deputy Store Manager">Deputy Store Manager</option>
              <option value="Store Manager">Store Manager</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-slate-600">Site / Location</label>
            <input name="site" value="<?php echo htmlspecialchars(STORE_SITE); ?>" readonly
              class="mt-1 w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-600 px-3 py-2">
          </div>
        </div>
        <div>
          <label class="text-sm text-slate-600">Start date <span class="text-rose-600">*</span></label>
          <input name="start_date" type="date" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-300">
        </div>
        <div id="addHireError" class="hidden text-sm text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2"></div>
        <div class="pt-2 flex items-center justify-end gap-2">
          <button type="button" id="btnAddHireCancel" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
          <button type="submit" id="btnAddHireSave" class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 disabled:opacity-60 disabled:cursor-not-allowed">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Hire Modal -->
<div id="editHireModal" class="fixed inset-0 z-50 hidden">
  <div id="editHireBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center px-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-slate-800">Edit Hire</h3>
        <button id="btnEditHireClose" class="w-9 h-9 grid place-items-center rounded-lg hover:bg-slate-100">
          <i class="fa-solid fa-xmark text-slate-500"></i>
        </button>
      </div>
     <form id="editHireForm" class="px-5 py-4 space-y-4">
      <input type="hidden" name="id" value="0">

      <div>
        <label class="text-sm text-slate-600">Full name <span class="text-rose-600">*</span></label>
        <input name="hire_name" required
          class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-300">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm text-slate-600">Role <span class="text-rose-600">*</span></label>
          <select name="role" required 
            class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-300">
            <option value="" disabled selected>Select role</option>
            <option value="Store Part Timer">Store Part Timer</option>
            <option value="Cashier">Cashier</option>
            <option value="Merchandiser / Promodiser">Merchandiser / Promodiser</option>
            <option value="Inventory Clerk / Stockman">Inventory Clerk / Stockman</option>
            <option value="Order Processor">Order Processor</option>
            <option value="Deputy Store Manager">Deputy Store Manager</option>
            <option value="Store Manager">Store Manager</option>
          </select>
        </div>

        <div>
          <label class="text-sm text-slate-600">Site / Location</label>
          <input name="site" value="<?php echo htmlspecialchars(STORE_SITE); ?>" readonly
            class="mt-1 w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-600 px-3 py-2">
        </div>
      </div>

      <div>
        <label class="text-sm text-slate-600">Start date <span class="text-rose-600">*</span></label>
        <input name="start_date" type="date" required
          class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-rose-300">
      </div>

      <div id="editHireError" class="hidden text-sm text-rose-600 bg-rose-50 border border-rose-200 rounded-lg px-3 py-2"></div>

      <div class="pt-2 flex items-center justify-end gap-2">
        <button type="button" id="btnEditHireCancel" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</button>
        <button type="submit" id="btnEditHireSave" class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 disabled:opacity-60 disabled:cursor-not-allowed">Save changes</button>
      </div>
    </form>

    </div>
  </div>
</div>

<!-- Details Modal -->
<div id="drawer" class="fixed inset-0 z-50 hidden">
  <div id="drawerBackdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl ring-1 ring-slate-200 flex flex-col max-h-[85vh]">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold" id="drawerTitle">Onboarding</h3>
        <button id="drawerClose" class="w-9 h-9 grid place-items-center rounded-lg hover:bg-slate-100">
          <i class="fa-solid fa-xmark text-slate-500"></i>
        </button>
      </div>
      <div class="p-4 space-y-4 overflow-auto" id="drawerBody"></div>
      <div class="p-3 border-t border-slate-200 flex items-center gap-2">
       <input id="newTaskTitle" class="flex-1 rounded-lg border border-slate-300 px-3 py-2" placeholder="New task title">
        <input type="hidden" id="newTaskOwner" value="Employee">
        <span class="rounded-lg border border-slate-300 bg-slate-50 text-slate-600 px-3 py-2 select-none">Employee</span>
        <input id="newTaskDue" type="date" class="rounded-lg border border-slate-300 px-2 py-2">
        <button id="newTaskAdd" class="px-3 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700">Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="fixed inset-0 z-[60] hidden">
  <div id="confirmBackdrop" class="absolute inset-0 bg-black/40"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-slate-200">
      <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h3 id="confirmTitle" class="text-lg font-semibold text-slate-800">Confirm</h3>
        <button id="confirmClose" class="w-9 h-9 grid place-items-center rounded-lg hover:bg-slate-100">
          <i class="fa-solid fa-xmark text-slate-500"></i>
        </button>
      </div>
      <div id="confirmMessage" class="px-5 py-4 text-sm text-slate-700">
        Are you sure?
      </div>
      <div class="px-5 py-4 border-t border-slate-200 flex items-center justify-end gap-2">
        <button id="confirmCancel"
                class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">
          Cancel
        </button>
        <button id="confirmOk"
                class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700">
          Delete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed inset-x-0 top-16 z-[60] hidden flex justify-center pointer-events-none">
  <div id="toastMsg"
       class="pointer-events-auto rounded-xl bg-slate-900 text-white
              text-base md:text-lg font-medium tracking-wide
              px-5 md:px-6 py-3 md:py-3.5 shadow-lg ring-1 ring-black/10">
  </div>
</div>

<script>
// ===== Confirm modal (reusable) =====
window.openConfirm = function({ title='Confirm', message='Are you sure?', okText='OK', onOk=()=>{} } = {}) {
  const root=document.getElementById('confirmModal');
  const titleEl=document.getElementById('confirmTitle');
  const msgEl=document.getElementById('confirmMessage');
  const okBtn=document.getElementById('confirmOk');
  const cancel=document.getElementById('confirmCancel');
  const closeBtn=document.getElementById('confirmClose');
  const backdrop=document.getElementById('confirmBackdrop');

  if (!root) { if (confirm(message)) onOk(); return; }

  titleEl.textContent=title; msgEl.textContent=message; okBtn.textContent=okText; root.classList.remove('hidden');
  const cleanup=()=>{ root.classList.add('hidden'); okBtn.onclick=cancel.onclick=closeBtn.onclick=backdrop.onclick=null; document.removeEventListener('keydown', escHandler); };
  const escHandler=(e)=>{ if(e.key==='Escape') cleanup(); };
  okBtn.onclick=async()=>{ try{ await onOk(); } finally{ cleanup(); } };
  cancel.onclick=cleanup; closeBtn.onclick=cleanup; backdrop.onclick=cleanup; document.addEventListener('keydown', escHandler);
};

document.addEventListener('DOMContentLoaded', () => {
  /* ===== Sidebar / layout ===== */
  const btnSidebar=document.getElementById('btnSidebar');
  const sb=document.getElementById('sidebar');
  const scrim=document.getElementById('sbScrim');
  const main=document.getElementById('contentWrap');
  const topbarPad=document.getElementById('topbarPad');

  function isMobile(){ return window.matchMedia('(max-width:1024px)').matches; }
  function applyDesktopShift(){ if (sb.classList.contains('collapsed')) { topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16'); main.classList.add('collapsed'); } else { topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64'); main.classList.remove('collapsed'); } }
  function syncLayout(){
    if (isMobile()){
      sb.classList.remove('collapsed'); sb.classList.remove('show'); scrim.classList.add('hidden'); topbarPad.classList.remove('ml-64','ml-16'); main.classList.remove('collapsed');
    } else {
      scrim.classList.add('hidden'); sb.classList.remove('show');
      if (localStorage.getItem('sb-collapsed') === '1') sb.classList.add('collapsed'); else sb.classList.remove('collapsed');
      if (!topbarPad.classList.contains('ml-64') && !topbarPad.classList.contains('ml-16')) topbarPad.classList.add('ml-64');
      applyDesktopShift();
    }
  }
  btnSidebar?.addEventListener('click', ()=>{ if (isMobile()){ const open=!sb.classList.contains('show'); sb.classList.toggle('show', open); scrim.classList.toggle('hidden', !open); } else { sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed') ? '1' : '0'); applyDesktopShift(); } });
  scrim?.addEventListener('click', ()=>{ sb.classList.remove('show'); scrim.classList.add('hidden'); });
  window.addEventListener('resize', ()=>{ clearTimeout(window.__sb_rsz); window.__sb_rsz=setTimeout(syncLayout,120); });
  syncLayout();

  /* ===== API helper ===== */
  async function api(action, payload = {}) {
    const res = await fetch('api/onboarding_api.php', {
      method: 'POST',
      cache: 'no-store',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, ...payload })
    });
    const raw = await res.text();
    let j; try { j = JSON.parse(raw); } catch (e) { console.error('API raw response:', raw); throw new Error((raw || '').trim().slice(0, 600) || 'Invalid JSON from server'); }
    if (!j.ok) throw new Error(j.error || 'API error');
    return j;
  }

  /* ===== Elements ===== */
  const els = {
    hireList: document.getElementById('hireList'),
    upcomingList: document.getElementById('upcomingList'),
    overdueList: document.getElementById('overdueList'),
    kpiIn: document.getElementById('kpiIn'), kpiDue: document.getElementById('kpiDue'),
    kpiOverdue: document.getElementById('kpiOverdue'), kpiAvg: document.getElementById('kpiAvg'),
    fQuery: document.getElementById('fQuery'), fStatus: document.getElementById('fStatus'),
    btnAddHire: document.getElementById('btnAddHire'),
  };

  /* ===== Suggested tasks per role (auto-generate) ===== */
const ROLE_TASKS = {
  "__COMMON__": [
    { t:"Submit pre-employment requirements", d:-2 },
    { t:"Sign contract & NDA", d:0 },
    { t:"Attend HR orientation (policies & house rules)", d:0 },
    { t:"Enroll to biometric/timekeeping", d:0 },
    { t:"Safety & emergency briefing", d:0 },
    { t:"Store tour & meet the team", d:1 },
    { t:"Understand attendance & payroll cutoffs", d:1 },
    { t:"30-Day check-in", d:30 },
    { t:"90-Day probationary evaluation", d:90 },
  ],
  "Store Part Timer": [
    { t:"Product & POS quick start", d:1 },
    { t:"Opening/closing checklist coaching", d:2 },
    { t:"Customer service standards", d:3 },
    { t:"Merchandising basics (facing, FIFO)", d:3 },
  ],
  "Cashier": [
    { t:"POS & cash handling training", d:1 },
    { t:"End-of-day balancing practice", d:2 },
    { t:"Refund/void/exchange policy", d:3 },
  ],
  "Merchandiser / Promodiser": [
    { t:"Planogram & display rules", d:1 },
    { t:"FIFO/FEFO & shelf-life checks", d:2 },
    { t:"Promo & price tag compliance", d:3 },
  ],
  "Inventory Clerk / Stockman": [
    { t:"Warehouse safety orientation", d:1 },
    { t:"Receiving & stock transfer SOP", d:2 },
    { t:"Cycle count procedure", d:3 },
  ],
  "Order Processor": [
    { t:"Order picking/packing flow", d:1 },
    { t:"Dispatch cutoff & SLA", d:2 },
    { t:"Returns/exceptions handling", d:3 },
  ],
  "Deputy Store Manager": [
    { t:"Daily store KPIs dashboard", d:3 },
    { t:"People management basics", d:7 },
    { t:"Loss prevention & incident reporting", d:10 },
  ],
  "Store Manager": [
    { t:"Leadership & compliance training", d:3 },
    { t:"Prepare staff duty roster", d:7 },
    { t:"Coordinate probationary reviews", d:30 },
  ],
};

function addDays(dateStr, n){
  if(!dateStr) return "";
  const d=new Date(dateStr.replace(/-/g,'/'));
  d.setDate(d.getDate()+Number(n||0));
  return d.toISOString().slice(0,10);
}

async function addSuggestedTasksForPlan(plan){
  const base = (ROLE_TASKS["__COMMON__"]||[]);
  const roleSet = (ROLE_TASKS[plan.role]||[]);
  const items = [...base, ...roleSet];
  if (!items.length) return;

  for (const it of items){
    await api('add_task', {
      plan_id: plan.id,
      title: it.t,
      owner: 'Employee',               // HR assigns; Employee completes
      due_date: it.d!=null ? addDays(plan.start_date, it.d) : null
    });
  }
}


  /* ===== Render helpers ===== */
  function badgeClass(s){ if(s==='Completed')return'text-green-600'; if(s==='In Progress')return'text-orange-600'; return'text-slate-600'; }
  function escapeHtml(s){ return (s??'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function fmtDate(d, short=true){ if(!d)return''; const dt=new Date(d.replace(/-/g,'/')); const opt=short?{month:'short',day:'2-digit'}:{month:'short',day:'2-digit',year:'numeric'}; return dt.toLocaleDateString(undefined,opt); }

  function renderPlans(plans){
    els.hireList.innerHTML='';
    if(!plans.length){ els.hireList.innerHTML='<div class="px-4 py-6 text-sm text-slate-500">No records found.</div>'; return; }
    plans.forEach(h=>{
      const pct = Math.max(0, Math.min(100, Number(h.progress||0)));
      const row = document.createElement('div');
      row.className = 'px-4 py-3 row-grid';
      row.innerHTML = `
        <div class="min-w-0">
          <div class="font-medium text-slate-800 truncate">
            ${escapeHtml(h.hire_name)} <span class="text-slate-600">• ${escapeHtml(h.role||'')}</span>
          </div>
          <div class="text-xs text-slate-500">
            <?php echo htmlspecialchars(STORE_SITE); ?> •
            <span class="font-medium ${badgeClass(h.status)}">${escapeHtml(h.status)}</span>
          </div>
        </div>
        <div class="text-xs text-slate-500">Start: ${fmtDate(h.start_date)}</div>
        <div>
          <div class="h-2 rounded-full bg-slate-200 overflow-hidden"><div class="h-full bg-rose-500" style="width:${pct}%"></div></div>
          <div class="text-[11px] text-slate-500 mt-1">${pct}% complete</div>
        </div>
        <button class="text-rose-600 hover:underline text-sm" data-open="${h.id}" title="Open">Open</button>
        <button class="icon-btn" data-edit="${h.id}" title="Edit"><i class="fa-regular fa-pen-to-square"></i></button>
        <button class="icon-btn danger" data-del="${h.id}" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
      `;
      els.hireList.appendChild(row);
    });
  }

  function renderUpcoming(list){
    els.upcomingList.innerHTML = list.length?'' : '<div class="px-4 py-6 text-sm text-slate-500">—</div>';
    list.forEach(u=>{
      const div=document.createElement('div'); div.className='px-4 py-3';
      div.innerHTML=`<div class="font-medium text-slate-800">${escapeHtml(u.hire_name)}</div>
        <div class="text-xs text-slate-500">${escapeHtml(u.role||'')} • <?php echo htmlspecialchars(STORE_SITE); ?></div>
        <div class="text-xs text-rose-600 font-medium mt-1">${fmtDate(u.start_date,true)}</div>`;
      els.upcomingList.appendChild(div);
    });
  }
  function renderOverdue(list){
    els.overdueList.innerHTML = list.length?'' : '<div class="px-4 py-6 text-sm text-slate-500">—</div>';
    list.forEach(o=>{
      const age=Number(o.age_days||0), div=document.createElement('div'); div.className='px-4 py-3';
      div.innerHTML=`<div class="font-medium text-slate-800">${escapeHtml(o.hire)}</div>
        <div class="text-xs text-slate-500">Task: ${escapeHtml(o.task)} • Owner: ${escapeHtml(o.owner)}</div>
        <div class="text-xs text-rose-600 font-medium mt-1">Due ${fmtDate(o.due,true)} • ${age}d overdue</div>`;
      els.overdueList.appendChild(div);
    });
  }
  function setKPI(k){ els.kpiIn.textContent=k.in_onboarding??0; els.kpiDue.textContent=k.due_today??0; els.kpiOverdue.textContent=k.overdue??0; els.kpiAvg.textContent=k.avg_completion??0; }

  async function loadList(){
    const q=els.fQuery?.value||''; const status=els.fStatus?.value||'';
    const {data}=await api('list',{q,status});
    renderPlans(data.plans); renderUpcoming(data.upcoming); renderOverdue(data.overdue); setKPI(data.kpi);
    return true;
  }

  function debounce(fn,ms){let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms);} }
  els.fQuery?.addEventListener('input',debounce(loadList,250));
  els.fStatus?.addEventListener('change',loadList);

  /* ===== Add Hire ===== */
  const addRefs={ modal:document.getElementById('addHireModal'), form:document.getElementById('addHireForm'),
    error:document.getElementById('addHireError'), save:document.getElementById('btnAddHireSave'),
    cancel:document.getElementById('btnAddHireCancel'), close:document.getElementById('btnAddHireClose'),
    backdrop:document.getElementById('addHireBackdrop') };
  function todayStr(){ return new Date().toISOString().slice(0,10); }
  function showAddHire(){ addRefs.form.reset(); addRefs.error.classList.add('hidden'); addRefs.error.textContent=''; addRefs.form.elements['start_date'].value=todayStr(); addRefs.modal.classList.remove('hidden'); setTimeout(()=> addRefs.form.elements['hire_name'].focus(), 0); }
  function hideAddHire(){ addRefs.modal.classList.add('hidden'); }
  document.getElementById('btnAddHire')?.addEventListener('click', e=>{ e.preventDefault(); showAddHire(); });
  addRefs.cancel?.addEventListener('click', hideAddHire); addRefs.close?.addEventListener('click', hideAddHire); addRefs.backdrop?.addEventListener('click', hideAddHire);
  document.addEventListener('keydown', e=>{ if(e.key==='Escape' && !addRefs.modal.classList.contains('hidden')) hideAddHire(); });

  addRefs.form?.addEventListener('submit', async e=>{
    e.preventDefault();
    addRefs.error.classList.add('hidden'); addRefs.error.textContent='';
    const fd=new FormData(addRefs.form);
    const payload={ hire_name:fd.get('hire_name')?.toString().trim(), role:fd.get('role')?.toString().trim(), start_date:fd.get('start_date')?.toString() };
    if (!payload.hire_name || !payload.role || !payload.start_date){ addRefs.error.textContent='Please complete all required fields (Full name, Role, Start date).'; addRefs.error.classList.remove('hidden'); return; }
    addRefs.save.disabled=true; addRefs.save.textContent='Saving...';
    try{ await api('add_plan',payload); hideAddHire(); showToast('New hire added.'); await loadList(); }
    catch(err){ addRefs.error.textContent=(err?.message||'Failed to add.'); addRefs.error.classList.remove('hidden'); }
    finally{ addRefs.save.disabled=false; addRefs.save.textContent='Save'; }
  });

  /* ===== Edit Hire ===== */
  const editRefs={ modal:document.getElementById('editHireModal'), form:document.getElementById('editHireForm'),
    error:document.getElementById('editHireError'), save:document.getElementById('btnEditHireSave'),
    cancel:document.getElementById('btnEditHireCancel'), close:document.getElementById('btnEditHireClose'),
    backdrop:document.getElementById('editHireBackdrop') };
  function showEdit(){ editRefs.modal.classList.remove('hidden'); setTimeout(()=> editRefs.form.elements['hire_name'].focus(), 0); }
  function hideEdit(){ editRefs.modal.classList.add('hidden'); }
  editRefs.cancel?.addEventListener('click', hideEdit); editRefs.close?.addEventListener('click', hideEdit); editRefs.backdrop?.addEventListener('click', hideEdit);
  document.addEventListener('keydown', e=>{ if(e.key==='Escape' && !editRefs.modal.classList.contains('hidden')) hideEdit(); });

  editRefs.form?.addEventListener('submit', async e=>{
    e.preventDefault();
    editRefs.error.classList.add('hidden'); editRefs.error.textContent='';
    const fd=new FormData(editRefs.form);
    const payload={ id:Number(fd.get('id')||0), hire_name:fd.get('hire_name')?.toString().trim(), role:fd.get('role')?.toString().trim(), start_date:fd.get('start_date')?.toString() };
    if (!payload.id || !payload.hire_name || !payload.role || !payload.start_date){ editRefs.error.textContent='Please complete all required fields.'; editRefs.error.classList.remove('hidden'); return; }
    editRefs.save.disabled=true; editRefs.save.textContent='Saving...';
    try{ await api('update_plan',payload); hideEdit(); showToast('Saved changes'); await loadList(); }
    catch(err){ editRefs.error.textContent=(err?.message||'Failed to save.'); editRefs.error.classList.remove('hidden'); }
    finally{ editRefs.save.disabled=false; editRefs.save.textContent='Save changes'; }
  });

  /* ===== Drawer ===== */
  const drawer={ root:document.getElementById('drawer'), body:document.getElementById('drawerBody'),
    title:document.getElementById('drawerTitle'), close:document.getElementById('drawerClose'),
    addBtn:document.getElementById('newTaskAdd'), newTitle:document.getElementById('newTaskTitle'),
    newOwner:document.getElementById('newTaskOwner'), newDue:document.getElementById('newTaskDue') };
  let currentPlanId=null;

  function showDrawer(){ drawer.root.classList.remove('hidden'); document.body.style.overflow='hidden'; }
  function hideDrawer(){ drawer.root.classList.add('hidden'); drawer.body.innerHTML=''; currentPlanId=null; document.body.style.overflow=''; }
  drawer.close?.addEventListener('click', hideDrawer);
  document.getElementById('drawerBackdrop')?.addEventListener('click', hideDrawer);
  document.addEventListener('keydown', (e)=>{ if (e.key==='Escape' && !drawer.root.classList.contains('hidden')) hideDrawer(); });

  function renderDrawer(data){
  const p = data.plan;
  const pct = Math.max(0, Math.min(100, Number(p.progress || 0)));
  drawer.title.textContent = p.hire_name;

  // ONE opening backtick here ↓ and ONE closing backtick at the very end ↑
  drawer.body.innerHTML = `
    <div class="rounded-lg ring-1 ring-slate-200 p-4">
      <div class="font-medium">${escapeHtml(p.hire_name)}</div>
      <div class="text-xs text-slate-500">
        <?php echo htmlspecialchars(STORE_SITE); ?> • ${escapeHtml(p.role || '')} • ${escapeHtml(p.status)}
      </div>
      <div class="mt-2 h-2 rounded-full bg-slate-200 overflow-hidden">
        <div class="h-full bg-rose-500" style="width:${pct}%"></div>
      </div>
      <div class="text-[11px] text-slate-500 mt-1">
        ${pct}% complete • Start ${fmtDate(p.start_date)}
      </div>
    </div>

    <div class="rounded-lg ring-1 ring-slate-200">
      <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
        <div class="font-semibold">Tasks</div>
        <button class="text-sm px-3 py-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700"
                data-suggest="${escapeHtml(p.role || '')}">
          Add suggested tasks
        </button>
      </div>
      <div id="taskList"></div>
    </div>
  `;

  const listEl = drawer.body.querySelector('#taskList');
  if (!data.tasks.length) {
    listEl.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500">No tasks yet.</div>';
  } else {
    data.tasks.forEach(t => {
      const li = document.createElement('div');
      li.className = 'px-4 py-3 border-t border-slate-100 flex items-center justify-between gap-3';
       li.innerHTML = `
        <div class="min-w-0">
          <div class="text-sm text-slate-800 truncate">${escapeHtml(t.title)}</div>
          <div class="text-[11px] text-slate-500">
            ${escapeHtml(t.owner)} • ${t.due_date ? ('Due ' + fmtDate(t.due_date,true)) : 'No due date'}
          </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <select data-task="${t.id}" class="text-sm rounded-lg border border-slate-300 px-2 py-1">
            <option${t.status==='Pending'?' selected':''}>Pending</option>
            <option${t.status==='In Progress'?' selected':''}>In Progress</option>
            <option${t.status==='Completed'?' selected':''}>Completed</option>
          </select>

          <!-- EDIT DUE -->
          <button type="button" class="icon-btn" title="Edit due date" data-task-edit="${t.id}">
            <i class="fa-regular fa-calendar"></i>
          </button>

          <!-- DELETE -->
          <button type="button" class="icon-btn danger" title="Delete task" data-task-del="${t.id}">
            <i class="fa-regular fa-trash-can"></i>
          </button>
        </div>`;


      listEl.appendChild(li);
    });
  }
}


  async function refreshPlanAndList(){ if (!currentPlanId) return; const {data}=await api('get_plan',{id:currentPlanId}); renderDrawer(data); await loadList(); }

  // Open / Edit / Delete
  document.getElementById('hireList').addEventListener('click', async (e)=>{
    const el = e.target.closest('[data-open],[data-edit],[data-del]'); if (!el) return;
    const idOpen = el.getAttribute('data-open');
    const idEdit = el.getAttribute('data-edit');
    const idDel  = el.getAttribute('data-del');

    if (idOpen){
      const {data}=await api('get_plan',{id:Number(idOpen)});
      currentPlanId=data.plan.id; renderDrawer(data); showDrawer(); return;
    }
    if (idEdit){
      const {data}=await api('get_plan',{id:Number(idEdit)});
      const f = document.getElementById('editHireForm');
      f.elements['id'].value = data.plan.id;
      f.elements['hire_name'].value = data.plan.hire_name || '';
      f.elements['role'].value = data.plan.role || '';
      f.elements['start_date'].value = (data.plan.start_date||'').slice(0,10);
      document.getElementById('editHireError').classList.add('hidden');
      document.getElementById('editHireError').textContent='';
      document.getElementById('editHireModal').classList.remove('hidden');
      return;
    }
    if (idDel){
      openConfirm({
        title: 'Delete Onboarding Plan',
        message: 'Delete this onboarding plan? This will also remove its tasks.',
        okText: 'Delete',
        onOk: async () => {
          await api('delete_plan', { id: Number(idDel) });
          if (currentPlanId && Number(idDel) === Number(currentPlanId)) document.getElementById('drawer').classList.add('hidden');
          await loadList(); showToast('Deleted');
        }
      });
      return;
    }
  });

  // Task status change
  document.getElementById('drawerBody').addEventListener('change', async (e)=>{
    const sel=e.target; const tid=sel?.getAttribute('data-task'); if(!tid) return;
    const chosen = sel.value; sel.disabled = true;
    try{ await api('set_task_status',{task_id:Number(tid), status:chosen}); await refreshPlanAndList(); showToast('Updated'); }
    catch(err){ showToast(err?.message||'Update failed'); }
    finally{ sel.disabled = false; }
  });

  // Delete task
  document.getElementById('drawerBody').addEventListener('click', async (e)=>{
    const delBtn = e.target.closest('[data-task-del]'); if (!delBtn) return;
    const tid = Number(delBtn.getAttribute('data-task-del') || 0); if (!tid) return;
    openConfirm({ title:'Delete Task', message:'Remove this task from the onboarding plan?', okText:'Delete', onOk: async()=>{ await api('delete_task', { task_id: tid }); await refreshPlanAndList(); showToast('Task deleted'); } });
  });

  // === ENTER EDIT MODE (calendar icon) ===
document.getElementById('drawerBody').addEventListener('click', (e) => {
  const editBtn = e.target.closest('[data-task-edit]');
  if (!editBtn) return;
  const tid = Number(editBtn.getAttribute('data-task-edit') || 0);
  if (!tid) return;

  // Palitan lang ang maliit na area na may due buttons
  // Target: ang container ng editBtn (yung <div class="flex items-center ...">)
  const actionCell = editBtn.parentElement;
  // kunin ang kasalukuyang due sa maliit na text sa taas kung gusto mo; puwede ring blank
  const current = actionCell.querySelector('input[type="date"]')?.value || '';

  actionCell.innerHTML = `
    <input type="date"
           class="rounded-lg border border-slate-300 px-2 py-1"
           data-task-date="${tid}" value="${current}">
    <button type="button" class="icon-btn ring-1 ring-slate-200 bg-slate-100"
            title="Save" data-task-save="${tid}">
      <i class="fa-regular fa-floppy-disk"></i>
    </button>
    <button type="button" class="icon-btn danger"
            title="Cancel" data-task-cancel="${tid}">
      <i class="fa-regular fa-circle-xmark"></i>
    </button>
  `;
});

// === SAVE DUE DATE ===
document.getElementById('drawerBody').addEventListener('click', async (e) => {
  const saveBtn = e.target.closest('[data-task-save]');
  if (!saveBtn) return;
  const tid = Number(saveBtn.getAttribute('data-task-save') || 0);
  if (!tid) return;

  const dateInput = document.querySelector(`[data-task-date="${tid}"]`);
  const newDue = dateInput ? dateInput.value : '';

  try {
    await api('set_task_due', { task_id: tid, due_date: newDue || null });
    await refreshPlanAndList();
    showToast('Due date updated');
  } catch (err) {
    showToast(err?.message || 'Failed to update due date');
  }
});

// === CANCEL EDIT ===
document.getElementById('drawerBody').addEventListener('click', async (e) => {
  const cancelBtn = e.target.closest('[data-task-cancel]');
  if (!cancelBtn) return;
  await refreshPlanAndList(); // ibalik ang normal na view
});



  // Add suggested tasks button
document.getElementById('drawerBody').addEventListener('click', async (e)=>{
  const btn = e.target.closest('[data-suggest]'); if (!btn) return;
  if (!currentPlanId) return;
  try{
    const {data} = await api('get_plan', { id: currentPlanId });
    await addSuggestedTasksForPlan(data.plan);
    await refreshPlanAndList();
    showToast('Suggested tasks added');
  }catch(err){
    showToast(err?.message || 'Failed to add suggested tasks');
  }
});


  // Add task
  document.getElementById('newTaskAdd')?.addEventListener('click', async ()=>{
    if (!currentPlanId) return;
    const title=document.getElementById('newTaskTitle').value.trim(); if(!title) return document.getElementById('newTaskTitle').focus();
    try{
      await api('add_task',{plan_id:currentPlanId,title,owner:document.getElementById('newTaskOwner').value,due_date:document.getElementById('newTaskDue').value||null});
      document.getElementById('newTaskTitle').value=''; document.getElementById('newTaskDue').value='';
      await refreshPlanAndList(); showToast('Task added');
    }catch(err){ showToast(err?.message||'Add failed'); }
  });

  function showToast(msg){ const t=document.getElementById('toast'); const m=document.getElementById('toastMsg'); if(!t) return alert(msg); m.textContent=msg||''; t.classList.remove('hidden'); setTimeout(()=>t.classList.add('hidden'),2200); }

  // Initial load
  loadList().catch(err=>showToast(err?.message||'Failed to load'));
});
</script>
</body>
</html>
