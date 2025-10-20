<?php
// apply.php — O!Save Careers • Banawe (Public Apply)
session_start();
$SITE  = 'O!Save — Banawe';
$BRAND = 'O!Save Careers – Banawe';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo htmlspecialchars($BRAND); ?></title>
  <link rel="icon" type="image/png" href="assets/logo3.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    html{ font-size:17px; }
    @media (min-width:1024px){ html{ font-size:18px; } }

    .hero-bg{ background-image:url('assets/logo1.jpg'); background-size:cover; background-position:center; background-repeat:no-repeat; }
    .card{ box-shadow:0 10px 30px rgba(0,0,0,.08) }
    .ring-soft{ box-shadow:0 0 0 1px rgba(15,23,42,.08) inset; }
    .focus-ring:focus{ outline:none; box-shadow:0 0 0 3px rgba(244,63,94,.25) }

    input[type="file"]::file-selector-button{
      background-color:#e11d48;color:#fff;border:none;padding:.5rem .75rem;border-radius:.625rem;margin-right:.75rem;cursor:pointer;
    }
    input[type="file"]::file-selector-button:hover{ background-color:#be123c; }

    /* Same color as header: bg-rose-600/95 */
    .hero-glass{
      background: rgba(225, 29, 72, 0.95); /* rose-600 @ 95% */
      color:#fff;
      border:1px solid rgba(255,255,255,.22);
      -webkit-backdrop-filter:saturate(160%);
      backdrop-filter:saturate(160%);
    }
    /* keep all hero card icons white… */
    .hero-glass i{ color:#fff !important; }
    /* …but preserve original icon colors for the role chips */
    .hero-glass .roles i{ color:inherit !important; }

    /* ensure light text inside the red cards */
    .hero-glass .text-slate-600,
    .hero-glass .text-slate-700,
    .hero-glass .text-slate-900{ color:rgba(255,255,255,.95)!important; }


    @keyframes toastIn{from{transform:translateY(-8px);opacity:0}to{transform:translateY(0);opacity:1}}
    @keyframes toastOut{from{transform:translateY(0);opacity:1}to{transform:translateY(-8px);opacity:0}}
    #toastMsg.animate-in{animation:toastIn .20s ease-out}
    #toastMsg.animate-out{animation:toastOut .18s ease-in forwards}

    #openApply {
  display: none !important;
  }

  </style>
</head>
<body class="bg-slate-50 text-slate-800">
  <!-- HERO -->
  <header class="hero-bg text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="inline-flex flex-wrap sm:flex-nowrap items-center gap-3 rounded-2xl px-4 py-2
                  bg-rose-600/95 text-white ring-1 ring-rose-500/60
                  shadow-xl backdrop-blur-sm max-w-fit">
        <div class="w-10 h-10 rounded-xl bg-white text-rose-600 grid place-items-center shadow flex-shrink-0">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="leading-tight min-w-[220px]">
          <div class="text-3xl sm:text-4xl font-extrabold tracking-tight">
            <?php echo htmlspecialchars($BRAND); ?>
          </div>
          <div class="text-sm/5 text-white/90">Happy employees, happy customers. Work with us!</div>
        </div>
      </div>

      <div class="mt-6 grid grid-cols-1 gap-5 max-w-3xl">
        <!-- Card 1 -->
        <div class="card rounded-2xl hero-glass p-5 shadow-xl">
          <div class="text-lg font-semibold mb-2">
            <i class="fa-solid fa-heart mr-2"></i>Our Core Values
          </div>
          <ul class="space-y-2 text-sm">
            <li class="flex gap-2 flex-wrap"><i class="fa-solid fa-circle-check mt-1"></i> Continuous growth and learning</li>
            <li class="flex gap-2 flex-wrap"><i class="fa-solid fa-circle-check mt-1"></i> Diversity, collaboration, inclusion</li>
            <li class="flex gap-2 flex-wrap"><i class="fa-solid fa-circle-check mt-1"></i> Ownership & customer obsession</li>
            <li class="flex gap-2 flex-wrap"><i class="fa-solid fa-circle-check mt-1"></i> Speed, simplicity, excellence</li>
          </ul>
        </div>

        <!-- Card 2 -->
        <div class="card rounded-2xl hero-glass p-5 shadow-xl">
          <div class="text-lg font-semibold mb-3">
            <i class="fa-solid fa-bullhorn mr-2"></i>We’re Hiring
          </div>
          <div class="roles flex flex-wrap gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-shirt mr-2"></i>Store Part Timer</span>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-cash-register mr-2"></i>Cashier</span>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-tags mr-2"></i>Merchandiser / Promodiser</span>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-clipboard-list mr-2"></i>Inventory Clerk / Stockman</span>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-box-open mr-2"></i>Order Processor</span>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-user-tie mr-2"></i>Deputy Store Manager</span>
            <span class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 ring-1 ring-rose-100 text-sm"><i class="fa-solid fa-store mr-2"></i>Store Manager</span>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="card rounded-2xl hero-glass p-5 shadow-xl">
          <div class="text-lg font-semibold mb-1">
            <i class="fa-solid fa-location-dot mr-2"></i>Office Location
          </div>
          <div class="text-sm"><?php echo htmlspecialchars($SITE); ?></div>
          <div class="text-xs text-slate-600 mt-1">Near transit • Accessible location • Growing team</div>
          <button id="openApply" class="mt-4 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl focus-ring">
            Apply Now
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- GUIDE -->
  <section class="bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="text-center">
        <h2 class="text-2xl font-bold text-slate-800">Step-by-Step Application Guide</h2>
        <p class="text-slate-600 mt-2">Our online application is quick and secure. Review the steps, then proceed.</p>
      </div>

      <div class="mt-8 space-y-7 text-slate-700">
        <div class="flex gap-4 flex-wrap">
          <i class="fa-solid fa-list-check text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">1. Check Basic Requirements</div>
            <ul class="list-disc pl-5 text-sm">
              <li>At least Senior High Graduate (or equivalent)</li>
              <li>Willing to work on shifting schedules</li>
              <li>Can communicate in the required language</li>
            </ul>
          </div>
        </div>
        <div class="flex gap-4 flex-wrap">
          <i class="fa-solid fa-magnifying-glass text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">2. Find a Role</div>
            <div class="text-sm">Use the quick fit evaluation to find the best position for you.</div>
          </div>
        </div>
        <div class="flex gap-4 flex-wrap">
          <i class="fa-regular fa-square-check text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">3. Submit Application Form</div>
            <div class="text-sm">Fill in your personal details and preferred start date.</div>
          </div>
        </div>
        <div class="flex gap-4 flex-wrap">
          <i class="fa-solid fa-folder-open text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">4. Prepare Required Documents</div>
            <div class="text-sm">Upload your latest resume/CV (PDF/DOC/DOCX). Max 5MB.</div>
          </div>
        </div>
        <div class="flex gap-4 flex-wrap">
          <i class="fa-regular fa-envelope text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">5. Provide a Valid Email</div>
            <div class="text-sm">Updates and instructions will be sent here. Check regularly.</div>
          </div>
        </div>
        <div class="flex gap-4 flex-wrap">
          <i class="fa-solid fa-shield text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">6. Ensure Accuracy</div>
            <div class="text-sm">Double-check entries before submitting to avoid delays.</div>
          </div>
        </div>
        <div class="flex gap-4 flex-wrap">
          <i class="fa-regular fa-circle-check text-rose-600 mt-1.5"></i>
          <div>
            <div class="font-semibold">7. Receive Confirmation</div>
            <div class="text-sm">We’ll contact you via email/SMS regarding next steps.</div>
          </div>
        </div>
      </div>

      <div class="text-center mt-10">
        <button id="proceedBtn" class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-2.5 rounded-xl">
          Proceed to Online Application
        </button>
      </div>
    </div>
  </section>

  <!-- Hidden sections until Terms -->
  <div id="flowSections" class="hidden">
    <!-- STEPS BAR -->
    <section id="stepsBar" class="bg-white border-y border-slate-200">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h3 class="text-xl font-semibold text-slate-800 mb-4">Application Process</h3>
        <ol class="grid md:grid-cols-4 gap-4 text-sm">
          <li class="flex items-center gap-3">
            <span class="w-7 h-7 grid place-items-center rounded-full bg-rose-600 text-white">1</span>
            <div><div class="font-semibold">Terms</div><div class="text-slate-500">Agree to proceed</div></div>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-7 h-7 grid place-items-center rounded-full bg-rose-100 text-rose-700">2</span>
            <div><div class="font-semibold">Evaluation</div><div class="text-slate-500">Quick fit check</div></div>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-7 h-7 grid place-items-center rounded-full bg-rose-100 text-rose-700">3</span>
            <div><div class="font-semibold">Application</div><div class="text-slate-500">Fill out the form</div></div>
          </li>
          <li class="flex items-center gap-3">
            <span class="w-7 h-7 grid place-items-center rounded-full bg-rose-100 text-rose-700">4</span>
            <div><div class="font-semibold">Submit</div><div class="text-slate-500">Upload your CV</div></div>
          </li>
        </ol>
      </div>
    </section>

    <!-- QUICK EVALUATION -->
    <section id="eval" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 card">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <h4 class="text-lg font-semibold"><i class="fa-solid fa-clipboard-check mr-2 text-rose-600"></i>Quick Role Fit Evaluation</h4>
          <span class="text-xs text-slate-500">1–2 minutes</span>
        </div>
        <div class="p-5 space-y-5 text-sm">
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="font-medium">Comfortable assisting customers on the sales floor?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q1" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q1" value="no" class="mr-2">No</label>
              </div>
            </div>
            <div>
              <label class="font-medium">Enjoy organizing stocks & backroom tasks?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q2" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q2" value="no" class="mr-2">No</label>
              </div>
            </div>
            <div>
              <label class="font-medium">Have leadership or team-assigning experience?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q3" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q3" value="no" class="mr-2">No</label>
              </div>
            </div>
            <div>
              <label class="font-medium">Okay with shifting schedules or weekends?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q4" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q4" value="no" class="mr-2">No</label>
              </div>
            </div>

            <!-- NEW Q5–Q8 -->
            <div>
              <label class="font-medium">Confident with cash handling and POS transactions?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q5" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q5" value="no" class="mr-2">No</label>
              </div>
            </div>
            <div>
              <label class="font-medium">Can lift up to ~15kg and do physical tasks?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q6" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q6" value="no" class="mr-2">No</label>
              </div>
            </div>
            <div>
              <label class="font-medium">Basic computer skills (email, spreadsheets, chat)?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q7" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q7" value="no" class="mr-2">No</label>
              </div>
            </div>
            <div>
              <label class="font-medium">Prefer customer-facing work over backroom duties?</label>
              <div class="mt-1 flex gap-5">
                <label><input type="radio" name="q8" value="yes" class="mr-2">Yes</label>
                <label><input type="radio" name="q8" value="no" class="mr-2">No</label>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between flex-wrap gap-3">
            <div id="evalHint" class="text-slate-500">Your suggested role will appear here.</div>
            <button id="btnEval" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl">Get Suggestion</button>
          </div>
        </div>
      </div>
    </section>

    <!-- APPLICATION FORM -->
    <section id="apply" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
      <div class="bg-white rounded-2xl ring-1 ring-slate-200 card">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between flex-wrap gap-3">
          <h4 class="text-lg font-semibold"><i class="fa-solid fa-file-pen mr-2 text-rose-600"></i>Application Form</h4>
          <div class="text-xs text-slate-500">Fields with <span class="text-rose-600">*</span> are required</div>
        </div>

        <form id="appForm" class="p-5 space-y-6" enctype="multipart/form-data">
          <input type="hidden" name="action" value="public.apply">

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm text-slate-700">Full name <span class="text-rose-600">*</span></label>
              <input name="full_name" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring" placeholder="Juan Dela Cruz">
            </div>
            <div>
              <label class="text-sm text-slate-700">Mobile number <span class="text-rose-600">*</span></label>
              <input name="mobile" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring" placeholder="09XX-XXX-XXXX">
            </div>
            <div>
              <label class="text-sm text-slate-700">Email <span class="text-rose-600">*</span></label>
              <input type="email" name="email" required class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring" placeholder="you@email.com">
            </div>
            <div>
              <label class="text-sm text-slate-700">Address</label>
              <input name="address" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring" placeholder="Street, Barangay, City">
            </div>
            <div>
              <label class="text-sm text-slate-700">Highest Education</label>
              <input name="education" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring" placeholder="e.g., College Graduate">
            </div>
            <div>
              <label class="text-sm text-slate-700">Years of  Work Experience</label>
              <input name="yoe" type="number" min="0" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring" placeholder="0">
            </div>

            <!-- Position list -->
            <div>
             <label class="text-sm text-slate-700">Position applying for <span class="text-rose-600">*</span></label>
              <select name="role" id="roleSelect" required
                      class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring">
                <option value="" disabled selected>Select role</option>
                <option>Store Part Timer</option>
                <option>Cashier</option>
                <option>Merchandiser / Promodiser</option>
                <option>Inventory Clerk / Stockman</option>
                <option>Order Processor</option>
                <option>Deputy Store Manager</option>
                <option>Store Manager</option>
              </select>
            </div>

            <div>
              <label class="text-sm text-slate-700">Preferred start date</label>
              <input name="start_date" type="date" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring">
            </div>
            <div>
              <label class="text-sm text-slate-700">Site / Location</label>
              <input name="site" readonly value="Banawe" class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2">
            </div>
            <div>
              <label class="text-sm text-slate-700">Resume / CV (PDF / DOCX) <span class="text-rose-600">*</span></label>
              <input name="resume" type="file" accept=".pdf,.doc,.docx" required
                     class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus-ring">
              <div class="text-xs text-slate-500 mt-1">Max 5MB.</div>
            </div>
          </div>

          <div class="flex items-center justify-between flex-wrap gap-3 pt-2">
            <label class="text-sm"><input type="checkbox" id="agree2" class="mr-2">I certify that my information is true and correct.</label>
            <button id="btnSubmit" type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded-xl disabled:opacity-60 disabled:cursor-not-allowed">
              Submit Application
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>

  <footer class="bg-slate-900 text-white/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm">
      © <?php echo date('Y'); ?> O!Save. All rights reserved.
    </div>
  </footer>

  <!-- Terms Modal -->
  <div id="termsModal" class="fixed inset-0 hidden z-50">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="absolute inset-0 flex items-center justify-center px-4">
      <div class="w-full max-w-lg bg-white rounded-2xl p-5 ring-1 ring-slate-200 card">
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-lg font-semibold text-slate-800">Terms and Conditions</h4>
          <button id="termsClose" class="text-slate-500 hover:text-slate-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="text-sm text-slate-700 space-y-3 max-h-[50vh] overflow-auto">
          <p>By clicking “Agree”, you accept the terms of O!Save’s online recruitment. Make sure the information you submit is accurate and complete. Misrepresentation may lead to disqualification.</p>
          <ul class="list-disc pl-5 space-y-1 text-slate-600">
            <li>Your data will be used to evaluate your application.</li>
            <li>We may contact you via email or SMS regarding your status.</li>
            <li>Uploading a CV is required for screening.</li>
          </ul>
        </div>
        <div class="mt-5 text-right">
          <button id="termsCancel" class="px-4 py-2 rounded-xl border border-slate-300 mr-2">Cancel</button>
          <button id="termsAgree" class="px-4 py-2 rounded-xl bg-rose-600 text-white">Agree</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast (top-center) -->
  <div id="toast" class="fixed inset-x-0 top-16 z-[60] hidden flex justify-center pointer-events-none">
    <div id="toastMsg"
         class="pointer-events-auto rounded-xl bg-slate-900 text-white
                text-base md:text-lg font-medium tracking-wide
                px-5 md:px-6 py-3 md:py-3.5 shadow-lg ring-1 ring-black/10">
    </div>
  </div>

<script>
(function(){
  const toast = (m)=>{
    const wrap=document.getElementById('toast');
    const box=document.getElementById('toastMsg');
    box.textContent=m;
    wrap.classList.remove('hidden');
    box.classList.remove('animate-out'); void box.offsetWidth;
    box.classList.add('animate-in');
    clearTimeout(window.__toastTimer);
    window.__toastTimer=setTimeout(()=>{
      box.classList.remove('animate-in');
      box.classList.add('animate-out');
      setTimeout(()=>wrap.classList.add('hidden'),220);
    },2400);
  };

  const flow = document.getElementById('flowSections');
  const revealFlow = ()=>{
    flow.classList.remove('hidden');
    document.getElementById('stepsBar').scrollIntoView({behavior:'smooth'});
  };

  const openTerms = ()=> document.getElementById('termsModal').classList.remove('hidden');
  const closeTerms = ()=> document.getElementById('termsModal').classList.add('hidden');
  document.getElementById('openApply').addEventListener('click', openTerms);
  document.getElementById('proceedBtn').addEventListener('click', openTerms);
  document.getElementById('termsCancel').addEventListener('click', closeTerms);
  document.getElementById('termsClose').addEventListener('click', closeTerms);
  document.getElementById('termsAgree').addEventListener('click', ()=>{ closeTerms(); revealFlow(); toast('Terms accepted. Proceed to evaluation.'); });

  // ===== Role suggestion with weighted scoring =====
  document.getElementById('btnEval').addEventListener('click', ()=>{
    const v = n => (document.querySelector('input[name="q'+n+'"]:checked')||{}).value||'';

    const cust  = v(1)==='yes';
    const stock = v(2)==='yes';
    const lead  = v(3)==='yes';
    const shift = v(4)==='yes';
    const cash  = v(5)==='yes';
    const lift  = v(6)==='yes';
    const comp  = v(7)==='yes';
    const face  = v(8)==='yes'; // prefer customer-facing

    // score map
    const S = {
      'Store Part Timer': 0,
      'Cashier': 0,
      'Merchandiser / Promodiser': 0,
      'Inventory Clerk / Stockman': 0,
      'Order Processor': 0,
      'Deputy Store Manager': 0,
      'Store Manager': 0
    };

    if (cust){ S['Store Part Timer']+=2; S['Cashier']+=2; S['Merchandiser / Promodiser']+=2; S['Deputy Store Manager']+=1; S['Store Manager']+=1; }
    if (stock){ S['Inventory Clerk / Stockman']+=2; S['Order Processor']+=2; S['Merchandiser / Promodiser']+=1; }
    if (lead){ S['Deputy Store Manager']+=3; S['Store Manager']+=4; }
    if (shift){ for (const k of Object.keys(S)) S[k]+=1; }

    if (cash){ S['Cashier']+=3; S['Store Part Timer']+=1; S['Deputy Store Manager']+=1; S['Store Manager']+=1; }
    if (lift){ S['Inventory Clerk / Stockman']+=2; S['Merchandiser / Promodiser']+=2; S['Order Processor']+=1; }
    if (comp){ S['Order Processor']+=2; S['Deputy Store Manager']+=1; S['Store Manager']+=1; }
    if (face){ S['Cashier']+=2; S['Store Part Timer']+=2; S['Merchandiser / Promodiser']+=2; }
    else     { S['Inventory Clerk / Stockman']+=2; S['Order Processor']+=2; }

    const priority = [
      'Store Manager',
      'Deputy Store Manager',
      'Order Processor',
      'Inventory Clerk / Stockman',
      'Merchandiser / Promodiser',
      'Cashier',
      'Store Part Timer'
    ];
    let best = priority[priority.length-1], bestScore = -1;
    for (const r of priority){
      if (S[r] > bestScore){ best = r; bestScore = S[r]; }
    }

    document.getElementById('roleSelect').value = best;
    document.getElementById('evalHint').innerHTML =
      `<span class="text-rose-600 font-semibold">Suggested role:</span> ${best}`;
    document.getElementById('apply').scrollIntoView({behavior:'smooth'});
  });

  // ===== Submit handler =====
  const form = document.getElementById('appForm');
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!document.getElementById('agree2').checked)
      return toast('Please certify your information before submitting.');

    const fd = new FormData(form);
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true; btn.textContent='Submitting...';

    try{
      const res = await fetch('api/recruitment_api.php', { method:'POST', body: fd });
      const raw = await res.text();
      let j; try{ j = JSON.parse(raw);}catch(e){ j = {ok:false,error: raw.slice(0,200)}; }
      if (j.ok){
        toast('Application submitted! Thank you.');
        form.reset();
        window.scrollTo({top:0,behavior:'smooth'});
      }else{
        toast(j.error || 'Submission failed — please try again.');
      }
    }catch(err){
      toast('Network error — please try again.');
    }finally{
      btn.disabled=false; btn.textContent='Submit Application';
    }
  });
})();
</script>
</body>
</html>
