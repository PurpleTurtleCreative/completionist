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
        <h2>(Title) Automation {this.state.ID}</h2>
        <ul>
          <li>Last Modified: {this.state.last_modified}</li>
          <li>Total Actions: <em>Unknown</em></li>
          <li>Last Action Trigger: <em>Unknown</em></li>
          <li>Total Action Triggers: <em>Unknown</em></li>
        </ul>
        <button onClick={() => this.goToAutomation(this.state.ID)}>Edit</button>
        <button onClick={() => console.log('Delete '+this.state.ID)}>Delete</button>
      </div>
    );
  }//end render()

}//end class AutomationsListing