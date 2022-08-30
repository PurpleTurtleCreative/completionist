/******/ (function() { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/components/task/util.js":
/*!*************************************!*\
  !*** ./src/components/task/util.js ***!
  \*************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "getTaskUrl": function() { return /* binding */ getTaskUrl; },
/* harmony export */   "isCriticalTask": function() { return /* binding */ isCriticalTask; },
/* harmony export */   "filterIncompleteTasks": function() { return /* binding */ filterIncompleteTasks; },
/* harmony export */   "filterCriticalTasks": function() { return /* binding */ filterCriticalTasks; },
/* harmony export */   "filterMyTasks": function() { return /* binding */ filterMyTasks; },
/* harmony export */   "filterGeneralTasks": function() { return /* binding */ filterGeneralTasks; },
/* harmony export */   "filterPinnedTasks": function() { return /* binding */ filterPinnedTasks; },
/* harmony export */   "getAssigneeDisplayName": function() { return /* binding */ getAssigneeDisplayName; },
/* harmony export */   "getWorkspaceProjectSelectOptions": function() { return /* binding */ getWorkspaceProjectSelectOptions; },
/* harmony export */   "getWorkspaceUserSelectOptions": function() { return /* binding */ getWorkspaceUserSelectOptions; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


/**
 * Utility functions unrelated to application state.
 */
function getTaskUrl(taskGID) {
  return `https://app.asana.com/0/0/${taskGID}/f`;
}
function isCriticalTask(task) {
  const DAY_IN_SECONDS = 86400;
  const limit = 7 * DAY_IN_SECONDS;
  return Date.parse(task.due_on) - Date.now() < limit;
}
function filterIncompleteTasks(tasks) {
  return tasks.filter(t => !t.completed);
}
function filterCriticalTasks(tasks) {
  return tasks.filter(t => isCriticalTask(t));
}
function filterMyTasks(userGID, tasks) {
  return tasks.filter(t => {
    if (t.assignee) {
      return userGID === t.assignee.gid;
    }

    return false;
  });
}
function filterGeneralTasks(tasks) {
  return tasks.filter(t => {
    if (t.action_link && t.action_link.post_id > 0) {
      return false;
    }

    return true;
  });
}
function filterPinnedTasks(tasks) {
  return tasks.filter(t => {
    if (t.action_link && t.action_link.post_id > 0) {
      return true;
    }

    return false;
  });
}
function getAssigneeDisplayName(task) {
  let assigneeDisplayName = null;

  if (task.assignee) {
    if (window.PTCCompletionist.users[task.assignee.gid]) {
      assigneeDisplayName = window.PTCCompletionist.users[task.assignee.gid].data.display_name;
    } else {
      assigneeDisplayName = '(Not Connected)';
    }
  }

  return assigneeDisplayName;
}
function getWorkspaceProjectSelectOptions() {
  const projectOptions = [];

  for (const projectGID in window.PTCCompletionist.projects) {
    projectOptions.push((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: projectGID,
      key: projectGID
    }, window.PTCCompletionist.projects[projectGID]));
  }

  return projectOptions;
}
function getWorkspaceUserSelectOptions() {
  const userOptions = [];

  for (const userGID in window.PTCCompletionist.users) {
    const user = window.PTCCompletionist.users[userGID].data;
    userOptions.push((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: userGID,
      key: userGID
    }, `${user.display_name} (${user.user_email})`));
  }

  return userOptions;
}

/***/ }),

/***/ "./src/components/BlockEditorPanelTasks.jsx":
/*!**************************************************!*\
  !*** ./src/components/BlockEditorPanelTasks.jsx ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ BlockEditorPanelTasks; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _task_TaskPinToPostBar_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./task/TaskPinToPostBar.jsx */ "./src/components/task/TaskPinToPostBar.jsx");
