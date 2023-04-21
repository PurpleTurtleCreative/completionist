/**
 * Global namespace variable for this plugin's frontend scripts.
 *
 * @since [unreleased]
 */

import { createHooks } from '@wordpress/hooks';

/**
 * Initializes the plugin's global namespace if it doesn't
 * already exist.
 *
 * If you need to add data for the plugin to use on the frontend,
 * then don't do it via WordPress's PHP functions. You should
 * instead fetch the data asynchronously from an API
 * endpoint. This keeps things performant and forces cleaner
 * code organization on the frontend and backend. Additionally,
 * it means the frontend isn't dependent on mysterious global
 * variables already existing within the frontend. That's not
 * very secure since those globals could be missing or hijacked.
 * Additionally, requiring a fetch to retrieve data also makes
 * it easier to follow where data is coming from because it has
 * an explicit route. Global variables in JavaScript can be
 * generated through PHP or another JavaScript script, so their
 * origin and modifications can be tricky to fully track down.
 *
 * @since [unreleased]
 */
export default function initGlobalNamespace() {

  if (
    'Completionist' in window &&
    'undefined' !== typeof window.Completionist
  ) {
    // Plugin has already been added.
    return;
  }

  // Add the plugin's data to the global namespace.
  window.Completionist = {
    "hooks": createHooks()
  };
}
