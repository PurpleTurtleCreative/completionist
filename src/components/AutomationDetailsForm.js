import ptc_completionist_automations from '../index.js';

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

  }//end constructor()

  /* Change Handlers */
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

  handleEventChange(hook_name) {
    this.setState((state) => ({
      hook_name: hook_name
    }));
  }

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

  handleActionChange(action) {}

  handleActionMetaChange(meta) {}
  /* END Change Handlers */

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
    let userEventOptions = this.createSelectOptions( ptc_completionist_automations.event_user_options );
    let postEventOptions = this.createSelectOptions( ptc_completionist_automations.event_post_options );
    return (
      <div className="automation-event">
        <select value={this.props.hook_name} onChange={(e) => this.props.changeEvent(e.target.value)}>
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
    if ( Object.keys( ptc_completionist_automations.event_user_options ).includes( this.props.event ) ) {
      this.propertyOptions = this.createSelectOptions( ptc_completionist_automations.field_user_options );
    } else if ( Object.keys( ptc_completionist_automations.event_post_options ).includes( this.props.event ) ) {
      this.propertyOptions = this.createSelectOptions( ptc_completionist_automations.field_post_options );
    } else {
      this.propertyOptions = <option>ERROR</option>;
    }
  }//end loadPropertyOptions()

  loadComparisonMethodOptions() {
    this.comparisonMethodOptions = ptc_completionist_automations.field_comparison_methods.map((value) => (
      <option value={value} key={value}>{value}</option>
    ));
  }//end loadComparisonMethodOptions()

  loadConditionFieldsets() {
    this.conditionFieldsets = this.props.conditions.map((condition, index) => (
      <fieldset className="automation-condition" key={index}>
        <legend>Condition</legend>
        <select value={condition.property} key={index} onChange={(e) => this.props.changeConditionProperty(index, e.target.value)}>
          {this.propertyOptions}
        </select>
        <select value={condition.comparison_method} key={index} onChange={(e) => this.props.changeConditionMethod(index, e.target.value)}>
          {this.comparisonMethodOptions}
        </select>
        <input type="text" value={condition.value} key={index} onChange={(e) => this.props.changeConditionValue(index, e.target.value)} />
        <button onClick={() => this.props.removeCondition(index)}>Remove</button>
      </fieldset>
    ));
  }//end loadConditionFieldsets()

  render() {

    this.loadPropertyOptions();
    this.loadComparisonMethodOptions();
    this.loadConditionFieldsets();

    return (
      <div className="automation-conditions-list">
        {this.conditionFieldsets}
        <button onClick={this.props.addCondition}>Add Condition</button>
      </div>
    );
  }//end render()

}//end class AutomationConditionsInputs

class AutomationActionsInputs extends Component {

  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div>
        <p>Action Select</p>
        <p>(Display Appropriate Meta Form)</p>
      </div>
    );
  }

}//end class AutomationActionsInputs