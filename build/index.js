!function(t){var e={};function n(o){if(e[o])return e[o].exports;var a=e[o]={i:o,l:!1,exports:{}};return t[o].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=t,n.c=e,n.d=function(t,e,o){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var a in t)n.d(o,a,function(e){return t[e]}.bind(null,a));return o},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=16)}([function(t,e){!function(){t.exports=this.wp.element}()},function(t,e){t.exports=function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}},function(t,e){function n(e){return t.exports=n=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)},n(e)}t.exports=n},function(t,e){t.exports=function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}},function(t,e){function n(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}t.exports=function(t,e,o){return e&&n(t.prototype,e),o&&n(t,o),t}},function(t,e,n){var o=n(11);t.exports=function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&o(t,e)}},function(t,e,n){var o=n(9),a=n(1);t.exports=function(t,e){return!e||"object"!==o(e)&&"function"!=typeof e?a(t):e}},function(t,e,n){var o=n(12),a=n(13),i=n(14),r=n(15);t.exports=function(t){return o(t)||a(t)||i(t)||r()}},function(t,e){t.exports=function(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}},function(t,e){function n(e){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?t.exports=n=function(t){return typeof t}:t.exports=n=function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},n(e)}t.exports=n},function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,o=new Array(e);n<e;n++)o[n]=t[n];return o}},function(t,e){function n(e,o){return t.exports=n=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t},n(e,o)}t.exports=n},function(t,e,n){var o=n(10);t.exports=function(t){if(Array.isArray(t))return o(t)}},function(t,e){t.exports=function(t){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}},function(t,e,n){var o=n(10);t.exports=function(t,e){if(t){if("string"==typeof t)return o(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?o(t,e):void 0}}},function(t,e){t.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}},function(t,e,n){"use strict";n.r(e);var o=n(3),a=n.n(o),i=n(4),r=n.n(i),c=n(1),s=n.n(c),u=n(5),l=n.n(u),p=n(6),d=n.n(p),m=n(2),f=n.n(m),h=n(0);function b(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=f()(t);if(e){var a=f()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return d()(this,n)}}var v=function(t){l()(n,t);var e=b(n);function n(t){var o;return a()(this,n),(o=e.call(this,t)).state=t.automation,o.goToAutomation=t.goToAutomation,o}return r()(n,[{key:"render",value:function(){var t=this;return Object(h.createElement)("div",{className:"ptc-completionist-automation-row"},Object(h.createElement)("h2",null,this.state.title),Object(h.createElement)("ul",null,Object(h.createElement)("li",null,"ID: ",this.state.ID),Object(h.createElement)("li",null,"Last Modified: ",this.state.last_modified),Object(h.createElement)("li",null,"Total Conditions: ",this.state.total_conditions),Object(h.createElement)("li",null,"Total Actions: ",this.state.total_actions),Object(h.createElement)("li",null,"Last Triggered: ",this.state.last_triggered),Object(h.createElement)("li",null,"Total Action Triggers: ",this.state.total_triggered)),Object(h.createElement)("button",{onClick:function(){return t.goToAutomation(t.state.ID)}},"Edit"),Object(h.createElement)("button",{onClick:function(){return console.log("Delete "+t.state.ID)}},"Delete"))}}]),n}(wp.element.Component);function g(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=f()(t);if(e){var a=f()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return d()(this,n)}}var y=function(t){l()(n,t);var e=g(n);function n(t){var o;return a()(this,n),(o=e.call(this,t)).state={automations:t.automations,orderBy:"title"},o.goToAutomation=t.goToAutomation,o}return r()(n,[{key:"render",value:function(){var t=this,e=this.state.automations.map((function(e){return Object(h.createElement)(v,{automation:e,goToAutomation:t.goToAutomation,key:e.ID})}));return Object(h.createElement)("div",{className:"ptc-completionist-automations-listing"},Object(h.createElement)("h1",null,"Automations"),Object(h.createElement)("button",{onClick:function(){return t.goToAutomation("new")}},"Add New"),Object(h.createElement)("div",{className:"ptc-completionist-automations-list"},e))}}]),n}(wp.element.Component),O=n(7),j=n.n(O),_=n(8),E=n.n(_),k=n(9),w=n.n(k);function C(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=f()(t);if(e){var a=f()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return d()(this,n)}}var A=function(t){l()(n,t);var e=C(n);function n(t){var o;return a()(this,n),(o=e.call(this,t)).state={isLoading:!1,currentRequest:{},textInputHasFocus:!1,options:[],currentValue:"",currentLabel:""},"initialValue"in t&&t.initialValue&&(o.state.currentValue=t.initialValue),"initialLabel"in t&&t.initialLabel&&(o.state.currentLabel=t.initialLabel),o.handleSearchChange=o.handleSearchChange.bind(s()(o)),o.handleOptionChange=o.handleOptionChange.bind(s()(o)),o.createSelectOptions=o.createSelectOptions.bind(s()(o)),o.handleSearchBlur=o.handleSearchBlur.bind(s()(o)),o}return r()(n,[{key:"handleSearchChange",value:function(t){var e=this;t.trim().length>=3?this.setState({isLoading:!0,currentValue:"",currentLabel:t,options:[]},(function(){var t={action:"ptc_get_post_options_by_title",nonce:window.ptc_completionist_automations.nonce,title:e.state.currentLabel},n=window.jQuery.post(window.ajaxurl,t,(function(t){console.log(t),e.setState({isLoading:!1,currentRequest:{},options:t.data})}),"json").fail((function(t,n){"abort"!=n&&(alert("Failed to search for posts by title."),e.setState({isLoading:!1,options:[]}))}));e.setState((function(t){return"object"===w()(t.currentRequest)&&"function"==typeof t.currentRequest.abort&&e.state.currentRequest.abort(),{currentRequest:n}}))})):this.setState({isLoading:!1,currentValue:"",currentLabel:t,options:[]})}},{key:"handleOptionChange",value:function(t,e){var n=this;this.setState((function(n){return{currentValue:t,currentLabel:e}}),(function(){n.props.onSelectOption(n.state.currentValue)}))}},{key:"handleSearchBlur",value:function(){this.setState((function(t){return{textInputHasFocus:!1,currentLabel:""===t.currentValue?"":t.currentLabel}}))}},{key:"createSelectOptions",value:function(){var t=this;return this.state.options.length<1?!0===this.state.isLoading?Object(h.createElement)("li",null,"Searching for posts..."):this.state.currentLabel.trim().length>=3?Object(h.createElement)("li",null,"No post results."):Object(h.createElement)("li",null,"Enter at least 3 characters to search..."):this.state.options.map((function(e){return Object(h.createElement)("li",{"data-value":e.ID,key:e.ID,onMouseDown:function(){return t.handleOptionChange(e.ID,e.post_title)}},e.post_title+" ["+e.ID+"]")}))}},{key:"componentDidMount",value:function(){console.log(this.state),""!==this.state.currentValue.trim()&&""===this.state.currentLabel.trim()&&console.error("Post title needed!")}},{key:"componentDidUpdate",value:function(t,e){this.state.currentValue!==e.currentValue&&this.props.onSelectOption(this.state.currentValue)}},{key:"render",value:function(){var t=this,e=null;if(!0===this.state.textInputHasFocus){var n=this.createSelectOptions(this.state.options);e=Object(h.createElement)("ul",{className:"select-options"},n)}return Object(h.createElement)("div",{className:"ptc-ajax-search-select-input"},Object(h.createElement)("input",{id:this.props.id,type:"text",value:this.state.currentLabel,onChange:function(e){return t.handleSearchChange(e.target.value)},onFocus:function(){return t.setState({textInputHasFocus:!0})},onBlur:function(){return t.handleSearchBlur()}}),Object(h.createElement)("input",{type:"hidden",value:this.state.currentValue}),e)}}]),n}(wp.element.Component);function S(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,o)}return n}function R(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?S(Object(n),!0).forEach((function(e){E()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):S(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function x(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=f()(t);if(e){var a=f()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return d()(this,n)}}var D=wp.element.Component,P=function(t){l()(n,t);var e=x(n);function n(t){var o;return a()(this,n),o=e.call(this,t),"automation"in t?(o.state=t.automation,o.state.saveButtonLabel="Create","ID"in t.automation&&t.automation.ID>0&&(o.state.saveButtonLabel="Update"),o.state.isSubmitting=!1):o.state={ID:0,title:"",description:"",hook_name:"",last_modified:"",conditions:[],actions:[],saveButtonLabel:"Create",isSubmitting:!1},o.handleAutomationChange=o.handleAutomationChange.bind(s()(o)),o.handleConditionChange=o.handleConditionChange.bind(s()(o)),o.handleAddCondition=o.handleAddCondition.bind(s()(o)),o.handleRemoveCondition=o.handleRemoveCondition.bind(s()(o)),o.handleActionChange=o.handleActionChange.bind(s()(o)),o.handleActionMetaChange=o.handleActionMetaChange.bind(s()(o)),o.handleAddAction=o.handleAddAction.bind(s()(o)),o.handleRemoveAction=o.handleRemoveAction.bind(s()(o)),o}return r()(n,[{key:"saveAutomation",value:function(){var t=this;this.state.isSubmitting||this.setState({isSubmitting:!0},(function(){var e={action:"ptc_save_automation",nonce:window.ptc_completionist_automations.nonce,automation:t.state};window.jQuery.post(window.ajaxurl,e,(function(e){console.log(e),t.setState({isSubmitting:!1})}),"json").fail((function(){t.setState({isSubmitting:!1})}))}))}},{key:"handleAutomationChange",value:function(t,e){this.setState((function(n){return E()({},t,e)}))}},{key:"handleConditionChange",value:function(t,e,n){this.setState((function(o){var a=j()(o.conditions);return a[t]=R(R({},o.conditions[t]),{},E()({},e,n)),{conditions:a}}))}},{key:"handleAddCondition",value:function(){this.setState((function(t){return{conditions:[].concat(j()(t.conditions),[{ID:0,property:"",comparison_method:window.ptc_completionist_automations.field_comparison_methods[0],value:""}])}}))}},{key:"handleRemoveCondition",value:function(t){this.setState((function(e){return{conditions:e.conditions.filter((function(e,n){return n!==t}))}}))}},{key:"handleActionChange",value:function(t,e){var n=this;this.setState((function(o){var a=j()(o.actions);return a[t]=R(R({},o.actions[t]),{},{action:e,meta:n.getDefaultActionMeta(e)}),{actions:a}}))}},{key:"handleActionMetaChange",value:function(t,e,n){this.setState((function(o){var a=j()(o.actions);return a[t]=R(R({},o.actions[t]),{},{meta:R(R({},o.actions[t].meta),{},E()({},e,n))}),{actions:a}}))}},{key:"handleAddAction",value:function(){var t=this;this.setState((function(e){return{actions:[].concat(j()(e.actions),[{ID:0,action:"create_task",triggered_count:0,last_triggered:"",meta:t.getDefaultActionMeta("create_task")}])}}))}},{key:"handleRemoveAction",value:function(t){this.setState((function(e){return{actions:e.actions.filter((function(e,n){return n!==t}))}}))}},{key:"getDefaultActionMeta",value:function(t){switch(t){case"create_task":return{task_author:window.ptc_completionist_automations.connected_workspace_users[0]};default:return{}}}},{key:"render",value:function(){var t=this;return Object(h.createElement)("div",{className:"ptc-completionist-automation-details-form"},Object(h.createElement)(M,{title:this.state.title,changeTitle:function(e){return t.handleAutomationChange("title",e)},description:this.state.description,changeDescription:function(e){return t.handleAutomationChange("description",e)}}),Object(h.createElement)(L,{hook_name:this.state.hook_name,changeEvent:function(e){return t.handleAutomationChange("hook_name",e)}}),Object(h.createElement)(T,{conditions:this.state.conditions,event:this.state.hook_name,changeCondition:this.handleConditionChange,addCondition:this.handleAddCondition,removeCondition:this.handleRemoveCondition}),Object(h.createElement)(I,{actions:this.state.actions,changeAction:this.handleActionChange,addAction:this.handleAddAction,removeAction:this.handleRemoveAction,changeActionMeta:this.handleActionMetaChange}),Object(h.createElement)("button",{onClick:function(){return t.saveAutomation()},disabled:this.state.isSubmitting},this.state.saveButtonLabel))}}]),n}(D),M=function(t){l()(n,t);var e=x(n);function n(t){return a()(this,n),e.call(this,t)}return r()(n,[{key:"render",value:function(){var t=this;return Object(h.createElement)("div",{className:"automation-info"},Object(h.createElement)("input",{type:"text",value:this.props.title,onChange:function(e){return t.props.changeTitle(e.target.value)}}),Object(h.createElement)("textarea",{value:this.props.description,onChange:function(e){return t.props.changeDescription(e.target.value)}}))}}]),n}(D),L=function(t){l()(n,t);var e=x(n);function n(t){return a()(this,n),e.call(this,t)}return r()(n,[{key:"createSelectOptions",value:function(t){return Object.keys(t).map((function(e){return Object(h.createElement)("option",{value:e,key:e},t[e])}))}},{key:"render",value:function(){var t=this,e=this.createSelectOptions(window.ptc_completionist_automations.event_user_options),n=this.createSelectOptions(window.ptc_completionist_automations.event_post_options);return Object(h.createElement)("div",{className:"automation-event"},Object(h.createElement)("h2",null,Object(h.createElement)("span",{className:"automation-step-number"},"1")," Trigger Event"),Object(h.createElement)("select",{value:this.props.hook_name,onChange:function(e){return t.props.changeEvent(e.target.value)}},Object(h.createElement)("option",{value:""},"(Choose Event)"),Object(h.createElement)("optgroup",{label:"User Events"},e),Object(h.createElement)("optgroup",{label:"Post Events"},n)))}}]),n}(D),T=function(t){l()(n,t);var e=x(n);function n(t){var o;return a()(this,n),(o=e.call(this,t)).loadPropertyOptions=o.loadPropertyOptions.bind(s()(o)),o.loadComparisonMethodOptions=o.loadComparisonMethodOptions.bind(s()(o)),o.loadConditionFieldsets=o.loadConditionFieldsets.bind(s()(o)),o}return r()(n,[{key:"createSelectOptions",value:function(t){return Object.keys(t).map((function(e){return Object(h.createElement)("option",{value:e,key:e},t[e])}))}},{key:"loadPropertyOptions",value:function(){Object.keys(window.ptc_completionist_automations.event_user_options).includes(this.props.event)?this.propertyOptions=this.createSelectOptions(window.ptc_completionist_automations.field_user_options):Object.keys(window.ptc_completionist_automations.event_post_options).includes(this.props.event)?this.propertyOptions=this.createSelectOptions(window.ptc_completionist_automations.field_post_options):this.propertyOptions=Object(h.createElement)("option",null,"(Choose Event)")}},{key:"loadComparisonMethodOptions",value:function(){this.comparisonMethodOptions=window.ptc_completionist_automations.field_comparison_methods.map((function(t){return Object(h.createElement)("option",{value:t,key:t},t)}))}},{key:"loadConditionFieldsets",value:function(){var t=this;this.conditionFieldsets=this.props.conditions.map((function(e,n){var o=null;return"is empty"!==e.comparison_method&&"is filled"!==e.comparison_method&&(o=Object(h.createElement)("input",{type:"text",value:e.value,key:n,onChange:function(e){return t.props.changeCondition(n,"value",e.target.value)}})),Object(h.createElement)("fieldset",{className:"automation-condition",key:n},Object(h.createElement)("legend",null,"Condition"),Object(h.createElement)("select",{value:e.property,key:n,onChange:function(e){return t.props.changeCondition(n,"property",e.target.value)}},t.propertyOptions),Object(h.createElement)("select",{value:e.comparison_method,key:n,onChange:function(e){return t.props.changeCondition(n,"comparison_method",e.target.value)}},t.comparisonMethodOptions),o,Object(h.createElement)("button",{onClick:function(){return t.props.removeCondition(n)}},"Remove"))}))}},{key:"render",value:function(){return this.loadPropertyOptions(),this.loadComparisonMethodOptions(),this.loadConditionFieldsets(),Object(h.createElement)("div",{className:"automation-conditions-list"},Object(h.createElement)("h2",null,Object(h.createElement)("span",{className:"automation-step-number"},"2")," Conditions"),this.conditionFieldsets,Object(h.createElement)("button",{onClick:this.props.addCondition},"Add Condition"))}}]),n}(D),I=function(t){l()(n,t);var e=x(n);function n(t){var o;return a()(this,n),(o=e.call(this,t)).loadActionMetaInputs=o.loadActionMetaInputs.bind(s()(o)),o.loadActionFieldsets=o.loadActionFieldsets.bind(s()(o)),o}return r()(n,[{key:"createSelectOptions",value:function(t){return Object.keys(t).map((function(e){return Object(h.createElement)("option",{value:e,key:e},t[e])}))}},{key:"loadActionMetaInputs",value:function(t,e){var n=this;switch(t.action){case"create_task":return Object(h.createElement)("div",{className:"action-meta_create_task"},Object(h.createElement)("input",{id:"ptc-new-task_name_"+e,type:"text",placeholder:"Write a task name...",value:t.meta.name,onChange:function(t){return n.props.changeActionMeta(e,"name",t.target.value)}}),Object(h.createElement)("div",{class:"form-group"},Object(h.createElement)("label",{for:"ptc-new-task_task_author_"+e},"Creator"),Object(h.createElement)("select",{id:"ptc-new-task_task_author_"+e,value:t.meta.task_author,onChange:function(t){return n.props.changeActionMeta(e,"task_author",t.target.value)}},this.createSelectOptions(window.ptc_completionist_automations.connected_workspace_users))),Object(h.createElement)("div",{class:"form-group"},Object(h.createElement)("label",{for:"ptc-new-task_assignee_"+e},"Assignee"),Object(h.createElement)("select",{id:"ptc-new-task_assignee_"+e,value:t.meta.assignee,onChange:function(t){return n.props.changeActionMeta(e,"assignee",t.target.value)}},Object(h.createElement)("option",{value:""},"None (Unassigned)"),this.createSelectOptions(window.ptc_completionist_automations.workspace_users))),Object(h.createElement)("div",{class:"form-group"},Object(h.createElement)("label",{for:"ptc-new-task_due_on_"+e},"Due Date"),Object(h.createElement)("input",{id:"ptc-new-task_due_on_"+e,type:"date",pattern:"\\d\\d\\d\\d-\\d\\d-\\d\\d",placeholder:"yyyy-mm-dd",value:t.meta.due_on,onChange:function(t){return n.props.changeActionMeta(e,"due_on",t.target.value)}})),Object(h.createElement)("div",{class:"form-group"},Object(h.createElement)("label",{for:"ptc-new-task_project_"+e},"Project"),Object(h.createElement)("select",{id:"ptc-new-task_project_"+e,value:t.meta.project,onChange:function(t){return n.props.changeActionMeta(e,"project",t.target.value)}},Object(h.createElement)("option",{value:""},"None (Private Task)"),this.createSelectOptions(window.ptc_completionist_automations.workspace_projects))),Object(h.createElement)("div",{class:"form-group"},Object(h.createElement)("label",{for:"ptc-new-task_notes_"+e},"Description"),Object(h.createElement)("textarea",{id:"ptc-new-task_notes_"+e,value:t.meta.notes,onChange:function(t){return n.props.changeActionMeta(e,"notes",t.target.value)}})),Object(h.createElement)("div",{class:"form-group"},Object(h.createElement)("label",{for:"ptc-new-task_post_id_"+e},"Pin"),Object(h.createElement)(A,{id:"ptc-new-task_post_id_"+e,initialValue:t.meta.post_id,onSelectOption:function(t){return n.props.changeActionMeta(e,"post_id",t)}})));default:return Object(h.createElement)("div",{className:"automation-meta-default"},Object(h.createElement)("p",null,Object(h.createElement)("em",null,"Choose an action to see additional options.")))}}},{key:"loadActionFieldsets",value:function(){var t=this,e=this.createSelectOptions(window.ptc_completionist_automations.action_options);this.actionFieldsets=this.props.actions.map((function(n,o){return Object(h.createElement)("fieldset",{className:"automation-action",key:o},Object(h.createElement)("legend",null,"Action"),Object(h.createElement)("select",{value:n.action,onChange:function(e){return t.props.changeAction(o,e.target.value)},key:o},e),t.loadActionMetaInputs(n,o),Object(h.createElement)("button",{onClick:function(){return t.props.removeAction(o)}},"Remove"))}))}},{key:"render",value:function(){return this.loadActionFieldsets(),Object(h.createElement)("div",{className:"automation-actions-list"},Object(h.createElement)("h2",null,Object(h.createElement)("span",{className:"automation-step-number"},"3")," Actions"),this.actionFieldsets,Object(h.createElement)("button",{onClick:this.props.addAction},"Add Action"))}}]),n}(D);function N(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=f()(t);if(e){var a=f()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return d()(this,n)}}jQuery((function(t){try{var e=document.getElementById("ptc-completionist-automations-root");if(null!==e){var n=wp.element,o=n.render,i=function(t){l()(n,t);var e=N(n);function n(t){var o;return a()(this,n),(o=e.call(this,t)).state={automations:window.ptc_completionist_automations.automations},o.goToAutomation=o.goToAutomation.bind(s()(o)),o}return r()(n,[{key:"goToAutomation",value:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0;if("new"===t){var e=new URLSearchParams(location.search);e.set("automation",t),history.pushState({},"Completionist &ndash; Add New Automation","?"+e.toString())}else if(t<=0){var n=new URLSearchParams(location.search);n.delete("automation"),history.pushState({},"Completionist &ndash; Automation "+t,"?"+n.toString())}else{var o=new URLSearchParams(location.search);o.set("automation",t),history.pushState({automationId:t},"Completionist &ndash; Automation "+t,"?"+o.toString())}this.forceUpdate()}},{key:"componentDidMount",value:function(){window.addEventListener("popstate",this.goToAutomation)}},{key:"render",value:function(){var t=this,e=new URLSearchParams(location.search).get("automation");if("new"===e)return Object(h.createElement)("div",{className:"ptc-completionist-automation-create"},Object(h.createElement)("h1",null,"New Automation"),Object(h.createElement)(P,null),Object(h.createElement)("button",{onClick:function(){return t.goToAutomation()}},"Back"));var n=this.state.automations.findIndex((function(t){return t.ID==e}));return n>-1?Object(h.createElement)("div",{className:"ptc-completionist-automation-details"},Object(h.createElement)("h1",null,"Viewing automation ",e),Object(h.createElement)(P,{automation:this.state.automations[n]}),Object(h.createElement)("button",{onClick:function(){return t.goToAutomation()}},"Back")):Object(h.createElement)(y,{automations:this.state.automations,goToAutomation:this.goToAutomation})}}]),n}(n.Component);o(Object(h.createElement)(i,null),e)}}catch(t){console.error(t)}}))}]);