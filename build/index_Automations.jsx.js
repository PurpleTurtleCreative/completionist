/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/components/AutomationDetailsForm.js":
/*!*************************************************!*\
  !*** ./src/components/AutomationDetailsForm.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "AutomationDetailsForm": function() { return /* binding */ AutomationDetailsForm; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _PostSearchSelectInput_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./PostSearchSelectInput.js */ "./src/components/PostSearchSelectInput.js");


const {
  Component
} = wp.element; // --- Helpers --- //

function isCustomHookName(hook_name) {
  return true === Object.keys(window.ptc_completionist_automations.event_custom_options).some(option => hook_name.startsWith(option));
}

function getCustomHookNameFromValue(option) {
  if (true === isCustomHookName(option)) {
    return option.replace(getHookOptionValueFromName(option), '');
  }

  return '';
}

function getHookOptionValueFromName(hook_name) {
  let actualHookName = Object.keys(window.ptc_completionist_automations.event_custom_options).find(option => hook_name.startsWith(option));
  return actualHookName !== null && actualHookName !== void 0 ? actualHookName : hook_name;
}

function createSelectOptions(optionsObj) {
  return Object.keys(optionsObj).map(key => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: key,
    key: key
  }, optionsObj[key]));
} // --- Components --- //


class AutomationDetailsForm extends Component {
  constructor(props) {
    /*
    Required Props:
    - (function) goToAutomation
    Optional Props:
    - (object) automation
    */
    super(props);

    if ('automation' in props) {
      this.state = props.automation;
      this.state.saveButtonLabel = 'Create';

      if ('ID' in props.automation && props.automation.ID > 0) {
        this.state.saveButtonLabel = 'Update';
      }

      this.state.isSubmitting = false;
    } else {
      this.state = {
        "ID": 0,
        "title": '',
        "description": '',
        "hook_name": '',
        "last_modified": '',
        "conditions": [],
        "actions": [],
        "saveButtonLabel": 'Create',
        "isSubmitting": false
      };
    }

    this.handleAutomationChange = this.handleAutomationChange.bind(this);
    this.handleConditionChange = this.handleConditionChange.bind(this);
    this.handleAddCondition = this.handleAddCondition.bind(this);
    this.handleRemoveCondition = this.handleRemoveCondition.bind(this);
    this.handleActionChange = this.handleActionChange.bind(this);
    this.handleActionMetaChange = this.handleActionMetaChange.bind(this);
    this.handleAddAction = this.handleAddAction.bind(this);
    this.handleRemoveAction = this.handleRemoveAction.bind(this);
  } //end constructor()

  /* HANDLERS */


  saveAutomation() {
    if (!this.state.isSubmitting) {
      // TODO: validate data for submission
      this.setState({
        "isSubmitting": true
      }, () => {
        let data = {
          "action": 'ptc_save_automation',
          "nonce": window.ptc_completionist_automations.nonce,
          "automation": this.state
        };
        window.jQuery.post(window.ajaxurl, data, res => {
          if (res.status && res.status == 'success' && res.code && res.data && typeof res.data == 'object' && 'ID' in res.data && res.data.ID && res.data.ID > 0) {
            if (res.code == 201) {
              //               console.log( res.message );
              this.props.goToAutomation(res.data.ID);
            } else if (res.code == 200) {
              // TODO: display success message in notice section
              //               console.log( res.message );
              this.setState({ ...res.data,
                "isSubmitting": false
              });
            }
          } else {
            // TODO: display error messages in notice section
            if (res.message && res.code) {
              alert('Error ' + res.code + '. The automation could not be saved. ' + res.message);
            } else {
              alert('Error 409. The automation could not be saved.');
            }

            this.setState({
              "isSubmitting": false
            });
          }
        }, 'json').fail(() => {
          alert('Error 500. The automation could not be saved.');
          this.setState({
            "isSubmitting": false
          });
        });
      });
    }
  } //end saveAutomation()

  /** Core Info **/


  handleAutomationChange(property_key, value) {
    this.setState(state => ({ ...state,
      [property_key]: value
    }));
  }
  /** Conditions **/


  handleConditionChange(condition_id, property_key, value) {
    this.setState(state => {
      const conditions = state.conditions.map(c => {
        if (c.ID === condition_id) {
          return { ...c,
            [property_key]: value
          };
        }

        return c;
      });
      return {
        "conditions": conditions
      };
    });
  }

