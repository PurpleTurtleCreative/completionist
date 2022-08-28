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
/* harmony export */   "filterPinnedTasks": function() { return /* binding */ filterPinnedTasks; }
/* harmony export */ });
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

/***/ }),

/***/ "./src/components/PTCCompletionistTasksDashboardWidget.jsx":
/*!*****************************************************************!*\
  !*** ./src/components/PTCCompletionistTasksDashboardWidget.jsx ***!
  \*****************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ PTCCompletionistTasksDashboardWidget; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _task_TaskOverview_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./task/TaskOverview.jsx */ "./src/components/task/TaskOverview.jsx");
/* harmony import */ var _task_TaskFilters_jsx__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./task/TaskFilters.jsx */ "./src/components/task/TaskFilters.jsx");
/* harmony import */ var _task_TaskListPaginated_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./task/TaskListPaginated.jsx */ "./src/components/task/TaskListPaginated.jsx");
/* harmony import */ var _task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./task/TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _task_util__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./task/util */ "./src/components/task/util.js");






const {
  useContext,
  useCallback,
  useState,
  useEffect
} = wp.element;
function PTCCompletionistTasksDashboardWidget() {
  const {
    tasks
  } = useContext(_task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_4__.TaskContext);
  const [visibleTasks, setVisibleTasks] = useState((0,_task_util__WEBPACK_IMPORTED_MODULE_5__.filterIncompleteTasks)(tasks));
  const handleFilterChange = useCallback((_key, selectedTasks) => setVisibleTasks(selectedTasks), []);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-PTCCompletionistTasksDashboardWidget"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskOverview_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    tasks: tasks
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskFilters_jsx__WEBPACK_IMPORTED_MODULE_2__["default"], {
    tasks: tasks,
    onChange: handleFilterChange
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_task_TaskListPaginated_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], {
    limit: 5,
    tasks: visibleTasks
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

/***/ "./src/components/task/TaskFilters.jsx":
/*!*********************************************!*\
  !*** ./src/components/task/TaskFilters.jsx ***!
  \*********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskFilters; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util */ "./src/components/task/util.js");



const {
  useState,
  useCallback,
  useMemo,
  useEffect
} = wp.element;
function TaskFilters(_ref) {
  let {
    tasks,
    onChange
  } = _ref;
  const [activeFilter, setActiveFilter] = useState('none');
  const filters = useMemo(() => {
    const incompleteTasks = (0,_util__WEBPACK_IMPORTED_MODULE_2__.filterIncompleteTasks)(tasks);
    return [{
      "key": 'none',
      "title": 'All Tasks',
      "tasks": incompleteTasks
    }, {
      "key": 'pinned',
      "title": 'Pinned',
      "tasks": (0,_util__WEBPACK_IMPORTED_MODULE_2__.filterPinnedTasks)(incompleteTasks)
    }, {
      "key": 'general',
      "title": 'General',
      "tasks": (0,_util__WEBPACK_IMPORTED_MODULE_2__.filterGeneralTasks)(incompleteTasks)
    }, {
      "key": 'myTasks',
      "title": 'My Tasks',
      "tasks": (0,_util__WEBPACK_IMPORTED_MODULE_2__.filterMyTasks)(window.PTCCompletionist.me.gid, incompleteTasks)
    }, {
      "key": 'critical',
      "title": 'Critical',
      "tasks": (0,_util__WEBPACK_IMPORTED_MODULE_2__.filterCriticalTasks)(incompleteTasks)
    }];
  }, [tasks]);
  useEffect(() => {
    const filteredTasks = filters.find(f => activeFilter === f.key).tasks;
    onChange(activeFilter, filteredTasks);
  }, [filters, activeFilter, onChange]);
  const handleClickFilter = useCallback((key, filteredTasks) => {
    setActiveFilter(key);
  }, [setActiveFilter]);
  const renderedFilterButtons = filters.map(f => {
    let className = `filter-${f.key}`;

    if (activeFilter === f.key) {
      className += ' --is-active';
    }

    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      key: f.key,
      type: "button",
      className: className,
      onClick: () => handleClickFilter(f.key, f.tasks)
    }, `${f.title} (${f.tasks.length})`);
  });
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskFilters"
  }, renderedFilterButtons);
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

/***/ "./src/components/task/TaskListPaginated.jsx":
/*!***************************************************!*\
  !*** ./src/components/task/TaskListPaginated.jsx ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskListPaginated; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskList_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskList.jsx */ "./src/components/task/TaskList.jsx");


