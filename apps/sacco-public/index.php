<?php
declare(strict_types=1);
session_start();

function env_load(string $file): void {
    if (!is_file($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line=trim($line);
        if ($line==='' || str_starts_with($line,'#') || !str_contains($line,'=')) continue;
        [$key,$value]=array_map('trim',explode('=',$line,2));
        if (($value[0]??'')==='"' && str_ends_with($value,'"')) $value=substr($value,1,-1);
        $_ENV[$key]=$value;
    }
}
env_load(__DIR__.'/.env');
function envv(string $key,string $fallback=''): string { return (string)($_ENV[$key] ?? $fallback); }
function e(string $v): string { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }

$appName=envv('APP_NAME','OpenFinance');
$appUrl=envv('APP_URL','https://sacco.valron.co.ke');
$systemUrl=envv('SYSTEM_URL','https://system-sacco.valron.co.ke');
$githubUrl=envv('GITHUB_URL','https://github.com/wanjohi-11/OpenFinance');
$contactEmail=envv('CONTACT_EMAIL','demo@sacco.valron.co.ke');

$storage=__DIR__.'/storage/demo';
if (!is_dir($storage)) @mkdir($storage,0775,true);

function demo_read(string $name): array {
    global $storage;
    $path=$storage.'/'.$name.'.json';
    if (is_file($path)) {
        $data=json_decode((string)file_get_contents($path),true);
        if (is_array($data)) return $data;
    }
    return $_SESSION['demo_'.$name] ?? [];
}
function demo_write(string $name,array $data): void {
    global $storage;
    $path=$storage.'/'.$name.'.json';
    if (is_dir($storage) && is_writable($storage)) {
        @file_put_contents($path,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);
    } else {
        $_SESSION['demo_'.$name]=$data;
    }
}
function ref_code(): string { return 'OF-'.date('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6)); }

$flash='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action'] ?? '';
    if ($action==='apply') {
        $apps=demo_read('applications');
        $ref=ref_code();
        $apps[]=[
            'reference'=>$ref,
            'name'=>trim((string)($_POST['name']??'Demo Applicant')),
            'email'=>strtolower(trim((string)($_POST['email']??''))),
            'phone'=>trim((string)($_POST['phone']??'')),
            'goal'=>trim((string)($_POST['goal']??'')),
            'status'=>'Under review',
            'created_at'=>date(DATE_ATOM),
        ];
        demo_write('applications',$apps);
        $_SESSION['last_ref']=$ref;
        header('Location: ?page=join-success'); exit;
    }
    if ($action==='contact') {
        $items=demo_read('enquiries');
        $items[]=[
            'name'=>trim((string)($_POST['name']??'')),
            'email'=>strtolower(trim((string)($_POST['email']??''))),
            'message'=>trim((string)($_POST['message']??'')),
            'created_at'=>date(DATE_ATOM),
        ];
        demo_write('enquiries',$items);
        $flash='Your demo enquiry was cached locally. No email or SMS was sent.';
    }
}

$page=preg_replace('/[^a-z0-9-]/','',strtolower((string)($_GET['page']??'home')));
$allowed=['home','membership','savings','loans','resources','join','join-success','application-status','contact'];
if (!in_array($page,$allowed,true)) $page='home';

$meta=[
'home'=>['OpenFinance | Open-source SACCO demo','Explore a modern SACCO experience built for transparent, replicable financial-system prototyping.'],
'membership'=>['Membership | OpenFinance','Understand the synthetic OpenFinance membership journey.'],
'savings'=>['Savings | OpenFinance','Explore goal-based savings concepts and a browser-side planner.'],
'loans'=>['Loans | OpenFinance','Explore responsible-credit concepts and a simple repayment estimator.'],
'resources'=>['Resources | OpenFinance','Plain-language SACCO and financial-literacy resources.'],
'join'=>['Demo application | OpenFinance','Submit a synthetic membership application stored only in the demo cache.'],
'join-success'=>['Application received | OpenFinance','View the reference generated for your demo application.'],
'application-status'=>['Application status | OpenFinance','Test local application-status lookup.'],
'contact'=>['Contact | OpenFinance','Cache a demo enquiry locally without external messaging APIs.'],
][$page];

$statusResult=null;
if ($page==='application-status' && isset($_GET['reference'],$_GET['email'])) {
    $reference=strtoupper(trim((string)$_GET['reference']));
    $email=strtolower(trim((string)$_GET['email']));
    $all=array_merge([[
        'reference'=>'OF-DEMO-001','name'=>'Amina Demo','email'=>'demo@example.com',
        'status'=>'Approved for demo','created_at'=>'2026-09-01T09:00:00+03:00'
    ]],demo_read('applications'));
    foreach ($all as $item) {
        if (($item['reference']??'')===$reference && strtolower((string)($item['email']??''))===$email) { $statusResult=$item; break; }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#5b21b6">
<title><?=e($meta[0])?></title>
<meta name="description" content="<?=e($meta[1])?>">
<link rel="icon" type="image/svg+xml" href="assets/openfinance-hero.svg">
<link rel="stylesheet" href="assets/app.css">
</head>
<body>
<div class="demo-bar"><strong>OpenFinance demo</strong><span>Synthetic data · cached locally · no live financial APIs</span><a href="<?=e($githubUrl)?>" target="_blank" rel="noopener">GitHub ↗</a></div>
<header class="site-header">
  <a class="brand" href="?page=home"><span class="brand-mark">OF</span><span><strong>OpenFinance</strong><small>Cooperative finance demo</small></span></a>
  <button class="nav-toggle" type="button" aria-label="Toggle navigation">Menu</button>
  <nav class="nav">
    <a href="?page=membership">Membership</a>
    <a href="?page=savings">Savings</a>
    <a href="?page=loans">Loans</a>
    <a href="?page=resources">Resources</a>
    <a href="?page=contact">Contact</a>
    <a class="nav-system" href="<?=e($systemUrl)?>">Open system ↗</a>
  </nav>
</header>
<main>
<?php if($flash): ?><div class="flash"><?=e($flash)?></div><?php endif; ?>

<?php if($page==='home'): ?>
<section class="hero">
  <div class="hero-media"><img src="assets/openfinance-hero.svg" alt="OpenFinance synthetic cooperative finance illustration"></div>
  <div class="hero-overlay"></div>
  <div class="hero-copy">
    <span class="eyebrow">Pamoja. Wazi. Rahisi.</span>
    <h1>A SACCO experience people can actually understand.</h1>
    <p>Explore membership, savings and responsible credit through a self-contained demo. OpenFinance shows the product journey without requiring a database, payment provider, SMS gateway or identity API.</p>
    <div class="actions"><a class="btn primary" href="?page=join">Try membership flow</a><a class="btn glass" href="?page=application-status">Track demo application</a></div>
    <div class="hero-metrics"><div><strong>0</strong><span>production APIs</span></div><div><strong>2</strong><span>demo surfaces</span></div><div><strong>100%</strong><span>synthetic data</span></div></div>
  </div>
</section>
<section class="section intro-grid">
  <div><span class="eyebrow dark">Designed for exploration</span><h2>Finance workflows without integration overhead.</h2><p class="lead">The public site demonstrates acquisition and member education. The companion operations system demonstrates day-to-day SACCO workflows using browser-cached records.</p></div>
  <div class="principles"><article><span>01</span><h3>No secrets required</h3><p>Clone and run without credentials or paid services.</p></article><article><span>02</span><h3>Local demo persistence</h3><p>Forms use JSON/session caching. Operations use localStorage.</p></article><article><span>03</span><h3>Production boundaries</h3><p>Add payments, messaging, KYC and a database later behind adapters.</p></article></div>
</section>
<section class="section cards-section"><div class="section-head"><span class="eyebrow dark">Explore the journey</span><h2>Three financial conversations, one member experience.</h2></div>
<div class="cards">
<a class="feature-card violet" href="?page=membership"><span>Membership</span><h3>Start with a clear onboarding journey.</h3><p>Understand the steps, documents and demo status flow.</p><b>Explore →</b></a>
<a class="feature-card cyan" href="?page=savings"><span>Savings</span><h3>Turn goals into repeatable contributions.</h3><p>Use the planner to model a simple target and monthly rhythm.</p><b>Plan savings →</b></a>
<a class="feature-card amber" href="?page=loans"><span>Credit</span><h3>Borrow for a reason, repay with a plan.</h3><p>Estimate repayments before connecting any lending engine.</p><b>Estimate loan →</b></a>
</div></section>
<section class="story"><div><span class="eyebrow">Open-source by design</span><h2>Built to be forked, understood and extended.</h2><p>OpenFinance is deliberately small enough to inspect and useful enough to demonstrate. The GitHub repository includes local installation, cPanel deployment, security boundaries and a production-readiness checklist.</p><a class="btn white" href="<?=e($githubUrl)?>" target="_blank" rel="noopener">View repository ↗</a></div><div class="story-panel"><strong>Public</strong><span><?=e(parse_url($appUrl,PHP_URL_HOST) ?: 'sacco.valron.co.ke')?></span><strong>System</strong><span><?=e(parse_url($systemUrl,PHP_URL_HOST) ?: 'system-sacco.valron.co.ke')?></span></div></section>

<?php elseif($page==='membership'): ?>
<section class="page-hero"><span class="eyebrow">Membership</span><h1>Joining should feel like a guided process, not paperwork in the dark.</h1><p>The demo models a simple application → reference → review → status journey. A production fork would add identity verification, document handling, member numbers and approvals.</p><div class="actions"><a class="btn primary" href="?page=join">Start demo application</a><a class="btn secondary" href="?page=application-status">Check status</a></div></section>
<section class="section steps"><article><span>1</span><h3>Apply</h3><p>Capture only the fields needed to demonstrate the experience.</p></article><article><span>2</span><h3>Receive reference</h3><p>The app creates a synthetic reference for local tracking.</p></article><article><span>3</span><h3>Review</h3><p>A real implementation would route the record through controlled back-office approvals.</p></article><article><span>4</span><h3>Activate</h3><p>Production systems would provision the member account after approval and verification.</p></article></section>
<section class="section notice-panel"><h2>Production additions</h2><div class="tag-grid"><span>KYC / identity</span><span>Document storage</span><span>RBAC approvals</span><span>Member numbering</span><span>Audit history</span><span>Notifications</span></div></section>

<?php elseif($page==='savings'): ?>
<section class="page-hero"><span class="eyebrow">Savings</span><h1>Make the target visible before the contribution begins.</h1><p>This browser-side planner is intentionally simple. It demonstrates interaction and education, not an interest-bearing product calculation.</p></section>
<section class="section calculator-grid"><div><span class="eyebrow dark">Goal planner</span><h2>Estimate a monthly saving rhythm.</h2><p class="lead">Enter a target amount and number of months. Nothing leaves your browser.</p></div><div class="calculator"><label>Target amount (KES)<input id="saveTarget" type="number" value="120000" min="1"></label><label>Months<input id="saveMonths" type="number" value="12" min="1"></label><div class="result"><span>Approx. per month</span><strong id="saveResult">KES 10,000</strong></div></div></section>

<?php elseif($page==='loans'): ?>
<section class="page-hero"><span class="eyebrow">Responsible credit</span><h1>Borrow for a reason. Repay with a plan.</h1><p>The estimator below uses a simplified reducing-balance approximation for demonstration only. It is not a loan offer or authoritative SACCO pricing.</p></section>
<section class="section calculator-grid"><div><span class="eyebrow dark">Repayment estimator</span><h2>Test the interaction without a lending API.</h2><p class="lead">Adjust principal, annual rate and term. Calculations stay in the browser.</p></div><div class="calculator"><label>Principal (KES)<input id="loanPrincipal" type="number" value="250000" min="1"></label><label>Annual rate (%)<input id="loanRate" type="number" value="12" min="0" step=".1"></label><label>Months<input id="loanMonths" type="number" value="24" min="1"></label><div class="result"><span>Illustrative monthly payment</span><strong id="loanResult">—</strong></div></div></section>

<?php elseif($page==='resources'): ?>
<section class="page-hero"><span class="eyebrow">Resources</span><h1>Understand the system before you connect real money to it.</h1><p>These demo notes focus on concepts a SACCO product must make clear to members and the engineering boundaries a production implementation must respect.</p></section>
<section class="section resource-grid">
<article><small>SACCO basics</small><h3>Deposits, shares and contributions are not interchangeable concepts.</h3><p>Production terminology must match the cooperative's by-laws, product terms and applicable law.</p></article>
<article><small>Credit</small><h3>Repayment transparency matters as much as approval speed.</h3><p>Show principal, charges, term, repayment schedule and authoritative balance rules clearly.</p></article>
<article><small>Engineering</small><h3>A dashboard is not a ledger.</h3><p>Browser-cached values are suitable for demos only. Real financial records need transactional persistence, reconciliation and audit controls.</p></article>
<article><small>Security</small><h3>Integrations should be server-side boundaries.</h3><p>Keep secrets out of Git and avoid embedding provider-specific credentials or business logic in UI code.</p></article>
</section>

<?php elseif($page==='join'): ?>
<section class="page-hero compact"><span class="eyebrow">Demo application</span><h1>Create a synthetic membership application.</h1><p>Use invented details only. The form writes to a local JSON file when the server directory is writable, otherwise to the current PHP session.</p></section>
<section class="section form-shell"><form method="post" class="form-card"><input type="hidden" name="action" value="apply"><label>Demo name<input name="name" required placeholder="Amina Demo"></label><label>Demo email<input name="email" type="email" required placeholder="demo@example.com"></label><label>Demo phone<input name="phone" placeholder="+254 700 000 000"></label><label>Primary goal<select name="goal"><option>Build savings</option><option>Business growth</option><option>Emergency fund</option><option>Future borrowing</option></select></label><button class="btn primary" type="submit">Submit demo application</button><p class="form-note">No email, SMS, KYC or payment API is contacted.</p></form></section>

<?php elseif($page==='join-success'): $ref=$_SESSION['last_ref']??'OF-DEMO-001'; ?>
<section class="success-wrap"><div class="success-icon">✓</div><span class="eyebrow dark">Cached successfully</span><h1>Your demo reference is ready.</h1><p>Save this reference if you want to test the application-status flow in the same environment.</p><div class="reference"><?=e($ref)?></div><div class="actions"><a class="btn primary" href="?page=application-status">Check an application</a><a class="btn secondary" href="?page=home">Back home</a></div></section>

<?php elseif($page==='application-status'): ?>
<section class="page-hero compact"><span class="eyebrow">Application status</span><h1>Test a local lookup.</h1><p>Try the seeded record <code>OF-DEMO-001</code> with <code>demo@example.com</code>, or use a reference you created in this environment.</p></section>
<section class="section form-shell"><form method="get" class="form-card"><input type="hidden" name="page" value="application-status"><label>Reference<input name="reference" value="<?=e((string)($_GET['reference']??'OF-DEMO-001'))?>" required></label><label>Email<input name="email" type="email" value="<?=e((string)($_GET['email']??'demo@example.com'))?>" required></label><button class="btn primary" type="submit">Check status</button></form>
<?php if(isset($_GET['reference'])): ?><div class="status-card <?php if(!$statusResult) echo 'not-found'; ?>"><?php if($statusResult): ?><small>Application found</small><h2><?=e((string)$statusResult['status'])?></h2><p><strong>Reference:</strong> <?=e((string)$statusResult['reference'])?></p><p><strong>Applicant:</strong> <?=e((string)$statusResult['name'])?></p><?php else: ?><small>No match</small><h2>Application not found</h2><p>Check the reference and demo email. No external database was queried.</p><?php endif; ?></div><?php endif; ?></section>

<?php elseif($page==='contact'): ?>
<section class="page-hero compact"><span class="eyebrow">Contact demo</span><h1>Show the enquiry journey without sending a message.</h1><p>This form caches the submission locally. A production fork could connect a CRM, help desk or email adapter later.</p></section>
<section class="section form-shell"><form method="post" class="form-card"><input type="hidden" name="action" value="contact"><label>Demo name<input name="name" required></label><label>Demo email<input name="email" type="email" required></label><label>Message<textarea name="message" rows="5" required placeholder="This is synthetic demo content..."></textarea></label><button class="btn primary" type="submit">Cache enquiry</button><p class="form-note">Repository contact: <?=e($contactEmail)?></p></form></section>
<?php endif; ?>
</main>
<footer><div><strong>OpenFinance</strong><p>Open-source SACCO experience + operations demo.</p></div><div><a href="?page=membership">Membership</a><a href="?page=savings">Savings</a><a href="?page=loans">Loans</a><a href="<?=e($githubUrl)?>" target="_blank" rel="noopener">GitHub ↗</a></div><small>Demo only · Synthetic data · Built by Valron</small></footer>
<script src="assets/app.js"></script>
</body></html>