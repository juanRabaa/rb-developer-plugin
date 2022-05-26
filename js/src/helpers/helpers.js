/**
*   Freezes an object and all of its inner properties recursively
*/
export function deepFreeze(obj){
    Object.keys(obj).forEach(prop => {
        if (typeof obj[prop] === 'object') deepFreeze(obj[prop]);
    });
    return Object.freeze(obj);
};

/**
*   Listens to an element removal from the DOM and runs a callback when it happens
*   @param {DOMElement} element
*   @param {function} onDetachCallback                                          The function to run on removal
*   @return {MutationObserver}                                                  The MutationObserver. The listener can be
*                                                                               removed with the `disconnect` method.
*/
export function onElementRemoved(element, onDetachCallback) {
    const observer = new MutationObserver(function () {
        function isDetached(el) {
            if (el.parentNode === document) {
                return false;
            } else if (el.parentNode === null) {
                return true;
            } else {
                return isDetached(el.parentNode);
            }
        }

        if (isDetached(element)) {
            observer.disconnect();
            onDetachCallback();
        }
    })

    observer.observe(document, {
         childList: true,
         subtree: true
    });

    return observer;
}