/* harmony import */ var _task_TaskList_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./task/TaskList.jsx */ "./src/components/task/TaskList.jsx");
/* harmony import */ var _task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./task/TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _assets_styles_scss_components_BlockEditorPanelTasks_scss__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../../../../../../../assets/styles/scss/components/BlockEditorPanelTasks.scss */ "./assets/styles/scss/components/BlockEditorPanelTasks.scss");





const {
  useContext
} = wp.element;
function BlockEditorPanelTasks() {
  const {
    tasks
  } = useContext(_task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_3__.TaskContext);
  const currentPostId = wp.data.select('core/editor').getCurrentPostId();
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-BlockEditorPanelTasks"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskPinToPostBar_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    postId: currentPostId
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskList_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    tasks: tasks
  }));
}

/***/ }),

/***/ "./src/components/notice/NoteBox.jsx":
/*!*******************************************!*\
  !*** ./src/components/notice/NoteBox.jsx ***!
  \*******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ NoteBox; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

function NoteBox(_ref) {
  let {
    type,
    message,
    code
  } = _ref;
  let titleText = null;

  if ('error' === type) {
    titleText = 'Error';

    if (!!code) {
      titleText += ` ${code}`;
    }
  }

  let title = null;

  if (!!titleText) {
    title = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("strong", null, titleText + '. ');
  }

  let extraClassNames = '';

  if (!!type) {
    extraClassNames += ` --has-type-${type}`;
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-NoteBox" + extraClassNames
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, title, message));
}

/***/ }),

/***/ "./src/components/task/TaskActions.jsx":
/*!*********************************************!*\
  !*** ./src/components/task/TaskActions.jsx ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskActions; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util */ "./src/components/task/util.js");



const {
  useCallback,
  useContext
} = wp.element;
function TaskActions(_ref) {
  let {
    taskGID,
    processingStatus
  } = _ref;
  const {
    deleteTask,
    unpinTask,
    removeTask,
    setTaskProcessingStatus
  } = useContext(_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__.TaskContext);
  const handleUnpinTask = useCallback(taskGID => {
    if (processingStatus) {
      console.error(`Rejected handleUnpinTask. Currently ${processingStatus} task ${taskGID}.`);
      return;
    }

    setTaskProcessingStatus(taskGID, 'unpinning');
    unpinTask(taskGID).then(success => {
      if (!success) {
        // Only set processing status if task wasn't successfully removed.
        setTaskProcessingStatus(taskGID, false);
      }
    });
  }, [processingStatus, setTaskProcessingStatus, unpinTask]);
  const handleDeleteTask = useCallback(taskGID => {
    if (processingStatus) {
      console.error(`Rejected handleDeleteTask. Currently ${processingStatus} task ${taskGID}.`);
      return;
    }

    setTaskProcessingStatus(taskGID, 'deleting');
    deleteTask(taskGID).then(success => {
      if (!success) {
        // Only set processing status if task wasn't removed.
        setTaskProcessingStatus(taskGID, false);
      }
    });
  }, [processingStatus, setTaskProcessingStatus, removeTask]);
  const task_url = (0,_util__WEBPACK_IMPORTED_MODULE_2__.getTaskUrl)(taskGID);
  const unpinIcon = 'unpinning' === processingStatus ? 'fa-sync-alt fa-spin' : 'fa-thumbtack';
  const deleteIcon = 'deleting' === processingStatus ? 'fa-sync-alt fa-spin' : 'fa-minus';
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskActions"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: task_url,
    target: "_asana"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "View in Asana",
    className: "view",
    type: "button"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-link"
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Unpin from Site",
    className: "unpin",
    type: "button",
    onClick: () => handleUnpinTask(taskGID),
    disabled: !!processingStatus
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: `fas ${unpinIcon}`
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Delete from Asana",
    className: "delete",
    type: "button",
    onClick: () => handleDeleteTask(taskGID),
    disabled: !!processingStatus
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: `fas ${deleteIcon}`
  })));
}

/***/ }),

/***/ "./src/components/task/TaskContext.jsx":
/*!*********************************************!*\
  !*** ./src/components/task/TaskContext.jsx ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "TaskContext": function() { return /* binding */ TaskContext; },
