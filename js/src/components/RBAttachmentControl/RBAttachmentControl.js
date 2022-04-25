import React, { useState, useEffect } from "react";
const { MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { __ } = wp.i18n;
const { useSelect } = wp.data;
const { Button, Spinner, ResponsiveWrapper } = wp.components;
import './styles.scss';

/**
*   @param {int[]} attachmentsIDs                                               Array of attachments ids. If its not a gallery, only the first index is used
*   @param {bool} gallery                                                       Indicates if multiple attachments can be selected.
*   @param {string[]} allowedMediaTypes                                         Array of allowed media types
*   @param {callback} onChange                                                  Function to excecute when the attachments change. Recieves an object with `attachments` key.
*                                                                               `attachments` is allways an array, independently of it being a gallery or a single attachment selector.
*   @param {object} labels                                                      Object that defined the labels to use in the component
*/
export default function RBAttachmentControl(props){
    const attachmentsIDs = props.attachmentsIDs?.length ? props.attachmentsIDs : [];
    const {
        gallery = false,
        allowedMediaTypes = [ 'image' ],
        onChange,
        labels: passedLabels = {},
    } = props;

    function getLabels(){
        const galleryDefaultLabels = {
            popupTitle: __( "Modify Gallery", 'rb-plugin' ),
            imageAlt: __( "Selected Image", 'rb-plugin' ),
            emptyAction: __( "Select Images", 'rb-plugin' ),
            modifyAction: __( "Change Images", 'rb-plugin' ),
            remove: __( "Remove All", 'rb-plugin' ),
            permission: __( "To edit this gallery, you need permission to upload media.", 'rb-plugin' ),
        };

        const singleDefaultLabels = {
            popupTitle: __( "Selecte Image", 'rb-plugin' ),
            imageAlt: __( "Selected Image", 'rb-plugin' ),
            emptyAction: __( "Select Image", 'rb-plugin' ),
            modifyAction: __( "Change Image", 'rb-plugin' ),
            remove: __( "Remove", 'rb-plugin' ),
            permission: __( "To edit the image, you need permission to upload media.", 'rb-plugin' ),
        };

        let labels = {};

        if(gallery)
            labels = galleryDefaultLabels;
        else
            labels = singleDefaultLabels;

        if(passedLabels)
            labels = {...labels, passedLabels};

        return labels;
    }


    const labels = getLabels();
    const attachments = useSelect( ( select, props ) => {
        const { getMedia } = select( 'core' );
        const result = [];
        if(attachmentsIDs){
            attachmentsIDs.forEach((attachmentID, i) => {
                const media = attachmentID ? getMedia( attachmentID ) : null;
                if(media)
                    result.push(media);
            });
        }
        return result;
    } );
    const hasAttachments = attachmentsIDs?.length > 0;
    const attachmentsDataLoaded = (attachments && attachments.length > 0) || !hasAttachments;
    const mediaComponentValue = gallery ? attachmentsIDs : attachmentsIDs?.[0];

    const doOnChange = (attachments) => {
        if(onChange)
            onChange({
                attachments: gallery ? attachments?.map(({id}) => id)  : [attachments.id],
            });
    }

    return (
        <div className="rb-attachments-control">
            <MediaUploadCheck fallback={ <p>{ labels.permission }</p> }>
                <MediaUpload
                    title={ labels.popupTitle }
                    onSelect={ doOnChange }
                    allowedTypes={ allowedMediaTypes }
                    value={ mediaComponentValue }
                    multiple = { gallery }
                    gallery = { gallery }
                    render={ ( { open } ) => (
                        <Button
                            className={ !hasAttachments ? 'editor-post-featured-image__toggle' : 'editor-post-featured-image__preview' }
                            onClick={ open }>
                            { !hasAttachments && attachmentsDataLoaded && ( labels.emptyAction ) }
                            { !attachmentsDataLoaded && <Spinner /> }
                            { hasAttachments && attachmentsDataLoaded &&
                                <div>
                                    { gallery &&
                                    <>
                                        <div className={`gallery-images`}>
                                            { attachments?.map( ({source_url}, index) =>
                                                <img src={source_url} key={`${source_url}_${index}`} alt={`Gallery image ${index}`} className={`gallery-image`} />
                                            )}
                                        </div>
                                        <p>{labels.modifyAction}</p>
                                    </>
                                    }
                                    { !gallery &&
                                    <img src={ attachments[0].source_url } alt={ labels.imageAlt } />
                                    }
                                </div>
                            }
                        </Button>
                    ) }
                />
            </MediaUploadCheck>
            { !! attachmentsIDs &&
            <MediaUploadCheck>
                <Button onClick={ () => doOnChange(null) } isLink isDestructive>
                    { labels.remove }
                </Button>
            </MediaUploadCheck>
            }
        </div>
    )
};