  handleAddCondition() {
    this.setState(state => ({
      "conditions": [...state.conditions, {
        "ID": -Date.now(),
        "property": '',
        "comparison_method": window.ptc_completionist_automations.field_comparison_methods[0],
        "value": ''
      }]
    }));
  }

  handleRemoveCondition(condition_id) {
    this.setState(state => ({
      "conditions": state.conditions.filter(c => c.ID !== condition_id)
    }));
  }
  /** Actions **/


  handleActionChange(action_id, action) {
    this.setState(state => {
      const actions = state.actions.map(a => {
        if (a.ID === action_id) {
          return { ...a,
            "action": action,
            "meta": this.getDefaultActionMeta(action)
          };
        }

        return a;
      });
      return {
        "actions": actions
      };
    });
  }

  handleActionMetaChange(action_id, meta_key, meta_value) {
    this.setState(state => {
      const actions = state.actions.map(a => {
        if (a.ID === action_id) {
          return { ...a,
            "meta": { ...a.meta,
              [meta_key]: meta_value
            }
          };
        }

        return a;
      });
      return {
        "actions": actions
      };
    });
  }

  handleAddAction() {
    this.setState(state => ({
      "actions": [...state.actions, {
        "ID": -Date.now(),
        "action": 'create_task',
        "triggered_count": 0,
        "last_triggered": '',
        "meta": this.getDefaultActionMeta('create_task')
      }]
    }));
  }

  handleRemoveAction(action_id) {
    this.setState(state => ({
      "actions": state.actions.filter(a => a.ID !== action_id)
    }));
  }
  /* END HANDLERS */


  getDefaultActionMeta(action) {
    switch (action) {
      case 'create_task':
        return {
          "task_author": Object.keys(window.ptc_completionist_automations.connected_workspace_users)[0]
        };
        break;

      default:
        return {};
        break;
    }
  }

  render() {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "ptc-completionist-automation-details-form"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(AutomationInfoInputs, {
      title: this.state.title,
      changeTitle: value => this.handleAutomationChange('title', value),
      description: this.state.description,
      changeDescription: value => this.handleAutomationChange('description', value)
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(AutomationEventInput, {
      hook_name: this.state.hook_name,
      changeEvent: value => this.handleAutomationChange('hook_name', value)
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(AutomationConditionsInputs, {
      event: this.state.hook_name,
      conditions: this.state.conditions,
      changeCondition: this.handleConditionChange,
      addCondition: this.handleAddCondition,
      removeCondition: this.handleRemoveCondition
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(AutomationActionsInputs, {
      event: this.state.hook_name,
      actions: this.state.actions,
      changeAction: this.handleActionChange,
      addAction: this.handleAddAction,
      removeAction: this.handleRemoveAction,
      changeActionMeta: this.handleActionMetaChange
    }), this.state.isSubmitting ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "save-automation",
      onClick: () => this.saveAutomation(),
      disabled: "disabled"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-spinner fa-pulse"
    }), " Saving...") : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "save-automation",
      onClick: () => this.saveAutomation()
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-save"
    }), " ", this.state.saveButtonLabel));
  } //end render()


} //end class AutomationsListing

class AutomationInfoInputs extends Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-info"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "form-group"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
      for: "automation-title"
    }, "Title"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
      id: "automation-title",
      type: "text",
      value: this.props.title,
      onChange: e => this.props.changeTitle(e.target.value)
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "form-group"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
      for: "automation-description"
    }, "Description"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
      id: "automation-description",
      value: this.props.description,
      onChange: e => this.props.changeDescription(e.target.value)
    })));
  }

} //end class AutomationInfoInputs


class AutomationEventInput extends Component {
  constructor(props) {
    super(props);
    this.state = {
      "selected_hook_name": getHookOptionValueFromName(this.props.hook_name),
      "custom_hook_name": getCustomHookNameFromValue(this.props.hook_name)
    };
    this.updateEventHookName = this.updateEventHookName.bind(this);
    this.handleCustomHookNameChange = this.handleCustomHookNameChange.bind(this);
    this.handleEventChange = this.handleEventChange.bind(this);
  }