/* harmony export */   "TaskContextProvider": function() { return /* binding */ TaskContextProvider; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const {
  createContext,
  useState
} = wp.element;
const TaskContext = createContext(false);
function TaskContextProvider(_ref) {
  let {
    children
  } = _ref;
  const [tasks, setTasks] = useState(Object.values(window.PTCCompletionist.tasks));
  const context = {
    "tasks": tasks,
    setTaskProcessingStatus: (taskGID, processingStatus) => {
      setTasks(prevTasks => {
        return prevTasks.map(t => {
          if (t.gid == taskGID) {
            return { ...t,
              'processingStatus': processingStatus
            };
          } else {
            return { ...t
            };
          }
        });
      });
    },
    completeTask: async function (taskGID) {
      let completed = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
      const task = context.tasks.find(t => taskGID === t.gid);
      let data = {
        'action': 'ptc_update_task',
        'nonce': window.PTCCompletionist.api.nonce,
        'task_gid': taskGID,
        'completed': completed
      };
      const init = {
        'method': 'POST',
        'credentials': 'same-origin',
        'body': new URLSearchParams(data)
      };
      return await window.fetch(window.ajaxurl, init).then(res => res.json()).then(res => {
        console.log(res);

        if (res.status == 'success' && res.data) {
          context.updateTask({
            "gid": task.gid,
            "completed": completed
          });
          return true;
        } else if (res.status == 'error' && res.data) {
          console.error(res.data);
        } else {
          alert('[Completionist] Error ' + res.code + ': ' + res.message);
        }

        return false;
      }).catch(err => {
        console.error('Promise catch:', err);
        alert('[Completionist] Failed to complete task.');
        return false;
      });
    },
    deleteTask: async taskGID => {
      const task = context.tasks.find(t => taskGID === t.gid);
      let data = {
        'action': 'ptc_delete_task',
        'nonce': window.PTCCompletionist.api.nonce,
        'task_gid': taskGID
      };

      if (task.action_link.post_id) {
        data.post_id = task.action_link.post_id;
      }

      const init = {
        'method': 'POST',
        'credentials': 'same-origin',
        'body': new URLSearchParams(data)
      };
      return await window.fetch(window.ajaxurl, init).then(res => res.json()).then(res => {
        console.log(res);

        if (res.status == 'success' && res.data) {
          context.removeTask(res.data);
          return true;
        } else if (res.status == 'error' && res.data) {
          console.error(res.data);
        } else {
          alert('[Completionist] Error ' + res.code + ': ' + res.message);
        }

        return false;
      }).catch(err => {
        console.error('Promise catch:', err);
        alert('[Completionist] Failed to delete task.');
        return false;
      });
    },
    unpinTask: async function (taskGID) {
      let postID = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      const task = context.tasks.find(t => taskGID === t.gid);
      let data = {
        'action': 'ptc_unpin_task',
        'nonce': window.PTCCompletionist.api.nonce,
        'task_gid': taskGID
      };

      if (postID) {
        data.post_id = postID;
      }

      const init = {
        'method': 'POST',
        'credentials': 'same-origin',
        'body': new URLSearchParams(data)
      };
      return await window.fetch(window.ajaxurl, init).then(res => res.json()).then(res => {
        console.log(res);

        if (res.status == 'success' && res.data) {
          context.removeTask(res.data);
          return true;
        } else if (res.status == 'error' && res.data) {
          console.error(res.data);
        } else {
          alert('[Completionist] Error ' + res.code + ': ' + res.message);
        }

        return false;
      }).catch(err => {
        console.error('Promise catch:', err);
        alert('[Completionist] Failed to unpin task.');
        return false;
      });
    },

    /**
     * @param string taskLink The Asana task link.
     * @param int postID The WordPress post ID to pin the task.
     */
    pinTask: async (taskLink, postID) => {
      let data = {
        'action': 'ptc_pin_task',
        'nonce': window.PTCCompletionist.api.nonce_pin,
        'task_link': taskLink,
        'post_id': postID
      };
      const init = {
        'method': 'POST',
        'credentials': 'same-origin',
        'body': new URLSearchParams(data)
      };
      return await window.fetch(window.ajaxurl, init).then(res => res.json()).then(res => {
        console.log(res);

        if (res.status == 'success' && res.data) {
          context.addTask(res.data);
          return true;
        } else if (res.status == 'error' && res.data) {
          console.error(res.data);
        } else {
          alert('[Completionist] Error ' + res.code + ': ' + res.message);
        }

        return false;
      }).catch(err => {
        console.error('Promise catch:', err);
        alert('[Completionist] Failed to pin task.');
        return false;
      });
    },

    /**
     * @param object taskData The new task's data.
     * @param int postID (Optional) The WordPress post ID
     * to pin the new task. Default null to simply create the task.
     */
    createTask: async function (taskData) {
      let postID = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      let data = {
        'action': 'ptc_create_task',
        'nonce': window.PTCCompletionist.api.nonce_create,
        ...taskData
      };

      if (postID) {
        data.post_id = postID;
      }

      const init = {
        'method': 'POST',
        'credentials': 'same-origin',
        'body': new URLSearchParams(data)
      };
      return await window.fetch(window.ajaxurl, init).then(res => res.json()).then(res => {
        console.log(res);

        if (res.status == 'success' && res.data) {
          context.addTask(res.data);
          return true;
        } else if (res.status == 'error' && res.data) {
          console.error(res.data);
        } else {
          alert('[Completionist] Error ' + res.code + ': ' + res.message);
        }

        return false;
      }).catch(err => {
        console.error('Promise catch:', err);
        alert('[Completionist] Failed to pin task.');
        return false;
      });
    },

    /**
     * @param object taskUpdates A task object containing the "gid" and only
     * the necessary fields to override.
     */
    updateTask: taskUpdates => {
      setTasks(prevTasks => {
        return prevTasks.map(t => {
          if (t.gid == taskUpdates.gid) {
            return { ...t,
              ...taskUpdates
            };
          } else {
            return { ...t
            };
          }
        });
      });
    },
    addTask: task => {
      setTasks(prevTasks => {
        return [{ ...task
        }, ...prevTasks];
      });
    },
    removeTask: taskGID => {
      setTasks(prevTasks => {
        return prevTasks.map(t => {
          if (t.gid != taskGID) {
            return { ...t
            };
          }
        }).filter(t => !!t);
      });
    }
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(TaskContext.Provider, {
    value: context
  }, children);
}

/***/ }),

