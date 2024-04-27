import { PostSearchSelectInput } from './PostSearchSelectInput.js';

const { Component } = wp.element;

// --- Helpers --- //

function isCustomHookName( hook_name ) {
  return ( true === Object.keys(window.ptc_completionist_automations.event_custom_options).some(option => hook_name.startsWith(option)));
}

function getCustomHookNameFromValue( option ) {
  if ( true === isCustomHookName( option ) ) {
    return option.replace( getHookOptionValueFromName( option ), '' );
  }
  return '';
}

function getHookOptionValueFromName( hook_name ) {
  let actualHookName = Object.keys(window.ptc_completionist_automations.event_custom_options).find(option => hook_name.startsWith(option));
  return actualHookName ?? hook_name;
}

function createSelectOptions( optionsObj ) {
  return Object.keys(optionsObj).map((key) => (
    <option value={key} key={key}>{optionsObj[key]}</option>
  ));
}

// --- Components --- //

export class AutomationDetailsForm extends Component {

  constructor(props) {

    /*
    Required Props:
    - (function) goToAutomation
    Optional Props:
    - (object) automation
    */

    super(props);

    if ( 'automation' in props ) {
      this.state = props.automation;
      this.state.saveButtonLabel = 'Create';
      if ( 'ID' in props.automation && props.automation.ID > 0 ) {
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

  }//end constructor()

  /* HANDLERS */

  saveAutomation() {
    if ( ! this.state.isSubmitting ) {

      // TODO: validate data for submission

      this.setState({ "isSubmitting": true }, () => {

        const { saveButtonLabel, isSubmitting, ...automation } = this.state;

        const ajaxRequest = {
          'method': 'PUT',
          'url': `${window.ptc_completionist_automations.api.v1}/automations/${automation.ID}`,
          'headers': {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.ptc_completionist_automations.api.auth_nonce
          },
          'contentType': 'application/json',
          'data': {
            'nonce': window.ptc_completionist_automations.api.nonce_update_automation,
            'automation': automation
          },
          'dataType': 'json',
        };

        if ( 0 === automation.ID ) {
          ajaxRequest.method = 'POST';
          ajaxRequest.url = `${window.ptc_completionist_automations.api.v1}/automations`;
          ajaxRequest.data.nonce = window.ptc_completionist_automations.api.nonce_create_automation;
        }

        ajaxRequest.data = JSON.stringify(ajaxRequest.data);

        window.jQuery
          .ajax(ajaxRequest)
          .done((res) => {

            if (
              'success' === res?.status
              && [ 200, 201 ].includes(res?.code)
              && res?.data?.automation?.ID > 0
            ) {
              if ( 201 === res.code ) {
  //               console.log( res.message );
                this.props.goToAutomation( res.data.automation.ID );
              } else if ( 200 === res.code ) {
                // TODO: display success message in notice section
  //               console.log( res.message );
                this.setState({
                  ...res.data.automation,
                  "isSubmitting": false
                });
              }
            } else {
              // TODO: display error messages in notice section
              if ( res?.message && res?.code ) {
                alert( 'Error ' + res.code + '. The automation could not be saved. ' + res.message);
              } else {
                alert( 'Error 409. The automation could not be saved.' );
              }
              this.setState({ "isSubmitting": false });
            }
          })
          .fail(() => {
            alert( 'Error 500. The automation could not be saved.' );
            this.setState({ "isSubmitting": false });
          });

      });
    }
  }//end saveAutomation()

  /** Core Info **/

  handleAutomationChange(property_key, value) {
    this.setState(state => ({
      ...state,
      [ property_key ]: value
    }));
  }

  /** Conditions **/

  handleConditionChange(condition_id, property_key, value) {
    this.setState(state => {
      const conditions = state.conditions.map(c => {
        if ( c.ID === condition_id ) {
          return {
            ...c,
            [ property_key ]: value
          };
        }
        return c;
      });
      return { "conditions": conditions };
    });
  }

  handleAddCondition() {
    this.setState(state => ({
      "conditions": [
        ...state.conditions,
        {
          "ID": -Date.now(),
          "property": '',
          "comparison_method": window.ptc_completionist_automations.field_comparison_methods[0],
          "value": ''
        }
      ]
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
        if ( a.ID === action_id ) {
          return {
            ...a,
            "action": action,
            "meta": this.getDefaultActionMeta(action)
          };
        }
        return a;
      });
      return { "actions": actions };
    });
  }

  handleActionMetaChange(action_id, meta_key, meta_value) {
    this.setState(state => {
      const actions = state.actions.map(a => {
        if ( a.ID === action_id ) {
          return {
            ...a,
            "meta": {
              ...a.meta,
              [ meta_key ]: meta_value
            }
          };
        }
        return a;
      });
      return { "actions": actions };
    });
  }

  handleAddAction() {
    this.setState(state => ({
      "actions": [
        ...state.actions,
        {
          "ID": -Date.now(),
          "action": 'create_task',
          "triggered_count": 0,
          "last_triggered": '',
          "meta": this.getDefaultActionMeta('create_task')
        }
      ]
    }));
  }

  handleRemoveAction(action_id) {
    this.setState(state => ({
      "actions": state.actions.filter(a => a.ID !== action_id)
    }));
  }

  /* END HANDLERS */

  getDefaultActionMeta(action) {
    switch(action) {
      case 'create_task':
        return {
          "task_author": Object.keys( window.ptc_completionist_automations.connected_workspace_users )[0]
        };
        break;
      default:
        return {};
        break;
    }
  }

  render() {
    return (
      <div className="ptc-completionist-automation-details-form">
        <AutomationInfoInputs
          title={this.state.title}
          changeTitle={(value) => this.handleAutomationChange('title', value)}
          description={this.state.description}
          changeDescription={(value) => this.handleAutomationChange('description', value)}
        />
        <AutomationEventInput
          hook_name={this.state.hook_name}
          changeEvent={(value) => this.handleAutomationChange('hook_name', value)}
        />
        <AutomationConditionsInputs
          event={this.state.hook_name}
          conditions={this.state.conditions}
          changeCondition={this.handleConditionChange}
          addCondition={this.handleAddCondition}
          removeCondition={this.handleRemoveCondition}
        />
        <AutomationActionsInputs
          event={this.state.hook_name}
          actions={this.state.actions}
          changeAction={this.handleActionChange}
          addAction={this.handleAddAction}
          removeAction={this.handleRemoveAction}
          changeActionMeta={this.handleActionMetaChange}
        />
        { this.state.isSubmitting ?
          <button className='save-automation' onClick={() => this.saveAutomation()} disabled='disabled'><i className="fas fa-spinner fa-pulse"></i> Saving...</button>
          :
          <button className='save-automation' onClick={() => this.saveAutomation()}><i className="fas fa-save"></i> {this.state.saveButtonLabel}</button>
        }
      </div>
    );
  }//end render()

}//end class AutomationsListing

class AutomationInfoInputs extends Component {

  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div className="automation-info">
        <div className="form-group">
          <label htmlFor="automation-title">Title</label>
          <input id="automation-title" type="text" value={this.props.title} onChange={(e) => this.props.changeTitle(e.target.value)} />
        </div>
        <div className="form-group">
          <label htmlFor="automation-description">Description</label>
          <textarea id="automation-description" value={this.props.description} onChange={(e) => this.props.changeDescription(e.target.value)} />
        </div>
      </div>
    );
  }

}//end class AutomationInfoInputs