  updateEventHookName(state) {
    if (true === isCustomHookName(state.selected_hook_name)) {
      this.props.changeEvent(state.selected_hook_name + state.custom_hook_name);
    } else {
      this.props.changeEvent(state.selected_hook_name);
    }
  }

  handleCustomHookNameChange(value) {
    this.setState({
      "custom_hook_name": value
    }, () => {
      this.updateEventHookName(this.state);
    });
  }

  handleEventChange(value) {
    this.setState({
      "selected_hook_name": value
    }, () => {
      this.updateEventHookName(this.state);
    });
  }

  render() {
    const userEventOptions = createSelectOptions(window.ptc_completionist_automations.event_user_options);
    const postEventOptions = createSelectOptions(window.ptc_completionist_automations.event_post_options);
    const customEventOptions = createSelectOptions(window.ptc_completionist_automations.event_custom_options);
    let customHookNameInput = null;

    if (true === isCustomHookName(this.state.selected_hook_name)) {
      customHookNameInput = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
        type: "text",
        value: this.state.custom_hook_name,
        placeholder: "custom_hook_name",
        onChange: e => this.handleCustomHookNameChange(e.target.value)
      });
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-event"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "automation-step-number"
    }, "1"), "Trigger\xA0Event"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
      value: getHookOptionValueFromName(this.state.selected_hook_name),
      onChange: e => this.handleEventChange(e.target.value)
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: ""
    }, "(Choose Event)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
      label: "User Events"
    }, userEventOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
      label: "Post Events"
    }, postEventOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
      label: "Custom Events"
    }, customEventOptions)), customHookNameInput);
  }

} //end class AutomationEventInput


class AutomationConditionsInputs extends Component {
  constructor(props) {
    super(props);
    this.loadPropertyOptions = this.loadPropertyOptions.bind(this);
    this.loadComparisonMethodOptions = this.loadComparisonMethodOptions.bind(this);
    this.loadConditionFieldsets = this.loadConditionFieldsets.bind(this);
  } //end constructor()


  loadPropertyOptions() {
    if (Object.keys(window.ptc_completionist_automations.event_user_options).includes(this.props.event)) {
      this.propertyOptions = createSelectOptions(window.ptc_completionist_automations.field_user_options);
    } else if (Object.keys(window.ptc_completionist_automations.event_post_options).includes(this.props.event)) {
      this.propertyOptions = createSelectOptions(window.ptc_completionist_automations.field_post_options);
    } else {
      this.propertyOptions = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", null, "(Choose Event)");
    }
  } //end loadPropertyOptions()


  loadComparisonMethodOptions() {
    this.comparisonMethodOptions = window.ptc_completionist_automations.field_comparison_methods.map(value => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: value,
      key: value
    }, value));
  } //end loadComparisonMethodOptions()


  loadConditionFieldsets() {
    if (true === isCustomHookName(this.props.event)) {
      this.conditionFieldsets = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
        className: "ptc-error-not-supported"
      }, "Custom events do not support conditions. Be careful when choosing a custom hook.");
    } else {
      this.conditionFieldsets = this.props.conditions.map(condition => {
        let valueInput = null;

        if (condition.comparison_method !== 'is empty' && condition.comparison_method !== 'is filled') {
          valueInput = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
            type: "text",
            value: condition.value,
            onChange: e => this.props.changeCondition(condition.ID, 'value', e.target.value),
            placeholder: "value"
          });
        }

        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", {
          className: "automation-condition",
          key: condition.ID
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("legend", null, "Condition"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          value: condition.property,
          onChange: e => this.props.changeCondition(condition.ID, 'property', e.target.value)
        }, this.propertyOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          value: condition.comparison_method,
          onChange: e => this.props.changeCondition(condition.ID, 'comparison_method', e.target.value)
        }, this.comparisonMethodOptions), valueInput, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
          className: "remove-item",
          title: "Remove Condition",
          onClick: () => this.props.removeCondition(condition.ID)
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
          className: "fas fa-minus"
        }), " Delete")));
      });
    }
  } //end loadConditionFieldsets()


  render() {
    let allowAllConditionsMessage = true;
    let content;

    if (!this.props.event.length) {
      content = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
        className: "ptc-message"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "No trigger event selected."), "Conditions control if Actions should run after a trigger event.");
      allowAllConditionsMessage = false;
    } else if (true === isCustomHookName(this.props.event)) {
      content = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
        className: "ptc-message"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, "Custom events do not support conditions."), "Actions will always run for the specified custom event.");
      allowAllConditionsMessage = false;
    } else {
      this.loadPropertyOptions();
      this.loadComparisonMethodOptions();
      this.loadConditionFieldsets();
      content = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, this.conditionFieldsets, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
        className: "add-item",
        onClick: this.props.addCondition
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
        className: "fas fa-plus"
      }), " Add Condition"));
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-conditions-list"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "section-header"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "automation-step-number"
    }, "2"), "Conditions"), true === allowAllConditionsMessage && this.conditionFieldsets.length > 1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "ptc-message"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("em", null, "All"), " conditions must evaluate to true for the Actions to run.")), content);
  } //end render()


} //end class AutomationConditionsInputs


