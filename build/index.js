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
} = wp.element;
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
        ID: 0,
        title: '',
        description: '',
        hook_name: '',
        last_modified: '',
        conditions: [],
        actions: [],
        saveButtonLabel: 'Create',
        isSubmitting: false
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
        isSubmitting: true
      }, () => {
        let data = {
          'action': 'ptc_save_automation',
          'nonce': window.ptc_completionist_automations.nonce,
          'automation': this.state
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
                isSubmitting: false
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
              isSubmitting: false
            });
          }
        }, 'json').fail(() => {
          alert('Error 500. The automation could not be saved.');
          this.setState({
            isSubmitting: false
          });
        });
      });
    }
  } //end saveAutomation()

  /** Core Info **/


  handleAutomationChange(property_key, value) {
    this.setState(state => ({
      [property_key]: value
    }));
  }
  /** Conditions **/


  handleConditionChange(index, property_key, value) {
    this.setState(state => {
      let conditions = [...state.conditions];
      conditions[index] = { ...state.conditions[index],
        [property_key]: value
      };
      return {
        conditions: conditions
      };
    });
  }

  handleAddCondition() {
    this.setState(state => ({
      conditions: [...state.conditions, {
        ID: 0,
        property: '',
        comparison_method: window.ptc_completionist_automations.field_comparison_methods[0],
        value: ''
      }]
    }));
  }

  handleRemoveCondition(index) {
    this.setState(state => ({
      conditions: state.conditions.filter((_, i) => i !== index)
    }));
  }
  /** Actions **/


  handleActionChange(index, action) {
    this.setState(state => {
      let actions = [...state.actions];
      actions[index] = { ...state.actions[index],
        action: action,
        meta: this.getDefaultActionMeta(action)
      };
      return {
        actions: actions
      };
    });
  }

  handleActionMetaChange(index, meta_key, meta_value) {
    this.setState(state => {
      let actions = [...state.actions];
      actions[index] = { ...state.actions[index],
        meta: { ...state.actions[index].meta,
          [meta_key]: meta_value
        }
      };
      return {
        actions: actions
      };
    });
  }

  handleAddAction() {
    this.setState(state => ({
      actions: [...state.actions, {
        ID: 0,
        action: 'create_task',
        triggered_count: 0,
        last_triggered: '',
        meta: this.getDefaultActionMeta('create_task')
      }]
    }));
  }

  handleRemoveAction(index) {
    this.setState(state => ({
      actions: state.actions.filter((_, i) => i !== index)
    }));
  }
  /* END HANDLERS */


  getDefaultActionMeta(action) {
    switch (action) {
      case 'create_task':
        return {
          task_author: Object.keys(window.ptc_completionist_automations.connected_workspace_users)[0]
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
  }

  createSelectOptions(optionsObj) {
    return Object.keys(optionsObj).map(key => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: key,
      key: key
    }, optionsObj[key]));
  } //end createSelectOptions()


  render() {
    const userEventOptions = this.createSelectOptions(window.ptc_completionist_automations.event_user_options);
    const postEventOptions = this.createSelectOptions(window.ptc_completionist_automations.event_post_options);
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-event"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "automation-step-number"
    }, "1"), " Trigger Event"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
      value: this.props.hook_name,
      onChange: e => this.props.changeEvent(e.target.value)
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: ""
    }, "(Choose Event)"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
      label: "User Events"
    }, userEventOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("optgroup", {
      label: "Post Events"
    }, postEventOptions)));
  }

} //end class AutomationEventInput


class AutomationConditionsInputs extends Component {
  constructor(props) {
    super(props);
    this.loadPropertyOptions = this.loadPropertyOptions.bind(this);
    this.loadComparisonMethodOptions = this.loadComparisonMethodOptions.bind(this);
    this.loadConditionFieldsets = this.loadConditionFieldsets.bind(this);
  } //end constructor()


