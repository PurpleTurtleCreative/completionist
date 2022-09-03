(()=>{"use strict";const e=window.wp.element,{createContext:t,useState:a,useEffect:s}=wp.element,n=t(!1);function o(t){let{children:o}=t;const[l,c]=a(window.PTCCompletionist.tasks);s((()=>()=>{window.PTCCompletionist.tasks=l}),[l]);const i={tasks:l,setTaskProcessingStatus:(e,t)=>{c((a=>a.map((a=>a.gid==e?{...a,processingStatus:t}:{...a}))))},completeTask:async function(e){let t=!(arguments.length>1&&void 0!==arguments[1])||arguments[1];const a=i.tasks.find((t=>e===t.gid));let s={action:"ptc_update_task",nonce:window.PTCCompletionist.api.nonce,task_gid:e,completed:t};const n={method:"POST",credentials:"same-origin",body:new URLSearchParams(s)};return await window.fetch(window.ajaxurl,n).then((e=>e.json())).then((e=>(console.log(e),"success"==e.status&&e.data?(i.updateTask({gid:a.gid,completed:t}),!0):("error"==e.status&&e.data?console.error(e.data):alert("[Completionist] Error "+e.code+": "+e.message),!1)))).catch((e=>(console.error("Promise catch:",e),alert("[Completionist] Failed to complete task."),!1)))},deleteTask:async e=>{const t=i.tasks.find((t=>e===t.gid));let a={action:"ptc_delete_task",nonce:window.PTCCompletionist.api.nonce,task_gid:e};t.action_link.post_id&&(a.post_id=t.action_link.post_id);const s={method:"POST",credentials:"same-origin",body:new URLSearchParams(a)};return await window.fetch(window.ajaxurl,s).then((e=>e.json())).then((e=>(console.log(e),"success"==e.status&&e.data?(i.removeTask(e.data),!0):("error"==e.status&&e.data?console.error(e.data):alert("[Completionist] Error "+e.code+": "+e.message),!1)))).catch((e=>(console.error("Promise catch:",e),alert("[Completionist] Failed to delete task."),!1)))},unpinTask:async function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;i.tasks.find((t=>e===t.gid));let a={action:"ptc_unpin_task",nonce:window.PTCCompletionist.api.nonce,task_gid:e};t&&(a.post_id=t);const s={method:"POST",credentials:"same-origin",body:new URLSearchParams(a)};return await window.fetch(window.ajaxurl,s).then((e=>e.json())).then((e=>(console.log(e),"success"==e.status&&e.data?(i.removeTask(e.data),!0):("error"==e.status&&e.data?console.error(e.data):alert("[Completionist] Error "+e.code+": "+e.message),!1)))).catch((e=>(console.error("Promise catch:",e),alert("[Completionist] Failed to unpin task."),!1)))},pinTask:async(e,t)=>{let a={action:"ptc_pin_task",nonce:window.PTCCompletionist.api.nonce_pin,task_link:e,post_id:t};const s={method:"POST",credentials:"same-origin",body:new URLSearchParams(a)};return await window.fetch(window.ajaxurl,s).then((e=>e.json())).then((e=>(console.log(e),"success"==e.status&&e.data?(i.addTask(e.data),!0):("error"==e.status&&e.data?console.error(e.data):alert("[Completionist] Error "+e.code+": "+e.message),!1)))).catch((e=>(console.error("Promise catch:",e),alert("[Completionist] Failed to pin task."),!1)))},createTask:async function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null,a={action:"ptc_create_task",nonce:window.PTCCompletionist.api.nonce_create,...e};t&&(a.post_id=t);const s={method:"POST",credentials:"same-origin",body:new URLSearchParams(a)};return await window.fetch(window.ajaxurl,s).then((e=>e.json())).then((e=>(console.log(e),"success"==e.status&&e.data?(i.addTask(e.data),!0):("error"==e.status&&e.data?console.error(e.data):alert("[Completionist] Error "+e.code+": "+e.message),!1)))).catch((e=>(console.error("Promise catch:",e),alert("[Completionist] Failed to pin task."),!1)))},updateTask:e=>{c((t=>t.map((t=>t.gid==e.gid?{...t,...e}:{...t}))))},addTask:e=>{c((t=>[...t,{...e}]))},removeTask:e=>{c((t=>t.map((t=>{if(t.gid!=e)return{...t}})).filter((e=>!!e))))}};return(0,e.createElement)(n.Provider,{value:i},o)}function l(e){return Date.parse(e.due_on)-Date.now()<604800}function c(e){return e.filter((e=>!e.completed))}function i(e){return e.filter((e=>l(e)))}function r(e,t){return t.filter((t=>!!t.assignee&&e===t.assignee.gid))}function m(e){return e.filter((e=>!(e.action_link&&e.action_link.post_id>0)))}function d(e){return e.filter((e=>!!(e.action_link&&e.action_link.post_id>0)))}const{useContext:u,useMemo:p}=wp.element;function k(){const{tasks:t}=u(n),a=p((()=>c(t)),[t]),s=t.length-a.length;let o=0;return t.length>0&&(o=Math.round(s/t.length*100)),(0,e.createElement)("div",{className:"ptc-TaskOverview"},(0,e.createElement)("div",{className:"feature"},(0,e.createElement)("p",{className:"large"},o,(0,e.createElement)("span",{className:"small"},"%")),(0,e.createElement)("p",{className:"caption"},"Complete")),(0,e.createElement)("div",{className:"details"},(0,e.createElement)("p",{className:"incomplete"},(0,e.createElement)("span",{className:"count"},a.length)," Remaining"),(0,e.createElement)("div",{className:"progress"},(0,e.createElement)("div",{className:"progress-bar-wrapper"},(0,e.createElement)("div",{className:"progress-bar",style:{width:`${o}%`}})),(0,e.createElement)("p",{className:"caption"},(0,e.createElement)("span",{className:"completed"},"Completed ",s)," of ",t.length))))}const{useState:g,useCallback:f,useMemo:w,useEffect:E}=wp.element;function h(t){let{tasks:a,onChange:s}=t;const[n,o]=g("none"),l=w((()=>{const e=c(a);return[{key:"none",title:"All Tasks",tasks:e},{key:"pinned",title:"Pinned",tasks:d(e)},{key:"general",title:"General",tasks:m(e)},{key:"myTasks",title:"My Tasks",tasks:r(window.PTCCompletionist.me.gid,e)},{key:"critical",title:"Critical",tasks:i(e)}]}),[a]);E((()=>{const e=l.find((e=>n===e.key)).tasks;s(n,e)}),[l,n,s]);const u=f(((e,t)=>{o(e)}),[o]),p=l.map((t=>{let a=`filter-${t.key}`;return n===t.key&&(a+=" --is-active"),(0,e.createElement)("button",{key:t.key,type:"button",className:a,onClick:()=>u(t.key,t.tasks)},`${t.title} (${t.tasks.length})`)}));return(0,e.createElement)("div",{className:"ptc-TaskFilters"},p)}const{useCallback:C,useContext:T}=wp.element;function N(t){let{taskGID:a,processingStatus:s}=t;const{deleteTask:o,unpinTask:l,removeTask:c,setTaskProcessingStatus:i}=T(n),r=C((e=>{s?console.error(`Rejected handleUnpinTask. Currently ${s} task ${e}.`):(i(e,"unpinning"),l(e).then((t=>{t||i(e,!1)})))}),[s,i,l]),m=C((e=>{s?console.error(`Rejected handleDeleteTask. Currently ${s} task ${e}.`):(i(e,"deleting"),o(e).then((t=>{t||i(e,!1)})))}),[s,i,c]),d=function(e){return`https://app.asana.com/0/0/${e}/f`}(a),u="unpinning"===s?"fa-sync-alt fa-spin":"fa-thumbtack",p="deleting"===s?"fa-sync-alt fa-spin":"fa-minus";return(0,e.createElement)("div",{className:"ptc-TaskActions"},(0,e.createElement)("a",{href:d,target:"_asana"},(0,e.createElement)("button",{title:"View in Asana",className:"view",type:"button"},(0,e.createElement)("i",{className:"fas fa-link"}))),(0,e.createElement)("button",{title:"Unpin from Site",className:"unpin",type:"button",onClick:()=>r(a),disabled:!!s},(0,e.createElement)("i",{className:`fas ${u}`})),(0,e.createElement)("button",{title:"Delete from Asana",className:"delete",type:"button",onClick:()=>m(a),disabled:!!s},(0,e.createElement)("i",{className:`fas ${p}`})))}const{useState:y,useCallback:b,useContext:P}=wp.element;function _(t){let{task:a}=t;const[s,o]=y(!1),{completeTask:c,setTaskProcessingStatus:i}=P(n),r=b((e=>{a.processingStatus?console.error(`Rejected handleMarkComplete. Currently ${a.processingStatus} task ${e}.`):(i(e,"completing"),c(e,!a.completed).then((t=>{i(e,!1)})))}),[a.processingStatus,i,c]),m=b((()=>{a.notes&&o(!s)}),[a,s,o]),d=s?"fas":"far";let u=function(e){let t=null;return e.assignee&&(t=window.PTCCompletionist.users[e.assignee.gid]?window.PTCCompletionist.users[e.assignee.gid].data.display_name:"(Not Connected)"),t}(a),p="";l(a)&&(p+=" --is-critical"),!0===a.completed&&(p+=" --is-complete"),a.processingStatus&&(p+=` --is-processing --is-${a.processingStatus}`),a.notes&&(p+=" --has-description");const k="completing"===a.processingStatus?"fa-sync-alt fa-spin":"fa-check",g=new Date(a.due_on).toLocaleDateString(void 0,{month:"short",day:"numeric",year:"numeric",timeZone:"UTC"});return(0,e.createElement)("div",{className:"ptc-TaskRow"+p},(0,e.createElement)("button",{title:"Mark Complete",className:"mark-complete",type:"button",onClick:()=>r(a.gid),disabled:!!a.processingStatus},(0,e.createElement)("i",{className:`fas ${k}`})),(0,e.createElement)("div",{className:"body"},(0,e.createElement)("p",{className:"name",onClick:m},a.name,!!a.notes&&(0,e.createElement)("i",{className:`${d} fa-sticky-note`})),(0,e.createElement)("div",{className:"details"},u&&(0,e.createElement)("p",{className:"assignee"},(0,e.createElement)("i",{class:"fas fa-user"})," ",u),a.due_on&&(0,e.createElement)("p",{className:"due"},(0,e.createElement)("i",{className:"fas fa-clock"})," ",g)),s&&(0,e.createElement)("p",{className:"description"},a.notes)),(0,e.createElement)("div",{className:"actions"},(0,e.createElement)("a",{className:"cta-button",href:a.action_link.href,target:a.action_link.target},a.action_link.label," ",(0,e.createElement)("i",{className:"fas fa-long-arrow-alt-right"})),(0,e.createElement)(N,{taskGID:a.gid,processingStatus:a.processingStatus})))}function v(t){let{tasks:a}=t,s=(0,e.createElement)("p",{className:"ptc-no-results"},(0,e.createElement)("i",{className:"fas fa-clipboard-check"}),"No tasks to display.");return a.length>0&&(s=a.map((t=>(0,e.createElement)(_,{key:t.gid,task:t})))),(0,e.createElement)("div",{className:"ptc-TaskList"},s)}const{useState:S,useCallback:$,useMemo:x,useEffect:j}=wp.element;function D(t){let{limit:a,tasks:s}=t;const[n,o]=S(1),l=x((()=>Math.ceil(s.length/a)),[s,a]),c=$((e=>{o(e<=1?1:e>=l?l:e)}),[n,o,l]);j((()=>{c(n)}),[s]);const i=Math.max(0,(n-1)*a),r=s.slice(i,n*a),m=[];for(let t=1;t<=l;++t)m.push((0,e.createElement)("button",{className:"num",type:"button",title:`Page ${t}`,disabled:t===n,onClick:()=>c(t)},t));return(0,e.createElement)("div",{className:"ptc-TaskListPaginated"},(0,e.createElement)(v,{tasks:r}),(0,e.createElement)("nav",{className:"pagination"},l>1&&(0,e.createElement)(e.Fragment,null,(0,e.createElement)("button",{className:"prev",type:"button",title:"Previous Page",disabled:1===n,onClick:()=>c(n-1)},(0,e.createElement)("i",{className:"fas fa-angle-left"})),m,(0,e.createElement)("button",{className:"next",type:"button",title:"Next Page",disabled:l===n,onClick:()=>c(n+1)},(0,e.createElement)("i",{className:"fas fa-angle-right"})))),(0,e.createElement)("a",{href:window.PTCCompletionist.tag_url,target:"_asana",className:"view-tag"},(0,e.createElement)("button",{title:"View All Site Tasks in Asana",className:"view",type:"button"},(0,e.createElement)("i",{class:"fas fa-link"}))))}const{useContext:L,useCallback:M,useState:R,useEffect:U}=wp.element;function F(){const{tasks:t}=L(n),[a,s]=R(c(t)),o=M(((e,t)=>s(t)),[]);return(0,e.createElement)("div",{className:"ptc-PTCCompletionistTasksDashboardWidget"},(0,e.createElement)(k,{tasks:t}),(0,e.createElement)(h,{tasks:t,onChange:o}),(0,e.createElement)(D,{limit:5,tasks:a}))}function O(t){let{type:a,message:s,code:n}=t,o=null;"error"===a&&(o="Error",n&&(o+=` ${n}`));let l=null;o&&(l=(0,e.createElement)("strong",null,o+". "));let c="";return a&&(c+=` --has-type-${a}`),(0,e.createElement)("div",{className:"ptc-NoteBox"+c},(0,e.createElement)("p",null,l,s))}const{render:A}=wp.element;document.addEventListener("DOMContentLoaded",(()=>{const t=document.getElementById("ptc-PTCCompletionistTasksDashboardWidget");null!==t&&("error"in window.PTCCompletionist?A((0,e.createElement)(O,{type:"error",message:window.PTCCompletionist.error.message,code:window.PTCCompletionist.error.code}),t):A((0,e.createElement)(o,null,(0,e.createElement)(F,null)),t))}))})();