/***/ "./src/components/task/TaskCreationForm.jsx":
/*!**************************************************!*\
  !*** ./src/components/task/TaskCreationForm.jsx ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskCreationForm; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util */ "./src/components/task/util.js");



const {
  useContext,
  useState,
  useMemo
} = wp.element;
function TaskCreationForm(_ref) {
  let {
    postId = null
  } = _ref;
  const [isProcessing, setIsProcessing] = useState(false);
  const [taskName, setTaskName] = useState('');
  const [taskAssigneeGID, setTaskAssigneeGID] = useState('');
  const [taskDueDate, setTaskDueDate] = useState('');
  const [taskProjectGID, setTaskProjectGID] = useState('');
  const [taskNotes, setTaskNotes] = useState('');
  const {
    createTask
  } = useContext(_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__.TaskContext);
  const workspaceProjectSelectOptions = useMemo(_util__WEBPACK_IMPORTED_MODULE_2__.getWorkspaceProjectSelectOptions, []);
  const workspaceUserSelectOptions = useMemo(_util__WEBPACK_IMPORTED_MODULE_2__.getWorkspaceUserSelectOptions, []);

  const handleFormSubmit = event => {
    event.preventDefault();

    if (isProcessing) {
      return;
    }

    setIsProcessing(true);
    const taskData = {
      'name': taskName,
      'assignee': taskAssigneeGID,
      'due_on': taskDueDate,
      'project': taskProjectGID,
      'notes': taskNotes
    };
    createTask(taskData, postId).then(success => {
      if (success) {
        setTaskName('');
        setTaskAssigneeGID('');
        setTaskDueDate('');
        setTaskProjectGID('');
        setTaskNotes('');
      }

      setIsProcessing(false);
    });
  };

  const submitButtonContent = isProcessing ? (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-circle-notch fa-spin",
    "aria-hidden": "true"
  }), "Creating task...") : (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-plus",
    "aria-hidden": "true"
  }), "Add Task");
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    className: "ptc-TaskCreationForm",
    onSubmit: handleFormSubmit,
    disabled: isProcessing
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "ptc-new-task_name",
    type: "text",
    placeholder: "Write a task name...",
    value: taskName,
    onChange: e => setTaskName(e.target.value),
    disabled: isProcessing,
    required: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    for: "ptc-new-task_assignee"
  }, "Assignee"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    id: "ptc-new-task_assignee",
    value: taskAssigneeGID,
    onChange: e => setTaskAssigneeGID(e.target.value),
    disabled: isProcessing
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: ""
  }, "None (Unassigned)"), workspaceUserSelectOptions)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    for: "ptc-new-task_due_on"
  }, "Due Date"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    id: "ptc-new-task_due_on",
    type: "date",
    pattern: "\\d\\d\\d\\d-\\d\\d-\\d\\d",
    placeholder: "yyyy-mm-dd",
    value: taskDueDate,
    onChange: e => setTaskDueDate(e.target.value),
    disabled: isProcessing
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    for: "ptc-new-task_project"
  }, "Project"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
    id: "ptc-new-task_project",
    value: taskProjectGID,
    onChange: e => setTaskProjectGID(e.target.value),
    disabled: isProcessing
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
    value: ""
  }, "None (Private Task)"), workspaceProjectSelectOptions)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "form-group"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    for: "ptc-new-task_notes"
  }, "Description"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("textarea", {
    id: "ptc-new-task_notes",
    value: taskNotes,
    onChange: e => setTaskNotes(e.target.value),
    disabled: isProcessing
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "submit",
    disabled: isProcessing
  }, submitButtonContent));
}