  createSelectOptions(optionsObj) {
    return Object.keys(optionsObj).map(key => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: key,
      key: key
    }, optionsObj[key]));
  } //end createSelectOptions()


  loadPropertyOptions() {
    if (Object.keys(window.ptc_completionist_automations.event_user_options).includes(this.props.event)) {
      this.propertyOptions = this.createSelectOptions(window.ptc_completionist_automations.field_user_options);
    } else if (Object.keys(window.ptc_completionist_automations.event_post_options).includes(this.props.event)) {
      this.propertyOptions = this.createSelectOptions(window.ptc_completionist_automations.field_post_options);
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
    this.conditionFieldsets = this.props.conditions.map((condition, index) => {
      let valueInput = null;

      if (condition.comparison_method !== 'is empty' && condition.comparison_method !== 'is filled') {
        valueInput = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
          type: "text",
          value: condition.value,
          key: index,
          onChange: e => this.props.changeCondition(index, 'value', e.target.value)
        });
      }

      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", {
        className: "automation-condition",
        key: index
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("legend", null, "Condition"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
        className: "form-group"
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        value: condition.property,
        key: index,
        onChange: e => this.props.changeCondition(index, 'property', e.target.value)
      }, this.propertyOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
        value: condition.comparison_method,
        key: index,
        onChange: e => this.props.changeCondition(index, 'comparison_method', e.target.value)
      }, this.comparisonMethodOptions), valueInput, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
        className: "remove-item",
        title: "Remove Condition",
        onClick: () => this.props.removeCondition(index)
      }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
        className: "fas fa-minus"
      }), " Delete")));
    });
  } //end loadConditionFieldsets()


  render() {
    // TODO: Do not show add button until an event is set
    this.loadPropertyOptions();
    this.loadComparisonMethodOptions();
    this.loadConditionFieldsets();
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-conditions-list"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "automation-step-number"
    }, "2"), " Conditions"), this.conditionFieldsets, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "add-item",
      onClick: this.props.addCondition
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-plus"
    }), " Add Condition"));
  } //end render()


} //end class AutomationConditionsInputs


class AutomationActionsInputs extends Component {
  constructor(props) {
    super(props);
    this.loadActionMetaInputs = this.loadActionMetaInputs.bind(this);
    this.loadActionFieldsets = this.loadActionFieldsets.bind(this);
  }

