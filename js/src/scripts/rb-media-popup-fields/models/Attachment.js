/**
 * wp.media.model.Attachment
 *
 * @memberOf wp.media.model
 *
 * @class
 * @augments Backbone.Model
 */


/**
*   Updates attachment meta data and updates model with the latest values
*/
wp.media.model.Attachment.prototype.saveRBFields = function( data, options ) {
    var model = this;

    // If we do not have the necessary nonce, fail immediately.
    if ( ! this.get('nonces') || ! this.get('nonces').update ) {
        return $.Deferred().rejectWith( this ).promise();
    }

    return wp.apiFetch( {
        path: `/wp/v2/media/${this.id}`,
        method: 'POST',
        data: {
            meta: {
                ...data,
            },
        },
    } ).then( function( resp, status, xhr ) {
        model.set( model.parse( {
            rbfields: {
                ...model.get("rbfields"),
                values: resp.meta ?? {},
            },
        }, xhr ), options );
    });
};