class AutomationActionsInputs extends Component {
  constructor(props) {
    super(props);
    this.getActionMetaFields = this.getActionMetaFields.bind(this);
    this.loadActionFieldsets = this.loadActionFieldsets.bind(this);
  }

  getActionMetaFields(action, index) {
    // TODO: Allow create_tasks to be dynamically pinned to created/updated/delete post if relevent: {post.ID}
    switch (action.action) {
      case 'create_task':
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "action-meta_create_task"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
          id: "ptc-new-task_name_" + index,
          type: "text",
          placeholder: "Write a task name...",
          value: action.meta.name,
          onChange: e => this.props.changeActionMeta(action.ID, 'name', e.target.value)
        }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_task_author_" + index
        }, "Creator"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          id: "ptc-new-task_task_author_" + index,
          value: action.meta.task_author,
          onChange: e => this.props.changeActionMeta(action.ID, 'task_author', e.target.value)
        }, createSelectOptions(window.ptc_completionist_automations.connected_workspace_users))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_assignee_" + index
        }, "Assignee"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          id: "ptc-new-task_assignee_" + index,
          value: action.meta.assignee,
          onChange: e => this.props.changeActionMeta(action.ID, 'assignee', e.target.value)
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          value: ""
        }, "None (Unassigned)"), createSelectOptions(window.ptc_completionist_automations.workspace_users))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_due_on_" + index
        }, "Due Date"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
          id: "ptc-new-task_due_on_" + index,
          type: "date",
          pattern: "\\d\\d\\d\\d-\\d\\d-\\d\\d",
          placeholder: "yyyy-mm-dd",
          value: action.meta.due_on,
          onChange: e => this.props.changeActionMeta(action.ID, 'due_on', e.target.value)
        })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_project_" + index
        }, "Project"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          id: "ptc-new-task_project_" + index,
          value: action.meta.project,
          onChange: e => this.props.changeActionMeta(action.ID, 'project', e.target.value)
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          value: ""
        }, "None (Private Task)"), createSelectOptions(window.ptc_completionist_automations.workspace_projects))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_notes_" + index
        }, "Description"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
          id: "ptc-new-task_notes_" + index,
          value: action.meta.notes,
          onChange: e => this.props.changeActionMeta(action.ID, 'notes', e.target.value)
        })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_post_id_" + index
        }, "Pin to Post"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_PostSearchSelectInput_js__WEBPACK_IMPORTED_MODULE_1__.PostSearchSelectInput, {
          id: "ptc-new-task_post_id_" + index,
          initialValue: action.meta.post_id,
          onSelectOption: value => this.props.changeActionMeta(action.ID, 'post_id', value)
        })));
        break;

      default:
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "automation-meta-default"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("em", null, "Choose an action to see additional options.")));
    }
  }

  loadActionFieldsets() {
    let actionOptions = createSelectOptions(window.ptc_completionist_automations.action_options);
    this.actionFieldsets = this.props.actions.map((action, index) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", {
      className: "automation-action",
      key: action.ID
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("legend", null, "Action"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "form-group"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
      value: action.action,
      onChange: e => this.props.changeAction(action.ID, e.target.value)
    }, actionOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, this.props.actions.length > 1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "remove-item",
      title: "Remove Action",
      onClick: () => this.props.removeAction(action.ID)
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-minus"
    }), " Delete"))), this.getActionMetaFields(action, index)));
  } //end loadActionFieldsets()


  render() {
    // TODO: Add list of available merge fields based on selected event, similar to Awesome Support email templates
    this.loadActionFieldsets();
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-actions-list"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "section-header"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "automation-step-number"
    }, "3"), "Actions"), this.props.actions.length <= 1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "ptc-message"
    }, "At least 1 Action is required.")), this.actionFieldsets, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "add-item",
      onClick: this.props.addAction
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-plus"
    }), " Add Action"));
  } //end render()


} //end class AutomationActionsInputs

