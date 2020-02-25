jQuery(function($) {

  /* Get globals */
  var taskContainer = $('#ptc-completionist_pinned-tasks #task-list');
  var post_id = ptc_completionist_pinned_tasks.post_id;
  if(post_id === undefined || post_id < 1) {
    alert('Error: Could not identify the current post for task management.');
    return false;
  }

  /* Insert metabox reload button */
  $('<button id="reload-task-list" type="button"><i class="fas fa-sync-alt"></i>Reload</button>')
    .insertAfter('#ptc-completionist_pinned-tasks button.handlediv');
  var reloadButton = $('#ptc-completionist_pinned-tasks button#reload-task-list');

  /* Load task data */
  list_pinned_tasks();

  /* TOGGLE NEW TASK FORM VISIBILITY */
  $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new').on('click', function() {
    var thisButton = $(this);
    var newTaskForm = thisButton.closest('#pin-a-task').find('#pin-new-task');
    var formIsVisible = newTaskForm.toggle().is( ":visible" );
    if(formIsVisible) {
      disable_element(thisButton.siblings('input#asana-task-link-url'), true);
      disable_element(thisButton.siblings('button#submit-pin-existing'), true);
      thisButton.html('<i class="fas fa-ban"></i>');
      newTaskForm.find('input:first-of-type').focus();
    } else {
      disable_element(thisButton.siblings('input#asana-task-link-url'), false);
      disable_element(thisButton.siblings('button#submit-pin-existing'), false);
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
      } else {
        alert('Error '+res.code+': '+res.message);
        reloadButton.html('<i class="fas fa-sync-alt"></i>Reload');
        disable_element(reloadButton, false);
      }

    }, 'json')
      .fail(function() {
        alert('Failed to reload task list.');
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
        } else {
          alert('Error '+res.code+': '+res.message);
        }

      }, 'json')
        .fail(function() {
          alert('Failed to pin task.');
        })
        .always(function() {
          disable_element(thisButton, false);
          disable_element(inputField, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
        });

    } else {
      // invalid submission, notify of issue
      alert("Failed to pin existing task. Invalid input.\r\n\r\nPlease provide a copied task link from Asana to pin an existing task. To create a new task to pin, click the green [ + ] button.");
      disable_element(thisButton, false);
      disable_element(inputField, false);
      inputField.val('');
      inputField.focus();
    }

  });//end submit pin existing

  /* CREATE NEW TASK AND PIN TO POST */
  $('#ptc-completionist_pinned-tasks #pin-new-task button#submit-create-new').on('click', function() {

    var thisButton = $(this);
    var inputFields = thisButton.siblings(':input');
    var toggleButton = $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new');

    disable_element(thisButton, true);
    disable_element(inputFields, true);
    disable_element(toggleButton, true);

    var buttonHTML = thisButton.html();

    /* Validate Input */

    var name = thisButton.siblings('#ptc-new-task_name').val();//string,required
    var assignee_gid = thisButton.siblings('#ptc-new-task_assignee').val();//numeric
    var due_on = thisButton.siblings('#ptc-new-task_due_on').val();//yyyy-mm-dd
    var project_gid = thisButton.siblings('#ptc-new-task_project').val();//numeric
    var notes = thisButton.siblings('#ptc-new-task_notes').val();//string

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
        //TODO: Check for code == 201, task was created but not pinned
        $('#ptc-completionist_pinned-tasks #task-list').prepend(res.data);
        var task_gid = jQuery('#task-list .ptc-completionist-task:first-of-type').data('gid');
        apply_task_list_listeners(task_gid);
        taskContainer.children(':not(.ptc-completionist-task):not(.task-loader)').remove();
        thisButton.siblings(':input').val('');
        thisButton.siblings('select').prop('selectedIndex',0);
        $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new').click();
      } else {
        alert('Error '+res.code+': '+res.message);
      }
    }, 'json')
      .fail(function() {
        alert('Failed to create task.');
      }).always(function() {

        disable_element(thisButton, false);
        disable_element(inputFields, false);
        disable_element(toggleButton, false);

        thisButton.html(buttonHTML);

      });

  });//end submit pin new

  /* -------- HELPERS -------- */

  function list_pinned_tasks() {

    if(ptc_completionist_pinned_tasks.pinned_task_gids.length > 0) {

      var total_tasks = ptc_completionist_pinned_tasks.pinned_task_gids.length;
      var completion_count = 0;

      taskContainer.html('<section class="task-loader"><p><i class="fas fa-circle-notch fa-spin"></i>Loading tasks from Asana...</p></section>');

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
          if(res.data != '') {
            $(res.data).insertBefore('#task-list .task-loader');
            apply_task_list_listeners(data.task_gid);
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
              taskContainer.html('<p><i class="fas fa-eye-slash"></i>There are no visible tasks!</p>');
            }
          });
      });//end forEach pinned task gid

    } else {
      taskContainer.html('');
      display_if_empty_list();
    }

  }//end function list_pinned_tasks()

  function apply_task_list_listeners( task_gid = 0 ) {

    if ( task_gid < 1 ) {
      var parentRow = $('#ptc-completionist_pinned-tasks .ptc-completionist-task');
    } else {
      var parentRow = $('#ptc-completionist_pinned-tasks .ptc-completionist-task[data-gid="' + task_gid + '"]');
    }

    /* TOGGLE DESCRIPTION VISIBILITY */
    parentRow.find('button.view-task-notes').on('click', function() {
      parentRow.find('.description').toggle();
    });//end toggle description

    /* MARK COMPLETE */
    parentRow.find('button.mark-complete').on('click', function() {

      var thisButton = $(this);
      disable_element(thisButton, true);

      var buttonIcon = thisButton.find('i.fas');

      var completed = (parentRow.data('completed') === false) ? true : false;

      var data = {
        'action': 'ptc_update_task',
        'nonce': ptc_completionist_pinned_tasks.nonce_update,
        'task_gid': parentRow.data('gid'),
        'completed': completed,
      };

      buttonIcon.removeClass('fa-check').addClass('fa-circle-notch fa-spin');

      $.post(ajaxurl, data, function(res) {

        if(res.status == 'success' && res.data != '') {
          parentRow.replaceWith(res.data);
          apply_task_list_listeners(data.task_gid);
        } else {
          alert('Error '+res.code+': '+res.message);
          disable_element(thisButton, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
        }

      }, 'json')
        .fail(function() {
          alert('Failed to update task.');
          disable_element(thisButton, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-check');
        });

    });//end submit mark complete

    /* UNPIN TASK FROM POST */
    parentRow.find('button.unpin-task').on('click', function() {

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
        } else {
          alert('Error '+res.code+': '+res.message);
          disable_element(thisButton, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
        }

      }, 'json')
        .fail(function() {
          alert('Failed to pin task.');
          disable_element(thisButton, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
        });

    });//end submit unpin task

    /* DELETE TASK */
    parentRow.find('button.delete-task').on('click', function() {

      var thisButton = $(this);
      disable_element(thisButton, true);

      var buttonIcon = thisButton.find('i.fas');

      var data = {
        'action': 'ptc_delete_task',
        'nonce': ptc_completionist_pinned_tasks.nonce_delete,
        'post_id': post_id,
        'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
      };

      buttonIcon.removeClass('fa-trash-alt').addClass('fa-circle-notch fa-spin');

      $.post(ajaxurl, data, function(res) {

        if(res.status == 'success' && res.data != '') {
          remove_task_row(res.data);
        } else {
          alert('Error '+res.code+': '+res.message);
          disable_element(thisButton, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-trash-alt');
        }

      }, 'json')
        .fail(function() {
          alert('Failed to pin task.');
          disable_element(thisButton, false);
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-trash-alt');
        });

    });//end submit delete task

  }//end apply_task_list_listeners()

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
        apply_task_list_listeners(data.task_gid);
        taskContainer.children(':not(.ptc-completionist-task):not(.task-loader)').remove();
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

  // function display_alert_html( note_box_html ) {}

  // function display_alert( status, message ) {}

});//end document ready