/***/ }),

/***/ "./src/components/task/TaskList.jsx":
/*!******************************************!*\
  !*** ./src/components/task/TaskList.jsx ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskList; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskRow_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskRow.jsx */ "./src/components/task/TaskRow.jsx");


function TaskList(_ref) {
  let {
    tasks
  } = _ref;
  let listContent = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "ptc-no-results"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-clipboard-check"
  }), "No tasks to display.");

  if (tasks.length > 0) {
    listContent = tasks.map(t => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskRow_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
      key: t.gid,
      task: t
    }));
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskList"
  }, listContent);
}

/***/ }),

/***/ "./src/components/task/TaskPinToPostBar.jsx":
/*!**************************************************!*\
  !*** ./src/components/task/TaskPinToPostBar.jsx ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskPinToPostBar; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskPinToPostForm_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskPinToPostForm.jsx */ "./src/components/task/TaskPinToPostForm.jsx");
/* harmony import */ var _TaskCreationForm_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./TaskCreationForm.jsx */ "./src/components/task/TaskCreationForm.jsx");
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");




const {
  useState
} = wp.element;
function TaskPinToPostBar(_ref) {
  let {
    postId
  } = _ref;
  const [showTaskCreationForm, setShowTaskCreationForm] = useState(false);

  const toggleTaskCreationForm = () => {
    setShowTaskCreationForm(!showTaskCreationForm);
  };

  const creationFormToggleIconClass = showTaskCreationForm ? 'fas fa-times' : 'fas fa-plus';
  let extraClassNames = '';

  if (showTaskCreationForm) {
    extraClassNames += ' --is-showing-creation-form';
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskPinToPostBar" + extraClassNames
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "toolbar"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskPinToPostForm_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    postId: postId
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    type: "button",
    title: "Create new task",
    onClick: toggleTaskCreationForm
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: creationFormToggleIconClass,
    "aria-hidden": "true"
  }))), showTaskCreationForm && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskCreationForm_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    postId: postId
  }));
}

