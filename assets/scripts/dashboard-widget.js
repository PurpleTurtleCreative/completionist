jQuery(function($) {

  /* Get globals */
  var metaboxContainer = $('#ptc-completionist_site-tasks .inside');

  var taskContainer = metaboxContainer.find('#ptc-asana-task-list');
  if(taskContainer.length < 1) {
    return;
  }

  var paginationNavContainer = metaboxContainer.find('#ptc-asana-tasks-pagination');
  if(paginationNavContainer.length < 1) {
    return;
  }

  try {
    ptc_completionist_dashboard_widget;
    var page_size = ptc_completionist_dashboard_widget.page_size;
    var current_page = ptc_completionist_dashboard_widget.current_page;
    var current_category = ptc_completionist_dashboard_widget.current_category;
    var current_task_gids = metaboxContainer.find('header button#'+current_category).data('category-task-gids');
  } catch(e) {
    console.error(e);
    return;
  }

  refresh_site_tasks_progress();
  display_if_empty_list();
  lock_interface(false);

  /* ALERT BANNER DISMISSAL */
  metaboxContainer.on('click', 'div.note-box-dismiss', function() {
    $(this).closest('.note-box').fadeOut(400, function() {
      $(this).remove();
    });
  });

  /* TOGGLE DESCRIPTION VISIBILITY */
  taskContainer.on('click', '.ptc-completionist-task .body .name', function() {
    $this_task = $(this).closest('.ptc-completionist-task');
    if ( $this_task.hasClass('open') ) {
      // Is open, so close it.
      $this_task.removeClass('open');
      $this_task.find('.name i.fa-sticky-note').removeClass('fas').addClass('far');
      $this_task.find('.description').hide();
    } else {
      // Is not open, so open it.
      $this_task.addClass('open');
      $this_task.find('.name i.fa-sticky-note').removeClass('far').addClass('fas');
      $this_task.find('.description').show();
    }
  });//end toggle description

  /* PREV PAGE */
  paginationNavContainer.on('click', 'button[data-page=prev]', function() {
    if(current_page > 1) {
      --current_page;
      list_current_page_tasks();
    }
  });

  /* NEXT PAGE */
  paginationNavContainer.on('click', 'button[data-page=next]', function() {
    if(current_page < calculate_page_final_index()) {
      ++current_page;
      list_current_page_tasks();
    }
  });

  /* SELECT PAGE */
  paginationNavContainer.on('click', 'button.page-option', function() {
    var chosen_page = $(this).data('page');
    if(chosen_page < calculate_page_final_index() && chosen_page > 0) {
      current_page = chosen_page;
      list_current_page_tasks();
    }
  });

  /* SELECT CATEGORY */
  metaboxContainer.on('click', 'header button[data-category-task-gids]', function() {
    var chosen_category = $(this).attr('id');
    if(chosen_category !== current_category) {
      metaboxContainer.find('header button[data-category-task-gids]').attr('data-viewing-tasks', false);
      $(this).attr('data-viewing-tasks', true);
      current_page = 1;
      current_category = chosen_category;
      current_task_gids = $(this).data('category-task-gids');
      load_pagination_navigation();
      list_current_page_tasks();
    }
  });

  /* MARK COMPLETE */
  metaboxContainer.on('click', '.ptc-completionist-task[data-gid] button.mark-complete', function() {

    var thisButton = $(this);
    disable_element(thisButton, true);

    var buttonIcon = thisButton.find('i.fas');

    var completed = (thisButton.closest('.ptc-completionist-task').data('completed') === false) ? true : false;

    var data = {
      'action': 'ptc_update_task',
      'nonce': ptc_completionist_dashboard_widget.nonce_update,
      'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
      'completed': completed,
    };

    buttonIcon.removeClass('fa-check').addClass('fa-circle-notch fa-spin');

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        remove_task_row(data.task_gid);
        remove_task_gid(data.task_gid, false);
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

  /* UNPIN TASK */
  metaboxContainer.on('click', '.ptc-completionist-task[data-gid] button.unpin-task', function() {

    var thisButton = $(this);
    disable_element(thisButton, true);

    var buttonIcon = thisButton.find('i.fas');

    var data = {
      'action': 'ptc_unpin_task',
      'nonce': ptc_completionist_dashboard_widget.nonce_pin,
      'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
    };

    buttonIcon.removeClass('fa-thumbtack').addClass('fa-circle-notch fa-spin');

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        remove_task_row(res.data);
        remove_task_gid(res.data, true);
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
        alert('[Completionist] Failed to unpin task.');
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
      'nonce': ptc_completionist_dashboard_widget.nonce_delete,
      'task_gid': thisButton.closest('.ptc-completionist-task').data('gid'),
    };

    buttonIcon.removeClass('fa-minus').addClass('fa-circle-notch fa-spin');

    $.post(ajaxurl, data, function(res) {

      if(res.status == 'success' && res.data != '') {
        remove_task_row(res.data);
        remove_task_gid(res.data, true);
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
        alert('[Completionist] Failed to delete task.');
        disable_element(thisButton, false);
        buttonIcon.removeClass('fa-circle-notch fa-spin').addClass('fa-minus');
      });

  });//end submit delete task

  /* -------- HELPERS -------- */

  function list_current_page_tasks() {

    if(current_task_gids.length > 0) {
      lock_interface(true);
      taskContainer.html('<p class="task-loader"><i class="fas fa-circle-notch fa-spin"></i>Loading tasks from Asana...</p>');

      var final_index = calculate_page_final_index();
      var start_index = calculate_page_start_index();
      var task_gids_arr = [];
      for(var i = start_index; i <= final_index; ++i) {
        task_gids_arr.push(current_task_gids[i]);
      }

      var task_gids_str = JSON.stringify(task_gids_arr);
      var data = {
        'action': 'ptc_list_tasks',
        'nonce': ptc_completionist_dashboard_widget.nonce_list,
        'task_gids': task_gids_str,
        'detailed': true,
      };

      $.post(ajaxurl, data, function(res) {
        if(res.status == 'success' && res.data != '') {
          $(res.data).insertBefore('#ptc-asana-task-list .task-loader');
        } else if(res.status == 'error' && res.data != '') {
          display_alert_html(res.data);
        }
      }, 'json')
        .always(function() {
          taskContainer.find('.task-loader').remove();
          lock_interface(false);
        });

    } else {
      taskContainer.html('');
      display_if_empty_list();
      lock_interface(false);
    }

  }//end function list_current_page_tasks()

  function load_tasks_recursive(task_gids, load_index, final_index) {

    var data = {
      'action': 'ptc_list_task',
      'nonce': ptc_completionist_dashboard_widget.nonce_list,
      'task_gid': task_gids[ load_index ],
    };

    $.post(ajaxurl, data, function(res) {
      if(res.status == 'success' && res.data != '') {
        $(res.data).insertBefore('#ptc-asana-task-list .task-loader');
      } else if(res.status == 'error' && res.data != '') {
        display_alert_html(res.data);
      }
    }, 'json')
      .always(function() {
        if(load_index === final_index) {
          taskContainer.find('.task-loader').remove();
          lock_interface(false);
        } else if(load_index < final_index) {
          load_tasks_recursive(task_gids, ++load_index, final_index);
        }
      });

  }//end load_tasks_recursive()

  function remove_task_row(task_gid) {
    $('#ptc-completionist_site-tasks .ptc-completionist-task[data-gid="' + task_gid + '"]')
      .fadeOut(800, function() {
        $(this).remove();
        display_if_empty_list();
      });
  }//end remove_task_row()

  function display_if_empty_list() {
    if(taskContainer.html().trim() == '') {
      taskContainer.html('<p class="nothing-to-see"><i class="fas fa-clipboard-check"></i>There are no incomplete tasks!</p>');
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

  function lock_interface(if_disable = true) {
    if(if_disable) {
      disable_element(metaboxContainer.find('button'), true);
    } else {
      disable_element(metaboxContainer.find('button'), false);
      disable_element(metaboxContainer.find('header button#'+current_category), true);
      disable_element(paginationNavContainer.find('button[data-page='+current_page+']'), true);
      if(current_page <= 1) {
        disable_element(paginationNavContainer.find('button[data-page=prev]'), true);
      }
      if(current_page >= calculate_last_page_number()) {
        disable_element(paginationNavContainer.find('button[data-page=next]'), true);
      }
    }
  }//end lock_interface()

  function load_pagination_navigation() {
    paginationNavContainer.find('button.page-option:not([data-page=1])').remove();
    var total_pages = calculate_last_page_number();
    var page_option_HTML = '';
    for (var i = 2; i <= total_pages; ++i) {
      var disabled = (i === current_page) ? ' disabled="disabled"' : '';
      page_option_HTML += '<button class="page-option" data-page="'+i+'" type="button" title="Page '+i+'"'+disabled+'>'+i+'</button>';
    }
    $(page_option_HTML).insertAfter(paginationNavContainer.find('button.page-option[data-page=1]'));
  }

  function calculate_last_page_number() {
    return Math.ceil(current_task_gids.length / page_size);
  }

  function display_alert_html(note_box_html) {
    var alertBanner = $(note_box_html);
    alertBanner.insertBefore(taskContainer);
  }//end display_alert_html()

  function calculate_page_start_index() {
    return page_size * (current_page - 1);
  }

  function calculate_page_final_index() {
    var final_index = (page_size * current_page) - 1;
    var last_index = current_task_gids.length - 1;
    if (final_index > last_index) {
      final_index = last_index;
    }
    return final_index;
  }

  function remove_task_gid(task_gid, deleted = true) {
    metaboxContainer.find('header button[data-category-task-gids]').each(function() {
      if($(this).attr('id') === 'all-site-tasks' && deleted === true) {
        // update total count if deleted or unpinned
        var total_tasks_count = $(this).find('.total-tasks-count').html();
        $(this).find('.total-tasks-count').html(--total_tasks_count);
      }
      var task_list = $(this).data('category-task-gids');
      task_list = remove_array_item(task_list, task_gid);
      $(this).find('.task-count').html(task_list.length);
      $(this).data('category-task-gids', task_list);
    });
    refresh_site_tasks_progress();
  }

  function refresh_site_tasks_progress() {
    var all_site_tasks_button = metaboxContainer.find('header button#all-site-tasks');
    var task_list = all_site_tasks_button.data('category-task-gids');

    var total_tasks_count = all_site_tasks_button.find('.total-tasks-count').html();
    var incomplete_tasks_count = task_list.length;
    var completed_tasks_count = total_tasks_count - incomplete_tasks_count;

    all_site_tasks_button.find('.completed-tasks-count').html(completed_tasks_count);

    var completion_percentage = 0;
    if(total_tasks_count > 0) {
      var completion_percentage = Math.ceil((completed_tasks_count / total_tasks_count) * 100);
    }
    all_site_tasks_button.find('.progress-bar-wrapper .progress-bar').css('width', completion_percentage+'%');
  }

  function remove_array_item(array, remove_value) {
    return jQuery.grep(array, function(value) {
      return value != remove_value;
    });
  }

});//end document ready
