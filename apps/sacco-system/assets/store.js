const KEY="openfinance_demo_v1";
const seed={
members:[
{id:"MBR-001",name:"Amina Njeri",phone:"+254 700 111 222",status:"Active",joined:"2026-01-14"},
{id:"MBR-002",name:"Brian Otieno",phone:"+254 711 223 344",status:"Active",joined:"2026-02-03"},
{id:"MBR-003",name:"Carol Wambui",phone:"+254 722 334 455",status:"Review",joined:"2026-03-19"},
{id:"MBR-004",name:"David Kiptoo",phone:"+254 733 445 566",status:"Active",joined:"2026-04-28"}],
contributions:[
{id:"CTR-1001",member:"Amina Njeri",amount:12500,date:"2026-09-02",channel:"Cash"},
{id:"CTR-1002",member:"Brian Otieno",amount:8000,date:"2026-09-05",channel:"M-Pesa placeholder"},
{id:"CTR-1003",member:"Carol Wambui",amount:15000,date:"2026-09-09",channel:"Bank placeholder"}],
loans:[
{id:"LN-201",member:"Amina Njeri",principal:180000,balance:132000,status:"Active",term:"24 months"},
{id:"LN-202",member:"Brian Otieno",principal:95000,balance:95000,status:"Pending",term:"12 months"},
{id:"LN-203",member:"David Kiptoo",principal:260000,balance:214500,status:"Active",term:"30 months"}],
guarantors:[
{id:"G-01",loan:"LN-201",member:"Brian Otieno",amount:90000,status:"Accepted"},
{id:"G-02",loan:"LN-201",member:"David Kiptoo",amount:90000,status:"Accepted"},
{id:"G-03",loan:"LN-202",member:"Amina Njeri",amount:50000,status:"Pending"}],
ledger:[
{id:"LED-01",date:"2026-09-09",member:"Carol Wambui",details:"Demo contribution",debit:0,credit:15000,balance:548000},
{id:"LED-02",date:"2026-09-07",member:"Amina Njeri",details:"Demo loan repayment",debit:0,credit:12000,balance:533000},
{id:"LED-03",date:"2026-09-06",member:"OpenFinance",details:"Demo expense",debit:18500,credit:0,balance:521000},
{id:"LED-04",date:"2026-09-05",member:"Brian Otieno",details:"Demo contribution",debit:0,credit:8000,balance:539500}],
receipts:[
{id:"RCP-301",member:"Carol Wambui",amount:15000,date:"2026-09-09",type:"Contribution"},
{id:"RCP-302",member:"Amina Njeri",amount:12000,date:"2026-09-07",type:"Loan repayment"}],
expenses:[
{id:"EXP-401",category:"Operations",description:"Synthetic office expense",amount:18500,date:"2026-09-06"},
{id:"EXP-402",category:"Technology",description:"Synthetic hosting allocation",amount:6500,date:"2026-09-10"}],
communications:[
{id:"MSG-501",audience:"All members",subject:"Demo monthly update",status:"Queued",channel:"SMS placeholder"},
{id:"MSG-502",audience:"Loan applicants",subject:"Demo application reminder",status:"Draft",channel:"Email placeholder"}],
tickets:[
{id:"TKT-601",member:"Brian Otieno",subject:"Statement request",status:"Open",priority:"Normal"},
{id:"TKT-602",member:"Carol Wambui",subject:"Update contact details",status:"Open",priority:"High"}]
};
const clone=x=>JSON.parse(JSON.stringify(x));
function load(){try{const v=JSON.parse(localStorage.getItem(KEY));return v&&v.members? v:clone(seed)}catch(e){return clone(seed)}}
let data=load();
function save(){localStorage.setItem(KEY,JSON.stringify(data))}
if(!localStorage.getItem(KEY)) save();
const money=n=>"KES "+Math.round(Number(n)||0).toLocaleString("en-KE");
const esc=s=>String(s??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[c]));
const pill=s=>{const v=String(s),l=v.toLowerCase();const cls=/active|accepted|paid|sent|closed/.test(l)?"good":/pending|queued|review|draft/.test(l)?"warn":/rejected|overdue|failed/.test(l)?"bad":"";return `<span class="pill ${cls}">${esc(v)}</span>`};

