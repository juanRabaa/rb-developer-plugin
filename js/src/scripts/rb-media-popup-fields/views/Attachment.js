/**
 * wp.media.view.Attachment
 *
 * @memberOf wp.media.view
 *
 * @class
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 */

const oldInitialize = wp.media.view.Attachment.prototype.initialize;

wp.media.view.Attachment.prototype.initialize = function(){
    oldInitialize.apply(this, arguments);
	// this.listenTo( this.controller.states, 'attachment:compat:waiting attachment:compat:ready', this.updateSave );
    this.listenTo( this.controller.states, 'attachment:rbfields:waiting attachment:rbfields:ready', this.updateSave );
};