  createSelectOptions(optionsObj) {
    return Object.keys(optionsObj).map(key => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: key,
      key: key
    }, optionsObj[key]));
  } //end createSelectOptions()


  loadActionMetaInputs(action, index) {
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
          onChange: e => this.props.changeActionMeta(index, 'name', e.target.value)
        }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_task_author_" + index
        }, "Creator"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          id: "ptc-new-task_task_author_" + index,
          value: action.meta.task_author,
          onChange: e => this.props.changeActionMeta(index, 'task_author', e.target.value)
        }, this.createSelectOptions(window.ptc_completionist_automations.connected_workspace_users))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_assignee_" + index
        }, "Assignee"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          id: "ptc-new-task_assignee_" + index,
          value: action.meta.assignee,
          onChange: e => this.props.changeActionMeta(index, 'assignee', e.target.value)
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          value: ""
        }, "None (Unassigned)"), this.createSelectOptions(window.ptc_completionist_automations.workspace_users))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_due_on_" + index
        }, "Due Date"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
          id: "ptc-new-task_due_on_" + index,
          type: "date",
          pattern: "\\d\\d\\d\\d-\\d\\d-\\d\\d",
          placeholder: "yyyy-mm-dd",
          value: action.meta.due_on,
          onChange: e => this.props.changeActionMeta(index, 'due_on', e.target.value)
        })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_project_" + index
        }, "Project"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
          id: "ptc-new-task_project_" + index,
          value: action.meta.project,
          onChange: e => this.props.changeActionMeta(index, 'project', e.target.value)
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
          value: ""
        }, "None (Private Task)"), this.createSelectOptions(window.ptc_completionist_automations.workspace_projects))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_notes_" + index
        }, "Description"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
          id: "ptc-new-task_notes_" + index,
          value: action.meta.notes,
          onChange: e => this.props.changeActionMeta(index, 'notes', e.target.value)
        })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "form-group"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
          for: "ptc-new-task_post_id_" + index
        }, "Pin to Post"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_PostSearchSelectInput_js__WEBPACK_IMPORTED_MODULE_1__.PostSearchSelectInput, {
          id: "ptc-new-task_post_id_" + index,
          initialValue: action.meta.post_id,
          onSelectOption: value => this.props.changeActionMeta(index, 'post_id', value)
        })));
        break;

      default:
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
          className: "automation-meta-default"
        }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("em", null, "Choose an action to see additional options.")));
    }
  }

  loadActionFieldsets() {
    let actionOptions = this.createSelectOptions(window.ptc_completionist_automations.action_options);
    this.actionFieldsets = this.props.actions.map((action, index) => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("fieldset", {
      className: "automation-action",
      key: index
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("legend", null, "Action"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "form-group"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
      value: action.action,
      onChange: e => this.props.changeAction(index, e.target.value),
      key: index
    }, actionOptions), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "remove-item",
      title: "Remove Action",
      onClick: () => this.props.removeAction(index)
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-minus"
    }), " Delete"))), this.loadActionMetaInputs(action, index)));
  } //end loadActionFieldsets()


  render() {
    // TODO: Do not show add button until an event is set
    // TODO: Add list of available merge fields based on selected event, similar to Awesome Support email templates
    this.loadActionFieldsets();
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "automation-actions-list"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("h2", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "automation-step-number"
    }, "3"), " Actions"), this.actionFieldsets, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
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

/***/ "./src/components/PTCCompletionist_Automations.js":
/*!********************************************************!*\
  !*** ./src/components/PTCCompletionist_Automations.js ***!
  \********************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ PTCCompletionist_Automations; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _AutomationsListing_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AutomationsListing.js */ "./src/components/AutomationsListing.js");
/* harmony import */ var _AutomationDetailsForm_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./AutomationDetailsForm.js */ "./src/components/AutomationDetailsForm.js");



const {
  Component
} = wp.element;
class PTCCompletionist_Automations extends Component {
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

/***/ "./src/components/PTCCompletionistTasksDashboardWidget.jsx":
/*!*****************************************************************!*\
  !*** ./src/components/PTCCompletionistTasksDashboardWidget.jsx ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ PTCCompletionistTasksDashboardWidget; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _task_TaskOverview_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./task/TaskOverview.jsx */ "./src/components/task/TaskOverview.jsx");
/* harmony import */ var _task_TaskFilters_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./task/TaskFilters.jsx */ "./src/components/task/TaskFilters.jsx");
/* harmony import */ var _task_TaskListPaginated_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./task/TaskListPaginated.jsx */ "./src/components/task/TaskListPaginated.jsx");




const {
  useState
} = wp.element;
function PTCCompletionistTasksDashboardWidget(_ref) {
  let {
    tasks
  } = _ref;
  const [visibleTasks, setVisibleTasks] = useState(tasks);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-PTCCompletionistTasksDashboardWidget"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskOverview_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    tasks: tasks
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskFilters_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    tasks: tasks,
    onChange: (_key, selectedTasks) => setVisibleTasks(selectedTasks)
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskListPaginated_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    limit: 3,
    tasks: visibleTasks
  }));
}

/***/ }),

/***/ "./src/components/task/TaskActions.jsx":
/*!*********************************************!*\
  !*** ./src/components/task/TaskActions.jsx ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskActions; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./taskUtil.jsx */ "./src/components/task/taskUtil.jsx");


