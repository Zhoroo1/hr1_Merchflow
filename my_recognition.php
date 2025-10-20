<?php
require_once __DIR__.'/includes/auth.php';
require_role(['employee']);
require_once __DIR__.'/includes/db.php';
require __DIR__ . '/includes/idle_logout.php';

date_default_timezone_set('Asia/Manila');
try { $pdo->exec("SET time_zone = '+08:00'"); } catch(Throwable $e) {}
$u = $_SESSION['user'] ?? [];

function tbl_exists(PDO $pdo,$t){ try{return (bool)$pdo->query("SHOW TABLES LIKE ".$pdo->quote($t))->fetch();}catch(Throwable $e){return false;}}
function col_exists(PDO $pdo,$t,$c){ if(!tbl_exists($pdo,$t))return false; try{return (bool)$pdo->query("SHOW COLUMNS FROM `$t` LIKE ".$pdo->quote($c))->fetch();}catch(Throwable $e){return false;}}
function pick_col(PDO $pdo,$t,$cands){ foreach($cands as $c){ if(col_exists($pdo,$t,$c)) return $c; } return null; }
function isActive($p){ return basename($_SERVER['PHP_SELF'])===$p ? 'bg-rose-900/60 text-rose-500':'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40'; }

$list=[];
if (tbl_exists($pdo,'recognitions')) {
  $toUserC = pick_col($pdo,'recognitions',['user_id','employee_id','emp_id','recipient_id']);
  $dateC   = pick_col($pdo,'recognitions',['date','created_at','event_date']);
  $titleC  = pick_col($pdo,'recognitions',['title','name','award']);
  $descC   = pick_col($pdo,'recognitions',['description','details','note']);
  $where="1=1"; $pr=[];
  if($toUserC){ $where="`$toUserC`=?"; $pr[]=(int)($u['id']??0); }
  $sql="SELECT ".($titleC?"`$titleC`":"NULL")." AS title,
               ".($descC?"`$descC`":"NULL")." AS descr,
               ".($dateC?"`$dateC`":"NULL")." AS dt
        FROM recognitions WHERE $where
        ORDER BY COALESCE(`$dateC`,id) DESC LIMIT 20";
  try{ $st=$pdo->prepare($sql); $st->execute($pr); $list=$st->fetchAll(PDO::FETCH_ASSOC)?:[]; }catch(Throwable $e){}
}

$brandName='Nextgenmms'; $brandLogo='assets/logo2.jpg';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Recognitions | <?=htmlspecialchars($brandName)?></title>
<link rel="icon" type="image/png" href="assets/logo3.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
 #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
 #sidebar .nav-item{padding:.6rem .85rem}
 #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
 #sidebar.collapsed .item-label,.section-title{display:block}
 #sidebar.collapsed .item-label,#sidebar.collapsed .section-title{display:none}
 #contentWrap{padding-left:16rem;transition:padding .25s ease}
 #contentWrap.collapsed{padding-left:4rem}
 #sidebar{scrollbar-width:none;-ms-overflow-style:none} #sidebar::-webkit-scrollbar{display:none}
</style>
</head>
<body class="bg-slate-50">

<header class="sticky top-0 z-40">
  <div id="topbarPad" class="ml-64 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="text-slate-700 font-medium">Recognitions</div>
      <div class="flex-1"></div>
      <div class="ml-1 flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow">
        <div class="w-8 h-8 rounded-md bg-rose-500 text-white grid place-items-center text-xs font-semibold"><?= strtoupper(substr($u['name']??'U',0,2)) ?></div>
        <div class="leading-tight pr-1">
          <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]"><?= htmlspecialchars($u['name']??'User') ?></div>
          <div class="text-[11px] text-slate-500 capitalize"><?= htmlspecialchars($u['role']??'') ?></div>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="relative">
  <aside id="sidebar" class="fixed top-0 bottom-0 left-0 text-slate-100 overflow-y-auto transition-all duration-200"
         style="background:linear-gradient(to bottom,#121214 0%,#121214 70%,#e11d48 100%)">
    <div class="h-14 bg-rose-600 flex items-center justify-center gap-2">
      <div class="w-10 h-10 overflow-hidden rounded-md"><img src="<?= $brandLogo ?>" class="w-full h-full object-cover" alt="Logo"></div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>
    <nav class="py-4">
      <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>
      <a href="employee_home.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('employee_home.php') ?>">
        <i class="fa-solid fa-house"></i><span class="item-label font-medium">Dashboard</span>
      </a>
      <a href="profile.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('profile.php') ?>">
        <i class="fa-regular fa-user"></i><span class="item-label">My Profile</span>
      </a>
      <a href="my_evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('my_evaluations.php') ?>">
        <i class="fa-solid fa-chart-line"></i><span class="item-label">My Evaluations</span>
      </a>
      <a href="my_recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('my_recognition.php') ?>">
        <i class="fa-solid fa-award"></i><span class="item-label">Recognitions</span>
      </a>

      <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">ACCOUNT</div>
      <a href="logout.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
        <i class="fa-solid fa-arrow-right-from-bracket"></i><span class="item-label">Log out</span>
      </a>
    </nav>
  </aside>

  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-6 py-6">
      <div class="rounded-2xl bg-white ring-1 ring-slate-200 divide-y">
        <?php if ($list): foreach($list as $r): ?>
          <div class="p-4">
            <div class="flex items-center justify-between">
              <div class="min-w-0">
                <div class="font-medium text-slate-800 truncate"><?= htmlspecialchars($r['title']??'—') ?></div>
                <div class="text-sm text-slate-500"><?= htmlspecialchars($r['descr']??'') ?></div>
              </div>
              <div class="text-xs text-slate-500 shrink-0"><?= !empty($r['dt']) ? date('M d, Y', strtotime($r['dt'])) : '' ?></div>
            </div>
          </div>
        <?php endforeach; else: ?>
          <div class="p-8 text-center text-slate-500">No recognitions to show.</div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script>
const btn=document.getElementById('btnSidebar');
const sb=document.getElementById('sidebar');
const main=document.getElementById('contentWrap');
const topbarPad=document.getElementById('topbarPad');
function applyShift(){ if(sb.classList.contains('collapsed')){ topbarPad.classList.remove('ml-64'); topbarPad.classList.add('ml-16'); main.classList.add('collapsed'); } else { topbarPad.classList.remove('ml-16'); topbarPad.classList.add('ml-64'); main.classList.remove('collapsed'); } }
btn?.addEventListener('click',()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
if(localStorage.getItem('sb-collapsed')==='1'){ sb.classList.add('collapsed'); } applyShift();
</script>
</body></html>
