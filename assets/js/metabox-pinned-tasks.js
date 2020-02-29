jQuery(function($) {

  /* Get globals */
  var metaboxContainer = $('#ptc-completionist_pinned-tasks .inside');

  var taskContainer = metaboxContainer.find('#task-list');
  if(taskContainer.length < 1) {
    return false;
  }

  var pinNewTaskForm = metaboxContainer.find('#pin-new-task');
  if(pinNewTaskForm.length < 1) {
    return false;
  }

  var post_id = ptc_completionist_pinned_tasks.post_id;
  if(post_id === undefined || post_id < 1) {
    alert('[Completionist] Error: Could not identify the current post for task management.');
    return false;
  }

  /* Insert metabox reload button */
  $('<button id="reload-task-list" type="button"><i class="fas fa-sync-alt"></i>Reload</button>')
    .insertAfter('#ptc-completionist_pinned-tasks button.handlediv');
  var reloadButton = $('#ptc-completionist_pinned-tasks button#reload-task-list');

  /* Load task data */
  list_pinned_tasks();

  /* ALERT BANNER DISMISSAL */
  metaboxContainer.on('click', 'div.note-box-dismiss', function() {
    $(this).closest('.note-box').fadeOut(400, function() {
      $(this).remove();
    });
  });

  /* TOGGLE NEW TASK FORM VISIBILITY */
  $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new').on('click', function() {
    var thisButton = $(this);
    var newTaskForm = thisButton.closest('#pin-a-task').find('#pin-new-task');
    var formIsVisible = newTaskForm.toggle().is( ":visible" );
    if(formIsVisible) {
      disable_element(thisButton.siblings('input#asana-task-link-url'), true);
      disable_element(thisButton.siblings('button#submit-pin-existing'), true);
      thisButton.addClass('open');
      thisButton.html('<i class="fas fa-ban"></i>');
      newTaskForm.find(':focusable:first').focus();
    } else {
      disable_element(thisButton.siblings('input#asana-task-link-url'), false);
      disable_element(thisButton.siblings('button#submit-pin-existing'), false);
      thisButton.removeClass('open');
      thisButton.html('<i class="fas fa-plus"></i>');
      newTaskForm.find(':focusable').blur();
    }
  });//end toggle new task form

  /* RELOAD TASK LIST */
  reloadButton.on('click', function() {

    disable_element(reloadButton, true);
    reloadButton.html('<i class="fas fa-sync-alt fa-spin"></i>Loading...');

    var data = {
      'action': 'ptc_get_pins',
      'nonce': ptc_completionist_pinned_tasks.nonce_list,
      'post_id': post_id,
    };

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        ptc_completionist_pinned_tasks.pinned_task_gids = res.data;
        list_pinned_tasks();
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
        reloadButton.html('<i class="fas fa-sync-alt"></i>Reload');
        disable_element(reloadButton, false);
      } else {
        alert('[Completionist] Error '+res.code+': '+res.message);
        reloadButton.html('<i class="fas fa-sync-alt"></i>Reload');
        disable_element(reloadButton, false);
      }

    }, 'json')
      .fail(function() {
        alert('[Completionist] Failed to reload task list.');
        reloadButton.html('<i class="fas fa-sync-alt"></i>Reload');
        disable_element(reloadButton, false);
      });

  });//end reload task list

  /* PIN EXISTING TASK FROM ASANA TASK LINK */
  $('#ptc-completionist_pinned-tasks #task-toolbar button#submit-pin-existing').on('click', function() {

    var thisButton = $(this);
    var buttonIcon = thisButton.find('i.fas');
    var inputField = thisButton.siblings('input#asana-task-link-url');

    disable_element(thisButton, true);
    disable_element(inputField, true);

    var input = inputField.val();

    if(/https:\/\/app\.asana\.com\/.\/[0-9]+\/[0-9]+\/./.test( input )) {

      var data = {
        'action': 'ptc_pin_task',
        'nonce': ptc_completionist_pinned_tasks.nonce_pin,
        'post_id': post_id,
        'task_link': input,
      };

      buttonIcon.removeClass('fa-thumbtack').addClass('fa-circle-notch fa-spin');

      $.post(ajaxurl, data, function(res) {

        if(res.status == 'success' && res.data != '') {
          load_task(res.data, data.post_id);
          inputField.val('');
        } else if(res.status == 'error' && res.data != '') {
          display_alert_html(res.data);
          inputField.val('');
        } else {
          alert('[Completionist] Error '+res.code+': '+res.message);
        }

      }, 'json')
        .fail(function() {
          alert('[Completionist] Failed to pin task.');
        })
        .always(function() {
          disable_element(thisButton, false);
          disable_element(inputField, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
        });

    } else {
      // invalid submission, notify of issue
      alert("[Completionist] Failed to pin existing task. Invalid input.\r\n\r\nPlease provide a copied task link from Asana to pin an existing task. To create a new task to pin, click the green [ + ] button.");
      disable_element(thisButton, false);
      disable_element(inputField, false);
      inputField.val('');
      inputField.focus();
    }

  });//end submit pin existing

  /* CREATE NEW TASK AND PIN TO POST */
  $('#ptc-completionist_pinned-tasks #pin-new-task button#submit-create-new').on('click', function() {

    var thisButton = $(this);
    var inputFields = pinNewTaskForm.find(':input');
    var toggleButton = $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new');

    disable_element(thisButton, true);
    disable_element(inputFields, true);
    disable_element(toggleButton, true);

    var buttonHTML = thisButton.html();

    /* Validate Input */

    var name = pinNewTaskForm.find('#ptc-new-task_name').val();//string,required
    var assignee_gid = pinNewTaskForm.find('#ptc-new-task_assignee').val();//numeric
    var due_on = pinNewTaskForm.find('#ptc-new-task_due_on').val();//yyyy-mm-dd
    var project_gid = pinNewTaskForm.find('#ptc-new-task_project').val();//numeric
    var notes = pinNewTaskForm.find('#ptc-new-task_notes').val();//string

    //TODO: Validate inputs... focus field, display error, and return... if good, remove error

    var data = {
      'action': 'ptc_create_task',
      'nonce': ptc_completionist_pinned_tasks.nonce_create,
      'post_id': post_id,
      'name': name,
      'assignee': assignee_gid,
      'due_on': due_on,
      'project': project_gid,
      'notes': notes,
    };

    thisButton.html('<i class="fas fa-circle-notch fa-spin"></i>Creating task...');

    $.post(ajaxurl, data, function(res) {
      if(res.status == 'success' && res.data != '') {
        $('#ptc-completionist_pinned-tasks #task-list').append(res.data);
        var task_gid = jQuery('#task-list .ptc-completionist-task:first-of-type').data('gid');
        taskContainer.children(':not(.ptc-completionist-task):not(.task-loader)').remove();
        inputFields.val('');
        inputFields.prop('selectedIndex',0);
        $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new').click();
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
      } else {
        alert('[Completionist] Error '+res.code+': '+res.message);
      }
    }, 'json')
      .fail(function() {
        alert('[Completionist] Failed to create task.');
      }).always(function() {
        disable_element(thisButton, false);
        disable_element(inputFields, false);
        disable_element(toggleButton, false);
        thisButton.html(buttonHTML);
      });

  });//end submit pin new

  /* INPUT FIELD KEYPRESS SUBMISSIONS */
  $('#ptc-completionist_pinned-tasks input#asana-task-link-url').on('keypress', function(e) {
    var code = e.keyCode || e.which;
    if(code == 13) {
      e.preventDefault();
      $('#ptc-completionist_pinned-tasks #task-toolbar button#submit-pin-existing').click();
    }
  });//end pin existing task keypress

  $('#ptc-completionist_pinned-tasks #pin-new-task :input:not(button)').on('keypress', function(e) {
    var code = e.keyCode || e.which;
    if(code == 13) {
      e.preventDefault();
      $('#ptc-completionist_pinned-tasks #pin-new-task button#submit-create-new').click();
    }
  });//end create new task keypress

  /* MARK COMPLETE */
  metaboxContainer.on('click', '.ptc-completionist-task[data-gid] button.mark-complete', function() {

    var thisButton = $(this);
    disable_element(thisButton, true);

    var buttonIcon = thisButton.find('i.fas');

    var completed = (thisButton.closest('.ptc-completionist-task').data('completed') === false) ? true : false;

    var data = {
      'action': 'ptc_update_task',
      'nonce': ptc_completionist_pinned_tasks.nonce_update,
      'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
      'completed': completed,
    };

    buttonIcon.removeClass('fa-check').addClass('fa-circle-notch fa-spin');

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        thisButton.closest('.ptc-completionist-task').replaceWith(res.data);
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
      } else {
        alert('[Completionist] Error '+res.code+': '+res.message);
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
      }

    }, 'json')
      .fail(function() {
        alert('[Completionist] Failed to update task.');
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
      });

  });//end submit mark complete

  /* UNPIN TASK FROM POST */
  metaboxContainer.on('click', '.ptc-completionist-task[data-gid] button.unpin-task', function() {

    var thisButton = $(this);
    disable_element(thisButton, true);

    var buttonIcon = thisButton.find('i.fas');

    var data = {
      'action': 'ptc_unpin_task',
      'nonce': ptc_completionist_pinned_tasks.nonce_pin,
      'post_id': post_id,
      'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
    };

    buttonIcon.removeClass('fa-thumbtack').addClass('fa-circle-notch fa-spin');

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        remove_task_row(res.data);
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
      } else {
        alert('[Completionist] Error '+res.code+': '+res.message);
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
      }

    }, 'json')
      .fail(function() {
        alert('[Completionist] Failed to pin task.');
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
      });

  });//end submit unpin task

  /* DELETE TASK */
  metaboxContainer.on('click', '.ptc-completionist-task[data-gid] button.delete-task', function() {

    var thisButton = $(this);
    disable_element(thisButton, true);

    var buttonIcon = thisButton.find('i.fas');

    var data = {
      'action': 'ptc_delete_task',
      'nonce': ptc_completionist_pinned_tasks.nonce_delete,
      'post_id': post_id,
      'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
    };

    buttonIcon.removeClass('fa-minus').addClass('fa-circle-notch fa-spin');

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        remove_task_row(res.data);
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-minus');
      } else {
        alert('[Completionist] Error '+res.code+': '+res.message);
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-minus');
      }

    }, 'json')
      .fail(function() {
        alert('[Completionist] Failed to pin task.');
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-minus');
      });

  });//end submit delete task

  /* -------- HELPERS -------- */

  function list_pinned_tasks() {

    if(ptc_completionist_pinned_tasks.pinned_task_gids.length > 0) {

      var total_tasks = ptc_completionist_pinned_tasks.pinned_task_gids.length;
      var completion_count = 0;

      taskContainer.html('<p class="task-loader"><i class="fas fa-circle-notch fa-spin"></i>Loading tasks from Asana...</p>');

      disable_element(reloadButton, true);
      reloadButton.html('<i class="fas fa-sync-alt fa-spin"></i>Loading...');

      ptc_completionist_pinned_tasks.pinned_task_gids.forEach(function (task_gid) {

        var data = {
          'action': 'ptc_list_task',
          'nonce': ptc_completionist_pinned_tasks.nonce_list,
          'post_id': post_id,
          'task_gid': task_gid,
        };

        $.post(ajaxurl, data, function(res) {
          if(res.status == 'success' && res.data != '') {
            $(res.data).insertBefore('#task-list .task-loader');
          } else if(res.status == 'error' && res.data != '') {
            display_alert_html(res.data);
          }
        }, 'json')
          .always(function() {
            ++completion_count;
            if(completion_count === total_tasks) {
              $('#task-list .task-loader').remove();
              reloadButton.html('<i class="fas fa-sync-alt"></i>Reload');
              disable_element(reloadButton, false);
            }
            if(taskContainer.html() == '') {
              taskContainer.html('<p class="nothing-to-see"><i class="fas fa-eye-slash"></i>There are no visible tasks!</p>');
            }
          });
      });//end forEach pinned task gid

    } else {
      taskContainer.html('');
      display_if_empty_list();
    }

  }//end function list_pinned_tasks()

  function load_task(task_gid, post_id) {

    var data = {
      'action': 'ptc_list_task',
      'nonce': ptc_completionist_pinned_tasks.nonce_list,
      'post_id': post_id,
      'task_gid': task_gid,
    };

    $.post(ajaxurl, data, function(res) {
      if(res.status == 'success' && res.data != '') {
        taskContainer.append(res.data);
        taskContainer.children(':not(.ptc-completionist-task):not(.task-loader)').remove();
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
      }
    }, 'json');

  }//end load_task()

  function remove_task_row(task_gid) {
    $('#ptc-completionist_pinned-tasks .ptc-completionist-task[data-gid="' + task_gid + '"]')
      .fadeOut(800, function() {
        $(this).remove();
        display_if_empty_list();
      });
  }//end remove_task_row()

  function display_if_empty_list() {
    if(taskContainer.html().trim() == '') {
      taskContainer.html('<p><i class="fas fa-clipboard-check"></i>There are no pinned tasks!</p>');
    }
  }//end display_if_empty_list()

  function disable_element(jquery_obj, if_disable = true) {
    if(if_disable) {
      jquery_obj.css('pointer-events', 'none');
      jquery_obj.prop('disabled', true);
    } else {
      jquery_obj.css('pointer-events', 'auto');
      jquery_obj.prop('disabled', false);
    }
  }//end disable_element()

  function display_alert_html(note_box_html) {
    var alertBanner = $(note_box_html);
    alertBanner.insertAfter('#ptc-completionist_pinned-tasks #pin-a-task');
  }//end display_alert_html()

});//end document ready