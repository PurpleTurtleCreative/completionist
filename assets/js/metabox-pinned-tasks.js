jQuery(function($) {

  list_pinned_tasks();

  /* LIST PINNED TASKS */
  function list_pinned_tasks() {

    var taskContainer = $('#task-list');

    if(ptc_completionist_pinned_tasks.pinned_task_gids.length > 0) {

      var post_id = ptc_completionist_pinned_tasks.post_id;

      if(post_id === undefined || post_id < 1) {
        alert('Error: Could not identify the current post. Failed to load tasks.');
        return;
      }

      var total_tasks = ptc_completionist_pinned_tasks.pinned_task_gids.length;
      var completion_count = 0;

      taskContainer.html('<section class="task-loader"><p><i class="fas fa-circle-notch fa-spin"></i>Loading tasks from Asana...</p></section>');
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
            }
          });
      });//end forEach pinned task gid

    } else {
      taskContainer.html('<p><i class="fas fa-clipboard-check"></i>There are no pinned tasks!</p>');
    }

  }//end function list_pinned_tasks()

  function apply_task_list_listeners( task_gid = 0 ) {

    if ( task_gid < 1 ) {
      var parentRow = $('#ptc-completionist_pinned-tasks .ptc-completionist-task');
    } else {
      var parentRow = $('#ptc-completionist_pinned-tasks .ptc-completionist-task[data-task-gid="' + task_gid + '"]');
    }

    /* TOGGLE DESCRIPTION VISIBILITY */
    parentRow.find('button.view-task-notes').on('click', function() {
      $(this).closest('.ptc-completionist-task').find('.description').toggle();
    });//end toggle description

  }//end apply_task_list_listeners()

  /* TOGGLE NEW TASK FORM VISIBILITY */
  $('#ptc-completionist_pinned-tasks #pin-a-task button#toggle-create-new').on('click', function() {
    var thisButton = $(this);
    var newTaskForm = thisButton.closest('#pin-a-task').find('#pin-new-task');
    var formIsVisible = newTaskForm.toggle().is( ":visible" );
    if(formIsVisible) {
      thisButton.siblings('input#asana-task-link-url').prop('disabled', true);
      thisButton.siblings('button#submit-pin-existing').prop('disabled', true);
      thisButton.html('<i class="fas fa-ban"></i>');
      newTaskForm.find('input:first-of-type').focus();
    } else {
      thisButton.siblings('input#asana-task-link-url').prop('disabled', false);
      thisButton.siblings('button#submit-pin-existing').prop('disabled', false);
      thisButton.html('<i class="fas fa-plus"></i>');
      newTaskForm.find(':focusable').blur();
    }
  });//end toggle new task form

  /* PIN EXISTING TASK FROM ASANA TASK LINK */
  $('#ptc-completionist_pinned-tasks #task-toolbar button#submit-pin-existing').on('click', function() {

    var thisButton = $(this);
    thisButton.css('pointer-events', 'none');//disable while currently processing

    var buttonIcon = thisButton.find('i.fas');

    var inputField = thisButton.siblings('input#asana-task-link-url');
    inputField.prop('disabled', true);//disable while currently processing

    var input = inputField.val();
    var post_id = ptc_completionist_pinned_tasks.post_id;

    if(post_id === undefined || post_id < 1) {
      alert('Error: Could not identify the current post. Pinning has been disabled.');
      return false;
    }

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
          buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-thumbtack');
        });

    } else {
      // invalid submission, notify of issue
      alert("Failed to pin existing task. Invalid input.\r\n\r\nPlease provide a copied task link from Asana to pin an existing task. To create a new task to pin, click the green [ + ] button.");
      inputField.prop('disabled', false);
      inputField.val('');
      inputField.focus();
    }

    inputField.prop('disabled', false);
    thisButton.css('pointer-events', 'auto');

  });//end submit pin existing

  function load_task(task_gid, post_id) {

    var data = {
      'action': 'ptc_list_task',
      'nonce': ptc_completionist_pinned_tasks.nonce_list,
      'post_id': post_id,
      'task_gid': task_gid,
    };

    $.post(ajaxurl, data, function(res) {
      if(res.status == 'success' && res.data != '') {
        $('#task-list').append(res.data);
        apply_task_list_listeners(data.task_gid);
      }
    }, 'json');

  }//end load_task()

  function display_alert( note_box_html ) {}

});//end document ready