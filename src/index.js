import { AutomationsListing } from './components/AutomationsListing.js';
import { AutomationDetailsForm } from './components/AutomationDetailsForm.js';

jQuery(function($) {
  try {

    var rootNode = document.getElementById('ptc-completionist-automations-root');
    if ( rootNode !== null ) {

      const { render, Component } = wp.element;

      class PTCCompletionist_Automations extends Component {

        // TODO: Move class to separate file for optimized importing

        constructor(props) {

          super(props);

          this.state = { automations: window.ptc_completionist_automations.automations };

          this.goToAutomation = this.goToAutomation.bind(this);

        }//end constructor()

        goToAutomation( automationId = 0 ) {

          if ( automationId === 'new' ) {
            let queryParams = new URLSearchParams( location.search );
            queryParams.set('automation', automationId);
            history.pushState(
              {},
              'Completionist &ndash; Add New Automation',
              '?' + queryParams.toString()
            );
          } else if ( automationId <= 0 ) {
            let queryParams = new URLSearchParams( location.search );
            queryParams.delete('automation');
            history.pushState(
              {},
              'Completionist &ndash; Automation ' + automationId,
              '?' + queryParams.toString()
            );
          } else {
            let queryParams = new URLSearchParams( location.search );
            queryParams.set('automation', automationId);
            history.pushState(
              { "automationId": automationId },
              'Completionist &ndash; Automation ' + automationId,
              '?' + queryParams.toString()
            );
          }

          this.forceUpdate();

        };//end goToAutomation()

        componentDidMount() {
          window.addEventListener( 'popstate', this.goToAutomation );
        }//end componentDidMount()

        render() {
          let queryParams = new URLSearchParams( location.search );
          const automationParam = queryParams.get('automation');
          if ( automationParam === 'new' ) {
            /* Add Automation... */
            return (
              <div className='ptc-completionist-automation-create'>
                <h1>New Automation</h1>
                <AutomationDetailsForm />
                <button onClick={() => this.goToAutomation()}>Back</button>
              </div>
            );
          }

          // TODO: Request automation data by ID and pass result to AutomationDetailsForm
          const automationIndex = this.state.automations.findIndex((automation) => automation.ID == automationParam);
          if ( automationIndex > -1 ) {
            /* Edit Automation... */
            return (
              <div className='ptc-completionist-automation-details'>
                <h1>Viewing automation {automationParam}</h1>
                <AutomationDetailsForm automation={this.state.automations[ automationIndex ]} />
                <button onClick={() => this.goToAutomation()}>Back</button>
              </div>
            );
          } else {
            /* List Automations... */
            return (
              <AutomationsListing automations={this.state.automations} goToAutomation={this.goToAutomation} />
            );
          }
        }//end render()

      }//end class PTCCompletionist_Automations

      render( <PTCCompletionist_Automations />, rootNode );

    }//end if rootNode
  } catch ( e ) {
    console.error( e );
  }
});