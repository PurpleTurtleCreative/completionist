const { render, Component } = wp.element;

export class AutomationsListing extends Component {

  constructor(props) {

    /*
    Required Props:
    - (object) automations
    - (function) goToAutomation
    */

    super(props);
    this.state = {
      automations: props.automations,
      orderBy: 'title' //'title' or 'lastModified' or 'lastTriggered'
    };

    this.goToAutomation = props.goToAutomation;

  }//end constructor()

  render() {
    /* List Automations... */
    const automationLinks = this.state.automations.map((automation) =>
      <button onClick={() => this.goToAutomation(automation.id)} key={automation.id.toString()}>View Automation {automation.id}</button>
    );
    return (
      <div className='ptc-completionist-automations-list'>
        <h1>Automations Listing</h1>
        <div>{automationLinks}</div>
      </div>
    );
  }

}