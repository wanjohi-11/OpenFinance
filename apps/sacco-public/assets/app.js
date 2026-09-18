const $=(s)=>document.querySelector(s);
const money=(n)=>"KES "+Math.round(Number(n)||0).toLocaleString("en-KE");

const toggle=$(".nav-toggle"), nav=$(".nav");
if(toggle&&nav) toggle.addEventListener("click",()=>nav.classList.toggle("open"));

function savings(){
  const target=$("#saveTarget"), months=$("#saveMonths"), out=$("#saveResult");
  if(!target||!months||!out) return;
  const run=()=>out.textContent=money((Number(target.value)||0)/Math.max(1,Number(months.value)||1));
  target.addEventListener("input",run);months.addEventListener("input",run);run();
}
function loan(){
  const p=$("#loanPrincipal"), rate=$("#loanRate"), months=$("#loanMonths"), out=$("#loanResult");
  if(!p||!rate||!months||!out) return;
  const run=()=>{
    const principal=Number(p.value)||0,n=Math.max(1,Number(months.value)||1),r=(Number(rate.value)||0)/1200;
    const payment=r===0?principal/n:(principal*r*Math.pow(1+r,n))/(Math.pow(1+r,n)-1);
    out.textContent=money(payment);
  };
  [p,rate,months].forEach(el=>el.addEventListener("input",run));run();
}
savings();loan();
