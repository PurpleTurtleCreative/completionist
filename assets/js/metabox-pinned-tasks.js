jQuery(function($) {

  /* PIN EXISTING TASK FROM ASANA TASK LINK */
  $('#ptc-completionist_pinned-tasks #pin-existing-task button#submit-pin-existing').on('click', function() {

    var thisButton = $(this);
    thisButton.css('pointer-events', 'none');//disable while currently processing

    var buttonIcon = thisButton.find('i.fas');

    var inputField = thisButton.siblings('input#asana-task-link-url');
    inputField.prop('disabled', true);//disable while currently processing

    var input = inputField.val();
    // var post_id = $('input#post_ID').val();
    var post_id = ptc_completionist_pinned_tasks.post_id;

    if(post_id === undefined || post_id < 1) {
      alert('Error: Could not identify the current post. Pinning has been disabled.');
      return false;
    }

    if(/https:\/\/app\.asana\.com\/.\/[0-9]+\/[0-9]+\/./.test( input )) {

      var data = {
        'action': 'ptc_pin_task',
        'nonce': ptc_completionist_pinned_tasks.nonce,
        'post_id': post_id,
        'task_link': input,
      };

      buttonIcon.removeClass('fa-thumbtack').addClass('fa-circle-notch fa-spin');

      $.post(ajaxurl, data, function(res) {

        if(res.status == 'success') {
          // TODO: trigger reloading task list
          alert('Woohoo!');
          inputField.val('');
        } else {
          alert(res.data);
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
      alert("Failed to pin existing task. Invalid input.\r\n\r\nPlease provide a copied task link from Asana to pin an existing task. To create a new task to pin, click the green [ + New Task ] button.");
      inputField.prop('disabled', false);
      inputField.val('');
      inputField.focus();
    }

    inputField.prop('disabled', false);
    thisButton.css('pointer-events', 'auto');

  });//end submit pin existing

});