const { Component } = wp.element;

export class PostSearchSelectInput extends Component {

  constructor(props) {

    /*
    Required Props:
    - (function) onSelectOption(value)
    Optional Props:
    - (string) initialValue
    - (string) initialLabel
    */

    super(props);

    this.state = {
      isLoading: false,
      currentRequest: {},
      textInputHasFocus: false,
      options: [],
      currentValue: '',
      currentLabel: ''
    };

    if ( 'initialValue' in props && props.initialValue ) {
      this.state.currentValue = props.initialValue;
    }

    if ( 'initialLabel' in props && props.initialLabel ) {
      this.state.currentLabel = props.initialLabel;
    }

    this.handleSearchChange = this.handleSearchChange.bind(this);
    this.handleOptionChange = this.handleOptionChange.bind(this);
    this.createSelectOptions = this.createSelectOptions.bind(this);
    this.handleSearchBlur = this.handleSearchBlur.bind(this);

  }//end constructor()

  handleSearchChange(input) {
    if ( input.trim().length >= 3 ) {
      this.setState({
        isLoading: true,
        currentValue: '',
        currentLabel: input,
        options: []
      }, () => {

        let data = {
          'action': 'ptc_get_post_options_by_title',
          'nonce': window.ptc_completionist_automations.nonce,
          'title': this.state.currentLabel,
        };

        let post_search_request = window.jQuery.post(window.ajaxurl, data, (res) => {

          // TODO: Look at using WP REST API: https://developer.wordpress.org/rest-api/reference/search-results/

          this.setState({
            isLoading: false,
            currentRequest: {},
            options: res.data
          });

          // TODO: handle error responses
          // if(res.status == 'success' && res.data != '') {
          //   remove_task_row(data.task_gid);
          //   remove_task_gid(data.task_gid, false);
          // } else if(res.status == 'error' && res.data != '') {
          //   display_alert_html(res.data);
          //   disable_element(thisButton, false);
          //   buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
          // } else {
          //   alert('[Completionist] Error '+res.code+': '+res.message);
          //   disable_element(thisButton, false);
          //   buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
          // }

        }, 'json')
          .fail((jqXHR, exception) => {
            if ( exception != 'abort' ) {
              alert('Failed to search for posts by title.');
              this.setState({
                isLoading: false,
                options: []
              });
            }
          });

        this.setState((state) => {
          if (
            typeof state.currentRequest === 'object'
            && typeof state.currentRequest.abort === 'function'
          ) {
            this.state.currentRequest.abort();
          }
          return {currentRequest: post_search_request};
        });

      });
    } else {
      this.setState({
        isLoading: false,
        currentValue: '',
        currentLabel: input,
        options: []
      });
    }
  }//end handleSearchChange()

  handleOptionChange(value, label) {
    this.setState((state) => ({
      currentValue: value,
      currentLabel: label
    }), () => {
      this.props.onSelectOption(this.state.currentValue);
    });
  }//end handleOptionChange()

  handleSearchBlur() {
    this.setState((state) => ({
      textInputHasFocus: false,
      currentLabel: ( state.currentValue === '' ) ? '' : state.currentLabel
    }));
  }//end handleSearchBlur()

  createSelectOptions() {
    if ( this.state.options.length < 1 ) {
      if ( this.state.isLoading === true ) {
        return <li><i className="fas fa-spinner fa-pulse"></i> Searching for posts...</li>;
      } else if ( this.state.currentLabel.trim().length >= 3 ) {
        return <li>No post results.</li>;
      } else {
        return <li>Enter at least 3 characters to search...</li>;
      }
    }
    return this.state.options.map((post) => (
      <li className='post-option' data-value={post.ID} key={post.ID} onMouseDown={() => this.handleOptionChange(post.ID, post.post_title)}>{post.post_title + ' [' + post.ID + ']'}</li>
    ));
  }//end createSelectOptions()

  componentDidMount() {
    if ( this.state.currentValue.trim() !== '' && this.state.currentLabel.trim() === '' ) {
      this.setState({ currentLabel: '(Loading...)' }, () => {

        let data = {
          'action': 'ptc_get_post_title_by_id',
          'nonce': window.ptc_completionist_automations.nonce,
          'post_id': this.state.currentValue,
        };

        window.jQuery.post(window.ajaxurl, data, (res) => {
          if ( res.status == 'success' && res.data != '' ) {
            this.setState({ currentLabel: res.data });
          } else {
            console.error( 'Failed to load initial PostSearchSelectInput label for initial value.' );
            console.error( res );
            this.setState({ currentLabel: '(Error: Failed to load post title)' });
          }
        }, 'json')
          .fail(() => {
            console.error( 'Failed to load initial PostSearchSelectInput label for initial value.' );
            this.setState({ currentLabel: '(Error: Failed to load post title)' });
          });

      });
    }
  }//end componentDidMount()

  componentDidUpdate(prevProps, prevState) {
    if ( this.state.currentValue !== prevState.currentValue ) {
      this.props.onSelectOption(this.state.currentValue);
    }
  }//end componentDidUpdate()

  render() {

    let selectList = null;
    if ( this.state.textInputHasFocus === true ) {
      const selectOptions = this.createSelectOptions( this.state.options );
      selectList = <ul className='select-options'>{selectOptions}</ul>;
    }

    return (
      <div className='ptc-ajax-search-select-input'>
        <input
          id={this.props.id}
          type='text'
          value={this.state.currentLabel}
          onChange={(e) => this.handleSearchChange(e.target.value)}
          onFocus={() => this.setState({ textInputHasFocus: true })}
          onBlur={() => this.handleSearchBlur()}
        />
        <input type='hidden' value={this.state.currentValue} />
        {selectList}
      </div>
    );
  }//end render()

}//end class PostSearchSelectInput