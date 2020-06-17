const { Component } = wp.element;

export class AutomationDetailsForm extends Component {

  constructor(props) {

    /*
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
    } else {
      this.state = {
        ID: 0,
        title: '',
        description: '',
        hook_name: '',
        last_modified: '',
        conditions: [],
        actions: [],
        saveButtonLabel: 'Create'
      };
    }

    this.handleTitleChange = this.handleTitleChange.bind(this);
    this.handleDescriptionChange = this.handleDescriptionChange.bind(this);
    this.handleEventChange = this.handleEventChange.bind(this);

    this.handleConditionPropertyChange = this.handleConditionPropertyChange.bind(this);
    this.handleConditionMethodChange = this.handleConditionMethodChange.bind(this);
    this.handleConditionValueChange = this.handleConditionValueChange.bind(this);
    this.handleAddCondition = this.handleAddCondition.bind(this);
    this.handleRemoveCondition = this.handleRemoveCondition.bind(this);

    this.handleActionChange = this.handleActionChange.bind(this);
    this.handleActionMetaChange = this.handleActionMetaChange.bind(this);
    this.handleAddAction = this.handleAddAction.bind(this);
    this.handleRemoveAction = this.handleRemoveAction.bind(this);

  }//end constructor()

  /* HANDLERS */

  /** Info **/

  handleTitleChange(title) {
    this.setState((state) => ({
      title: title
    }));
  }

  handleDescriptionChange(description) {
    this.setState((state) => ({
      description: description
    }));
  }

  /** Event **/

  handleEventChange(hook_name) {
    this.setState((state) => ({
      hook_name: hook_name
    }));
  }

  /** Conditions **/

  handleConditionPropertyChange(index, property) {
    this.setState((state) => {
      let conditions = [...state.conditions];
      conditions[ index ] = {
        ...state.conditions[ index ],
        property: property
      };
      return { conditions: conditions };
    });
  }

  handleConditionMethodChange(index, comparison_method) {
    this.setState((state) => {
      let conditions = [...state.conditions];
      conditions[ index ] = {
        ...state.conditions[ index ],
        comparison_method: comparison_method
      };
      return { conditions: conditions };
    });
  }

  handleConditionValueChange(index, value) {
    this.setState((state) => {
      let conditions = [...state.conditions];
      conditions[ index ] = {
        ...state.conditions[ index ],
        value: value
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
          comparison_method: '',
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
        action: action
      };
      return { actions: actions };
    });
  }

  handleActionMetaChange(index, meta) {}

  handleAddAction() {
    this.setState((state) => ({
      actions: [
        ...state.actions,
        {
          ID: 0,
          action: 'create_task',
          triggered_count: 0,
          last_triggered: '',
          meta: {}
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

  render() {
    return (
      <div className="ptc-completionist-automation-details-form">
        <AutomationInfoInputs
          title={this.state.title}
          changeTitle={this.handleTitleChange}
          description={this.state.description}
          changeDescription={this.handleDescriptionChange}
        />
        <AutomationEventInput
          hook_name={this.state.hook_name}
          changeEvent={this.handleEventChange}
        />
        <AutomationConditionsInputs
          conditions={this.state.conditions}
          event={this.state.hook_name}
          changeConditionProperty={this.handleConditionPropertyChange}
          changeConditionMethod={this.handleConditionMethodChange}
          changeConditionValue={this.handleConditionValueChange}
          addCondition={this.handleAddCondition}
          removeCondition={this.handleRemoveCondition}
        />
        <AutomationActionsInputs
          actions={this.state.actions}
          changeAction={this.handleActionChange}
          addAction={this.handleAddAction}
          removeAction={this.handleRemoveAction}
        />
        <button onClick={() => console.log('Save automation...')}>{this.state.saveButtonLabel}</button>
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
        valueInput = <input type="text" value={condition.value} key={index} onChange={(e) => this.props.changeConditionValue(index, e.target.value)} />;
      }
      return (
        <fieldset className="automation-condition" key={index}>
          <legend>Condition</legend>
          <select value={condition.property} key={index} onChange={(e) => this.props.changeConditionProperty(index, e.target.value)}>
            {this.propertyOptions}
          </select>
          <select value={condition.comparison_method} key={index} onChange={(e) => this.props.changeConditionMethod(index, e.target.value)}>
            {this.comparisonMethodOptions}
          </select>
          {valueInput}
          <button onClick={() => this.props.removeCondition(index)}>Remove</button>
        </fieldset>
      );
    });
  }//end loadConditionFieldsets()

  render() {

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
    switch(action.action) {
      case 'create_task':
        return (
          <div className="action-meta_create_task">

            <input id={"ptc-new-task_name_"+index} type="text" placeholder="Write a task name..." value={action.meta.name} />

            <div class="form-group">
              <label for={"ptc-new-task_task_author_"+index}>Creator</label>
              <select id={"ptc-new-task_task_author_"+index} value={action.meta.task_author}>
                {this.createSelectOptions(window.ptc_completionist_automations.workspace_users)}
              </select>
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_assignee_"+index}>Assignee</label>
              <select id={"ptc-new-task_assignee_"+index} value={action.meta.assignee}>
                <option value="">None (Unassigned)</option>
                {this.createSelectOptions(window.ptc_completionist_automations.workspace_users)}
              </select>
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_due_on_"+index}>Due Date</label>
              <input id={"ptc-new-task_due_on_"+index} type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd" value={action.meta.due_on} />
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_project_"+index}>Project</label>
              <select id={"ptc-new-task_project_"+index} value={action.meta.project}>
                <option value="">None (Private Task)</option>
                {this.createSelectOptions(window.ptc_completionist_automations.workspace_projects)}
              </select>
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_notes_"+index}>Description</label>
              <textarea id={"ptc-new-task_notes_"+index} value={action.meta.notes} />
            </div>

            <div class="form-group">
              <label for={"ptc-new-task_project_"+index}>Pin</label>
              <select id={"ptc-new-task_project_"+index} value={action.meta.post_id}>
                <option value="">None (General Task)</option>
              </select>
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
        <button onClick={() => this.props.removeAction(index)}>Remove</button>
      </fieldset>
    ));
  }//end loadActionFieldsets()

  render() {

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