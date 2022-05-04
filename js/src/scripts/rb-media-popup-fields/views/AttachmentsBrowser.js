const OldAttachmentsBrowser = wp.media.view.AttachmentsBrowser;
/**
 * wp.media.view.AttachmentsBrowser
 *
 * @memberOf wp.media.view
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 *
 * @param {object}         [options]               The options hash passed to the view.
 * @param {boolean|string} [options.filters=false] Which filters to show in the browser's toolbar.
 *                                                 Accepts 'uploaded' and 'all'.
 * @param {boolean}        [options.search=true]   Whether to show the search interface in the
 *                                                 browser's toolbar.
 * @param {boolean}        [options.date=true]     Whether to show the date filter in the
 *                                                 browser's toolbar.
 * @param {boolean}        [options.display=false] Whether to show the attachments display settings
 *                                                 view in the sidebar.
 * @param {boolean|string} [options.sidebar=true]  Whether to create a sidebar for the browser.
 *                                                 Accepts true, false, and 'errors'.
 */

/**
*   Extends AttachmentsBrowser to generate the view for the rbfields
*/
wp.media.view.AttachmentsBrowser = OldAttachmentsBrowser.extend(/** @lends wp.media.view.AttachmentsBrowser.prototype */{
	createSingle: function() {
		var sidebar = this.sidebar,
			single = this.options.selection.single();

        sidebar.set( 'rbfields', new wp.media.view.RBAttachmentFields({
            controller: this.controller,
            model:      single,
            priority:   120
        }) );

        OldAttachmentsBrowser.prototype.createSingle.apply(this, arguments);
	},

	disposeSingle: function() {
		var sidebar = this.sidebar;
		sidebar.unset('rbfields');
		// Hide the sidebar on mobile.
        OldAttachmentsBrowser.prototype.disposeSingle.apply(this, arguments);
	}
});
