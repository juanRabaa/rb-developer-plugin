import { unmountComponentAtNode } from "react-dom";
import renderField from "../renderField";
const View = wp.media.View;

/**
 * wp.media.view.RBAttachmentFields
 *
 * A view to display fields added via the `RB_Post_Meta_Fields_Manager` class.
 *
 * @memberOf wp.media.view
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */
wp.media.view.RBAttachmentFields = View.extend(/** @lends wp.media.view.AttachmentCompat.prototype */{
	tagName:   'form',
	className: 'rb-fields',

	events: {
		'submit':          'preventDefault',
		'change input':    'save',
		'change select':   'save',
		'change textarea': 'save'
	},

	initialize: function() {
		this.listenTo( this.model, 'change:compat', this.render );
	},
	/**
	 * @return {wp.media.view.AttachmentCompat} Returns itself to allow chaining.
	 */
	dispose: function() {
		if(this.rbfieldsContainers) // unmount react fields components
			this.rbfieldsContainers.forEach(($placeholder, i) => unmountComponentAtNode($placeholder[0]));

		if ( this.$(':focus').length ) {
			this.save();
		}
		/**
		 * call 'dispose' directly on the parent class
		 */
		return View.prototype.dispose.apply( this, arguments );
	},
	/**
	 * @return {wp.media.view.AttachmentCompat} Returns itself to allow chaining.
	 */
	render: function() {
        // REVIEW: Render the react component here?
		// var compat = this.model.get('compat'); // REVIEW: Where is the view comming from? ans: from get_compat_media_markup
		const rbfields = this.model.get("rbfields");
		this.rbfieldsContainers = [];
		this.views.detach();
		this.$el.html(rbfields.placeholder);
		this.views.render();
		// TODO: FIELDS SHOULD BE AN ARRAY, because a json has no order. Needs to be change in fields managers
		Object.keys(rbfields.fields).forEach((metaKey, i) => {
			const fieldConfig = rbfields.fields[metaKey].field;
			const $placeholder = this.$el.find(`#rb-media-field-placeholder__${metaKey}`);
			this.rbfieldsContainers.push($placeholder);
			renderField({
				$el: $placeholder,
				value: rbfields.values[metaKey] ?? "",
				fieldConfig,
			})
		});

		return this;
	},
	/**
	 * @param {Object} event
	 */
	preventDefault: function( event ) {
		event.preventDefault();
	},
	/**
	 * @param {Object} event
	 */
	save: function( event ) {
		const data = {};
		const rbfields = this.model.get("rbfields");

		if ( event ) {
			event.preventDefault();
            const mainFieldAttr = event.currentTarget.getAttribute("data-rb-main-value");
            const isMainField = typeof mainFieldAttr !== 'undefined' && mainFieldAttr !== null && mainFieldAttr !== false;

            if(!isMainField){
                return;
            }
		}

		_.each( this.$el.serializeArray(), function( pair ) {
			try {
				// For some reason, serializeArray returns inputs that are not from custom fields, should check that out
				if(rbfields.values.hasOwnProperty(pair.name))
					data[ pair.name ] = JSON.parse(pair.value);
			} catch (e) {
				console.log(e);
			}
		});

		this.controller.trigger( 'attachment:rbfields:waiting', ['waiting'] );
		// Save post meta
		// see wp.media.model.Attachment.saveCompat (media-models.js:214)
		this.model.saveRBFields( data ).then( _.bind( this.postSave, this ) );
	},

	postSave: function() {
		// End post meta save
		this.controller.trigger( 'attachment:rbfields:ready', ['ready'] );
	}
});
