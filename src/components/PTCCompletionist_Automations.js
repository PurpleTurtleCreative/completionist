import { AutomationsListing } from './AutomationsListing.js';
import { AutomationDetailsForm } from './AutomationDetailsForm.js';

const { Component } = wp.element;

export class PTCCompletionist_Automations extends Component {

  // TODO: Move class to separate file for optimized importing

  constructor(props) {

    super(props);

    this.state = {
      automations: window.ptc_completionist_automations.automations,
      isLoading: false
    };

    this.goToAutomation = this.goToAutomation.bind(this);

  }//end constructor()

  goToAutomation( automationId = 0 ) {
    this.setState({ isLoading: true }, () => {
      if ( automationId === 'new' ) {
        let queryParams = new URLSearchParams( location.search );
        queryParams.set( 'automation', automationId );
        history.pushState(
          {},
          'Completionist &ndash; Add New Automation',
          '?' + queryParams.toString()
        );
        this.setState({ isLoading: false });
      } else if ( automationId <= 0 ) {
        let queryParams = new URLSearchParams( location.search );
        queryParams.delete('automation');
        history.pushState(
          {},
          'Completionist &ndash; Automations',
          '?' + queryParams.toString()
        );
        this.setState({ isLoading: false });
      } else {

        let data = {
          'action': 'ptc_get_automation',
          'nonce': window.ptc_completionist_automations.nonce,
          'ID': automationId
        };

        window.jQuery.post(window.ajaxurl, data, (res) => {

          console.log(res);

          if ( res.status == 'success' && typeof res.data == 'object' ) {
            let queryParams = new URLSearchParams( location.search );
            queryParams.set( 'automation', automationId );
            history.pushState(
              res.data,
              'Completionist &ndash; Automation ' + automationId,
              '?' + queryParams.toString()
            );
            this.setState({ isLoading: false });
          } else {
            console.error( res );
            this.goToAutomation();
          }

        }, 'json')
          .fail(() => {
            console.error( 'Failed to get data for automation ' + automationId );
            this.goToAutomation();
          });
      }
    });
  };//end goToAutomation()

  componentDidMount() {
    window.addEventListener( 'popstate', this.goToAutomation );
  }//end componentDidMount()

  render() {
    if ( this.state.isLoading ) {
      return (
        <p>Loading...</p>
      );
    }

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

    if ( history.state && 'ID' in history.state && history.state.ID == automationParam ) {
      /* Edit Automation... */
      return (
        <div className='ptc-completionist-automation-details'>
          <h1>Viewing automation {automationParam}</h1>
          <AutomationDetailsForm automation={history.state} />
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