/***/ }),

/***/ "./src/components/AutomationRow.js":
/*!*****************************************!*\
  !*** ./src/components/AutomationRow.js ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "AutomationRow": function() { return /* binding */ AutomationRow; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const {
  Component
} = wp.element;
class AutomationRow extends Component {
  constructor(props) {
    /*
    Required Props:
    - (object) automation
    - (function) goToAutomation
    - (function) deleteAutomation
    */
    super(props);
    this.state = { ...props.automation,
      isDeleting: false
    };
    this.goToAutomation = props.goToAutomation;
    this.deleteAutomation = this.deleteAutomation.bind(this);
  } //end constructor()


  deleteAutomation() {
    this.setState({
      isDeleting: true
    }, () => {
      this.props.deleteAutomation(this.state.ID, () => {
        this.setState({
          isDeleting: false
        });
      });
    });
  }

  render() {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "ptc-completionist-automation-row"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("header", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", {
      title: 'Automation ID: ' + this.state.ID,
      onClick: () => this.goToAutomation(this.state.ID)
    }, this.state.title), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "last-modified"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("em", null, "Updated ", this.state.last_modified)), this.state.description.length > 0 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
      className: "description",
      dangerouslySetInnerHTML: {
        __html: this.state.description
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-actions"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "edit",
      onClick: () => this.goToAutomation(this.state.ID)
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-pen"
    }), " Edit"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "delete",
      onClick: this.deleteAutomation,
      disabled: this.state.isDeleting
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-trash"
    }), " Delete"))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
      title: this.state.total_conditions + ' Conditions'
    }, this.state.total_conditions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
      title: this.state.total_actions + ' Actions'
    }, this.state.total_actions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
      title: 'Triggered ' + this.state.total_triggered + ' times'
    }, this.state.total_triggered), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
      title: 'Last Triggered ' + this.state.last_triggered
    }, this.state.last_triggered)));
  } //end render()


} //end class AutomationsListing

/***/ }),

/***/ "./src/components/AutomationsListing.js":
/*!**********************************************!*\
  !*** ./src/components/AutomationsListing.js ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "AutomationsListing": function() { return /* binding */ AutomationsListing; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _AutomationRow_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AutomationRow.js */ "./src/components/AutomationRow.js");


const {
  Component
} = wp.element;
class AutomationsListing extends Component {
  constructor(props) {
    /*
    Required Props:
    - (object[]) automations
    - (function) goToAutomation
    - (function) deleteAutomation
    */
    super(props);
    this.state = {
      automations: props.automations,
      orderBy: 'title' //'title' or 'ID' or 'last_modified' or 'last_triggered' or 'triggered_count'

    };
    this.goToAutomation = props.goToAutomation;
  } //end constructor()


  sortAutomationsListing() {
    let orderBy = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'title';
  } //end sortAutomationsListing()


  componentDidUpdate(prevProps) {
    if (this.props.automations !== prevProps.automations) {
      this.setState({
        automations: this.props.automations
      });
    }
  }

  render() {
    // TODO: Add sorting functionality
    // TODO: Add default/empty state if no automations exist

    /* List Automations... */
    const automationRows = this.state.automations.map(automation => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_AutomationRow_js__WEBPACK_IMPORTED_MODULE_1__.AutomationRow, {
      key: automation.ID,
      automation: automation,
      goToAutomation: this.goToAutomation,
      deleteAutomation: this.props.deleteAutomation
    }));
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "ptc-completionist-automations-listing"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "title"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Automations"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "actions"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      onClick: () => this.goToAutomation('new')
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-plus"
    }), " Add New"))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("header", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, "Automation"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-question"
    }), " Conditions"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-running"
    }), " Actions"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-bolt"
    }), " Triggers"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-history"
    }), " Last Triggered")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "ptc-completionist-automations-list"
    }, automationRows));
  }

} //end class AutomationsListing

/***/ }),

