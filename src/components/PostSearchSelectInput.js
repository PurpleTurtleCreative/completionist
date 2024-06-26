const { Component } = wp.element;

export class PostSearchSelectInput extends Component {

  constructor(props) {

    /*
    Required Props:
    - (function) onSelectOption(value)
    Optional Props:
    - (string) initialValue
    - (string) initialLabel
    - (object[]) suggestedOptions [ { "value": string, "label": string } ]
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

    if ( 'suggestedOptions' in props && props.suggestedOptions ) {
      this.state.suggestedOptions = props.suggestedOptions;
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
          '_wpnonce': window.ptc_completionist_automations.api.auth_nonce,
          'nonce': window.ptc_completionist_automations.api.nonce_get_post,
          'like': `%${this.state.currentLabel}%`,
          'limit': 20,
          'offset': 0,
        };

        let post_search_request = window.jQuery.getJSON(`${window.ptc_completionist_automations.api.v1}/posts/where-title-like`, data, (res) => {
          if ( 'success' === res?.status && res?.data?.posts ) {
            this.setState({
              isLoading: false,
              currentRequest: {},
              options: res.data.posts
            });
          } else if ( res?.message ) {
            throw res.message;
          } else {
            throw 'unknown error';
          }
        })
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
        return <li>No post results. { ( this.state?.suggestedOptions?.length > 0 ) && "Clear your search to see suggested options." }</li>;
      } else if ( this.state?.suggestedOptions?.length > 0 ) {
        return (
          <>
            <li>Choose an option below or enter at least 3 characters to search...</li>
            {
              this.state.suggestedOptions.map((option) => (
                <li className='post-option' data-value={option.value} key={option.value} onMouseDown={() => this.handleOptionChange(option.value, option.label)}>{option.label}</li>
              ))
            }
          </>
        );
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

        if (
          Number.isNaN( parseFloat( this.state.currentValue ) ) ||
          ! Number.isFinite( this.state.currentValue )
        ) {
          // Not a numeric post ID value, so just display the value itself.
          this.setState({ currentLabel: this.state.currentValue });
          return;
        }

        let data = {
          '_wpnonce': window.ptc_completionist_automations.api.auth_nonce,
          'nonce': window.ptc_completionist_automations.api.nonce_get_post,
          'post_fields': [ 'post_title' ]
        };

        window.jQuery.getJSON(`${window.ptc_completionist_automations.api.v1}/posts/${this.state.currentValue}`, data, (res) => {
          if ( 'success' === res?.status && res?.data?.post?.post_title ) {
            this.setState({ currentLabel: res.data.post.post_title });
          } else {
            console.error( 'Failed to load initial PostSearchSelectInput label for initial value.' );
            console.error( res );
            this.setState({ currentLabel: `${this.state.currentValue} - [Error] Failed to load post title` });
          }
        })
          .fail(() => {
            console.error( 'Failed to load initial PostSearchSelectInput label for initial value.' );
            this.setState({ currentLabel: `${this.state.currentValue} - [Error] Failed to load post title` });
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
