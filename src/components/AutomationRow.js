const { Component } = wp.element;

export class AutomationRow extends Component {

  constructor(props) {

    /*
    Required Props:
    - (object) automation
    - (function) goToAutomation
    */

    super(props);
    this.state = props.automation;

    this.goToAutomation = props.goToAutomation;

  }//end constructor()

  render() {
    return (
      <div className='ptc-completionist-automation-row'>
        <h2>{this.state.title}</h2>
        <ul>
          <li>ID: {this.state.ID}</li>
          <li>Last Modified: {this.state.last_modified}</li>
          <li>Total Conditions: {this.state.total_conditions}</li>
          <li>Total Actions: {this.state.total_actions}</li>
          <li>Last Triggered: {this.state.last_triggered}</li>
          <li>Total Action Triggers: {this.state.total_triggered}</li>
        </ul>
        <button onClick={() => this.goToAutomation(this.state.ID)}>Edit</button>
        <button onClick={() => console.log('Delete '+this.state.ID)}>Delete</button>
      </div>
    );
  }//end render()

}//end class AutomationsListing