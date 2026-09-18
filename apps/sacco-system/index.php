<?php
declare(strict_types=1);
session_start();

if (isset($_GET['logout'])) { session_destroy(); header('Location: ./'); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['enter_demo'])) {
    $role=(string)($_POST['role']??'Administrator');
    $allowed=['Administrator','Finance','Member Services'];
    if (!in_array($role,$allowed,true)) $role='Administrator';
    $_SESSION['openfinance_demo_user']=[
        'name'=>trim((string)($_POST['name']??'Demo Administrator')) ?: 'Demo Administrator',
        'role'=>$role
    ];
    header('Location: ?view=dashboard'); exit;
}
$user=$_SESSION['openfinance_demo_user']??null;
if (!$user):
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="noindex,nofollow"><title>OpenFinance · SACCO System Demo</title><link rel="stylesheet" href="assets/system.css"></head><body class="login-body">
<div class="login-shell">
<section class="login-visual"><img src="assets/demo-finance.svg" alt="OpenFinance operations demo illustration"><div class="login-brand"><span>OF</span><strong>OpenFinance</strong></div><div class="login-copy"><small>DEMO MODE · CACHED DATA ONLY</small><h1>Finance operations without the integration overhead.</h1><p>Explore members, contributions, lending, ledger activity, receipts, expenses, support and reports using synthetic records.</p></div></section>
<section class="login-panel"><form method="post" class="login-card"><input type="hidden" name="enter_demo" value="1"><span class="eyebrow">Open workspace</span><h2>Enter the demo system.</h2><p>No password is required. Choose a role to inspect the workflow.</p><div class="notice"><strong>GitHub-safe:</strong> no database credentials, Firebase, email, SMS, payment or KYC keys are required.</div><label>Display name<input name="name" value="Demo Administrator" maxlength="60"></label><label>Demo role<select name="role"><option>Administrator</option><option>Finance</option><option>Member Services</option></select></label><button class="primary-button" type="submit">Enter OpenFinance</button><div class="login-links"><a href="https://sacco.valron.co.ke">Public demo ↗</a><a href="https://github.com/wanjohi-11/OpenFinance">GitHub ↗</a></div></form></section>
</div></body></html>
<?php exit; endif;

$views=[
'dashboard'=>['Dashboard','Overview and activity'],
'members'=>['Members','Synthetic member records'],
'contributions'=>['Contributions','Member contribution activity'],
'loans'=>['Loans','Requests, approvals and balances'],
'guarantors'=>['Guarantors','Illustrative guarantee relationships'],
'ledger'=>['Ledger','Synthetic financial postings'],
'receipts'=>['Receipts','Cached receipt records'],
'expenses'=>['Expenses','Illustrative operating expenses'],
'communications'=>['Communications','Messages queue — nothing is sent'],
'tickets'=>['Support Tickets','Member-service requests'],
'reports'=>['Reports','Demo operational summaries'],
'settings'=>['Settings','Demo environment configuration'],
];
$view=(string)($_GET['view']??'dashboard');
if (!isset($views[$view])) $view='dashboard';
function e(string $v): string { return htmlspecialchars($v,ENT_QUOTES,'UTF-8'); }
?><!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#4c1d95"><title><?=e($views[$view][0])?> · OpenFinance</title><link rel="stylesheet" href="assets/system.css"></head>
<body class="system-body" data-view="<?=e($view)?>">
<div class="system-shell">
<aside class="sidebar">
  <a class="system-brand" href="?view=dashboard"><span>OF</span><div><strong>OpenFinance</strong><small>SACCO operations demo</small></div></a>
  <nav>
    <small>OPERATIONS</small>
    <a class="<?= $view==='dashboard'?'active':'' ?>" href="?view=dashboard"><b>⌂</b>Dashboard</a>
    <a class="<?= $view==='members'?'active':'' ?>" href="?view=members"><b>◉</b>Members</a>
    <a class="<?= $view==='contributions'?'active':'' ?>" href="?view=contributions"><b>↧</b>Contributions</a>
    <a class="<?= $view==='loans'?'active':'' ?>" href="?view=loans"><b>↗</b>Loans</a>
    <a class="<?= $view==='guarantors'?'active':'' ?>" href="?view=guarantors"><b>◇</b>Guarantors</a>
    <small>FINANCE</small>
    <a class="<?= $view==='ledger'?'active':'' ?>" href="?view=ledger"><b>≡</b>Ledger</a>
    <a class="<?= $view==='receipts'?'active':'' ?>" href="?view=receipts"><b>▤</b>Receipts</a>
    <a class="<?= $view==='expenses'?'active':'' ?>" href="?view=expenses"><b>−</b>Expenses</a>
    <small>SERVICE</small>
    <a class="<?= $view==='communications'?'active':'' ?>" href="?view=communications"><b>✉</b>Communications</a>
    <a class="<?= $view==='tickets'?'active':'' ?>" href="?view=tickets"><b>?</b>Support Tickets</a>
    <a class="<?= $view==='reports'?'active':'' ?>" href="?view=reports"><b>▥</b>Reports</a>
    <a class="<?= $view==='settings'?'active':'' ?>" href="?view=settings"><b>⚙</b>Settings</a>
  </nav>
  <div class="sidebar-foot"><span class="demo-dot"></span><div><strong>Demo mode</strong><small>No live integrations</small></div></div>
</aside>

