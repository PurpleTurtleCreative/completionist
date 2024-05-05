---
title: Shortcodes
parent: Completionist
nav_order: 5
has_children: true
has_toc: false
---

# Shortcodes
{: .no_toc }

Display Asana tasks on your website to improve work transparency and collaboration—while reducing users within your Asana workspace.
{: .text-beta .fw-300 .text-grey-dk-000}

## Table of Contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## [ptc_asana_project]

Displays Asana project information and tasks.

Only "list" layout is displayed in the free version of Completionist. Additional layouts such as "calendar" and "board" are available to [Completionist Pro](https://purpleturtlecreative.com/completionist-pro/) users only.

### Attributes

#### Project Display

| Attribute          | Value Type                   | Required?                                                    | Description                                                  |
| ------------------ | ---------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------ |
| `src`              | URL                          | **Required**                                                 | The Asana project link.<br /><br />When viewing a project in Asana, copy the URL in the web browser address bar (eg. `https://app.asana.com/0/12345729186/12345167991`) or the URL from clicking "Copy project link" in the project's detail dropdown. |
| `exclude_sections` | CSV (Comma-Separated Values) | *Optional.* Default "" (empty) to display all sections within the Asana project. | A comma-separated list of the names of the Asana project sections to exclude from display.<br /><br />Note that HTML entities are encoded to keep compatibility with the WordPress Block Editor. For example, `exclude_sections="Design &amp; Development"` will exclude project sections named `Design & Development`. |
| `show_name`        | Boolean (true/false)         | *Optional.* Default "true".                                  | Set to "false" to hide the project's name.                   |
| `show_description` | Boolean (true/false)         | *Optional.* Default "true".                                  | Set to "false" to hide the project's description.            |
| `show_status`      | Boolean (true/false)         | *Optional.* Default "true".                                  | Set to "false" to hide the project's current status update and completion status. |
| `show_modified`    | Boolean (true/false)         | *Optional.* Default "true".                                  | Set to "false" to hide the project's last modified date and time. |
| `show_due`         | Boolean (true/false)         | *Optional.* Default "true".                                  | Set to "false" to hide the project's due date.               |

#### Tasks Display

| Attribute                                  | Value Type                                                   | Required?                                                 | Description                                                  |
| ------------------------------------------ | ------------------------------------------------------------ | --------------------------------------------------------- | ------------------------------------------------------------ |
| `layout`                                   | Choice of `list`, `calendar`, or `board`                     | *Optional.* Default "list".                               | The layout for displaying tasks. Only "list" is supported for free users.<br /><br />⭐️ **Pro users** can display an Asana project in calendar layout.<br /><br />⭐️ **Pro users** can display an Asana project in board (aka Kanban) layout. |
| `show_tasks_description`                   | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide tasks' descriptions.                  |
| `show_tasks_assignee`                      | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide tasks' assignee.                      |
| `show_tasks_subtasks`                      | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide tasks' subtasks.<br /><br />Note that only the immediate subtasks are ever displayed. Subtasks of subtasks are never displayed. |
| `show_tasks_completed`                     | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide completed tasks. If enabled, completed tasks will be shown (if any) and all "checkmark bubbles" will be displayed. |
| `show_tasks_due`                           | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide tasks' due dates.                     |
| `show_tasks_attachments`                   | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide tasks' additional attachments. Inline attachments and embeds in the tasks' descriptions are always displayed if `show_tasks_description="true"`.<br /><br />The following **image** file extensions are supported: `jpg`, `jpeg`, `png`, `bmp`, `gif`<br />The following **video** file extensions are supported: `mp4`<br />The following **document** file extensions are supported: `pdf` |
| `show_tasks_tags`                          | Boolean (true/false)                                         | *Optional.* Default "true".                               | Set to "false" to hide tasks' tags.                          |
| `show_tasks_comments`<br />⭐️ **Pro users** | Boolean (true/false)                                         | *Optional.* Default "false".                              | Set to "true" to display tasks' comments.<br /><br />This feature is available to ⭐️ **Pro users** only. |
| `sort_tasks_by`                            | Any top-level string, number, or object [Asana task field](https://developers.asana.com/reference/tasks). | *Optional.* Default "" to use unsorted (manual) ordering. | Common sorting examples include:<br />`assignee` - Sort tasks by their assignee's name<br />`completed` - Sort tasks by "completed" status and then alphabetically by their title<br />`completed_at` - Sort tasks by when they were marked "completed"<br />`due_on` - Sort tasks by their due date<br />`name` - Sort tasks by their title |


#### Configuration

| Attribute   | Value Type        | Required?                                                    | Description                                                  |
| ----------- | ----------------- | ------------------------------------------------------------ | ------------------------------------------------------------ |
| `auth_user` | WordPress user ID | *Optional.* Defaults to the *[Frontend Authentication User](/completionist/getting-started/#set-a-frontend-authentication-user)* saved in Completionist's settings. | A WordPress user's ID to authenticate the Asana API requests.<br /><br />The WordPress user must be connected to Asana via Completionist in wp-admin, or you may see a `401 Unauthorized` error on your website. |

### Quick Copy (with default values)

```
[ptc_asana_project src="<ASANA_PROJECT_URL>" layout="list" auth_user="" exclude_sections="" show_name="true" show_description="true" show_status="true" show_modified="true" show_due="true" show_tasks_description="true" show_tasks_assignee="true" show_tasks_subtasks="true" show_tasks_completed="true" show_tasks_due="true" show_tasks_attachments="true" show_tasks_tags="true" show_tasks_comments="false" sort_tasks_by="" /]
```

_**\*\*Remember** to change the `src` attribute value to the URL of the Asana project that you'd like to display!_


## [ptc_asana_project_list]

Displays a WordPress user's associated Asana projects' information and tasks.

To select which Asana projects to display, navigate to *Users* and click "Edit" to edit a WordPress user's profile information. You can then find the "Asana Projects" setting to select which Asana projects the WordPress user is allowed to view. Note that you must have the `edit_posts` capability and [connected your Asana account](/completionist/getting-started/#connect-your-asana-account) to update this setting.

<div class="banner banner-info">
  <p>⭐️ This shortcode is available to <strong><a href="https://purpleturtlecreative.com/completionist-pro/">Completionist Pro</a></strong> users only.</p>
</div>

### Attributes

This shortcode shares the same attributes as the singular `[ptc_asana_project]` shortcode, plus the following:

| Attribute | Value Type                               | Required?                                                    | Description                                                  |
| --------- | ---------------------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------ |
| `layout`  | Choice of `list`, `calendar`, or `board` | *Optional.* Default "list".                                  | The Asana project layout for all listed projects.<br /><br />Normally, the `src` attribute determines which project is displayed. However, a project source URL is not relevant for this shortcode since all projects are instead determined by the selected "Asana Projects" in the WordPress user's profile. |
| `user`    | WordPress user ID                        | *Optional.* Default "" (empty) to use the currently logged-in user for dynamic, personalized display. | A WordPress user's ID to determine which Asana projects to list.<br /><br />See the "Asana Projects" setting in the WordPress user's profile to select which Asana projects the WordPress user is allowed to view. |

### Quick Copy (with default values)

```
[ptc_asana_project_list layout="list" user="" auth_user="" exclude_sections="" show_name="true" show_description="true" show_status="true" show_modified="true" show_due="true" show_tasks_description="true" show_tasks_assignee="true" show_tasks_subtasks="true" show_tasks_completed="true" show_tasks_due="true" show_tasks_attachments="true" show_tasks_tags="true" show_tasks_comments="false" sort_tasks_by="" /]
```