const {
  useState,
  useCallback,
  useMemo,
  useEffect
} = wp.element;
function TaskListPaginated(_ref) {
  let {
    limit,
    tasks
  } = _ref;
  const [currentPage, setCurrentPage] = useState(1);
  const totalPages = useMemo(() => Math.ceil(tasks.length / limit), [tasks, limit]);
  const goToPage = useCallback(page => {
    if (page <= 1) {
      setCurrentPage(1);
    } else if (page >= totalPages) {
      setCurrentPage(totalPages);
    } else {
      setCurrentPage(page);
    }
  }, [currentPage, setCurrentPage, totalPages]);
  useEffect(() => {
    goToPage(currentPage);
  }, [tasks]);
  const start = Math.max(0, (currentPage - 1) * limit);
  const currentTasks = tasks.slice(start, currentPage * limit);
  const renderedPageButtons = [];

  for (let i = 1; i <= totalPages; ++i) {
    renderedPageButtons.push((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "num",
      type: "button",
      title: `Page ${i}`,
      disabled: i === currentPage,
      onClick: () => goToPage(i)
    }, i));
  }

  console.log('totalPages', totalPages);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskListPaginated"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_TaskList_jsx__WEBPACK_IMPORTED_MODULE_1__["default"], {
    tasks: currentTasks
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("nav", {
    className: "pagination"
  }, totalPages > 1 && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    className: "prev",
    type: "button",
    title: "Previous Page",
    disabled: 1 === currentPage,
    onClick: () => goToPage(currentPage - 1)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-angle-left"
  })), renderedPageButtons, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    className: "next",
    type: "button",
    title: "Next Page",
    disabled: totalPages === currentPage,
    onClick: () => goToPage(currentPage + 1)
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    className: "fas fa-angle-right"
  })))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("a", {
    href: window.PTCCompletionist.tag_url,
    target: "_asana",
    className: "view-tag"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
    title: "View All Site Tasks in Asana",
    className: "view",
    type: "button"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
    class: "fas fa-link"
  }))));
}

/***/ }),

/***/ "./src/components/task/TaskOverview.jsx":
/*!**********************************************!*\
  !*** ./src/components/task/TaskOverview.jsx ***!
  \**********************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": function() { return /* binding */ TaskOverview; }
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util */ "./src/components/task/util.js");



const {
  useContext,
  useMemo
} = wp.element;
function TaskOverview() {
  const {
    tasks
  } = useContext(_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_1__.TaskContext);
  const incompleteTasks = useMemo(() => (0,_util__WEBPACK_IMPORTED_MODULE_2__.filterIncompleteTasks)(tasks), [tasks]);
  const completedCount = tasks.length - incompleteTasks.length;
  let completedPercent = 0;

  if (tasks.length > 0) {
    completedPercent = Math.round(completedCount / tasks.length * 100);
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "ptc-TaskOverview"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "feature"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "large"
  }, completedPercent, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "small"
  }, "%")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "caption"
  }, "Complete")), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "details"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "incomplete"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "count"
  }, incompleteTasks.length), " Remaining"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "progress"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "progress-bar-wrapper"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "progress-bar",
    style: {
      width: `${completedPercent}%`
    }
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", {
    className: "caption"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
    className: "completed"
  }, "Completed ", completedCount), " of ", tasks.length))));
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
    completeTask(taskGID).then(success => {
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
  let assigneeDisplayName = null;

  if (task.assignee) {
    if (window.PTCCompletionist.users[task.assignee.gid]) {
      assigneeDisplayName = window.PTCCompletionist.users[task.assignee.gid].data.display_name;
    } else {
      assigneeDisplayName = '(Not Connected)';
    }
  }

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

/***/ "./assets/styles/scss/dashboard-widget.scss":
/*!**************************************************!*\
  !*** ./assets/styles/scss/dashboard-widget.scss ***!
  \**************************************************/
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
/* harmony import */ var _components_PTCCompletionistTasksDashboardWidget_jsx__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/PTCCompletionistTasksDashboardWidget.jsx */ "./src/components/PTCCompletionistTasksDashboardWidget.jsx");
/* harmony import */ var _components_notice_NoteBox_jsx__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./components/notice/NoteBox.jsx */ "./src/components/notice/NoteBox.jsx");
/* harmony import */ var _components_task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./components/task/TaskContext.jsx */ "./src/components/task/TaskContext.jsx");
/* harmony import */ var _assets_styles_scss_dashboard_widget_scss__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../assets/styles/scss/dashboard-widget.scss */ "./assets/styles/scss/dashboard-widget.scss");








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
    tasksPanelContent = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_task_TaskContext_jsx__WEBPACK_IMPORTED_MODULE_5__.TaskContextProvider, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_PTCCompletionistTasksDashboardWidget_jsx__WEBPACK_IMPORTED_MODULE_3__["default"], null));
  }

  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_edit_post__WEBPACK_IMPORTED_MODULE_2__.PluginDocumentSettingPanel, {
    name: "ptc-completionist-tasks",
    title: "Completionist",
    className: "ptc-completionist-tasks"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "The current post ID is: ", wp.data.select("core/editor").getCurrentPostId()), tasksPanelContent);
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