<section class="main">
<header class="topbar"><button id="sidebarToggle" class="sidebar-toggle">Menu</button><div><span class="top-kicker">OpenFinance Operations</span><h1><?=e($views[$view][0])?></h1><p><?=e($views[$view][1])?></p></div><div class="user-menu"><div class="avatar"><?=e(strtoupper(substr((string)$user['name'],0,1)))?></div><div><strong><?=e((string)$user['name'])?></strong><small><?=e((string)$user['role'])?></small></div><a href="?logout=1" title="Sign out">↗</a></div></header>
<div class="demo-banner"><strong>Demo environment.</strong><span>Figures are synthetic and stored only in this browser. No bank, SACCO, payment, email or SMS service is connected.</span><button id="resetDemo">Reset demo data</button></div>
<div class="content">

<?php if($view==='dashboard'): ?>
<section class="welcome"><div><span class="eyebrow dark">Today at a glance</span><h2>See the cooperative before opening a workflow.</h2><p>Every value below is generated from the local synthetic dataset.</p></div><a class="outline-button" href="?view=reports">Open reports →</a></section>
<section class="kpi-grid">
<article><span>Total members</span><strong id="kpiMembers">—</strong><small>synthetic records</small></article>
<article><span>Active loans</span><strong id="kpiLoans">—</strong><small id="kpiPending">— pending</small></article>
<article><span>Loan portfolio</span><strong id="kpiPortfolio">—</strong><small>illustrative principal</small></article>
<article><span>Ledger balance</span><strong id="kpiBalance">—</strong><small>latest cached balance</small></article>
</section>
<section class="dashboard-grid">
<div class="panel"><div class="panel-head"><div><h3>Start a task</h3><p>Actions write only to localStorage.</p></div></div><div class="task-grid">
<a href="?view=members" class="task"><b>+</b><span>Add member<small>Create a synthetic profile</small></span></a>
<a href="?view=contributions" class="task"><b>↧</b><span>Contribution<small>Cache a demo deposit</small></span></a>
<a href="?view=loans" class="task"><b>↗</b><span>Loan request<small>Add an illustrative loan</small></span></a>
<a href="?view=receipts" class="task"><b>▤</b><span>Receipt<small>Create a demo receipt</small></span></a>
</div></div>
<div class="panel"><div class="panel-head"><div><h3>Needs attention</h3><p>Seeded workflow queues.</p></div></div><div class="attention"><div><strong id="attentionLoans">—</strong><span>Pending loans</span></div><div><strong id="attentionTickets">—</strong><span>Open support tickets</span></div><div><strong id="attentionMessages">—</strong><span>Queued messages</span></div></div></div>
</section>
<section class="panel"><div class="panel-head"><div><h3>Recent ledger activity</h3><p>Latest synthetic transactions.</p></div><a href="?view=ledger">Open ledger →</a></div><div class="table-wrap"><table><thead><tr><th>Date</th><th>Member</th><th>Details</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody id="dashboardLedger"></tbody></table></div></section>

<?php elseif(in_array($view,['members','contributions','loans','guarantors','ledger','receipts','expenses','communications','tickets'],true)): ?>
<section class="module-head"><div><span class="eyebrow dark">Cached workspace</span><h2><?=e($views[$view][0])?></h2><p><?=e($views[$view][1])?>. All additions are synthetic and remain in this browser.</p></div><?php if(!in_array($view,['guarantors','ledger'],true)): ?><button class="primary-button small" data-add="<?=e($view)?>">+ Add demo record</button><?php endif; ?></section>
<section class="panel"><div class="table-wrap"><table id="moduleTable"><thead id="moduleHead"></thead><tbody id="moduleBody"></tbody></table></div><div id="emptyState" class="empty-state" hidden>No demo records in this module.</div></section>

<?php elseif($view==='reports'): ?>
<section class="module-head"><div><span class="eyebrow dark">Reports</span><h2>Operational summaries</h2><p>These cards are computed from the browser seed dataset and are not authoritative financial statements.</p></div></section>
<section class="report-grid"><article><small>Members</small><strong id="reportMembers">—</strong><p>Cached member profiles</p></article><article><small>Contributions</small><strong id="reportContributions">—</strong><p>Total synthetic contributions</p></article><article><small>Loan portfolio</small><strong id="reportLoans">—</strong><p>Illustrative principal</p></article><article><small>Expenses</small><strong id="reportExpenses">—</strong><p>Cached operating expenses</p></article></section>
<section class="panel report-note"><h3>Production reporting would require more than totals.</h3><p>A real SACCO implementation needs authoritative accounting rules, period controls, reconciliation, audit trails, approval history, export controls and clear definitions for every KPI.</p></section>

<?php elseif($view==='settings'): ?>
<section class="module-head"><div><span class="eyebrow dark">Environment</span><h2>Demo configuration</h2><p>This build intentionally exposes no secret-management or provider credential fields.</p></div></section>
<section class="settings-grid">
<article><small>Mode</small><strong>Demo / portfolio</strong><p>No production persistence or transaction processing.</p></article>
<article><small>Persistence</small><strong>Browser localStorage</strong><p>Each browser profile keeps an independent synthetic dataset.</p></article>
<article><small>Authentication</small><strong>PHP demo session</strong><p>Passwordless entry is deliberate and must be replaced in production.</p></article>
<article><small>Integrations</small><strong>Placeholders only</strong><p>M-Pesa, cards, SMS, email, KYC and databases are not called.</p></article>
</section>
<section class="panel danger-panel"><h3>Reset workspace</h3><p>Delete browser-cached records and recreate the original seed dataset.</p><button id="resetDemoSecondary" class="danger-button">Reset demo data</button></section>
<?php endif; ?>

</div>
<footer class="system-footer"><span>OpenFinance · SACCO system demo</span><a href="https://github.com/wanjohi-11/OpenFinance">GitHub ↗</a><span>Built by Valron</span></footer>
</section>
</div>
<script src="assets/store.js"></script>
</body></html>