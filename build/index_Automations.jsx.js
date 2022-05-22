!function(){"use strict";var t=window.wp.element;const{Component:e}=wp.element;class a extends e{constructor(t){super(t),this.state={...t.automation,isDeleting:!1},this.goToAutomation=t.goToAutomation,this.deleteAutomation=this.deleteAutomation.bind(this)}deleteAutomation(){this.setState({isDeleting:!0},(()=>{this.props.deleteAutomation(this.state.ID,(()=>{this.setState({isDeleting:!1})}))}))}render(){return(0,t.createElement)("div",{className:"ptc-completionist-automation-row"},(0,t.createElement)("header",null,(0,t.createElement)("h2",{title:"Automation ID: "+this.state.ID,onClick:()=>this.goToAutomation(this.state.ID)},this.state.title),(0,t.createElement)("p",{className:"last-modified"},(0,t.createElement)("em",null,"Updated ",this.state.last_modified)),this.state.description.length>0&&(0,t.createElement)("p",{className:"description",dangerouslySetInnerHTML:{__html:this.state.description}}),(0,t.createElement)("div",{className:"automation-actions"},(0,t.createElement)("button",{className:"edit",onClick:()=>this.goToAutomation(this.state.ID)},(0,t.createElement)("i",{className:"fas fa-pen"})," Edit"),(0,t.createElement)("button",{className:"delete",onClick:this.deleteAutomation,disabled:this.state.isDeleting},(0,t.createElement)("i",{className:"fas fa-trash"})," Delete"))),(0,t.createElement)("ul",null,(0,t.createElement)("li",{title:this.state.total_conditions+" Conditions"},this.state.total_conditions),(0,t.createElement)("li",{title:this.state.total_actions+" Actions"},this.state.total_actions),(0,t.createElement)("li",{title:"Triggered "+this.state.total_triggered+" times"},this.state.total_triggered),(0,t.createElement)("li",{title:"Last Triggered "+this.state.last_triggered},this.state.last_triggered)))}}const{Component:o}=wp.element;class n extends o{constructor(t){super(t),this.state={automations:t.automations,orderBy:"title"},this.goToAutomation=t.goToAutomation}sortAutomationsListing(){}componentDidUpdate(t){this.props.automations!==t.automations&&this.setState({automations:this.props.automations})}render(){const e=this.state.automations.map((e=>(0,t.createElement)(a,{key:e.ID,automation:e,goToAutomation:this.goToAutomation,deleteAutomation:this.props.deleteAutomation})));return(0,t.createElement)("div",{className:"ptc-completionist-automations-listing"},(0,t.createElement)("div",{className:"title"},(0,t.createElement)("h1",null,"Automations"),(0,t.createElement)("div",{className:"actions"},(0,t.createElement)("button",{onClick:()=>this.goToAutomation("new")},(0,t.createElement)("i",{className:"fas fa-plus"})," Add New"))),(0,t.createElement)("header",null,(0,t.createElement)("div",null,"Automation"),(0,t.createElement)("div",null,(0,t.createElement)("i",{className:"fas fa-question"})," Conditions"),(0,t.createElement)("div",null,(0,t.createElement)("i",{className:"fas fa-running"})," Actions"),(0,t.createElement)("div",null,(0,t.createElement)("i",{className:"fas fa-bolt"})," Triggers"),(0,t.createElement)("div",null,(0,t.createElement)("i",{className:"fas fa-history"})," Last Triggered")),(0,t.createElement)("div",{className:"ptc-completionist-automations-list"},e))}}const{Component:s}=wp.element;class i extends s{constructor(t){super(t),this.state={isLoading:!1,currentRequest:{},textInputHasFocus:!1,options:[],currentValue:"",currentLabel:""},"initialValue"in t&&t.initialValue&&(this.state.currentValue=t.initialValue),"initialLabel"in t&&t.initialLabel&&(this.state.currentLabel=t.initialLabel),this.handleSearchChange=this.handleSearchChange.bind(this),this.handleOptionChange=this.handleOptionChange.bind(this),this.createSelectOptions=this.createSelectOptions.bind(this),this.handleSearchBlur=this.handleSearchBlur.bind(this)}handleSearchChange(t){t.trim().length>=3?this.setState({isLoading:!0,currentValue:"",currentLabel:t,options:[]},(()=>{let t={action:"ptc_get_post_options_by_title",nonce:window.ptc_completionist_automations.nonce,title:this.state.currentLabel},e=window.jQuery.post(window.ajaxurl,t,(t=>{this.setState({isLoading:!1,currentRequest:{},options:t.data})}),"json").fail(((t,e)=>{"abort"!=e&&(alert("Failed to search for posts by title."),this.setState({isLoading:!1,options:[]}))}));this.setState((t=>("object"==typeof t.currentRequest&&"function"==typeof t.currentRequest.abort&&this.state.currentRequest.abort(),{currentRequest:e})))})):this.setState({isLoading:!1,currentValue:"",currentLabel:t,options:[]})}handleOptionChange(t,e){this.setState((a=>({currentValue:t,currentLabel:e})),(()=>{this.props.onSelectOption(this.state.currentValue)}))}handleSearchBlur(){this.setState((t=>({textInputHasFocus:!1,currentLabel:""===t.currentValue?"":t.currentLabel})))}createSelectOptions(){return this.state.options.length<1?!0===this.state.isLoading?(0,t.createElement)("li",null,(0,t.createElement)("i",{className:"fas fa-spinner fa-pulse"})," Searching for posts..."):this.state.currentLabel.trim().length>=3?(0,t.createElement)("li",null,"No post results."):(0,t.createElement)("li",null,"Enter at least 3 characters to search..."):this.state.options.map((e=>(0,t.createElement)("li",{className:"post-option","data-value":e.ID,key:e.ID,onMouseDown:()=>this.handleOptionChange(e.ID,e.post_title)},e.post_title+" ["+e.ID+"]")))}componentDidMount(){""!==this.state.currentValue.trim()&&""===this.state.currentLabel.trim()&&this.setState({currentLabel:"(Loading...)"},(()=>{let t={action:"ptc_get_post_title_by_id",nonce:window.ptc_completionist_automations.nonce,post_id:this.state.currentValue};window.jQuery.post(window.ajaxurl,t,(t=>{"success"==t.status&&""!=t.data?this.setState({currentLabel:t.data}):(console.error("Failed to load initial PostSearchSelectInput label for initial value."),console.error(t),this.setState({currentLabel:"(Error: Failed to load post title)"}))}),"json").fail((()=>{console.error("Failed to load initial PostSearchSelectInput label for initial value."),this.setState({currentLabel:"(Error: Failed to load post title)"})}))}))}componentDidUpdate(t,e){this.state.currentValue!==e.currentValue&&this.props.onSelectOption(this.state.currentValue)}render(){let e=null;if(!0===this.state.textInputHasFocus){const a=this.createSelectOptions(this.state.options);e=(0,t.createElement)("ul",{className:"select-options"},a)}return(0,t.createElement)("div",{className:"ptc-ajax-search-select-input"},(0,t.createElement)("input",{id:this.props.id,type:"text",value:this.state.currentLabel,onChange:t=>this.handleSearchChange(t.target.value),onFocus:()=>this.setState({textInputHasFocus:!0}),onBlur:()=>this.handleSearchBlur()}),(0,t.createElement)("input",{type:"hidden",value:this.state.currentValue}),e)}}const{Component:l}=wp.element;function c(t){return!0===Object.keys(window.ptc_completionist_automations.event_custom_options).some((e=>t.startsWith(e)))}function r(t){let e=Object.keys(window.ptc_completionist_automations.event_custom_options).find((e=>t.startsWith(e)));return null!=e?e:t}function m(e){return Object.keys(e).map((a=>(0,t.createElement)("option",{value:a,key:a},e[a])))}class d extends l{constructor(t){super(t),"automation"in t?(this.state=t.automation,this.state.saveButtonLabel="Create","ID"in t.automation&&t.automation.ID>0&&(this.state.saveButtonLabel="Update"),this.state.isSubmitting=!1):this.state={ID:0,title:"",description:"",hook_name:"",last_modified:"",conditions:[],actions:[],saveButtonLabel:"Create",isSubmitting:!1},this.handleAutomationChange=this.handleAutomationChange.bind(this),this.handleConditionChange=this.handleConditionChange.bind(this),this.handleAddCondition=this.handleAddCondition.bind(this),this.handleRemoveCondition=this.handleRemoveCondition.bind(this),this.handleActionChange=this.handleActionChange.bind(this),this.handleActionMetaChange=this.handleActionMetaChange.bind(this),this.handleAddAction=this.handleAddAction.bind(this),this.handleRemoveAction=this.handleRemoveAction.bind(this)}saveAutomation(){this.state.isSubmitting||this.setState({isSubmitting:!0},(()=>{let t={action:"ptc_save_automation",nonce:window.ptc_completionist_automations.nonce,automation:this.state};window.jQuery.post(window.ajaxurl,t,(t=>{t.status&&"success"==t.status&&t.code&&t.data&&"object"==typeof t.data&&"ID"in t.data&&t.data.ID&&t.data.ID>0?201==t.code?this.props.goToAutomation(t.data.ID):200==t.code&&this.setState({...t.data,isSubmitting:!1}):(t.message&&t.code?alert("Error "+t.code+". The automation could not be saved. "+t.message):alert("Error 409. The automation could not be saved."),this.setState({isSubmitting:!1}))}),"json").fail((()=>{alert("Error 500. The automation could not be saved."),this.setState({isSubmitting:!1})}))}))}handleAutomationChange(t,e){this.setState((a=>({...a,[t]:e})))}handleConditionChange(t,e,a){this.setState((o=>{let n=[...o.conditions];return n[t]={...o.conditions[t],[e]:a},{conditions:n}}))}handleAddCondition(){this.setState((t=>({conditions:[...t.conditions,{ID:-Date.now(),property:"",comparison_method:window.ptc_completionist_automations.field_comparison_methods[0],value:""}]})))}handleRemoveCondition(t){this.setState((e=>({conditions:e.conditions.filter(((e,a)=>a!==t))})))}handleActionChange(t,e){this.setState((a=>{let o=[...a.actions];return o[t]={...a.actions[t],action:e,meta:this.getDefaultActionMeta(e)},{actions:o}}))}handleActionMetaChange(t,e,a){this.setState((o=>{let n=[...o.actions];return n[t]={...o.actions[t],meta:{...o.actions[t].meta,[e]:a}},{actions:n}}))}handleAddAction(){this.setState((t=>({actions:[...t.actions,{ID:-Date.now(),action:"create_task",triggered_count:0,last_triggered:"",meta:this.getDefaultActionMeta("create_task")}]})))}handleRemoveAction(t){this.setState((e=>({actions:e.actions.filter(((e,a)=>a!==t))})))}getDefaultActionMeta(t){return"create_task"===t?{task_author:Object.keys(window.ptc_completionist_automations.connected_workspace_users)[0]}:{}}render(){return(0,t.createElement)("div",{className:"ptc-completionist-automation-details-form"},(0,t.createElement)(u,{title:this.state.title,changeTitle:t=>this.handleAutomationChange("title",t),description:this.state.description,changeDescription:t=>this.handleAutomationChange("description",t)}),(0,t.createElement)(h,{hook_name:this.state.hook_name,changeEvent:t=>this.handleAutomationChange("hook_name",t)}),(0,t.createElement)(p,{event:this.state.hook_name,conditions:this.state.conditions,changeCondition:this.handleConditionChange,addCondition:this.handleAddCondition,removeCondition:this.handleRemoveCondition}),(0,t.createElement)(g,{event:this.state.hook_name,actions:this.state.actions,changeAction:this.handleActionChange,addAction:this.handleAddAction,removeAction:this.handleRemoveAction,changeActionMeta:this.handleActionMetaChange}),this.state.isSubmitting?(0,t.createElement)("button",{className:"save-automation",onClick:()=>this.saveAutomation(),disabled:"disabled"},(0,t.createElement)("i",{className:"fas fa-spinner fa-pulse"})," Saving..."):(0,t.createElement)("button",{className:"save-automation",onClick:()=>this.saveAutomation()},(0,t.createElement)("i",{className:"fas fa-save"})," ",this.state.saveButtonLabel))}}class u extends l{constructor(t){super(t)}render(){return(0,t.createElement)("div",{className:"automation-info"},(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"automation-title"},"Title"),(0,t.createElement)("input",{id:"automation-title",type:"text",value:this.props.title,onChange:t=>this.props.changeTitle(t.target.value)})),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"automation-description"},"Description"),(0,t.createElement)("textarea",{id:"automation-description",value:this.props.description,onChange:t=>this.props.changeDescription(t.target.value)})))}}class h extends l{constructor(t){var e;super(t),this.state={selected_hook_name:r(this.props.hook_name),custom_hook_name:(e=this.props.hook_name,!0===c(e)?e.replace(r(e),""):"")}}handleCustomHookNameChange(t){this.setState({custom_hook_name:t},(()=>{!0===c(this.state.selected_hook_name)?this.props.changeEvent(this.state.selected_hook_name+this.state.custom_hook_name):this.props.changeEvent(this.state.selected_hook_name)}))}handleEventChange(t){this.setState((e=>(this.props.changeEvent(t),{...e,selected_hook_name:t})))}render(){const e=m(window.ptc_completionist_automations.event_user_options),a=m(window.ptc_completionist_automations.event_post_options),o=m(window.ptc_completionist_automations.event_custom_options);let n=null;return!0===c(this.state.selected_hook_name)&&(n=(0,t.createElement)("input",{type:"text",value:this.state.custom_hook_name,placeholder:"custom_hook_name",onChange:t=>this.handleCustomHookNameChange(t.target.value)})),(0,t.createElement)("div",{className:"automation-event"},(0,t.createElement)("h2",null,(0,t.createElement)("span",{className:"automation-step-number"},"1"),"Trigger Event"),(0,t.createElement)("select",{value:r(this.state.selected_hook_name),onChange:t=>this.handleEventChange(t.target.value)},(0,t.createElement)("option",{value:""},"(Choose Event)"),(0,t.createElement)("optgroup",{label:"User Events"},e),(0,t.createElement)("optgroup",{label:"Post Events"},a),(0,t.createElement)("optgroup",{label:"Custom Events"},o)),n)}}class p extends l{constructor(t){super(t),this.loadPropertyOptions=this.loadPropertyOptions.bind(this),this.loadComparisonMethodOptions=this.loadComparisonMethodOptions.bind(this),this.loadConditionFieldsets=this.loadConditionFieldsets.bind(this)}loadPropertyOptions(){Object.keys(window.ptc_completionist_automations.event_user_options).includes(this.props.event)?this.propertyOptions=m(window.ptc_completionist_automations.field_user_options):Object.keys(window.ptc_completionist_automations.event_post_options).includes(this.props.event)?this.propertyOptions=m(window.ptc_completionist_automations.field_post_options):this.propertyOptions=(0,t.createElement)("option",null,"(Choose Event)")}loadComparisonMethodOptions(){this.comparisonMethodOptions=window.ptc_completionist_automations.field_comparison_methods.map((e=>(0,t.createElement)("option",{value:e,key:e},e)))}loadConditionFieldsets(){!0===c(this.props.event)?this.conditionFieldsets=(0,t.createElement)("p",{className:"ptc-error-not-supported"},"Custom events do not support conditions. Be careful when choosing a custom hook."):this.conditionFieldsets=this.props.conditions.map(((e,a)=>{let o=null;return"is empty"!==e.comparison_method&&"is filled"!==e.comparison_method&&(o=(0,t.createElement)("input",{type:"text",value:e.value,onChange:t=>this.props.changeCondition(a,"value",t.target.value),placeholder:"value"})),(0,t.createElement)("fieldset",{className:"automation-condition",key:e.ID},(0,t.createElement)("legend",null,"Condition"),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("select",{value:e.property,onChange:t=>this.props.changeCondition(a,"property",t.target.value)},this.propertyOptions),(0,t.createElement)("select",{value:e.comparison_method,onChange:t=>this.props.changeCondition(a,"comparison_method",t.target.value)},this.comparisonMethodOptions),o,(0,t.createElement)("button",{className:"remove-item",title:"Remove Condition",onClick:()=>this.props.removeCondition(a)},(0,t.createElement)("i",{className:"fas fa-minus"})," Delete")))}))}render(){let e,a=!0;return this.props.event.length?!0===c(this.props.event)?(e=(0,t.createElement)("p",{className:"ptc-message"},(0,t.createElement)("strong",null,"Custom events do not support conditions."),"Actions will always run for the specified custom event."),a=!1):(this.loadPropertyOptions(),this.loadComparisonMethodOptions(),this.loadConditionFieldsets(),e=(0,t.createElement)(t.Fragment,null,this.conditionFieldsets,(0,t.createElement)("button",{className:"add-item",onClick:this.props.addCondition},(0,t.createElement)("i",{className:"fas fa-plus"})," Add Condition"))):(e=(0,t.createElement)("p",{className:"ptc-message"},(0,t.createElement)("strong",null,"No trigger event selected."),"Conditions control if Actions should run after a trigger event."),a=!1),(0,t.createElement)("div",{className:"automation-conditions-list"},(0,t.createElement)("div",{className:"section-header"},(0,t.createElement)("h2",null,(0,t.createElement)("span",{className:"automation-step-number"},"2"),"Conditions"),!0===a&&this.conditionFieldsets.length>1&&(0,t.createElement)("p",{className:"ptc-message"},(0,t.createElement)("em",null,"All")," conditions must evaluate to true for the Actions to run.")),e)}}class g extends l{constructor(t){super(t),this.loadActionMetaInputs=this.loadActionMetaInputs.bind(this),this.loadActionFieldsets=this.loadActionFieldsets.bind(this)}loadActionMetaInputs(e,a){return"create_task"===e.action?(0,t.createElement)("div",{className:"action-meta_create_task"},(0,t.createElement)("input",{id:"ptc-new-task_name_"+a,type:"text",placeholder:"Write a task name...",value:e.meta.name,onChange:t=>this.props.changeActionMeta(a,"name",t.target.value)}),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"ptc-new-task_task_author_"+a},"Creator"),(0,t.createElement)("select",{id:"ptc-new-task_task_author_"+a,value:e.meta.task_author,onChange:t=>this.props.changeActionMeta(a,"task_author",t.target.value)},m(window.ptc_completionist_automations.connected_workspace_users))),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"ptc-new-task_assignee_"+a},"Assignee"),(0,t.createElement)("select",{id:"ptc-new-task_assignee_"+a,value:e.meta.assignee,onChange:t=>this.props.changeActionMeta(a,"assignee",t.target.value)},(0,t.createElement)("option",{value:""},"None (Unassigned)"),m(window.ptc_completionist_automations.workspace_users))),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"ptc-new-task_due_on_"+a},"Due Date"),(0,t.createElement)("input",{id:"ptc-new-task_due_on_"+a,type:"date",pattern:"\\d\\d\\d\\d-\\d\\d-\\d\\d",placeholder:"yyyy-mm-dd",value:e.meta.due_on,onChange:t=>this.props.changeActionMeta(a,"due_on",t.target.value)})),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"ptc-new-task_project_"+a},"Project"),(0,t.createElement)("select",{id:"ptc-new-task_project_"+a,value:e.meta.project,onChange:t=>this.props.changeActionMeta(a,"project",t.target.value)},(0,t.createElement)("option",{value:""},"None (Private Task)"),m(window.ptc_completionist_automations.workspace_projects))),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"ptc-new-task_notes_"+a},"Description"),(0,t.createElement)("textarea",{id:"ptc-new-task_notes_"+a,value:e.meta.notes,onChange:t=>this.props.changeActionMeta(a,"notes",t.target.value)})),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("label",{for:"ptc-new-task_post_id_"+a},"Pin to Post"),(0,t.createElement)(i,{id:"ptc-new-task_post_id_"+a,initialValue:e.meta.post_id,onSelectOption:t=>this.props.changeActionMeta(a,"post_id",t)}))):(0,t.createElement)("div",{className:"automation-meta-default"},(0,t.createElement)("p",null,(0,t.createElement)("em",null,"Choose an action to see additional options.")))}loadActionFieldsets(){let e=m(window.ptc_completionist_automations.action_options);this.actionFieldsets=this.props.actions.map(((a,o)=>(0,t.createElement)("fieldset",{className:"automation-action",key:a.ID},(0,t.createElement)("legend",null,"Action"),(0,t.createElement)("div",{className:"form-group"},(0,t.createElement)("select",{value:a.action,onChange:t=>this.props.changeAction(o,t.target.value)},e),(0,t.createElement)("div",null,this.props.actions.length>1&&(0,t.createElement)("button",{className:"remove-item",title:"Remove Action",onClick:()=>this.props.removeAction(o)},(0,t.createElement)("i",{className:"fas fa-minus"})," Delete"))),this.loadActionMetaInputs(a,o))))}render(){return this.loadActionFieldsets(),(0,t.createElement)("div",{className:"automation-actions-list"},(0,t.createElement)("div",{className:"section-header"},(0,t.createElement)("h2",null,(0,t.createElement)("span",{className:"automation-step-number"},"3"),"Actions"),this.props.actions.length<=1&&(0,t.createElement)("p",{className:"ptc-message"},"At least 1 Action is required.")),this.actionFieldsets,(0,t.createElement)("button",{className:"add-item",onClick:this.props.addAction},(0,t.createElement)("i",{className:"fas fa-plus"})," Add Action"))}}const{Component:_}=wp.element;class E extends _{constructor(t){super(t),this.state={automations:window.ptc_completionist_automations.automations,isLoading:!1},this.goToAutomation=this.goToAutomation.bind(this),this.deleteAutomation=this.deleteAutomation.bind(this)}goToAutomation(){let t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0;this.setState({isLoading:!0},(()=>{if("new"===t){let e=new URLSearchParams(location.search);e.set("automation",t),history.pushState({ID:"new"},"Completionist – Add New Automation","?"+e.toString()),document.title="Completionist – Add New Automation",this.setState({isLoading:!1})}else if(isNaN(parseInt(t))||t<=0){let t={action:"ptc_get_automation_overviews",nonce:window.ptc_completionist_automations.nonce};window.jQuery.post(window.ajaxurl,t,(t=>{if("success"==t.status&&"object"==typeof t.data){let e=new URLSearchParams(location.search);e.delete("automation"),history.pushState({ID:0},"Completionist – Automations","?"+e.toString()),document.title="Completionist – Automations",this.setState({automations:t.data,isLoading:!1})}else console.error(t),this.setState({isLoading:!1})}),"json").fail((()=>{console.error("Failed to load automation overviews.");let t=new URLSearchParams(location.search);t.delete("automation"),history.pushState({ID:0},"Completionist – Automations","?"+t.toString()),document.title="Completionist – Automations",this.setState({isLoading:!1})}))}else{let e={action:"ptc_get_automation",nonce:window.ptc_completionist_automations.nonce,ID:t};window.jQuery.post(window.ajaxurl,e,(e=>{if("success"==e.status&&"object"==typeof e.data){let a="Completionist – Automation "+e.data.ID+" – "+e.data.title,o=new URLSearchParams(location.search);o.set("automation",t),history.pushState(e.data,a,"?"+o.toString()),document.title=a,this.setState({isLoading:!1})}else console.error(e),this.goToAutomation()}),"json").fail((()=>{console.error("Failed to get data for automation "+t),this.goToAutomation()}))}}))}deleteAutomation(t,e){let a={action:"ptc_delete_automation",nonce:window.ptc_completionist_automations.nonce,ID:t};window.jQuery.post(window.ajaxurl,a,(t=>{t.status&&"success"==t.status&&t.code&&200==t.code&&t.data?(console.log(t.message),this.setState((e=>({automations:e.automations.filter((e=>e.ID!==t.data))})))):t.message&&t.code?alert("Error "+t.code+". The automation could not be deleted. "+t.message):alert("Error. The automation could not be deleted."),"function"==typeof e&&e(t)}),"json").fail((()=>{alert("Error 500. The automation could not be deleted."),"function"==typeof e&&e()}))}componentDidMount(){const t=new URLSearchParams(location.search).get("automation");null!==t&&this.goToAutomation(t),window.addEventListener("popstate",(t=>{"state"in t&&t.state&&"ID"in t.state?this.goToAutomation(t.state.ID):this.goToAutomation()}))}render(){if(this.state.isLoading)return(0,t.createElement)("div",{className:"loading-screen"},(0,t.createElement)("p",null,(0,t.createElement)("i",{className:"fas fa-spinner fa-pulse fa-lg"})," Loading..."));const e=new URLSearchParams(location.search).get("automation");return"new"===e?(0,t.createElement)("div",{className:"ptc-completionist-automation-create"},(0,t.createElement)("header",null,(0,t.createElement)("button",{onClick:()=>this.goToAutomation()},(0,t.createElement)("i",{className:"fas fa-angle-left"})," Back"),(0,t.createElement)("h1",null,"New Automation"),(0,t.createElement)("div",{className:"spacer"})),(0,t.createElement)(d,{goToAutomation:this.goToAutomation})):history.state&&"ID"in history.state&&history.state.ID==e?(0,t.createElement)("div",{className:"ptc-completionist-automation-details"},(0,t.createElement)("header",null,(0,t.createElement)("button",{onClick:()=>this.goToAutomation()},(0,t.createElement)("i",{className:"fas fa-angle-left"})," Back"),(0,t.createElement)("h1",null,"Edit Automation ",history.state.ID),(0,t.createElement)("div",{className:"spacer"})),(0,t.createElement)(d,{automation:history.state,goToAutomation:this.goToAutomation})):(0,t.createElement)(n,{automations:this.state.automations,goToAutomation:this.goToAutomation,deleteAutomation:this.deleteAutomation})}}const{render:v}=wp.element;document.addEventListener("DOMContentLoaded",(()=>{try{const e=document.getElementById("ptc-PTCCompletionistAutomations");null!==e&&v((0,t.createElement)(E,null),e)}catch(t){console.error(t)}}))}();