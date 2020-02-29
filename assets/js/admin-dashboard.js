jQuery(function($) {

  var selectWorkspace = $('section#ptc-asana-workspace select#asana-workspace');
  var assignedWorkspaceValue = selectWorkspace.val();

  /* ALERT WORKSPACE CHANGE */
  selectWorkspace.on('change', function() {
    if($(this).val() !== assignedWorkspaceValue) {
      $(this).closest('form').find('p.warning-note').show();
    } else {
      $(this).closest('form').find('p.warning-note').hide();
    }
  });//end alert workspace change

  /* -------- HELPERS -------- */

  function disable_element(jquery_obj, if_disable = true) {
    if(if_disable) {
      jquery_obj.css('pointer-events', 'none');
      jquery_obj.prop('disabled', true);
    } else {
      jquery_obj.css('pointer-events', 'auto');
      jquery_obj.prop('disabled', false);
    }
  }//end disable_element()

});//end document ready