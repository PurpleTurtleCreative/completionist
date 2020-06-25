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

          /* Automation Object Structure follows \PTC_Completionist\Automations\Data::save_automation() */
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
              {
                ID: 223,
                title: 'Donor Members Listing',
                description: 'This automation does not actually exist in the database and is only for frontend testing purposes.',
                hook_name: 'user_register',
                last_modified: '2020/06/11 14:16',
                conditions: [
                  {
                    ID: 224,
                    property: 'first_name',
                    comparison_method: 'is filled',
                    value: ''
                  },
                  {
                    ID: 226,
                    property: 'last_name',
                    comparison_method: 'is filled',
                    value: ''
                  }
                ],
                actions: [
                  {
                    ID: 225,
                    action: 'create_task',
                    triggered_count: 17,
                    last_triggered: '2020/06/19 14:20',
                    meta: {
                      task_author: 1,
                      name: 'Verify and add donor to members listing'
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