import { AutomationsListing } from './AutomationsListing.js';
import { AutomationDetailsForm } from './AutomationDetailsForm.js';

const { Component } = wp.element;

export default class PTCCompletionistAutomations extends Component {

  constructor(props) {

    super(props);

    this.state = {
      automations: window.ptc_completionist_automations.automations,
      isLoading: false
    };

    this.goToAutomation = this.goToAutomation.bind(this);
    this.deleteAutomation = this.deleteAutomation.bind(this);

  }//end constructor()

  goToAutomation( automationId = 0 ) {

    this.setState({ isLoading: true }, () => {
      if ( automationId === 'new' ) {

        let queryParams = new URLSearchParams( location.search );
        queryParams.set( 'automation', automationId );
        history.pushState(
          { ID: 'new' },
          'Completionist – Add New Automation',
          '?' + queryParams.toString()
        );
        document.title = 'Completionist – Add New Automation';
        this.setState({ isLoading: false });

      } else if ( isNaN( parseInt( automationId ) ) || automationId <= 0 ) {

        let data = {
          '_wpnonce': window.ptc_completionist_automations.api.auth_nonce,
          'nonce': window.ptc_completionist_automations.api.nonce_get,
          'order_by': 'title',
          'return_html': true,
        };

        window.jQuery.getJSON(`${window.ptc_completionist_automations.api.v1}/automations`, data, (res) => {

          if ( res.status == 'success' && typeof res.data == 'object' ) {
            let queryParams = new URLSearchParams( location.search );
            queryParams.delete('automation');
            history.pushState(
              { ID: 0 },
              'Completionist – Automations',
              '?' + queryParams.toString()
            );
            document.title = 'Completionist – Automations';
            this.setState({
              automations: res.data?.automation_overviews ?? [],
              isLoading: false
            });
          } else {
            // TODO: Fix infinite requests when error
            console.error( res );
            this.setState({ isLoading: false });
          }

        })
          .fail(() => {
            console.error( 'Failed to load automation overviews.' );
            let queryParams = new URLSearchParams( location.search );
            queryParams.delete('automation');
            history.pushState(
              { ID: 0 },
              'Completionist – Automations',
              '?' + queryParams.toString()
            );
            document.title = 'Completionist – Automations';
            this.setState({ isLoading: false });
          });

      } else {

        let data = {
          '_wpnonce': window.ptc_completionist_automations.api.auth_nonce,
          'nonce': window.ptc_completionist_automations.api.nonce_get
        };

        window.jQuery.getJSON(`${window.ptc_completionist_automations.api.v1}/automations/${automationId}`, data, (res) => {

          if ( res.status == 'success' && typeof res.data == 'object' && res.data?.automation ) {
            let docTitle = 'Completionist – Automation ' + res.data.automation.ID + ' – ' + res.data.automation.title;
            let queryParams = new URLSearchParams( location.search );
            queryParams.set( 'automation', automationId );
            history.pushState(
              res.data.automation,
              docTitle,
              '?' + queryParams.toString()
            );
            document.title = docTitle;
            this.setState({ isLoading: false });
          } else {
            console.error( res );
            this.goToAutomation();
          }

        })
          .fail(() => {
            console.error( 'Failed to get data for automation ' + automationId );
            this.goToAutomation();
          });
      }
    });
  };//end goToAutomation()

  deleteAutomation( automationId, callback ) {

    window.jQuery
      .ajax({
        'method': 'DELETE',
        'url': `${window.ptc_completionist_automations.api.v1}/automations/${automationId}`,
        'headers': {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.ptc_completionist_automations.api.auth_nonce
        },
        'contentType': 'application/json',
        'data': JSON.stringify({
          'nonce': window.ptc_completionist_automations.api.nonce_delete
        }),
        'dataType': 'json',
      })
      .done((res) => {

        if (
          'success' == res?.status
          && 200 == res?.code
          && res?.data?.automation_id
        ) {
          // TODO: display success message in notice section
          console.log( res.message );
          this.setState((state) => ({
            automations: state.automations.filter((automation) => automation.ID !== res.data.automation_id)
          }));
        } else {
          // TODO: display error messages in notice section
          if ( res.message && res.code ) {
            alert( 'Error ' + res.code + '. The automation could not be deleted. ' + res.message);
          } else {
            alert( 'Error. The automation could not be deleted.' );
          }
        }

        typeof callback === 'function' && callback( res );

      })
      .fail(() => {
        // TODO: display error messages in notice section
        alert( 'Error 500. The automation could not be deleted.' );
        typeof callback === 'function' && callback();
      });

  }//end deleteAutomation()

  componentDidMount() {

    /* Go to requested automation */
    let queryParams = new URLSearchParams( location.search );
    const automationParam = queryParams.get('automation');
    if ( automationParam !== null ) {
      this.goToAutomation( automationParam );
    }

    /* Listen to browser history events */
    window.addEventListener( 'popstate', (e) => {
      // TODO: goToAutomation calls pushState which breaks history navigation
      if ( 'state' in e && e.state && 'ID' in e.state ) {
        this.goToAutomation( e.state.ID )
      } else {
        this.goToAutomation();
      }
    });

  }//end componentDidMount()

  render() {

    if ( this.state.isLoading ) {
      return (
        <div className='loading-screen'>
          <p><i className='fas fa-spinner fa-pulse fa-lg'></i> Loading...</p>
        </div>
      );
    }

    let queryParams = new URLSearchParams( location.search );
    const automationParam = queryParams.get('automation');
    if ( automationParam === 'new' ) {
      /* Add Automation... */
      return (
        <div className='ptc-completionist-automation-create'>
          <header>
            <button onClick={() => this.goToAutomation()}><i className="fas fa-angle-left"></i> Back</button>
            <h1>New Automation</h1>
            <div className='spacer'></div>
          </header>
          <AutomationDetailsForm goToAutomation={this.goToAutomation} />
        </div>
      );
    }

    if ( history.state && 'ID' in history.state && history.state.ID == automationParam ) {
      /* Edit Automation... */
      return (
        <div className='ptc-completionist-automation-details'>
          <header>
            <button onClick={() => this.goToAutomation()}><i className="fas fa-angle-left"></i> Back</button>
            <h1>Edit Automation {history.state.ID}</h1>
            <div className='spacer'></div>
          </header>
          <AutomationDetailsForm automation={history.state} goToAutomation={this.goToAutomation} />
        </div>
      );
    } else {
      /* List Automations... */
      return (
        <AutomationsListing
          automations={this.state.automations}
          goToAutomation={this.goToAutomation}
          deleteAutomation={this.deleteAutomation}
        />
      );
    }
  }//end render()

}//end class PTCCompletionist_Automations
