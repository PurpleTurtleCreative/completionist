jQuery(function($) {

  var workspaceFormInputs = $('section#ptc-asana-workspace form :input');

  /* SELECT WORKSPACE */
  var selectWorkspace = $('section#ptc-asana-workspace select#asana-workspace');
  var workspaceWarningNote = selectWorkspace.closest('form').find('#asana-workspace-warning');

  selectWorkspace.on('change', function() {
    load_tag_options(selectWorkspace.val());
    if(selectWorkspace.val() != ptc_completionist_dashboard.saved_workspace_gid) {
      workspaceWarningNote.show();
    } else {
      workspaceWarningNote.hide();
    }
    $('section#ptc-asana-workspace form p.error-note').remove();
  });
  //end select workspace

  /* SELECT SITE TAG */
  var selectTag = $('section#ptc-asana-workspace select#asana-tag');
  var selectTagLabelHTML = $('section#ptc-asana-workspace form label[for="asana-tag"]').html();
  var inputTagName = $('section#ptc-asana-workspace input#asana-tag-name');
  var tagWarningNote = selectWorkspace.closest('form').find('#asana-tag-warning');

  inputTagName.hide();

  selectTag.on('change', function() {
    inputTagName.hide();
    if(selectTag.val() != ptc_completionist_dashboard.saved_tag_gid) {
      tagWarningNote.show();
      if(selectTag.val() === 'create' ) {
        inputTagName.show();
        inputTagName.focus();
      }
    } else {
      tagWarningNote.hide();
    }
    $('section#ptc-asana-workspace form p.error-note').remove();
  });

  load_tag_options(selectWorkspace.val());

  function load_tag_options(workspace_gid) {

    disable_element(selectTag, true);
    $('section#ptc-asana-workspace form label[for="asana-tag"]').html('<i class="fas fa-circle-notch fa-spin">');

    selectTag.find('option:not(:first-of-type):not([value="create"])').remove();

    if ( workspace_gid == '' ) {
      $('section#ptc-asana-workspace form label[for="asana-tag"]').html(selectTagLabelHTML);
      return;
    }

    var data = {
      'action': 'ptc_get_tag_options',
      'nonce': ptc_completionist_dashboard.nonce,
      'workspace_gid': workspace_gid,
    };

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success') {
        if(res.data != '') {
          selectTag.append(res.data);
          if(ptc_completionist_dashboard.saved_tag_gid) {
            selectTag.find('option[value="'+ptc_completionist_dashboard.saved_tag_gid+'"]').prop('selected', true);
          }
        }
        disable_element(selectTag, false);
      } else if(res.status == 'error' && res.data != '') {
        display_alert_note_after(res.data, tagWarningNote);
      } else {
        alert('Failed to load tag options.');
      }

    }, 'json')
      .fail(function() {
        alert('Failed to load tag options.');
      })
      .always(function() {
        $('section#ptc-asana-workspace form label[for="asana-tag"]').html(selectTagLabelHTML);
      });

  }
  //end select site tag

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

  function display_alert_note_after(alert_note, jquery_obj) {
    $('<p class="error-note"><i class="fas fa-exclamation-circle"></i>'+alert_note+'</p>')
      .insertAfter(jquery_obj);
  }

});//end document ready