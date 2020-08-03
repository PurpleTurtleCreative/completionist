import { AutomationRow } from './AutomationRow.js';

const { Component } = wp.element;

export class AutomationsListing extends Component {

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

  }//end constructor()

  sortAutomationsListing(orderBy = 'title') {}//end sortAutomationsListing()

  componentDidUpdate(prevProps) {
    if ( this.props.automations !== prevProps.automations ) {
      this.setState({ automations: this.props.automations });
    }
  }

  render() {

    // TODO: Add sorting functionality
    // TODO: Add default/empty state if no automations exist

    /* List Automations... */
    const automationRows = this.state.automations.map((automation) =>
      <AutomationRow
        key={automation.ID}
        automation={automation}
        goToAutomation={this.goToAutomation}
        deleteAutomation={this.props.deleteAutomation}
      />
    );
    return (
      <div className='ptc-completionist-automations-listing'>
        <div className='title'>
          <h1>Automations</h1>
          <div className='actions'>
            <button onClick={() => this.goToAutomation('new')}><i className='fas fa-plus'></i> Add New</button>
          </div>
        </div>
        <header>
          <div>Automation</div>
          <div><i className="fas fa-question"></i> Conditions</div>
          <div><i className="fas fa-running"></i> Actions</div>
          <div><i className="fas fa-bolt"></i> Triggers</div>
          <div><i className="fas fa-history"></i> Last Triggered</div>
        </header>
        <div className='ptc-completionist-automations-list'>{automationRows}</div>
      </div>
    );
  }

}//end class AutomationsListing