import { AutomationRow } from './AutomationRow.js';

const { Component } = wp.element;

export class AutomationsListing extends Component {

  constructor(props) {

    /*
    Required Props:
    - (object[]) automations
    - (function) goToAutomation
    */

    super(props);
    this.state = {
      automations: props.automations,
      orderBy: 'title' //'title' or 'ID' or 'last_modified' or 'last_triggered' or 'triggered_count'
    };

    this.goToAutomation = props.goToAutomation;

  }//end constructor()

  render() {
    /* List Automations... */
    const automationRows = this.state.automations.map((automation) =>
      <AutomationRow automation={automation} goToAutomation={this.goToAutomation} key={automation.ID} />
    );
    return (
      <div className='ptc-completionist-automations-listing'>
        <h1>Automations</h1>
        <button onClick={() => this.goToAutomation('new')}>Add New</button>
        <div className='ptc-completionist-automations-list'>{automationRows}</div>
      </div>
    );
  }

}//end class AutomationsListing