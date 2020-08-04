import { PostSearchSelectInput } from './PostSearchSelectInput.js';

const { Component } = wp.element;

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

  }//end constructor()

  /* HANDLERS */

  saveAutomation() {
    if ( ! this.state.isSubmitting ) {

      // TODO: validate data for submission

      this.setState({ isSubmitting: true }, () => {

        let data = {
          'action': 'ptc_save_automation',
          'nonce': window.ptc_completionist_automations.nonce,
          'automation': this.state
        };

        window.jQuery.post(window.ajaxurl, data, (res) => {

          if (
            res.status
            && res.status == 'success'
            && res.code
            && res.data
            && typeof res.data == 'object'
            && 'ID' in res.data
            && res.data.ID
            && res.data.ID > 0
          ) {
            if ( res.code == 201 ) {
              console.log( res.message );
              this.props.goToAutomation( res.data.ID );
            } else if ( res.code == 200 ) {
              // TODO: display success message in notice section
              console.log( res.message );
              this.setState({
                ...res.data,
                isSubmitting: false
              });
            }
          } else {
            // TODO: display error messages in notice section
            if ( res.message && res.code ) {
              alert( 'Error ' + res.code + '. The automation could not be saved. ' + res.message);
            } else {
              alert( 'Error 409. The automation could not be saved.' );
            }
            this.setState({ isSubmitting: false });
          }

        }, 'json')
          .fail(() => {
            alert( 'Error 500. The automation could not be saved.' );
            this.setState({ isSubmitting: false });
          });

      });
    }
  }//end saveAutomation()

  /** Core Info **/

  handleAutomationChange(property_key, value) {
    this.setState((state) => ({
      [ property_key ]: value
    }));
  }

  /** Conditions **/

  handleConditionChange(index, property_key, value) {
    this.setState((state) => {
      let conditions = [...state.conditions];
      conditions[ index ] = {
        ...state.conditions[ index ],
        [ property_key ]: value
      };
      return { conditions: conditions };
    });
  }

  handleAddCondition() {
    this.setState((state) => ({
      conditions: [
        ...state.conditions,
        {
          ID: 0,
          property: '',
          comparison_method: window.ptc_completionist_automations.field_comparison_methods[0],
          value: ''
        }
      ]
    }));
  }

  handleRemoveCondition(index) {
    this.setState((state) => ({
      conditions: state.conditions.filter((_, i) => i !== index)
    }));
  }

  /** Actions **/

  handleActionChange(index, action) {
    this.setState((state) => {
      let actions = [...state.actions];
      actions[ index ] = {
        ...state.actions[ index ],
        action: action,
        meta: this.getDefaultActionMeta(action)
      };
      return { actions: actions };
    });
  }

  handleActionMetaChange(index, meta_key, meta_value) {
    this.setState((state) => {
      let actions = [...state.actions];
      actions[ index ] = {
        ...state.actions[ index ],
        meta: {
          ...state.actions[ index ].meta,
          [ meta_key ]: meta_value
        }
      };
      return { actions: actions };
    });
  }

  handleAddAction() {
    this.setState((state) => ({
      actions: [
        ...state.actions,
        {
          ID: 0,
          action: 'create_task',
          triggered_count: 0,
          last_triggered: '',
          meta: this.getDefaultActionMeta('create_task')
        }
      ]
    }));
  }

  handleRemoveAction(index) {
    this.setState((state) => ({
      actions: state.actions.filter((_, i) => i !== index)
    }));
  }

  /* END HANDLERS */

  getDefaultActionMeta(action) {
    switch(action) {
      case 'create_task':
        return {
          task_author: Object.keys( window.ptc_completionist_automations.connected_workspace_users )[0]
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
        <input type="text" value={this.props.title} onChange={(e) => this.props.changeTitle(e.target.value)} />
        <textarea value={this.props.description} onChange={(e) => this.props.changeDescription(e.target.value)} />
      </div>
    );
  }

}//end class AutomationInfoInputs

class AutomationEventInput extends Component {

  constructor(props) {
    super(props);
  }

  createSelectOptions( optionsObj ) {
    return Object.keys( optionsObj ).map((key) => (
      <option value={key} key={key}>{optionsObj[key]}</option>
    ));
  }//end createSelectOptions()