class AutomationEventInput extends Component {

  constructor(props) {
    super(props);
    this.state = {
      "selected_hook_name": getHookOptionValueFromName(this.props.hook_name),
      "custom_hook_name": getCustomHookNameFromValue(this.props.hook_name)
    }

    this.updateEventHookName = this.updateEventHookName.bind(this);
    this.handleCustomHookNameChange = this.handleCustomHookNameChange.bind(this);
    this.handleEventChange = this.handleEventChange.bind(this);
  }

  updateEventHookName(state) {
    if ( true === isCustomHookName(state.selected_hook_name) ) {
      this.props.changeEvent(state.selected_hook_name + state.custom_hook_name);
    } else {
      this.props.changeEvent(state.selected_hook_name);
    }
  }

  handleCustomHookNameChange(value) {
    this.setState(
      { "custom_hook_name": value },
      () => { this.updateEventHookName(this.state) }
    );
  }

  handleEventChange(value) {
    this.setState(
      { "selected_hook_name": value },
      () => { this.updateEventHookName(this.state) }
    );
  }

  render() {

    const userEventOptions = createSelectOptions(window.ptc_completionist_automations.event_user_options);
    const postEventOptions = createSelectOptions(window.ptc_completionist_automations.event_post_options);
    const customEventOptions = createSelectOptions(window.ptc_completionist_automations.event_custom_options);

    let customHookNameInput = null;
    if ( true === isCustomHookName(this.state.selected_hook_name) ) {
      customHookNameInput = <input type="text" value={this.state.custom_hook_name} placeholder="custom_hook_name" onChange={e => this.handleCustomHookNameChange(e.target.value)} />;
    }

    return (
      <div className="automation-event">
        <h2><span className="automation-step-number">1</span>Trigger&nbsp;Event</h2>
        <select value={getHookOptionValueFromName(this.state.selected_hook_name)} onChange={(e) => this.handleEventChange(e.target.value)}>
          <option value="">(Choose Event)</option>
          <optgroup label="User Events">
            {userEventOptions}
          </optgroup>
          <optgroup label="Post Events">
            {postEventOptions}
          </optgroup>
          <optgroup label="Custom Events">
            {customEventOptions}
          </optgroup>
        </select>
        {customHookNameInput}
      </div>
    );
  }
}//end class AutomationEventInput