/***/ "./src/components/PTCCompletionistAutomations.js":
/*!*******************************************************!*\
  !*** ./src/components/PTCCompletionistAutomations.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ PTCCompletionistAutomations; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _AutomationsListing_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AutomationsListing.js */ "./src/components/AutomationsListing.js");
/* harmony import */ var _AutomationDetailsForm_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AutomationDetailsForm.js */ "./src/components/AutomationDetailsForm.js");



const {
  Component
} = wp.element;
class PTCCompletionistAutomations extends Component {
  constructor(props) {
    super(props);
    this.state = {
      automations: window.ptc_completionist_automations.automations,
      isLoading: false
    };
    this.goToAutomation = this.goToAutomation.bind(this);
    this.deleteAutomation = this.deleteAutomation.bind(this);
  } //end constructor()


  goToAutomation() {
    let automationId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
    this.setState({
      isLoading: true
    }, () => {
      if (automationId === 'new') {
        let queryParams = new URLSearchParams(location.search);
        queryParams.set('automation', automationId);
        history.pushState({
          ID: 'new'
        }, 'Completionist – Add New Automation', '?' + queryParams.toString());
        document.title = 'Completionist – Add New Automation';
        this.setState({
          isLoading: false
        });
      } else if (isNaN(parseInt(automationId)) || automationId <= 0) {
        let data = {
          'action': 'ptc_get_automation_overviews',
          'nonce': window.ptc_completionist_automations.nonce
        };
        window.jQuery.post(window.ajaxurl, data, res => {
          if (res.status == 'success' && typeof res.data == 'object') {
            let queryParams = new URLSearchParams(location.search);
            queryParams.delete('automation');
            history.pushState({
              ID: 0
            }, 'Completionist – Automations', '?' + queryParams.toString());
            document.title = 'Completionist – Automations';
            this.setState({
              automations: res.data,
              isLoading: false
            });
          } else {
            // TODO: Fix infinite requests when error
            console.error(res);
            this.setState({
              isLoading: false
            });
          }
        }, 'json').fail(() => {
          console.error('Failed to load automation overviews.');
          let queryParams = new URLSearchParams(location.search);
          queryParams.delete('automation');
          history.pushState({
            ID: 0
          }, 'Completionist – Automations', '?' + queryParams.toString());
          document.title = 'Completionist – Automations';
          this.setState({
            isLoading: false
          });
        });
      } else {
        let data = {
          'action': 'ptc_get_automation',
          'nonce': window.ptc_completionist_automations.nonce,
          'ID': automationId
        };
        window.jQuery.post(window.ajaxurl, data, res => {
          if (res.status == 'success' && typeof res.data == 'object') {
            let docTitle = 'Completionist – Automation ' + res.data.ID + ' – ' + res.data.title;
            let queryParams = new URLSearchParams(location.search);
            queryParams.set('automation', automationId);
            history.pushState(res.data, docTitle, '?' + queryParams.toString());
            document.title = docTitle;
            this.setState({
              isLoading: false
            });
          } else {
            console.error(res);
            this.goToAutomation();
          }
        }, 'json').fail(() => {
          console.error('Failed to get data for automation ' + automationId);
          this.goToAutomation();
        });
      }
    });
  }

  //end goToAutomation()
  deleteAutomation(automationId, callback) {
    let data = {
      'action': 'ptc_delete_automation',
      'nonce': window.ptc_completionist_automations.nonce,
      'ID': automationId
    };
    window.jQuery.post(window.ajaxurl, data, res => {
      if (res.status && res.status == 'success' && res.code && res.code == 200 && res.data) {
        // TODO: display success message in notice section
        console.log(res.message);
        this.setState(state => ({
          automations: state.automations.filter(automation => automation.ID !== res.data)
        }));
      } else {
        // TODO: display error messages in notice section
        if (res.message && res.code) {
          alert('Error ' + res.code + '. The automation could not be deleted. ' + res.message);
        } else {
          alert('Error. The automation could not be deleted.');
        }
      }

      typeof callback === 'function' && callback(res);
    }, 'json').fail(() => {
      // TODO: display error messages in notice section
      alert('Error 500. The automation could not be deleted.');
      typeof callback === 'function' && callback();
    });
  } //end deleteAutomation()


