import { AutomationsListing } from './components/AutomationsListing.js';
import { AutomationDetailsForm } from './components/AutomationDetailsForm.js';

jQuery(function($) {
  try {

    var rootNode = document.getElementById('ptc-completionist-automations-root');
    if ( rootNode !== null ) {

      const { render, Component } = wp.element;

      class PTCCompletionist_Automations extends Component {

        constructor(props) {

          super(props);

          /*
          Automation Object Structure:
          - ID
          - title
          - description
          - hook_name (event)
          - last_modified
          - conditions[]
            - ID
            - property
            - comparison_method
            - value
          - actions[]
            - ID
            - action
            - triggered_count
            - last_triggered
            - meta[]
              - ...
          */
          this.state = {
            automations: [
              {
                ID: 123,
                title: 'Sample Automation',
                description: 'This automation does not actually exist in the database and is only for frontend testing purposes.',
                hook_name: 'post_updated',
                last_modified: '2020/06/11 14:16',
                conditions: [
                  {
                    ID: 124,
                    property: 'post_status',
                    comparison_method: 'equals',
                    value: 'publish'
                  },
                  {
                    ID: 126,
                    property: 'post_type',
                    comparison_method: 'equals',
                    value: 'post'
                  }
                ],
                actions: [
                  {
                    ID: 125,
                    action: 'create_task',
                    triggered_count: 79,
                    last_triggered: '2020/06/11 14:20',
                    meta: {
                      task_author: 1,
                      name: 'Finish coding Automations frontend with ReactJS'
                    }
                  }
                ]
              },
            ]
          };

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
          if ( queryParams.get('automation') === 'new' ) {
            /* Add Automation... */
            return (
              <div className='ptc-completionist-automation-create'>
                <h1>New Automation</h1>
                <AutomationDetailsForm />
                <button onClick={() => this.goToAutomation()}>Back</button>
              </div>
            );
          } else if ( queryParams.get('automation') > 0 ) {
            /* Edit Automation... */
            return (
              <div className='ptc-completionist-automation-details'>
                <h1>Viewing automation {queryParams.get('automation')}</h1>
                <AutomationDetailsForm automation={this.state.automations[0]} />
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