const {
  useState,
  useCallback
} = wp.element;
function TaskActions(_ref) {
  let {
    taskGID
  } = _ref;
  const [isProcessing, setIsProcessing] = useState(false);
  const handleUnpinTask = useCallback(taskGID => {
    console.log(`@TODO - Handle unpin task ${taskGID}`);
  }, []);
  const handleDeleteTask = useCallback(taskGID => {
    console.log(`@TODO - Handle delete task ${taskGID}`);
  }, []);
  const task_url = (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.getTaskUrl)(taskGID);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskActions"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: task_url,
    target: "_asana"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "View in Asana",
    className: "view-task",
    type: "button"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-link"
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Unpin",
    className: "unpin-task",
    type: "button",
    onClick: () => handleUnpinTask(taskGID)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-thumbtack"
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Delete",
    className: "delete-task",
    type: "button",
    onClick: () => handleDeleteTask(taskGID)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-minus"
  })));
}

/***/ }),

/***/ "./src/components/task/TaskFilters.jsx":
/*!*********************************************!*\
  !*** ./src/components/task/TaskFilters.jsx ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskFilters; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./taskUtil.jsx */ "./src/components/task/taskUtil.jsx");


const {
  useState,
  useCallback,
  useMemo
} = wp.element;
function TaskFilters(_ref) {
  let {
    tasks,
    onChange
  } = _ref;
  const [activeFilter, setActiveFilter] = useState('none');
  const filters = useMemo(() => {
    const incompleteTasks = (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.filterIncompleteTasks)(tasks);
    return [{
      "key": 'none',
      "title": 'All Tasks',
      "tasks": incompleteTasks
    }, {
      "key": 'pinned',
      "title": 'Pinned',
      "tasks": (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.filterPinnedTasks)(incompleteTasks)
    }, {
      "key": 'general',
      "title": 'General',
      "tasks": (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.filterGeneralTasks)(incompleteTasks)
    }, {
      "key": 'myTasks',
      "title": 'My Tasks',
      "tasks": (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.filterMyTasks)(window.PTCCompletionist.me.gid, incompleteTasks)
    }, {
      "key": 'critical',
      "title": 'Critical',
      "tasks": (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.filterCriticalTasks)(incompleteTasks)
    }];
  }, [tasks]);
  const handleClickFilter = useCallback((key, filteredTasks) => {
    setActiveFilter(key);
    onChange(key, filteredTasks);
  }, [activeFilter, setActiveFilter, onChange]);
  const renderedFilterButtons = filters.map(f => {
    let className = `filter-${f.key}`;

    if (activeFilter === f.key) {
      className += ' --is-active';
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      key: f.key,
      type: "button",
      className: className,
      onClick: () => handleClickFilter(f.key, f.tasks),
      style: {
        width: 'auto'
      }
    }, f.title, " ", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      class: "task-count"
    }, "(", f.tasks.length, ")"));
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskFilters"
  }, renderedFilterButtons);
}

/***/ }),

/***/ "./src/components/task/TaskList.jsx":
/*!******************************************!*\
  !*** ./src/components/task/TaskList.jsx ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskList; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskRow_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskRow.jsx */ "./src/components/task/TaskRow.jsx");


function TaskList(_ref) {
  let {
    tasks
  } = _ref;
  const renderedTasks = tasks.map(t => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskRow_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    key: t.gid,
    task: t
  }));
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskList"
  }, renderedTasks);
}

/***/ }),

/***/ "./src/components/task/TaskListPaginated.jsx":
/*!***************************************************!*\
  !*** ./src/components/task/TaskListPaginated.jsx ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskListPaginated; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskList_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskList.jsx */ "./src/components/task/TaskList.jsx");