  componentDidMount() {
    /* Go to requested automation */
    let queryParams = new URLSearchParams(location.search);
    const automationParam = queryParams.get('automation');

    if (automationParam !== null) {
      this.goToAutomation(automationParam);
    }
    /* Listen to browser history events */


    window.addEventListener('popstate', e => {
      // TODO: goToAutomation calls pushState which breaks history navigation
      if ('state' in e && e.state && 'ID' in e.state) {
        this.goToAutomation(e.state.ID);
      } else {
        this.goToAutomation();
      }
    });
  } //end componentDidMount()


  render() {
    if (this.state.isLoading) {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "loading-screen"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
        className: "fas fa-spinner fa-pulse fa-lg"
      }), " Loading..."));
    }

    let queryParams = new URLSearchParams(location.search);
    const automationParam = queryParams.get('automation');

    if (automationParam === 'new') {
      /* Add Automation... */
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "ptc-completionist-automation-create"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("header", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
        onClick: () => this.goToAutomation()
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
        className: "fas fa-angle-left"
      }), " Back"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "New Automation"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "spacer"
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_AutomationDetailsForm_js__WEBPACK_IMPORTED_MODULE_2__.AutomationDetailsForm, {
        goToAutomation: this.goToAutomation
      }));
    }

    if (history.state && 'ID' in history.state && history.state.ID == automationParam) {
      /* Edit Automation... */
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "ptc-completionist-automation-details"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("header", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
        onClick: () => this.goToAutomation()
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
        className: "fas fa-angle-left"
      }), " Back"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "Edit Automation ", history.state.ID), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "spacer"
      })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_AutomationDetailsForm_js__WEBPACK_IMPORTED_MODULE_2__.AutomationDetailsForm, {
        automation: history.state,
        goToAutomation: this.goToAutomation
      }));
    } else {
      /* List Automations... */
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_AutomationsListing_js__WEBPACK_IMPORTED_MODULE_1__.AutomationsListing, {
        automations: this.state.automations,
        goToAutomation: this.goToAutomation,
        deleteAutomation: this.deleteAutomation
      });
    }
  } //end render()


} //end class PTCCompletionist_Automations

/***/ }),

/***/ "./src/components/PostSearchSelectInput.js":
/*!*************************************************!*\
  !*** ./src/components/PostSearchSelectInput.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "PostSearchSelectInput": function() { return /* binding */ PostSearchSelectInput; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const {
  Component
} = wp.element;
class PostSearchSelectInput extends Component {
  constructor(props) {
    /*
    Required Props:
    - (function) onSelectOption(value)
    Optional Props:
    - (string) initialValue
    - (string) initialLabel
    */
    super(props);
    this.state = {
      isLoading: false,
      currentRequest: {},
      textInputHasFocus: false,
      options: [],
      currentValue: '',
      currentLabel: ''
    };

    if ('initialValue' in props && props.initialValue) {
      this.state.currentValue = props.initialValue;
    }

    if ('initialLabel' in props && props.initialLabel) {
      this.state.currentLabel = props.initialLabel;
    }

    this.handleSearchChange = this.handleSearchChange.bind(this);
    this.handleOptionChange = this.handleOptionChange.bind(this);
    this.createSelectOptions = this.createSelectOptions.bind(this);
    this.handleSearchBlur = this.handleSearchBlur.bind(this);
  } //end constructor()


  handleSearchChange(input) {
    if (input.trim().length >= 3) {
      this.setState({
        isLoading: true,
        currentValue: '',
        currentLabel: input,
        options: []
      }, () => {
        let data = {
          'action': 'ptc_get_post_options_by_title',
          'nonce': window.ptc_completionist_automations.nonce,
          'title': this.state.currentLabel
        };
        let post_search_request = window.jQuery.post(window.ajaxurl, data, res => {
          // TODO: Look at using WP REST API: https://developer.wordpress.org/rest-api/reference/search-results/
          this.setState({
            isLoading: false,
            currentRequest: {},
            options: res.data
          }); // TODO: handle error responses
          // if(res.status == 'success' && res.data != '') {
          //   remove_task_row(data.task_gid);
          //   remove_task_gid(data.task_gid, false);
          // } else if(res.status == 'error' && res.data != '') {
          //   display_alert_html(res.data);
          //   disable_element(thisButton, false);
          //   buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
          // } else {
          //   alert('[Completionist] Error '+res.code+': '+res.message);
          //   disable_element(thisButton, false);
          //   buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
          // }
        }, 'json').fail((jqXHR, exception) => {
          if (exception != 'abort') {
            alert('Failed to search for posts by title.');
            this.setState({
              isLoading: false,
              options: []
            });
          }
        });
        this.setState(state => {
          if (typeof state.currentRequest === 'object' && typeof state.currentRequest.abort === 'function') {
            this.state.currentRequest.abort();
          }

          return {
            currentRequest: post_search_request
          };
        });
      });
    } else {
      this.setState({
        isLoading: false,
        currentValue: '',
        currentLabel: input,
        options: []
      });
    }
  } //end handleSearchChange()


