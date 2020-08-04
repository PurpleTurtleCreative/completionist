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
        <header>
          <h2 title={'Automation ID: '+this.state.ID} onClick={() => this.goToAutomation(this.state.ID)}>{this.state.title}</h2>
          <p className='last-modified'><em>Updated {this.state.last_modified}</em></p>
          { this.state.description.length > 0 &&
            <p className='description' dangerouslySetInnerHTML={{__html: this.state.description}} />
          }
          <div className='automation-actions'>
            <button className='edit' onClick={() => this.goToAutomation(this.state.ID)}><i className='fas fa-pen'></i> Edit</button>
            <button className='delete' onClick={this.deleteAutomation} disabled={this.state.isDeleting}><i className='fas fa-trash'></i> Delete</button>
          </div>
        </header>
        <ul>
          <li title={this.state.total_conditions + ' Conditions'}>{this.state.total_conditions}</li>
          <li title={this.state.total_actions + ' Actions'}>{this.state.total_actions}</li>
          <li title={'Triggered ' + this.state.total_triggered + ' times'}>{this.state.total_triggered}</li>
          <li title={'Last Triggered ' + this.state.last_triggered}>{this.state.last_triggered}</li>
        </ul>
      </div>
    );
  }//end render()

}//end class AutomationsListing