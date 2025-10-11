<?php
declare(strict_types=1);

/* --- Boot (no output above this line) --- */
ob_start();
session_start();

require_once __DIR__ . '/includes/db.php'; // $pdo

/* Auth (adjust to your roles) */
if (empty($_SESSION['user'])) { header('Location: login.php'); exit; }
$role = strtolower($_SESSION['user']['role'] ?? '');
if (!in_array($role, ['admin','superadmin'])) { http_response_code(403); exit('Forbidden'); }

$u = $_SESSION['user'];

/* Brand */
$brandName = 'Nextgenmms';
$brandLogo = 'assets/logo2.jpg';

/* Active link helper */
function isActive(string $page): string {
  $is = basename($_SERVER['PHP_SELF']) === $page;
  return $is ? 'bg-rose-900/60 text-rose-500'
             : 'text-slate-300 hover:text-rose-500 hover:bg-rose-900/40';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Users | <?= htmlspecialchars($brandName) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    /* Layout */
    #sidebar{width:16rem} #sidebar.collapsed{width:4rem}
    #sidebar .nav-item{padding:.6rem .85rem}
    #sidebar.collapsed .nav-item{justify-content:center;padding:.6rem 0}
    #sidebar.collapsed .item-label, #sidebar.collapsed .section-title{display:none}
    #contentWrap{padding-left:16rem;transition:padding .25s ease}
    #contentWrap.collapsed{padding-left:4rem}
    #topbarPad{margin-left:16rem;transition:margin .25s ease}
    #topbarPad.collapsed{margin-left:4rem}

    /* Small helpers for action buttons */
    .iconbtn{width:32px;height:32px;display:grid;place-items:center;border-radius:.5rem;border:1px solid #e5e7eb;color:#475569}
    .iconbtn:hover{background:#f8fafc}
    .tooltip{position:relative}
    .tooltip:hover:after{content:attr(data-tip);position:absolute;bottom:110%;left:50%;transform:translateX(-50%);background:#0f172a;color:#fff;border-radius:.35rem;padding:.25rem .4rem;font-size:.7rem;white-space:nowrap}
    .tooltip::after{pointer-events:none}

    /* Custom Confirm + Toast */
    .ui-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;z-index:1000}
    .ui-card{width:440px;max-width:92vw;background:#fff;border-radius:16px;box-shadow:0 20px 40px rgba(0,0,0,.18);border:1px solid #e2e8f0}
    .ui-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #e2e8f0}
    .ui-title{font-weight:700;color:#0f172a}
    .ui-body{padding:14px 16px;color:#334155;font-size:.95rem}
    .ui-foot{display:flex;gap:8px;justify-content:flex-end;padding:14px 16px;border-top:1px solid #e2e8f0}
    .ui-btn{padding:.55rem .9rem;border-radius:.7rem;border:1px solid #e5e7eb;background:#fff;color:#0f172a}
    .ui-btn:hover{background:#f8fafc}
    .ui-btn.ok{background:#e11d48;color:#fff;border-color:#e11d48}
    .ui-btn.ok:hover{background:#be123c}
    .ui-x{border:none;background:transparent;color:#64748b;font-size:18px}
    #toast{position:fixed;top:72px;left:50%;transform:translateX(-50%);background:#0f172a;color:#fff;
      padding:.7rem 1rem;border-radius:.8rem;box-shadow:0 8px 24px rgba(0,0,0,.25);z-index:1100;display:none}
  </style>
</head>
<body class="bg-slate-50">

<!-- Topbar -->
<header class="sticky top-0 z-40">
  <div id="topbarPad" class="bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="h-14 px-3 md:px-4 flex items-center gap-3">
      <button id="btnSidebar" class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 shrink-0">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="flex-1"></div>
      <div class="relative ml-1 select-none">
        <button id="userBtn" class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-white ring-1 ring-slate-200 shadow hover:bg-rose-50">
          <div class="w-8 h-8 rounded-md bg-gradient-to-br from-rose-500 to-rose-600 text-white grid place-items-center text-xs font-semibold shadow-inner">
            <?= strtoupper(substr($u['name'] ?? 'U', 0, 2)); ?>
          </div>
          <div class="leading-tight pr-1 text-left hidden sm:block">
            <div class="text-sm font-medium text-slate-800 truncate max-w-[120px]"><?= htmlspecialchars($u['name'] ?? 'User'); ?></div>
            <div class="text-[11px] text-slate-500 capitalize"><?= htmlspecialchars($u['role'] ?? ''); ?></div>
          </div>
          <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
        </button>
        <div id="userMenu" class="hidden absolute right-0 mt-2 w-44 bg-white rounded-2xl shadow-xl border border-slate-200 ring-1 ring-rose-100 overflow-hidden z-50">
          <div class="py-1">
            <a href="profile.php" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-rose-50">
              <i class="fa-solid fa-user text-rose-500 w-4"></i> View Profile
            </a>
            <form method="post" action="logout.php">
              <button type="submit" class="w-full text-left flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-rose-50">
                <i class="fa-solid fa-right-from-bracket text-rose-500 w-4"></i> Log Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="relative">
  <!-- Sidebar -->
  <aside id="sidebar" class="fixed top-0 bottom-0 left-0 text-slate-100 overflow-y-auto transition-all duration-200"
         style="background:linear-gradient(to bottom,#121214 0%,#121214 70%,#e11d48 100%)">
    <div class="h-14 bg-rose-600 flex items-center justify-center gap-2">
      <div class="w-10 h-10 overflow-hidden rounded-md bg-white grid place-items-center">
        <img src="<?= htmlspecialchars($brandLogo) ?>" class="w-full h-full object-cover" alt="logo">
      </div>
      <span class="item-label font-semibold text-white"><?= htmlspecialchars($brandName) ?></span>
    </div>
    <nav class="py-4">
      <div class="px-4 text-[11px] tracking-wider text-slate-400/80 section-title">MAIN</div>
      <a href="index.php"       class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('index.php'); ?>"><i class="fa-solid fa-house"></i><span class="item-label">Dashboard</span></a>
      <a href="applicants.php"  class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('applicants.php'); ?>"><i class="fa-solid fa-user"></i><span class="item-label">Applicants</span></a>
      <a href="recruitment.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('recruitment.php'); ?>"><i class="fa-solid fa-briefcase"></i><span class="item-label">Recruitment</span></a>
      <a href="onboarding.php"  class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('onboarding.php'); ?>"><i class="fa-solid fa-square-check"></i><span class="item-label">Onboarding</span></a>
      <div class="px-4 mt-4 text-[11px] tracking-wider text-slate-400/80 section-title">MANAGEMENT</div>
      <a href="employees.php"   class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('employees.php'); ?>"><i class="fa-solid fa-users"></i><span class="item-label">Employees</span></a>
      <a href="evaluations.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('evaluations.php'); ?>"><i class="fa-solid fa-chart-line"></i><span class="item-label">Evaluations</span></a>
      <a href="recognition.php" class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('recognition.php'); ?>"><i class="fa-solid fa-award"></i><span class="item-label">Recognition</span></a>
      <a href="users.php"       class="nav-item mx-4 mt-2 flex items-center gap-3 rounded-xl px-3 py-2 <?= isActive('users.php'); ?>"><i class="fa-solid fa-user-gear"></i><span class="item-label">Users</span></a>
    </nav>
  </aside>

  <!-- Content -->
  <main id="contentWrap" class="min-h-[calc(100vh-56px)] transition-all duration-200">
    <div class="px-4 sm:px-6 lg:px-8 py-6">
      <div class="rounded-2xl bg-white ring-1 ring-slate-200">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
          <div class="font-semibold text-slate-800">Users</div>
          <!-- SINGLE BUTTON -->
          <button id="btnAddUser" class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-sm hover:bg-rose-700">
            <i class="fa-solid fa-plus mr-1"></i> Add User
          </button>
        </div>

        <div class="p-4 overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="bg-rose-100 text-slate-700">
                <th class="text-left py-2 px-3 rounded-l-lg">#</th>
                <th class="text-left py-2 px-3">Name</th>
                <th class="text-left py-2 px-3">Email</th>
                <th class="text-left py-2 px-3">Role</th>
                <th class="text-left py-2 px-3">Created At</th>
                <th class="text-left py-2 px-3 rounded-r-lg">Actions</th>
              </tr>
            </thead>
            <tbody id="userBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<div id="toast"></div>

<script>
/* ===== Custom confirm + toast ===== */
// ===== Custom confirm + toast + prompt =====
const ui = {
  toast(msg, ms=1800){
    const t = document.getElementById('toast') || (()=> {
      const x = document.createElement('div'); x.id='toast';
      x.style.cssText = 'position:fixed;top:72px;left:50%;transform:translateX(-50%);background:#0f172a;color:#fff;padding:.7rem 1rem;border-radius:.8rem;box-shadow:0 8px 24px rgba(0,0,0,.25);z-index:1100;display:none';
      document.body.appendChild(x); return x;
    })();
    t.textContent = msg; t.style.display='block';
    clearTimeout(t._t); t._t=setTimeout(()=>t.style.display='none', ms);
  },
  confirm({title='Please confirm', message='Are you sure?', okText='OK', cancelText='Cancel'}={}){
    return new Promise(res=>{
      const o = document.createElement('div'); o.className='ui-overlay';
      o.innerHTML = `
        <div class="ui-card">
          <div class="ui-head">
            <div class="ui-title">${title}</div>
            <button class="ui-x" aria-label="Close">&times;</button>
          </div>
          <div class="ui-body">${message}</div>
          <div class="ui-foot">
            <button class="ui-btn cancel">${cancelText}</button>
            <button class="ui-btn ok">${okText}</button>
          </div>
        </div>`;
      document.body.appendChild(o);
      const done = v=>{ o.remove(); res(v); };
      o.querySelector('.ok').onclick=()=>done(true);
      o.querySelector('.cancel').onclick=()=>done(false);
      o.querySelector('.ui-x').onclick=()=>done(false);
      o.addEventListener('click',e=>{ if(e.target===o) done(false); });
      window.addEventListener('keydown', function esc(e){ if(e.key==='Escape'){done(false);window.removeEventListener('keydown',esc);} });
    });
  },
  prompt({title='Input', message='', okText='Save', cancelText='Cancel', type='text', placeholder='', validate=null}={}){
    return new Promise(res=>{
      const o = document.createElement('div'); o.className='ui-overlay';
      o.innerHTML = `
        <div class="ui-card">
          <div class="ui-head">
            <div class="ui-title">${title}</div>
            <button class="ui-x" aria-label="Close">&times;</button>
          </div>
          <div class="ui-body">
            ${message ? `<div class="mb-2">${message}</div>` : ''}
            <input id="__promptInput" type="${type}" class="w-full border border-slate-300 rounded-lg px-3 py-2" placeholder="${placeholder}">
          </div>
          <div class="ui-foot">
            <button class="ui-btn cancel">${cancelText}</button>
            <button class="ui-btn ok">${okText}</button>
          </div>
        </div>`;
      document.body.appendChild(o);
      const ip = o.querySelector('#__promptInput'); ip.focus();
      const done = v=>{ o.remove(); res(v); };
      function submit(){
        const val = ip.value ?? '';
        if (validate && !validate(val)) return;
        done(val);
      }
      o.querySelector('.ok').onclick=submit;
      o.querySelector('.cancel').onclick=()=>done(null);
      o.querySelector('.ui-x').onclick=()=>done(null);
      o.addEventListener('click',e=>{ if(e.target===o) done(null); });
      ip.addEventListener('keydown',e=>{ if(e.key==='Enter') submit(); });
    });
  },
  promptPassword(opts={}){
    return this.prompt({
      title: 'Reset Password',
      message: opts.message || '',
      okText: opts.okText || 'Reset',
      cancelText: opts.cancelText || 'Cancel',
      type: 'password',
      placeholder: 'Min 6 characters',
      validate: (v)=>{
        if (!v || v.length < 6) { this.toast('Password must be at least 6 characters'); return false; }
        return true;
      }
    });
  }
};

/* ===== Page script ===== */
(function () {
  'use strict';

  // helpers
  const $ = sel => document.querySelector(sel);
  function esc(s){ return (s??'').toString().replace(/[&<>"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])); }

  // Sidebar + user menu
  const sb = $('#sidebar'), top = $('#topbarPad'), content = $('#contentWrap'), btnSidebar = $('#btnSidebar');
  function applyShift(){ const c = sb?.classList.contains('collapsed'); content?.classList.toggle('collapsed',c); top?.classList.toggle('collapsed',c); }
  btnSidebar?.addEventListener('click', ()=>{ sb.classList.toggle('collapsed'); localStorage.setItem('sb-collapsed', sb.classList.contains('collapsed')?'1':'0'); applyShift(); });
  if (localStorage.getItem('sb-collapsed')==='1') sb?.classList.add('collapsed'); applyShift();

  const userBtn = $('#userBtn'), userMenu = $('#userMenu');
  userBtn?.addEventListener('click', e=>{ e.stopPropagation(); userMenu?.classList.toggle('hidden'); });
  window.addEventListener('click', e=>{ if(userMenu && !userMenu.contains(e.target) && !userBtn?.contains(e.target)) userMenu.classList.add('hidden'); });

  // API
  async function API(body){
    const res = await fetch('api/users_api.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(body||{})
    });
    const text = await res.text();
    let j; try { j = JSON.parse(text); } catch(e){
      console.error('users_api.php returned non-JSON:', text);
      throw new Error('API: invalid JSON');
    }
    if (!j.ok) throw new Error(j.error||'API error');
    return j.data;
  }

  // Modal factory (roles trimmed: admin, hr manager, employee)
  function openForm({ title, user, mode }){
    const roles = ['admin','hr manager','employee'];
    const wrap=document.createElement('div');
    wrap.className='fixed inset-0 z-50 flex items-center justify-center bg-black/40';
    wrap.innerHTML=`
      <div class="w-[520px] max-w-[94vw] bg-white rounded-2xl ring-1 ring-slate-200 shadow-xl p-5">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold">${esc(title)}</h3>
          <button class="text-slate-500 hover:text-slate-700" data-x><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="space-y-3">
          <div><label class="text-sm text-slate-600">Name</label>
            <input id="fName" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" value="${esc(user?.name||'')}"></div>
          <div><label class="text-sm text-slate-600">Email</label>
            <input id="fEmail" type="email" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" value="${esc(user?.email||'')}"></div>
          <div><label class="text-sm text-slate-600">Role</label>
            <select id="fRole" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200">
              ${roles.map(r=>`<option ${user?.role===r?'selected':''}>${r}</option>`).join('')}
            </select></div>
          ${mode==='add' ? `
          <div><label class="text-sm text-slate-600">Password</label>
            <input id="fPwd" type="password" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200" placeholder="Min 6 chars"></div>`:''}
        </div>
        <div class="mt-5 text-right">
          <button class="px-4 py-2 rounded-lg border border-slate-300 mr-2" data-x>Cancel</button>
          <button id="fOk" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg">${mode==='add'?'Create':'Save'}</button>
        </div>
      </div>`;
    document.body.appendChild(wrap);
    const close=()=>wrap.remove();
    wrap.querySelectorAll('[data-x]').forEach(el=>el.onclick=close);
    wrap.addEventListener('click',e=>{ if(e.target===wrap) close(); });
    return { el:wrap, close };
  }

  // Render
  const tbody = document.getElementById('userBody');

  async function loadUsers(){
    try{
      const { rows } = await API({ action:'list' });
      tbody.innerHTML = rows?.length ? '' : '<tr><td colspan="6" class="px-6 py-6 text-sm text-slate-500">No users.</td></tr>';
      (rows||[]).forEach(u=>{
        const tr=document.createElement('tr'); tr.className='border-t';
        tr.innerHTML = `
          <td class="px-4 py-3">${u.id}</td>
          <td class="px-4 py-3 font-medium text-slate-800">${esc(u.name)}</td>
          <td class="px-4 py-3">${esc(u.email)}</td>
          <td class="px-4 py-3 capitalize">${esc(u.role)}</td>
          <td class="px-4 py-3">${u.created_at ? new Date(u.created_at).toLocaleDateString() : ''}</td>
          <td class="px-4 py-3">
            <div class="inline-flex gap-2">
              <button class="iconbtn tooltip act-edit" data-tip="Edit" data-id="${u.id}"><i class="fa-regular fa-pen-to-square"></i></button>
              <button class="iconbtn tooltip act-toggle" data-tip="${u.is_active ? 'Deactivate' : 'Activate'}" data-id="${u.id}">
                <i class="fa-solid ${u.is_active ? 'fa-user-slash' : 'fa-user-check'}"></i>
              </button>
              <button class="iconbtn tooltip act-reset" data-tip="Reset password" data-id="${u.id}"><i class="fa-solid fa-key"></i></button>
              <button class="iconbtn tooltip act-del text-rose-600" data-tip="Delete" data-id="${u.id}"><i class="fa-regular fa-trash-can"></i></button>
            </div>
          </td>`;
        tbody.appendChild(tr);
      });
    } catch (e){
      console.warn('loadUsers failed:', e.message);
      tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-6 text-sm text-rose-600">Cannot load users (check api/users_api.php).</td></tr>';
    }
  }

  // Add button
  function bindAdd(){
    const btn = document.getElementById('btnAddUser');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const { el, close } = openForm({ title:'Add User', mode:'add' });
      el.querySelector('#fOk').onclick = async () => {
        const name  = el.querySelector('#fName').value.trim();
        const email = el.querySelector('#fEmail').value.trim();
        const role  = el.querySelector('#fRole').value.trim();
        const pwd   = el.querySelector('#fPwd')?.value || '';
        if (!name || !email || !pwd) return ui.toast('Please complete all fields');
        try {
        const data = await API({ action:'add', name, email, role, password: pwd });
        close();
        ui.toast('User added');

        // ipakita kung may problema sa email
        if (data && data.mail_sent === 0 && data.mail_error) {
            ui.toast('Email failed: ' + data.mail_error);
            console.warn('Mail error:', data.mail_error);
        }

        loadUsers();
        } catch (e) {
        ui.toast(e.message);
        }

      };
    });
  }

  // Row actions (uses custom confirm)
  function bindRowActions(){
    tbody.addEventListener('click', async (e)=>{
      const b = e.target.closest('button'); if (!b) return;
      const id = +b.dataset.id;

      if (b.classList.contains('act-edit')) {
        const tr = b.closest('tr');
        const user = {
          id,
          name: tr.children[1].innerText.trim(),
          email: tr.children[2].innerText.trim(),
          role: tr.children[3].innerText.trim().toLowerCase()
        };
        const { el, close } = openForm({ title:'Edit User', user, mode:'edit' });
        el.querySelector('#fOk').onclick = async () => {
          const name  = el.querySelector('#fName').value.trim();
          const email = el.querySelector('#fEmail').value.trim();
          const role  = el.querySelector('#fRole').value.trim();
          if (!name || !email) return ui.toast('Please complete all fields');
          try { await API({ action:'update', id, name, email, role, is_active:1 }); close(); ui.toast('Saved'); loadUsers(); }
          catch(e){ ui.toast(e.message); }
        };
        return;
      }

      if (b.classList.contains('act-toggle')) {
        try { await API({ action:'toggle_active', id }); ui.toast('Updated'); loadUsers(); }
        catch(e){ ui.toast(e.message); }
        return;
      }

      if (b.classList.contains('act-reset')) {
        const tr = b.closest('tr');
        const name = tr?.children?.[1]?.innerText?.trim() || 'this user';
        const pwd = await ui.promptPassword({ message: `Set a new password for <b>${name}</b>.` });
        if (!pwd) return;
        try {
            await API({ action:'reset_pw', id, password: pwd });
            ui.toast('Password reset');
        } catch (e) {
            ui.toast(e.message);
        }
        return;
        }


      if (b.classList.contains('act-del')) {
        const ok = await ui.confirm({
          title:'Delete User',
          message:'This action cannot be undone.',
          okText:'Delete',
          cancelText:'Cancel'
        });
        if (!ok) return;
        try { await API({ action:'delete', id }); ui.toast('Deleted'); loadUsers(); }
        catch(e){ ui.toast(e.message); }
        return;
      }
    });
  }

  // Boot
  window.addEventListener('DOMContentLoaded', () => {
    bindAdd();
    bindRowActions();
    loadUsers();
  });
})();
</script>
</body>
</html>