  handleOptionChange(value, label) {
    this.setState(state => ({
      currentValue: value,
      currentLabel: label
    }), () => {
      this.props.onSelectOption(this.state.currentValue);
    });
  } //end handleOptionChange()


  handleSearchBlur() {
    this.setState(state => ({
      textInputHasFocus: false,
      currentLabel: state.currentValue === '' ? '' : state.currentLabel
    }));
  } //end handleSearchBlur()


  createSelectOptions() {
    if (this.state.options.length < 1) {
      if (this.state.isLoading === true) {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
          className: "fas fa-spinner fa-pulse"
        }), " Searching for posts...");
      } else if (this.state.currentLabel.trim().length >= 3) {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, "No post results.");
      } else {
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", null, "Enter at least 3 characters to search...");
      }
    }

    return this.state.options.map(post => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("li", {
      className: "post-option",
      "data-value": post.ID,
      key: post.ID,
      onMouseDown: () => this.handleOptionChange(post.ID, post.post_title)
    }, post.post_title + ' [' + post.ID + ']'));
  } //end createSelectOptions()


  componentDidMount() {
    if (this.state.currentValue.trim() !== '' && this.state.currentLabel.trim() === '') {
      this.setState({
        currentLabel: '(Loading...)'
      }, () => {
        let data = {
          'action': 'ptc_get_post_title_by_id',
          'nonce': window.ptc_completionist_automations.nonce,
          'post_id': this.state.currentValue
        };
        window.jQuery.post(window.ajaxurl, data, res => {
          if (res.status == 'success' && res.data != '') {
            this.setState({
              currentLabel: res.data
            });
          } else {
            console.error('Failed to load initial PostSearchSelectInput label for initial value.');
            console.error(res);
            this.setState({
              currentLabel: '(Error: Failed to load post title)'
            });
          }
        }, 'json').fail(() => {
          console.error('Failed to load initial PostSearchSelectInput label for initial value.');
          this.setState({
            currentLabel: '(Error: Failed to load post title)'
          });
        });
      });
    }
  } //end componentDidMount()


  componentDidUpdate(prevProps, prevState) {
    if (this.state.currentValue !== prevState.currentValue) {
      this.props.onSelectOption(this.state.currentValue);
    }
  } //end componentDidUpdate()


  render() {
    let selectList = null;

    if (this.state.textInputHasFocus === true) {
      const selectOptions = this.createSelectOptions(this.state.options);
      selectList = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("ul", {
        className: "select-options"
      }, selectOptions);
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "ptc-ajax-search-select-input"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
      id: this.props.id,
      type: "text",
      value: this.state.currentLabel,
      onChange: e => this.handleSearchChange(e.target.value),
      onFocus: () => this.setState({
        textInputHasFocus: true
      }),
      onBlur: () => this.handleSearchBlur()
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
      type: "hidden",
      value: this.state.currentValue
    }), selectList);
  } //end render()


} //end class PostSearchSelectInput

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!***********************************!*\
  !*** ./src/index_Automations.jsx ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_PTCCompletionistAutomations_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/PTCCompletionistAutomations.js */ "./src/components/PTCCompletionistAutomations.js");


const {
  render
} = wp.element;
document.addEventListener('DOMContentLoaded', () => {
  try {
    const rootNode = document.getElementById('ptc-PTCCompletionistAutomations');

    if (rootNode !== null) {
      render((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_PTCCompletionistAutomations_js__WEBPACK_IMPORTED_MODULE_1__["default"], null), rootNode);
    } //end if rootNode

  } catch (e) {
    console.error(e);
  }
});
}();
/******/ })()
;
//# sourceMappingURL=index_Automations.jsx.js.map