const {
  useState,
  useCallback,
  useMemo
} = wp.element;
function TaskListPaginated(_ref) {
  let {
    limit,
    tasks
  } = _ref;
  const [currentPage, setCurrentPage] = useState(1);
  const totalPages = useMemo(() => Math.ceil(tasks.length / limit), [tasks, limit]);
  const goToPage = useCallback(page => {
    if (page <= 1) {
      setCurrentPage(1);
    } else if (page >= totalPages) {
      setCurrentPage(totalPages);
    } else {
      setCurrentPage(page);
    }
  }, [currentPage, setCurrentPage, totalPages]);
  const start = Math.max(0, (currentPage - 1) * limit);
  const currentTasks = tasks.slice(start, currentPage * limit);
  const renderedPageButtons = [];

  for (let i = 1; i <= totalPages; ++i) {
    renderedPageButtons.push((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      type: "button",
      title: `Page ${i}`,
      disabled: i === currentPage,
      onClick: () => goToPage(i)
    }, i));
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskListPaginated"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskList_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    tasks: currentTasks
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("nav", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    title: "Previous Page",
    disabled: 1 === currentPage,
    onClick: () => goToPage(currentPage - 1)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    class: "fas fa-angle-left"
  })), renderedPageButtons, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    title: "Next Page",
    disabled: totalPages === currentPage,
    onClick: () => goToPage(currentPage + 1)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    class: "fas fa-angle-right"
  }))));
}

/***/ }),

/***/ "./src/components/task/TaskOverview.jsx":
/*!**********************************************!*\
  !*** ./src/components/task/TaskOverview.jsx ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskOverview; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./taskUtil.jsx */ "./src/components/task/taskUtil.jsx");


const {
  useMemo
} = wp.element;
function TaskOverview(_ref) {
  let {
    tasks
  } = _ref;
  const incompleteTasks = useMemo(() => (0,_taskUtil_jsx__WEBPACK_IMPORTED_MODULE_1__.filterIncompleteTasks)(tasks), [tasks]);
  const completedCount = tasks.length - incompleteTasks.length;
  const completedPercent = Math.round(completedCount / tasks.length * 100);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskOverview"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "feature"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, completedPercent, "%"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Complete")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "details"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "task-count"
  }, incompleteTasks.length), " Remaining"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "progress-bar-wrapper"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "progress-bar",
    style: {
      width: `${completedPercent}%`
    }
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Completed ", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "completed-tasks-count"
  }, completedCount), " of ", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "total-tasks-count"
  }, tasks.length)))));
}

/***/ }),

/***/ "./src/components/task/TaskRow.jsx":
/*!*****************************************!*\
  !*** ./src/components/task/TaskRow.jsx ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskRow; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskActions_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskActions.jsx */ "./src/components/task/TaskActions.jsx");


const {
  useState,
  useCallback
} = wp.element;
function TaskRow(_ref) {
  let {
    task
  } = _ref;
  const [showDescription, setShowDescription] = useState(false);
  const handleMarkComplete = useCallback(taskGID => {
    console.warn(`@TODO - Handle mark complete for task ${taskGID}`);
  }, []);
  const handleToggleDescription = useCallback(() => {
    if (!task.notes) {
      return;
    }

    setShowDescription(!showDescription);
  }, [task, showDescription, setShowDescription]);
  const notesIconClassName = showDescription ? 'fas' : 'far';
  let assigneeDisplayName = null;

  if (task.assignee) {
    if (window.PTCCompletionist.users[task.assignee.gid]) {
      assigneeDisplayName = window.PTCCompletionist.users[task.assignee.gid].data.display_name;
    } else {
      assigneeDisplayName = '(Not Connected)';
    }
  }

  let ctaButton = null;

  if (task.action_link) {
    ctaButton = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "cta-button"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
      href: task.action_link.href,
      target: task.action_link.target
    }, task.action_link.label, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-long-arrow-alt-right"
    })));
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskRow"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Mark Complete",
    className: "mark-complete",
    type: "button",
    onClick: () => handleMarkComplete(task.gid)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-check"
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "name",
    onClick: handleToggleDescription
  }, task.name, task.notes && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: `${notesIconClassName} fa-sticky-note`
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "details"
  }, assigneeDisplayName && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "assignee"
  }, assigneeDisplayName), task.due_on && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "due"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-clock"
  }), task.due_on)), showDescription && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "description"
  }, task.notes), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskActions_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    taskGID: task.gid
  }), ctaButton);
}

