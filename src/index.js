import { AutomationsListing } from './components/AutomationsListing.js';

jQuery(function($) {
  try {

    var rootNode = document.getElementById('ptc-completionist-automations-root');
    if ( rootNode !== null ) {

      const { render, Component } = wp.element;

      class PTCCompletionist_Automations extends Component {

        constructor(props) {

          super(props);
          this.state = {
            automations: [
              { id: 123 },
              { id: 790 },
              { id: 270 },
              { id: 315 },
              { id: 147 },
            ]
          };

          this.goToAutomation = ( automationId = 0 ) => {

            if ( automationId <= 0 ) {
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

            $(rootNode).slideToggle( 400, () => {
              this.forceUpdate();
              $(rootNode).slideToggle( 400 );
            });

          };//end goToAutomation()

        }//end constructor()

        componentDidMount() {
          window.addEventListener( 'popstate', this.goToAutomation );
        }

        render() {
          let queryParams = new URLSearchParams( location.search );
          if ( queryParams.get('automation') > 0 ) {
            /* Edit Automation data... */
            return (
              <div className='ptc-completionist-automation-details'>
                <h1>Viewing automation {queryParams.get('automation')}</h1>
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