class AutomationConditionsInputs extends Component {

  constructor(props) {
    super(props);
    this.loadPropertyOptions = this.loadPropertyOptions.bind(this);
    this.loadComparisonMethodOptions = this.loadComparisonMethodOptions.bind(this);
    this.loadConditionFieldsets = this.loadConditionFieldsets.bind(this);
  }//end constructor()

  loadPropertyOptions() {
    if ( Object.keys( window.ptc_completionist_automations.event_user_options ).includes( this.props.event ) ) {
      this.propertyOptions = createSelectOptions( window.ptc_completionist_automations.field_user_options );
    } else if ( Object.keys( window.ptc_completionist_automations.event_post_options ).includes( this.props.event ) ) {
      this.propertyOptions = createSelectOptions( window.ptc_completionist_automations.field_post_options );
    } else {
      this.propertyOptions = <option>(Choose Event)</option>;
    }
  }//end loadPropertyOptions()

  loadComparisonMethodOptions() {
    this.comparisonMethodOptions = window.ptc_completionist_automations.field_comparison_methods.map((value) => (
      <option value={value} key={value}>{value}</option>
    ));
  }//end loadComparisonMethodOptions()

  loadConditionFieldsets() {
    if ( true === isCustomHookName( this.props.event ) ) {
      this.conditionFieldsets = <p className="ptc-error-not-supported">Custom events do not support conditions. Be careful when choosing a custom hook.</p>;
    } else {
      this.conditionFieldsets = this.props.conditions.map(condition => {
        let valueInput = null;
        if ( condition.comparison_method !== 'is empty' && condition.comparison_method !== 'is filled' ) {
          valueInput = <input type="text" value={condition.value} onChange={(e) => this.props.changeCondition(condition.ID, 'value', e.target.value)} placeholder="value" />;
        }
        return (
          <fieldset className="automation-condition" key={condition.ID}>
            <legend>Condition</legend>
            <div className='form-group'>
              <select value={condition.property} onChange={(e) => this.props.changeCondition(condition.ID, 'property', e.target.value)}>
                {this.propertyOptions}
              </select>
              <select value={condition.comparison_method} onChange={(e) => this.props.changeCondition(condition.ID, 'comparison_method', e.target.value)}>
                {this.comparisonMethodOptions}
              </select>
              {valueInput}
              <button className='remove-item' title='Remove Condition' onClick={() => this.props.removeCondition(condition.ID)}><i className="fas fa-minus"></i> Delete</button>
            </div>
          </fieldset>
        );
      });
    }
  }//end loadConditionFieldsets()

  render() {

    let allowAllConditionsMessage = true;

    let content;
    if ( ! this.props.event.length ) {
      content = <p className="ptc-message"><strong>No trigger event selected.</strong>Conditions control if Actions should run after a trigger event.</p>;
      allowAllConditionsMessage = false;
    } else if ( true === isCustomHookName( this.props.event ) ) {
      content = <p className="ptc-message"><strong>Custom events do not support conditions.</strong>Actions will always run for the specified custom event.</p>;
      allowAllConditionsMessage = false;
    } else {
      this.loadPropertyOptions();
      this.loadComparisonMethodOptions();
      this.loadConditionFieldsets();
      content = (<>
        {this.conditionFieldsets}
        <button className='add-item' onClick={this.props.addCondition}><i className="fas fa-plus"></i> Add Condition</button>
      </>);
    }

    return (
      <div className="automation-conditions-list">
        <div className="section-header">
          <h2><span className="automation-step-number">2</span>Conditions</h2>
          { true === allowAllConditionsMessage && this.conditionFieldsets.length > 1 &&
            <p className="ptc-message"><em>All</em> conditions must evaluate to true for the Actions to run.</p> }
        </div>
        {content}
      </div>
    );
  }//end render()
}//end class AutomationConditionsInputs

class AutomationActionsInputs extends Component {

  constructor(props) {
    super(props);
    this.getActionMetaFields = this.getActionMetaFields.bind(this);
    this.loadActionFieldsets = this.loadActionFieldsets.bind(this);
  }

