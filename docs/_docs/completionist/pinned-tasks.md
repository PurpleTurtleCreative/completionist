---
title: Pinned Tasks
parent: Completionist
nav_order: 3
---

# Pinned Tasks
{: .no_toc }

Reduce context-switching and increase productivity by pinning Asana tasks directly to the content needing work done.
{: .text-beta .fw-300 .text-grey-dk-000}

## Table of Contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## View Pinned Tasks

Pinned tasks are displayed in the Tasks metabox on the edit screen of the WordPress content to which they've been pinned.

To quickly review and access pinned tasks, check out the [Dashboard Widget](https://docs.purpleturtlecreative.com/completionist/dashboard-widget/).

###  Task Visibility

There are a few reasons why a task might not be displayed even though it is pinned to the current post. Completionist respects collaborators' privacy and has automatic clean-up methods in place. Here are some reasons why a pinned task may be missing:

- If **the task no longer has the site tag**, Completionist will automatically unpin the task because it is no longer relevant to the WordPress site. If this was a mistake, simply [pin the existing Asana task](#pin-an-existing-task) back onto the post.
- **You may not be authorized to view the task in Asana.** This can happen when someone pins a task that is private to them or you are not a member of the task's project. To resolve this, ask someone to add you as a follower of the task.
- **The request to Asana may have failed.** Since tasks are hosted and managed by Asana, task data is dependent on [Asana's API servers](https://status.asana.com/) being operational. If a technical error is encountered, an error will be displayed.

### Supported Post Types

Tasks can be pinned to more than just WordPress *posts* and *pages*. Many popular plugins register their own custom post types to help with data management.

For example, [LearnDash](https://www.learndash.com/) registers custom post types for courses, modules, and topics. [WooCommerce](https://woocommerce.com/) implements their own custom post types for shop orders, products, and coupons.

Because **Completionist supports pinning tasks on any post type in WordPress**, you can easily collaborate on all the content your team manages on WordPress.

Furthermore, **Completionist is compatible with the Gutenberg block editor and WordPress Classic editor** to ensure support for all post types and preferences.

## Pin a New Task

Create a new Asana task to pin to the relevant content.

1. Navigate to the edit screen for the content in WordPress.
2. In the *Tasks* metabox, click the green plus sign (+) to show the task creation form.
3. Fill in the task details and submit the form.

Upon successful creation, the task will be added to Asana with your chosen site tag and displayed in the WordPress content's *Tasks* metabox.

## Pin an Existing Task

If a task already exists in Asana, you can pin it to your WordPress content.

1. In Asana, click the task you'd like to pin to view its details and options.
2. At the top of the task's details pane in Asana, click the chainlink icon to copy the task link.
3. In WordPress, navigate to the edit screen for the content to which you'd like to pin the Asana task.
4. In the *Tasks* metabox, paste the copied Asana task link in the input field at the top of the metabox.
5. Click the blue thumbtack (or press Enter) to confirm pinning the task.

If the task was pinned successfully, the site tag will be added to the Asana task and it will be displayed in the WordPress content's *Tasks* metabox.

<div class="banner banner-danger">
  <h3>
    Foreign Tasks
  </h3>
  <p>
    Tasks not belonging to the assigned site workspace are not able to be pinned. This ensures proper security, privacy, and tidiness.
  </p>
</div>

## Manage Pinned Tasks

There are various actions you can perform on pinned tasks within WordPress.

### Mark Complete

Click the grey checkmark to the left of a task to mark it complete. Upon success, the checkmark will turn green and the task's title will be crossed out.

You can also click a completed task's checkmark to mark the task as incomplete.

### View the Task Description

Tasks with additional details have a yellow note icon after their title.

Hover the task's title to reveal its description.

### Task Actions

When hovering pinned tasks, a row of actions will be displayed:

- Click the grey chainlink to **view the task in Asana.** This is useful when you want to edit a task or view its comments thread.
- Click the blue thumbtack to **unpin the task.** This will also remove the site tag from the task in Asana, so Completionist will no longer associate the task with the WordPress site.
- Click the red minus sign to **delete the task.** This will delete the task in Asana, so Completionist will not display it on the WordPress site.