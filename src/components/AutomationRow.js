const { Component } = wp.element;

export class AutomationRow extends Component {

  constructor(props) {

    /*
    Required Props:
    - (object) automation
    - (function) goToAutomation
    - (function) deleteAutomation
    */

    super(props);
    this.state = {
      ...props.automation,
      isDeleting: false
    };

    this.goToAutomation = props.goToAutomation;

    this.deleteAutomation = this.deleteAutomation.bind(this);

  }//end constructor()

  deleteAutomation() {
    this.setState({ isDeleting: true }, () => {
      this.props.deleteAutomation(this.state.ID, () => {
        this.setState({ isDeleting: false });
      });
    });
  }

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
        <button onClick={this.deleteAutomation} disabled={this.state.isDeleting}>Delete</button>
      </div>
    );
  }//end render()

}//end class AutomationsListing