/***/ }),

/***/ "./src/components/task/TaskPinToPostForm.jsx":
/*!***************************************************!*\
  !*** ./src/components/task/TaskPinToPostForm.jsx ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskPinToPostForm; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");


const {
  useState,
  useContext
} = wp.element;
function TaskPinToPostForm(_ref) {
  let {
    postId
  } = _ref;
  const [taskLink, setTaskLink] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);
  const {
    pinTask
  } = useContext(_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__.TaskContext);

  const handleFormSubmit = event => {
    event.preventDefault();

    if (isProcessing) {
      return;
    }

    setIsProcessing(true);
    pinTask(taskLink, postId).then(success => {
      if (success) {
        setTaskLink('');
      }

      setIsProcessing(false);
    });
  };

  const submitIconClass = isProcessing ? 'fas fa-sync-alt fa-spin' : 'fas fa-thumbtack';
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("form", {
    className: "ptc-TaskPinToPostForm",
    onSubmit: handleFormSubmit,
    disabled: isProcessing
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "url",
    placeholder: "Paste a task link...",
    value: taskLink,
    onChange: e => setTaskLink(e.target.value),
    disabled: isProcessing,
    required: true
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Pin existing Asana task",
    type: "submit"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: submitIconClass,
    disabled: isProcessing
  })));
}

/***/ }),

/***/ "./src/components/task/TaskRow.jsx":
/*!*****************************************!*\
  !*** ./src/components/task/TaskRow.jsx ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskRow; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskActions_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskActions.jsx */ "./src/components/task/TaskActions.jsx");
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./util */ "./src/components/task/util.js");




const {
  useState,
  useCallback,
  useContext
} = wp.element;
function TaskRow(_ref) {
  let {
    task
  } = _ref;
  const [showDescription, setShowDescription] = useState(false);
  const {
    completeTask,
    setTaskProcessingStatus
  } = useContext(_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_2__.TaskContext);
  const handleMarkComplete = useCallback(taskGID => {
    if (task.processingStatus) {
      console.error(`Rejected handleMarkComplete. Currently ${task.processingStatus} task ${taskGID}.`);
      return;
    }

    setTaskProcessingStatus(taskGID, 'completing');
    completeTask(taskGID, !task.completed).then(success => {
      setTaskProcessingStatus(taskGID, false);
    });
  }, [task.processingStatus, setTaskProcessingStatus, completeTask]);
  const handleToggleDescription = useCallback(() => {
    if (!task.notes) {
      return;
    }

    setShowDescription(!showDescription);
  }, [task, showDescription, setShowDescription]);
  const notesIconClassName = showDescription ? 'fas' : 'far';
  let assigneeDisplayName = (0,_util__WEBPACK_IMPORTED_MODULE_3__.getAssigneeDisplayName)(task);
  let extraClassNames = '';

  if ((0,_util__WEBPACK_IMPORTED_MODULE_3__.isCriticalTask)(task)) {
    extraClassNames += ' --is-critical';
  }

  if (true === task.completed) {
    extraClassNames += ' --is-complete';
  }

  if (task.processingStatus) {
    extraClassNames += ` --is-processing --is-${task.processingStatus}`;
  }

  if (!!task.notes) {
    extraClassNames += ' --has-description';
  }

  const markCompleteIcon = 'completing' === task.processingStatus ? 'fa-sync-alt fa-spin' : 'fa-check';
  const dueOnDateString = new Date(task.due_on).toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskRow" + extraClassNames
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "Mark Complete",
    className: "mark-complete",
    type: "button",
    onClick: () => handleMarkComplete(task.gid),
    disabled: !!task.processingStatus
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: `fas ${markCompleteIcon}`
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "body"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "name",
    onClick: handleToggleDescription
  }, task.name, !!task.notes && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: `${notesIconClassName} fa-sticky-note`
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "details"
  }, assigneeDisplayName && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "assignee"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    class: "fas fa-user"
  }), " ", assigneeDisplayName), task.due_on && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "due"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-clock"
  }), " ", dueOnDateString)), showDescription && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "description"
  }, task.notes)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "actions"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    className: "cta-button",
    href: task.action_link.href,
    target: task.action_link.target
  }, task.action_link.label, " ", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-long-arrow-alt-right"
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskActions_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    taskGID: task.gid,
    processingStatus: task.processingStatus
  })));
}