  getActionMetaFields(action, index) {
    // TODO: Allow create_tasks to be dynamically pinned to created/updated/delete post if relevent: {post.ID}
    switch(action.action) {
      case 'create_task':
        return (
          <div className="action-meta_create_task">

            <input
              id={"ptc-new-task_name_"+index}
              type="text"
              placeholder="Write a task name..."
              value={action.meta.name}
              onChange={(e) => this.props.changeActionMeta(action.ID, 'name', e.target.value)}
            />

            <div className='form-group'>
              <label htmlFor={"ptc-new-task_task_author_"+index}>Creator</label>
              <select id={"ptc-new-task_task_author_"+index} value={action.meta.task_author} onChange={(e) => this.props.changeActionMeta(action.ID, 'task_author', e.target.value)}>
                {createSelectOptions(window.ptc_completionist_automations.connected_workspace_users)}
              </select>
            </div>

            <div className='form-group'>
              <label htmlFor={"ptc-new-task_assignee_"+index}>Assignee</label>
              <select id={"ptc-new-task_assignee_"+index} value={action.meta.assignee} onChange={(e) => this.props.changeActionMeta(action.ID, 'assignee', e.target.value)}>
                <option value="">None (Unassigned)</option>
                {createSelectOptions(window.ptc_completionist_automations.workspace_users)}
              </select>
            </div>

            <div className='form-group'>
              <label htmlFor={"ptc-new-task_due_on_"+index}>Due Date</label>
              <input id={"ptc-new-task_due_on_"+index} type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd" value={action.meta.due_on} onChange={(e) => this.props.changeActionMeta(action.ID, 'due_on', e.target.value)} />
            </div>

            <div className='form-group'>
              <label htmlFor={"ptc-new-task_project_"+index}>Project</label>
              <select id={"ptc-new-task_project_"+index} value={action.meta.project} onChange={(e) => this.props.changeActionMeta(action.ID, 'project', e.target.value)}>
                <option value="">None (Private Task)</option>
                {createSelectOptions(window.ptc_completionist_automations.workspace_projects)}
              </select>
            </div>

            <div className='form-group'>
              <label htmlFor={"ptc-new-task_notes_"+index}>Description</label>
              <textarea id={"ptc-new-task_notes_"+index} value={action.meta.notes} onChange={(e) => this.props.changeActionMeta(action.ID, 'notes', e.target.value)} />
            </div>

            <div className='form-group' style={{ alignItems: 'flex-start' }}>
              <label htmlFor={"ptc-new-task_post_id_"+index} style={{ marginTop: '0.33em' }}>Pin to Post</label>
              <PostSearchSelectInput
                id={"ptc-new-task_post_id_"+index}
                suggestedOptions={[
                  {
                    "value": "{post.ID}",
                    "label": "{post.ID} - Dynamically pin to associated post"
                  },
                  {
                    /*
                    Note that Classic Editor creates the draft post after the post title
                    has been provided, which is often before the user has selected a
                    Parent post value. This makes it where using the "Post is Created"
                    trigger for posts created in Classic Editor has unexpected behavior.
                    So I say to you... Switch to the Block Editor already! Hahaha!
                    */
                    "value": "{post.post_parent}",
                    "label": "{post.post_parent} - Dynamically pin to associated post's parent post"
                  }
                ]}
                initialValue={action.meta.post_id}
                onSelectOption={(value) => this.props.changeActionMeta(action.ID, 'post_id', value)}
              />
            </div>

          </div>
        );
        break;
      default:
        return (
          <div className="automation-meta-default">
            <p><em>Choose an action to see additional options.</em></p>
          </div>
        );
    }
  }

  loadActionFieldsets() {
    let actionOptions = createSelectOptions( window.ptc_completionist_automations.action_options );
    this.actionFieldsets = this.props.actions.map((action, index) => (
      <fieldset className="automation-action" key={action.ID}>
        <legend>Action</legend>
        <div className='form-group'>
          <select value={action.action} onChange={(e) => this.props.changeAction(action.ID, e.target.value)}>
            {actionOptions}
          </select>
          <div>
            { this.props.actions.length > 1 &&
              <button className='remove-item' title='Remove Action' onClick={() => this.props.removeAction(action.ID)}><i className="fas fa-minus"></i> Delete</button> }
          </div>
        </div>
        {this.getActionMetaFields(action, index)}
      </fieldset>
    ));
  }//end loadActionFieldsets()

  render() {

    // TODO: Add list of available merge fields based on selected event, similar to Awesome Support email templates

    this.loadActionFieldsets();

    return (
      <div className="automation-actions-list">
        <div className="section-header">
          <h2><span className="automation-step-number">3</span>Actions</h2>
          { this.props.actions.length <= 1 &&
            <p className="ptc-message">At least 1 Action is required.</p> }
        </div>
        {this.actionFieldsets}
        <button className='add-item' onClick={this.props.addAction}><i className="fas fa-plus"></i> Add Action</button>
      </div>
    );
  }//end render()

}//end class AutomationActionsInputs