/***/ }),

/***/ "./src/components/task/taskUtil.jsx":
/*!******************************************!*\
  !*** ./src/components/task/taskUtil.jsx ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "deleteTask": function() { return /* binding */ deleteTask; },
/* harmony export */   "unpinTask": function() { return /* binding */ unpinTask; },
/* harmony export */   "getTaskUrl": function() { return /* binding */ getTaskUrl; },
/* harmony export */   "isCriticalTask": function() { return /* binding */ isCriticalTask; },
/* harmony export */   "filterIncompleteTasks": function() { return /* binding */ filterIncompleteTasks; },
/* harmony export */   "filterCriticalTasks": function() { return /* binding */ filterCriticalTasks; },
/* harmony export */   "filterMyTasks": function() { return /* binding */ filterMyTasks; },
/* harmony export */   "filterGeneralTasks": function() { return /* binding */ filterGeneralTasks; },
/* harmony export */   "filterPinnedTasks": function() { return /* binding */ filterPinnedTasks; }
/* harmony export */ });
function deleteTask(taskGID) {
  console.log(`@TODO: Delete task ${taskGID}`);
}
function unpinTask(taskGID) {
  console.log(`@TODO: Unpin task ${taskGID}`);
}
function getTaskUrl(taskGID) {
  return `https://app.asana.com/0/0/${taskGID}/f`;
}
function isCriticalTask(task) {
  const DAY_IN_SECONDS = 86400;
  const limit = 7 * DAY_IN_SECONDS;
  return Date.parse(task.due_on) - Date.now() < limit;
}
function filterIncompleteTasks(tasks) {
  return tasks.filter(t => !t.completed);
}
function filterCriticalTasks(tasks) {
  return tasks.filter(t => isCriticalTask(t));
}
function filterMyTasks(userGID, tasks) {
  return tasks.filter(t => {
    if (t.assignee) {
      return userGID === t.assignee.gid;
    }

    return false;
  });
}
function filterGeneralTasks(tasks) {
  return tasks.filter(t => {
    if (t.action_link && t.action_link.post_id > 0) {
      return false;
    }

    return true;
  });
}
function filterPinnedTasks(tasks) {
  return tasks.filter(t => {
    if (t.action_link && t.action_link.post_id > 0) {
      return true;
    }

    return false;
  });
}

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
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_PTCCompletionist_Automations_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/PTCCompletionist_Automations.js */ "./src/components/PTCCompletionist_Automations.js");
/* harmony import */ var _components_PTCCompletionistTasksDashboardWidget_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/PTCCompletionistTasksDashboardWidget.jsx */ "./src/components/PTCCompletionistTasksDashboardWidget.jsx");



const {
  render
} = wp.element;
jQuery(function ($) {
  try {
    const rootNode = document.getElementById('ptc-completionist-automations-root');

    if (rootNode !== null) {
      render((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_PTCCompletionist_Automations_js__WEBPACK_IMPORTED_MODULE_1__["default"], null), rootNode);
    } //end if rootNode

  } catch (e) {
    console.error(e);
  }
});
document.addEventListener('DOMContentLoaded', () => {
  const rootNode = document.getElementById('ptc-PTCCompletionistTasksDashboardWidget');

  if (null !== rootNode) {
    render((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_PTCCompletionistTasksDashboardWidget_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
      tasks: Object.values(window.PTCCompletionist.tasks)
    }), rootNode);
  }
});
}();
/******/ })()
;
//# sourceMappingURL=index.js.map