/***/ }),

/***/ "./assets/styles/scss/components/BlockEditorPanelTasks.scss":
/*!******************************************************************!*\
  !*** ./assets/styles/scss/components/BlockEditorPanelTasks.scss ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/edit-post":
/*!**********************************!*\
  !*** external ["wp","editPost"] ***!
  \**********************************/
/***/ (function(module) {

module.exports = window["wp"]["editPost"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/plugins":
/*!*********************************!*\
  !*** external ["wp","plugins"] ***!
  \*********************************/
/***/ (function(module) {

module.exports = window["wp"]["plugins"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
!function() {
/*!***********************************!*\
  !*** ./src/index_BlockEditor.jsx ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/plugins */ "@wordpress/plugins");
/* harmony import */ var _wordpress_plugins__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_plugins__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/edit-post */ "@wordpress/edit-post");
/* harmony import */ var _wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _components_BlockEditorPanelTasks_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/BlockEditorPanelTasks.jsx */ "./src/components/BlockEditorPanelTasks.jsx");
/* harmony import */ var _components_notice_NoteBox_jsx__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/notice/NoteBox.jsx */ "./src/components/notice/NoteBox.jsx");
/* harmony import */ var _components_task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/task/TaskContext.jsx */ "./src/components/task/TaskContext.jsx");







const registerCompletionistPlugin = () => {
  window.console.log('!! ptc-completionist plugin ReactJS !!');
  let tasksPanelContent = null;

  if ('error' in window.PTCCompletionist) {
    tasksPanelContent = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_notice_NoteBox_jsx__WEBPACK_IMPORTED_MODULE_4__["default"], {
      type: "error",
      message: window.PTCCompletionist.error.message,
      code: window.PTCCompletionist.error.code
    });
  } else {
    tasksPanelContent = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_5__.TaskContextProvider, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_BlockEditorPanelTasks_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], null));
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__.PluginDocumentSettingPanel, {
    name: "ptc-completionist-tasks",
    title: "Completionist",
    className: "ptc-completionist-tasks"
  }, tasksPanelContent);
};

(0,_wordpress_plugins__WEBPACK_IMPORTED_MODULE_1__.registerPlugin)('ptc-completionist', {
  render: registerCompletionistPlugin,
  icon: (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    xmlns: "http://www.w3.org/2000/svg",
    viewBox: "0 0 384 512"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    d: "M336 64h-53.88C268.9 26.8 233.7 0 192 0S115.1 26.8 101.9 64H48C21.5 64 0 85.48 0 112v352C0 490.5 21.5 512 48 512h288c26.5 0 48-21.48 48-48v-352C384 85.48 362.5 64 336 64zM192 64c17.67 0 32 14.33 32 32s-14.33 32-32 32S160 113.7 160 96S174.3 64 192 64zM282.9 262.8l-88 112c-4.047 5.156-10.02 8.438-16.53 9.062C177.6 383.1 176.8 384 176 384c-5.703 0-11.25-2.031-15.62-5.781l-56-48c-10.06-8.625-11.22-23.78-2.594-33.84c8.609-10.06 23.77-11.22 33.84-2.594l36.98 31.69l72.52-92.28c8.188-10.44 23.3-12.22 33.7-4.062C289.3 237.3 291.1 252.4 282.9 262.8z"
  }))
});
}();
/******/ })()
;
//# sourceMappingURL=index_BlockEditor.jsx.js.map