function dashboard(){
 const el=id=>document.getElementById(id);
 if(!el("kpiMembers"))return;
 const pending=data.loans.filter(x=>x.status==="Pending").length;
 const active=data.loans.filter(x=>x.status==="Active");
 const portfolio=active.reduce((a,x)=>a+Number(x.principal||0),0);
 const balance=data.ledger[0]?.balance||0;
 el("kpiMembers").textContent=data.members.length;
 el("kpiLoans").textContent=active.length;
 el("kpiPending").textContent=pending+" pending";
 el("kpiPortfolio").textContent=money(portfolio);
 el("kpiBalance").textContent=money(balance);
 el("attentionLoans").textContent=pending;
 el("attentionTickets").textContent=data.tickets.filter(x=>x.status==="Open").length;
 el("attentionMessages").textContent=data.communications.filter(x=>x.status==="Queued").length;
 el("dashboardLedger").innerHTML=data.ledger.slice(0,5).map(x=>`<tr><td>${esc(x.date)}</td><td>${esc(x.member)}</td><td>${esc(x.details)}</td><td>${x.debit?money(x.debit):"—"}</td><td>${x.credit?money(x.credit):"—"}</td><td>${money(x.balance)}</td></tr>`).join("");
}
const schemas={
members:[["ID","id"],["Member","name"],["Phone","phone"],["Status","status","pill"],["Joined","joined"]],
contributions:[["ID","id"],["Member","member"],["Amount","amount","money"],["Date","date"],["Channel","channel"]],
loans:[["ID","id"],["Member","member"],["Principal","principal","money"],["Balance","balance","money"],["Status","status","pill"],["Term","term"]],
guarantors:[["ID","id"],["Loan","loan"],["Guarantor","member"],["Guaranteed","amount","money"],["Status","status","pill"]],
ledger:[["ID","id"],["Date","date"],["Member","member"],["Details","details"],["Debit","debit","money"],["Credit","credit","money"],["Balance","balance","money"]],
receipts:[["ID","id"],["Member","member"],["Amount","amount","money"],["Date","date"],["Type","type"]],
expenses:[["ID","id"],["Category","category"],["Description","description"],["Amount","amount","money"],["Date","date"]],
communications:[["ID","id"],["Audience","audience"],["Subject","subject"],["Channel","channel"],["Status","status","pill"]],
tickets:[["ID","id"],["Member","member"],["Subject","subject"],["Priority","priority"],["Status","status","pill"]]
};
function moduleTable(view){
 const head=document.getElementById("moduleHead"),body=document.getElementById("moduleBody"),empty=document.getElementById("emptyState");
 if(!head||!body||!schemas[view])return;
 const rows=data[view]||[],cols=schemas[view];
 head.innerHTML="<tr>"+cols.map(c=>"<th>"+c[0]+"</th>").join("")+"</tr>";
 body.innerHTML=rows.map(r=>"<tr>"+cols.map(c=>{let v=r[c[1]];if(c[2]==="money")v=Number(v)?money(v):"—";if(c[2]==="pill")v=pill(v);else v=esc(v);return "<td>"+v+"</td>"}).join("")+"</tr>").join("");
 empty.hidden=rows.length>0;
}
function reports(){
 const $=id=>document.getElementById(id);if(!$("reportMembers"))return;
 $("reportMembers").textContent=data.members.length;
 $("reportContributions").textContent=money(data.contributions.reduce((a,x)=>a+Number(x.amount||0),0));
 $("reportLoans").textContent=money(data.loans.reduce((a,x)=>a+Number(x.principal||0),0));
 $("reportExpenses").textContent=money(data.expenses.reduce((a,x)=>a+Number(x.amount||0),0));
}
function add(view){
 const now=new Date().toISOString().slice(0,10);
 const name=prompt("Synthetic name / description for this demo record:","Demo record");if(!name)return;
 if(view==="members")data.members.unshift({id:"MBR-"+String(Date.now()).slice(-4),name,phone:"+254 700 000 000",status:"Review",joined:now});
 if(view==="contributions")data.contributions.unshift({id:"CTR-"+String(Date.now()).slice(-5),member:name,amount:10000,date:now,channel:"Demo cache"});
 if(view==="loans")data.loans.unshift({id:"LN-"+String(Date.now()).slice(-4),member:name,principal:100000,balance:100000,status:"Pending",term:"12 months"});
 if(view==="receipts")data.receipts.unshift({id:"RCP-"+String(Date.now()).slice(-4),member:name,amount:10000,date:now,type:"Demo"});
 if(view==="expenses")data.expenses.unshift({id:"EXP-"+String(Date.now()).slice(-4),category:"Demo",description:name,amount:5000,date:now});
 if(view==="communications")data.communications.unshift({id:"MSG-"+String(Date.now()).slice(-4),audience:name,subject:"Synthetic message",status:"Queued",channel:"No send"});
 if(view==="tickets")data.tickets.unshift({id:"TKT-"+String(Date.now()).slice(-4),member:name,subject:"Synthetic support request",status:"Open",priority:"Normal"});
 save();moduleTable(view);reports();dashboard();
}
function reset(){
 if(!confirm("Reset all browser-cached OpenFinance demo data?"))return;
 data=clone(seed);save();location.reload();
}
const view=document.body.dataset.view;
dashboard();moduleTable(view);reports();
document.querySelectorAll("[data-add]").forEach(b=>b.addEventListener("click",()=>add(b.dataset.add)));
["resetDemo","resetDemoSecondary"].forEach(id=>document.getElementById(id)?.addEventListener("click",reset));
const side=document.querySelector(".sidebar");document.getElementById("sidebarToggle")?.addEventListener("click",()=>side?.classList.toggle("open"));