  render() {
    let userEventOptions = this.createSelectOptions( window.ptc_completionist_automations.event_user_options );
    let postEventOptions = this.createSelectOptions( window.ptc_completionist_automations.event_post_options );
    return (
      <div className="automation-event">
        <h2><span className="automation-step-number">1</span> Trigger Event</h2>
        <select value={this.props.hook_name} onChange={(e) => this.props.changeEvent(e.target.value)}>
          <option value="">(Choose Event)</option>
          <optgroup label="User Events">
            {userEventOptions}
          </optgroup>
          <optgroup label="Post Events">
            {postEventOptions}
          </optgroup>
        </select>
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

  createSelectOptions( optionsObj ) {
    return Object.keys( optionsObj ).map((key) => (
      <option value={key} key={key}>{optionsObj[key]}</option>
    ));
  }//end createSelectOptions()

  loadPropertyOptions() {
    if ( Object.keys( window.ptc_completionist_automations.event_user_options ).includes( this.props.event ) ) {
      this.propertyOptions = this.createSelectOptions( window.ptc_completionist_automations.field_user_options );
    } else if ( Object.keys( window.ptc_completionist_automations.event_post_options ).includes( this.props.event ) ) {
      this.propertyOptions = this.createSelectOptions( window.ptc_completionist_automations.field_post_options );
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
    this.conditionFieldsets = this.props.conditions.map((condition, index) => {
      let valueInput = null;
      if ( condition.comparison_method !== 'is empty' && condition.comparison_method !== 'is filled' ) {
        valueInput = <input type="text" value={condition.value} key={index} onChange={(e) => this.props.changeCondition(index, 'value', e.target.value)} />;
      }
      return (
        <fieldset className="automation-condition" key={index}>
          <legend>Condition</legend>
          <select value={condition.property} key={index} onChange={(e) => this.props.changeCondition(index, 'property', e.target.value)}>
            {this.propertyOptions}
          </select>
          <select value={condition.comparison_method} key={index} onChange={(e) => this.props.changeCondition(index, 'comparison_method', e.target.value)}>
            {this.comparisonMethodOptions}
          </select>
          {valueInput}
          <button className='remove-action' title='Remove Condition' onClick={() => this.props.removeCondition(index)}><i className="fas fa-minus"></i></button>
        </fieldset>
      );
    });
  }//end loadConditionFieldsets()

  render() {

    // TODO: Do not show add button until an event is set

    this.loadPropertyOptions();
    this.loadComparisonMethodOptions();
    this.loadConditionFieldsets();

    return (
      <div className="automation-conditions-list">
        <h2><span className="automation-step-number">2</span> Conditions</h2>
        {this.conditionFieldsets}
        <button onClick={this.props.addCondition}>Add Condition</button>
      </div>
    );
  }//end render()

}//end class AutomationConditionsInputs

class AutomationActionsInputs extends Component {

  constructor(props) {
    super(props);
    this.loadActionMetaInputs = this.loadActionMetaInputs.bind(this);
    this.loadActionFieldsets = this.loadActionFieldsets.bind(this);
  }

  createSelectOptions( optionsObj ) {
    return Object.keys( optionsObj ).map((key) => (
      <option value={key} key={key}>{optionsObj[key]}</option>
    ));
  }//end createSelectOptions()

  loadActionMetaInputs(action, index) {
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
              onChange={(e) => this.props.changeActionMeta(index, 'name', e.target.value)}
            />

            <div class="form-group">
              <label for={"ptc-new-task_task_author_"+index}>Creator</label>
              <select id={"ptc-new-task_task_author_"+index} value={action.meta.task_author} onChange={(e) => this.props.changeActionMeta(index, 'task_author', e.target.value)}>
                {this.createSelectOptions(window.ptc_completionist_automations.connected_workspace_users)}
              </select>
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_assignee_"+index}>Assignee</label>
              <select id={"ptc-new-task_assignee_"+index} value={action.meta.assignee} onChange={(e) => this.props.changeActionMeta(index, 'assignee', e.target.value)}>
                <option value="">None (Unassigned)</option>
                {this.createSelectOptions(window.ptc_completionist_automations.workspace_users)}
              </select>
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_due_on_"+index}>Due Date</label>
              <input id={"ptc-new-task_due_on_"+index} type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd" value={action.meta.due_on} onChange={(e) => this.props.changeActionMeta(index, 'due_on', e.target.value)} />
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_project_"+index}>Project</label>
              <select id={"ptc-new-task_project_"+index} value={action.meta.project} onChange={(e) => this.props.changeActionMeta(index, 'project', e.target.value)}>
                <option value="">None (Private Task)</option>
                {this.createSelectOptions(window.ptc_completionist_automations.workspace_projects)}
              </select>
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_notes_"+index}>Description</label>
              <textarea id={"ptc-new-task_notes_"+index} value={action.meta.notes} onChange={(e) => this.props.changeActionMeta(index, 'notes', e.target.value)} />
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_post_id_"+index}>Pin</label>
              <PostSearchSelectInput
                id={"ptc-new-task_post_id_"+index}
                initialValue={action.meta.post_id}
                onSelectOption={(value) => this.props.changeActionMeta(index, 'post_id', value)}
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
    let actionOptions = this.createSelectOptions( window.ptc_completionist_automations.action_options );
    this.actionFieldsets = this.props.actions.map((action, index) => (
      <fieldset className="automation-action" key={index}>
        <legend>Action</legend>
          <select value={action.action} onChange={(e) => this.props.changeAction(index, e.target.value)} key={index}>
            {actionOptions}
          </select>
          {this.loadActionMetaInputs(action, index)}
        <button className='remove-action' title='Remove Action' onClick={() => this.props.removeAction(index)}><i className="fas fa-minus"></i></button>
      </fieldset>
    ));
  }//end loadActionFieldsets()

  render() {

    // TODO: Do not show add button until an event is set
    // TODO: Add list of available merge fields based on selected event, similar to Awesome Support email templates

    this.loadActionFieldsets();

    return (
      <div className="automation-actions-list">
        <h2><span className="automation-step-number">3</span> Actions</h2>
        {this.actionFieldsets}
        <button onClick={this.props.addAction}>Add Action</button>
      </div>
    );
  }//end render()

}//end class AutomationActionsInputs