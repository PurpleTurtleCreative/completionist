!function(t){var e={};function n(o){if(e[o])return e[o].exports;var a=e[o]={i:o,l:!1,exports:{}};return t[o].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=t,n.c=e,n.d=function(t,e,o){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:o})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var a in t)n.d(o,a,function(e){return t[e]}.bind(null,a));return o},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="",n(n.s=16)}([function(t,e){!function(){t.exports=this.wp.element}()},function(t,e){t.exports=function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}},function(t,e){function n(e){return t.exports=n=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)},n(e)}t.exports=n},function(t,e){t.exports=function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}},function(t,e){function n(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}t.exports=function(t,e,o){return e&&n(t.prototype,e),o&&n(t,o),t}},function(t,e,n){var o=n(11);t.exports=function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&o(t,e)}},function(t,e,n){var o=n(7),a=n(1);t.exports=function(t,e){return!e||"object"!==o(e)&&"function"!=typeof e?a(t):e}},function(t,e){function n(e){return"function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?t.exports=n=function(t){return typeof t}:t.exports=n=function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},n(e)}t.exports=n},function(t,e){t.exports=function(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}},function(t,e,n){var o=n(12),a=n(13),i=n(14),r=n(15);t.exports=function(t){return o(t)||a(t)||i(t)||r()}},function(t,e){t.exports=function(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,o=new Array(e);n<e;n++)o[n]=t[n];return o}},function(t,e){function n(e,o){return t.exports=n=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t},n(e,o)}t.exports=n},function(t,e,n){var o=n(10);t.exports=function(t){if(Array.isArray(t))return o(t)}},function(t,e){t.exports=function(t){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}},function(t,e,n){var o=n(10);t.exports=function(t,e){if(t){if("string"==typeof t)return o(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?o(t,e):void 0}}},function(t,e){t.exports=function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}},function(t,e,n){"use strict";n.r(e);var o=n(0),a=n(7),i=n.n(a),r=n(3),c=n.n(r),s=n(4),u=n.n(s),l=n(1),p=n.n(l),m=n(5),d=n.n(m),f=n(6),h=n.n(f),b=n(2),v=n.n(b),g=n(8),O=n.n(g);function y(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,o)}return n}function j(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?y(Object(n),!0).forEach((function(e){O()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):y(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function _(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=v()(t);if(e){var a=v()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return h()(this,n)}}var E=function(t){d()(n,t);var e=_(n);function n(t){var o;return c()(this,n),(o=e.call(this,t)).state=j(j({},t.automation),{},{isDeleting:!1}),o.goToAutomation=t.goToAutomation,o.deleteAutomation=o.deleteAutomation.bind(p()(o)),o}return u()(n,[{key:"deleteAutomation",value:function(){var t=this;this.setState({isDeleting:!0},(function(){t.props.deleteAutomation(t.state.ID,(function(){t.setState({isDeleting:!1})}))}))}},{key:"render",value:function(){var t=this;return Object(o.createElement)("div",{className:"ptc-completionist-automation-row"},Object(o.createElement)("header",null,Object(o.createElement)("h2",{title:"Automation ID: "+this.state.ID,onClick:function(){return t.goToAutomation(t.state.ID)}},this.state.title),Object(o.createElement)("p",{className:"last-modified"},Object(o.createElement)("em",null,"Updated ",this.state.last_modified)),this.state.description.length>0&&Object(o.createElement)("p",{className:"description",dangerouslySetInnerHTML:{__html:this.state.description}}),Object(o.createElement)("div",{className:"automation-actions"},Object(o.createElement)("button",{className:"edit",onClick:function(){return t.goToAutomation(t.state.ID)}},Object(o.createElement)("i",{className:"fas fa-pen"})," Edit"),Object(o.createElement)("button",{className:"delete",onClick:this.deleteAutomation,disabled:this.state.isDeleting},Object(o.createElement)("i",{className:"fas fa-trash"})," Delete"))),Object(o.createElement)("ul",null,Object(o.createElement)("li",{title:this.state.total_conditions+" Conditions"},this.state.total_conditions),Object(o.createElement)("li",{title:this.state.total_actions+" Actions"},this.state.total_actions),Object(o.createElement)("li",{title:"Triggered "+this.state.total_triggered+" times"},this.state.total_triggered),Object(o.createElement)("li",{title:"Last Triggered "+this.state.last_triggered},this.state.last_triggered)))}}]),n}(wp.element.Component);function w(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=v()(t);if(e){var a=v()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return h()(this,n)}}var A=function(t){d()(n,t);var e=w(n);function n(t){var o;return c()(this,n),(o=e.call(this,t)).state={automations:t.automations,orderBy:"title"},o.goToAutomation=t.goToAutomation,o}return u()(n,[{key:"sortAutomationsListing",value:function(){}},{key:"componentDidUpdate",value:function(t){this.props.automations!==t.automations&&this.setState({automations:this.props.automations})}},{key:"render",value:function(){var t=this,e=this.state.automations.map((function(e){return Object(o.createElement)(E,{key:e.ID,automation:e,goToAutomation:t.goToAutomation,deleteAutomation:t.props.deleteAutomation})}));return Object(o.createElement)("div",{className:"ptc-completionist-automations-listing"},Object(o.createElement)("div",{className:"title"},Object(o.createElement)("h1",null,"Automations"),Object(o.createElement)("div",{className:"actions"},Object(o.createElement)("button",{onClick:function(){return t.goToAutomation("new")}},Object(o.createElement)("i",{className:"fas fa-plus"})," Add New"))),Object(o.createElement)("header",null,Object(o.createElement)("div",null,"Automation"),Object(o.createElement)("div",null,Object(o.createElement)("i",{className:"fas fa-question"})," Conditions"),Object(o.createElement)("div",null,Object(o.createElement)("i",{className:"fas fa-running"})," Actions"),Object(o.createElement)("div",null,Object(o.createElement)("i",{className:"fas fa-bolt"})," Triggers"),Object(o.createElement)("div",null,Object(o.createElement)("i",{className:"fas fa-history"})," Last Triggered")),Object(o.createElement)("div",{className:"ptc-completionist-automations-list"},e))}}]),n}(wp.element.Component),k=n(9),C=n.n(k);function S(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=v()(t);if(e){var a=v()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return h()(this,n)}}var D=function(t){d()(n,t);var e=S(n);function n(t){var o;return c()(this,n),(o=e.call(this,t)).state={isLoading:!1,currentRequest:{},textInputHasFocus:!1,options:[],currentValue:"",currentLabel:""},"initialValue"in t&&t.initialValue&&(o.state.currentValue=t.initialValue),"initialLabel"in t&&t.initialLabel&&(o.state.currentLabel=t.initialLabel),o.handleSearchChange=o.handleSearchChange.bind(p()(o)),o.handleOptionChange=o.handleOptionChange.bind(p()(o)),o.createSelectOptions=o.createSelectOptions.bind(p()(o)),o.handleSearchBlur=o.handleSearchBlur.bind(p()(o)),o}return u()(n,[{key:"handleSearchChange",value:function(t){var e=this;t.trim().length>=3?this.setState({isLoading:!0,currentValue:"",currentLabel:t,options:[]},(function(){var t={action:"ptc_get_post_options_by_title",nonce:window.ptc_completionist_automations.nonce,title:e.state.currentLabel},n=window.jQuery.post(window.ajaxurl,t,(function(t){console.log(t),e.setState({isLoading:!1,currentRequest:{},options:t.data})}),"json").fail((function(t,n){"abort"!=n&&(alert("Failed to search for posts by title."),e.setState({isLoading:!1,options:[]}))}));e.setState((function(t){return"object"===i()(t.currentRequest)&&"function"==typeof t.currentRequest.abort&&e.state.currentRequest.abort(),{currentRequest:n}}))})):this.setState({isLoading:!1,currentValue:"",currentLabel:t,options:[]})}},{key:"handleOptionChange",value:function(t,e){var n=this;this.setState((function(n){return{currentValue:t,currentLabel:e}}),(function(){n.props.onSelectOption(n.state.currentValue)}))}},{key:"handleSearchBlur",value:function(){this.setState((function(t){return{textInputHasFocus:!1,currentLabel:""===t.currentValue?"":t.currentLabel}}))}},{key:"createSelectOptions",value:function(){var t=this;return this.state.options.length<1?!0===this.state.isLoading?Object(o.createElement)("li",null,"Searching for posts..."):this.state.currentLabel.trim().length>=3?Object(o.createElement)("li",null,"No post results."):Object(o.createElement)("li",null,"Enter at least 3 characters to search..."):this.state.options.map((function(e){return Object(o.createElement)("li",{"data-value":e.ID,key:e.ID,onMouseDown:function(){return t.handleOptionChange(e.ID,e.post_title)}},e.post_title+" ["+e.ID+"]")}))}},{key:"componentDidMount",value:function(){""!==this.state.currentValue.trim()&&""===this.state.currentLabel.trim()&&console.error("Post title needed!")}},{key:"componentDidUpdate",value:function(t,e){this.state.currentValue!==e.currentValue&&this.props.onSelectOption(this.state.currentValue)}},{key:"render",value:function(){var t=this,e=null;if(!0===this.state.textInputHasFocus){var n=this.createSelectOptions(this.state.options);e=Object(o.createElement)("ul",{className:"select-options"},n)}return Object(o.createElement)("div",{className:"ptc-ajax-search-select-input"},Object(o.createElement)("input",{id:this.props.id,type:"text",value:this.state.currentLabel,onChange:function(e){return t.handleSearchChange(e.target.value)},onFocus:function(){return t.setState({textInputHasFocus:!0})},onBlur:function(){return t.handleSearchBlur()}}),Object(o.createElement)("input",{type:"hidden",value:this.state.currentValue}),e)}}]),n}(wp.element.Component);function R(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(t);e&&(o=o.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,o)}return n}function x(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?R(Object(n),!0).forEach((function(e){O()(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):R(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}function P(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=v()(t);if(e){var a=v()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return h()(this,n)}}var T=wp.element.Component,I=function(t){d()(n,t);var e=P(n);function n(t){var o;return c()(this,n),o=e.call(this,t),"automation"in t?(o.state=t.automation,o.state.saveButtonLabel="Create","ID"in t.automation&&t.automation.ID>0&&(o.state.saveButtonLabel="Update"),o.state.isSubmitting=!1):o.state={ID:0,title:"",description:"",hook_name:"",last_modified:"",conditions:[],actions:[],saveButtonLabel:"Create",isSubmitting:!1},o.handleAutomationChange=o.handleAutomationChange.bind(p()(o)),o.handleConditionChange=o.handleConditionChange.bind(p()(o)),o.handleAddCondition=o.handleAddCondition.bind(p()(o)),o.handleRemoveCondition=o.handleRemoveCondition.bind(p()(o)),o.handleActionChange=o.handleActionChange.bind(p()(o)),o.handleActionMetaChange=o.handleActionMetaChange.bind(p()(o)),o.handleAddAction=o.handleAddAction.bind(p()(o)),o.handleRemoveAction=o.handleRemoveAction.bind(p()(o)),o}return u()(n,[{key:"saveAutomation",value:function(){var t=this;this.state.isSubmitting||this.setState({isSubmitting:!0},(function(){var e={action:"ptc_save_automation",nonce:window.ptc_completionist_automations.nonce,automation:t.state};window.jQuery.post(window.ajaxurl,e,(function(e){e.status&&"success"==e.status&&e.code&&e.data&&"object"==i()(e.data)&&"ID"in e.data&&e.data.ID&&e.data.ID>0?201==e.code?(console.log(e.message),t.props.goToAutomation(e.data.ID)):200==e.code&&(console.log(e.message),t.setState(x(x({},e.data),{},{isSubmitting:!1}))):(e.message&&e.code?alert("Error "+e.code+". The automation could not be saved. "+e.message):alert("Error 409. The automation could not be saved."),t.setState({isSubmitting:!1}))}),"json").fail((function(){alert("Error 500. The automation could not be saved."),t.setState({isSubmitting:!1})}))}))}},{key:"handleAutomationChange",value:function(t,e){this.setState((function(n){return O()({},t,e)}))}},{key:"handleConditionChange",value:function(t,e,n){this.setState((function(o){var a=C()(o.conditions);return a[t]=x(x({},o.conditions[t]),{},O()({},e,n)),{conditions:a}}))}},{key:"handleAddCondition",value:function(){this.setState((function(t){return{conditions:[].concat(C()(t.conditions),[{ID:0,property:"",comparison_method:window.ptc_completionist_automations.field_comparison_methods[0],value:""}])}}))}},{key:"handleRemoveCondition",value:function(t){this.setState((function(e){return{conditions:e.conditions.filter((function(e,n){return n!==t}))}}))}},{key:"handleActionChange",value:function(t,e){var n=this;this.setState((function(o){var a=C()(o.actions);return a[t]=x(x({},o.actions[t]),{},{action:e,meta:n.getDefaultActionMeta(e)}),{actions:a}}))}},{key:"handleActionMetaChange",value:function(t,e,n){this.setState((function(o){var a=C()(o.actions);return a[t]=x(x({},o.actions[t]),{},{meta:x(x({},o.actions[t].meta),{},O()({},e,n))}),{actions:a}}))}},{key:"handleAddAction",value:function(){var t=this;this.setState((function(e){return{actions:[].concat(C()(e.actions),[{ID:0,action:"create_task",triggered_count:0,last_triggered:"",meta:t.getDefaultActionMeta("create_task")}])}}))}},{key:"handleRemoveAction",value:function(t){this.setState((function(e){return{actions:e.actions.filter((function(e,n){return n!==t}))}}))}},{key:"getDefaultActionMeta",value:function(t){switch(t){case"create_task":return{task_author:Object.keys(window.ptc_completionist_automations.connected_workspace_users)[0]};default:return{}}}},{key:"render",value:function(){var t=this;return Object(o.createElement)("div",{className:"ptc-completionist-automation-details-form"},Object(o.createElement)(L,{title:this.state.title,changeTitle:function(e){return t.handleAutomationChange("title",e)},description:this.state.description,changeDescription:function(e){return t.handleAutomationChange("description",e)}}),Object(o.createElement)(N,{hook_name:this.state.hook_name,changeEvent:function(e){return t.handleAutomationChange("hook_name",e)}}),Object(o.createElement)(M,{event:this.state.hook_name,conditions:this.state.conditions,changeCondition:this.handleConditionChange,addCondition:this.handleAddCondition,removeCondition:this.handleRemoveCondition}),Object(o.createElement)(F,{event:this.state.hook_name,actions:this.state.actions,changeAction:this.handleActionChange,addAction:this.handleAddAction,removeAction:this.handleRemoveAction,changeActionMeta:this.handleActionMetaChange}),Object(o.createElement)("button",{onClick:function(){return t.saveAutomation()},disabled:this.state.isSubmitting},this.state.saveButtonLabel))}}]),n}(T),L=function(t){d()(n,t);var e=P(n);function n(t){return c()(this,n),e.call(this,t)}return u()(n,[{key:"render",value:function(){var t=this;return Object(o.createElement)("div",{className:"automation-info"},Object(o.createElement)("input",{type:"text",value:this.props.title,onChange:function(e){return t.props.changeTitle(e.target.value)}}),Object(o.createElement)("textarea",{value:this.props.description,onChange:function(e){return t.props.changeDescription(e.target.value)}}))}}]),n}(T),N=function(t){d()(n,t);var e=P(n);function n(t){return c()(this,n),e.call(this,t)}return u()(n,[{key:"createSelectOptions",value:function(t){return Object.keys(t).map((function(e){return Object(o.createElement)("option",{value:e,key:e},t[e])}))}},{key:"render",value:function(){var t=this,e=this.createSelectOptions(window.ptc_completionist_automations.event_user_options),n=this.createSelectOptions(window.ptc_completionist_automations.event_post_options);return Object(o.createElement)("div",{className:"automation-event"},Object(o.createElement)("h2",null,Object(o.createElement)("span",{className:"automation-step-number"},"1")," Trigger Event"),Object(o.createElement)("select",{value:this.props.hook_name,onChange:function(e){return t.props.changeEvent(e.target.value)}},Object(o.createElement)("option",{value:""},"(Choose Event)"),Object(o.createElement)("optgroup",{label:"User Events"},e),Object(o.createElement)("optgroup",{label:"Post Events"},n)))}}]),n}(T),M=function(t){d()(n,t);var e=P(n);function n(t){var o;return c()(this,n),(o=e.call(this,t)).loadPropertyOptions=o.loadPropertyOptions.bind(p()(o)),o.loadComparisonMethodOptions=o.loadComparisonMethodOptions.bind(p()(o)),o.loadConditionFieldsets=o.loadConditionFieldsets.bind(p()(o)),o}return u()(n,[{key:"createSelectOptions",value:function(t){return Object.keys(t).map((function(e){return Object(o.createElement)("option",{value:e,key:e},t[e])}))}},{key:"loadPropertyOptions",value:function(){Object.keys(window.ptc_completionist_automations.event_user_options).includes(this.props.event)?this.propertyOptions=this.createSelectOptions(window.ptc_completionist_automations.field_user_options):Object.keys(window.ptc_completionist_automations.event_post_options).includes(this.props.event)?this.propertyOptions=this.createSelectOptions(window.ptc_completionist_automations.field_post_options):this.propertyOptions=Object(o.createElement)("option",null,"(Choose Event)")}},{key:"loadComparisonMethodOptions",value:function(){this.comparisonMethodOptions=window.ptc_completionist_automations.field_comparison_methods.map((function(t){return Object(o.createElement)("option",{value:t,key:t},t)}))}},{key:"loadConditionFieldsets",value:function(){var t=this;this.conditionFieldsets=this.props.conditions.map((function(e,n){var a=null;return"is empty"!==e.comparison_method&&"is filled"!==e.comparison_method&&(a=Object(o.createElement)("input",{type:"text",value:e.value,key:n,onChange:function(e){return t.props.changeCondition(n,"value",e.target.value)}})),Object(o.createElement)("fieldset",{className:"automation-condition",key:n},Object(o.createElement)("legend",null,"Condition"),Object(o.createElement)("select",{value:e.property,key:n,onChange:function(e){return t.props.changeCondition(n,"property",e.target.value)}},t.propertyOptions),Object(o.createElement)("select",{value:e.comparison_method,key:n,onChange:function(e){return t.props.changeCondition(n,"comparison_method",e.target.value)}},t.comparisonMethodOptions),a,Object(o.createElement)("button",{onClick:function(){return t.props.removeCondition(n)}},"Remove"))}))}},{key:"render",value:function(){return this.loadPropertyOptions(),this.loadComparisonMethodOptions(),this.loadConditionFieldsets(),Object(o.createElement)("div",{className:"automation-conditions-list"},Object(o.createElement)("h2",null,Object(o.createElement)("span",{className:"automation-step-number"},"2")," Conditions"),this.conditionFieldsets,Object(o.createElement)("button",{onClick:this.props.addCondition},"Add Condition"))}}]),n}(T),F=function(t){d()(n,t);var e=P(n);function n(t){var o;return c()(this,n),(o=e.call(this,t)).loadActionMetaInputs=o.loadActionMetaInputs.bind(p()(o)),o.loadActionFieldsets=o.loadActionFieldsets.bind(p()(o)),o}return u()(n,[{key:"createSelectOptions",value:function(t){return Object.keys(t).map((function(e){return Object(o.createElement)("option",{value:e,key:e},t[e])}))}},{key:"loadActionMetaInputs",value:function(t,e){var n=this;switch(t.action){case"create_task":return Object(o.createElement)("div",{className:"action-meta_create_task"},Object(o.createElement)("input",{id:"ptc-new-task_name_"+e,type:"text",placeholder:"Write a task name...",value:t.meta.name,onChange:function(t){return n.props.changeActionMeta(e,"name",t.target.value)}}),Object(o.createElement)("div",{class:"form-group"},Object(o.createElement)("label",{for:"ptc-new-task_task_author_"+e},"Creator"),Object(o.createElement)("select",{id:"ptc-new-task_task_author_"+e,value:t.meta.task_author,onChange:function(t){return n.props.changeActionMeta(e,"task_author",t.target.value)}},this.createSelectOptions(window.ptc_completionist_automations.connected_workspace_users))),Object(o.createElement)("div",{class:"form-group"},Object(o.createElement)("label",{for:"ptc-new-task_assignee_"+e},"Assignee"),Object(o.createElement)("select",{id:"ptc-new-task_assignee_"+e,value:t.meta.assignee,onChange:function(t){return n.props.changeActionMeta(e,"assignee",t.target.value)}},Object(o.createElement)("option",{value:""},"None (Unassigned)"),this.createSelectOptions(window.ptc_completionist_automations.workspace_users))),Object(o.createElement)("div",{class:"form-group"},Object(o.createElement)("label",{for:"ptc-new-task_due_on_"+e},"Due Date"),Object(o.createElement)("input",{id:"ptc-new-task_due_on_"+e,type:"date",pattern:"\\d\\d\\d\\d-\\d\\d-\\d\\d",placeholder:"yyyy-mm-dd",value:t.meta.due_on,onChange:function(t){return n.props.changeActionMeta(e,"due_on",t.target.value)}})),Object(o.createElement)("div",{class:"form-group"},Object(o.createElement)("label",{for:"ptc-new-task_project_"+e},"Project"),Object(o.createElement)("select",{id:"ptc-new-task_project_"+e,value:t.meta.project,onChange:function(t){return n.props.changeActionMeta(e,"project",t.target.value)}},Object(o.createElement)("option",{value:""},"None (Private Task)"),this.createSelectOptions(window.ptc_completionist_automations.workspace_projects))),Object(o.createElement)("div",{class:"form-group"},Object(o.createElement)("label",{for:"ptc-new-task_notes_"+e},"Description"),Object(o.createElement)("textarea",{id:"ptc-new-task_notes_"+e,value:t.meta.notes,onChange:function(t){return n.props.changeActionMeta(e,"notes",t.target.value)}})),Object(o.createElement)("div",{class:"form-group"},Object(o.createElement)("label",{for:"ptc-new-task_post_id_"+e},"Pin"),Object(o.createElement)(D,{id:"ptc-new-task_post_id_"+e,initialValue:t.meta.post_id,onSelectOption:function(t){return n.props.changeActionMeta(e,"post_id",t)}})));default:return Object(o.createElement)("div",{className:"automation-meta-default"},Object(o.createElement)("p",null,Object(o.createElement)("em",null,"Choose an action to see additional options.")))}}},{key:"loadActionFieldsets",value:function(){var t=this,e=this.createSelectOptions(window.ptc_completionist_automations.action_options);this.actionFieldsets=this.props.actions.map((function(n,a){return Object(o.createElement)("fieldset",{className:"automation-action",key:a},Object(o.createElement)("legend",null,"Action"),Object(o.createElement)("select",{value:n.action,onChange:function(e){return t.props.changeAction(a,e.target.value)},key:a},e),t.loadActionMetaInputs(n,a),Object(o.createElement)("button",{onClick:function(){return t.props.removeAction(a)}},"Remove"))}))}},{key:"render",value:function(){return this.loadActionFieldsets(),Object(o.createElement)("div",{className:"automation-actions-list"},Object(o.createElement)("h2",null,Object(o.createElement)("span",{className:"automation-step-number"},"3")," Actions"),this.actionFieldsets,Object(o.createElement)("button",{onClick:this.props.addAction},"Add Action"))}}]),n}(T);function V(t){var e=function(){if("undefined"==typeof Reflect||!Reflect.construct)return!1;if(Reflect.construct.sham)return!1;if("function"==typeof Proxy)return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],(function(){}))),!0}catch(t){return!1}}();return function(){var n,o=v()(t);if(e){var a=v()(this).constructor;n=Reflect.construct(o,arguments,a)}else n=o.apply(this,arguments);return h()(this,n)}}var B=function(t){d()(n,t);var e=V(n);function n(t){var o;return c()(this,n),(o=e.call(this,t)).state={automations:window.ptc_completionist_automations.automations,isLoading:!1},o.goToAutomation=o.goToAutomation.bind(p()(o)),o.deleteAutomation=o.deleteAutomation.bind(p()(o)),o}return u()(n,[{key:"goToAutomation",value:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:0;this.setState({isLoading:!0},(function(){if("new"===e){var n=new URLSearchParams(location.search);n.set("automation",e),history.pushState({ID:"new"},"Completionist – Add New Automation","?"+n.toString()),document.title="Completionist – Add New Automation",t.setState({isLoading:!1})}else if(isNaN(parseInt(e))||e<=0){var o={action:"ptc_get_automation_overviews",nonce:window.ptc_completionist_automations.nonce};window.jQuery.post(window.ajaxurl,o,(function(e){if("success"==e.status&&"object"==i()(e.data)){var n=new URLSearchParams(location.search);n.delete("automation"),history.pushState({ID:0},"Completionist – Automations","?"+n.toString()),document.title="Completionist – Automations",t.setState({automations:e.data,isLoading:!1})}else console.error(e),t.goToAutomation()}),"json").fail((function(){console.error("Failed to load automation overviews.");var e=new URLSearchParams(location.search);e.delete("automation"),history.pushState({ID:0},"Completionist – Automations","?"+e.toString()),document.title="Completionist – Automations",t.setState({isLoading:!1})}))}else{var a={action:"ptc_get_automation",nonce:window.ptc_completionist_automations.nonce,ID:e};window.jQuery.post(window.ajaxurl,a,(function(n){if("success"==n.status&&"object"==i()(n.data)){var o="Completionist – Automation "+n.data.ID+" – "+n.data.title,a=new URLSearchParams(location.search);a.set("automation",e),history.pushState(n.data,o,"?"+a.toString()),document.title=o,t.setState({isLoading:!1})}else console.error(n),t.goToAutomation()}),"json").fail((function(){console.error("Failed to get data for automation "+e),t.goToAutomation()}))}}))}},{key:"deleteAutomation",value:function(t,e){var n=this,o={action:"ptc_delete_automation",nonce:window.ptc_completionist_automations.nonce,ID:t};window.jQuery.post(window.ajaxurl,o,(function(t){t.status&&"success"==t.status&&t.code&&200==t.code&&t.data?(console.log(t.message),n.setState((function(e){return{automations:e.automations.filter((function(e){return e.ID!==t.data}))}}))):t.message&&t.code?alert("Error "+t.code+". The automation could not be deleted. "+t.message):alert("Error. The automation could not be deleted."),"function"==typeof e&&e(t)}),"json").fail((function(){alert("Error 500. The automation could not be deleted."),"function"==typeof e&&e()}))}},{key:"componentDidMount",value:function(){var t=this,e=new URLSearchParams(location.search).get("automation");null!==e&&this.goToAutomation(e),window.addEventListener("popstate",(function(e){"state"in e&&e.state&&"ID"in e.state?t.goToAutomation(e.state.ID):t.goToAutomation()}))}},{key:"render",value:function(){var t=this;if(this.state.isLoading)return Object(o.createElement)("p",null,"Loading...");var e=new URLSearchParams(location.search).get("automation");return"new"===e?Object(o.createElement)("div",{className:"ptc-completionist-automation-create"},Object(o.createElement)("h1",null,"New Automation"),Object(o.createElement)(I,{goToAutomation:this.goToAutomation}),Object(o.createElement)("button",{onClick:function(){return t.goToAutomation()}},"Back")):history.state&&"ID"in history.state&&history.state.ID==e?Object(o.createElement)("div",{className:"ptc-completionist-automation-details"},Object(o.createElement)("h1",null,"Viewing automation ",e),Object(o.createElement)(I,{automation:history.state,goToAutomation:this.goToAutomation}),Object(o.createElement)("button",{onClick:function(){return t.goToAutomation()}},"Back")):Object(o.createElement)(A,{automations:this.state.automations,goToAutomation:this.goToAutomation,deleteAutomation:this.deleteAutomation})}}]),n}(wp.element.Component),U=wp.element,q=U.render;U.Component;jQuery((function(t){try{var e=document.getElementById("ptc-completionist-automations-root");null!==e&&q(Object(o.createElement)(B,null),e)}catch(t){console.error(t)}}))}]);