import { PTCCompletionist_Automations } from './components/PTCCompletionist_Automations.js';

const { render, Component } = wp.element;

jQuery(function($) {
  try {
    var rootNode = document.getElementById('ptc-completionist-automations-root');
    if ( rootNode !== null ) {
      render( <PTCCompletionist_Automations />, rootNode );
    }//end if rootNode
  } catch ( e ) {
    console.error( e );
  }
});