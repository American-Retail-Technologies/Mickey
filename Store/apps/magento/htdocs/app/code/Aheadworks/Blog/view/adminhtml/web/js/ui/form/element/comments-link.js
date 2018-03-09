/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function (ko, Abstract, $t) {
    'use strict';

    return Abstract.extend({
        defaults: {
            url: '',
            linkLabel: $t('Go To Comments'),
            template: 'Aheadworks_Blog/ui/form/element/comments-link'
        },

        /**
         * Init component
         */
        initialize: function () {
            this._super()
                .initVisibility();

            return this;
        },

        /**
         * Set initial visibility state
         * @returns {*}
         */
        initVisibility: function () {
            if (!this.url) {
                this.visible(false);
            }

            return this;
        }
    });
});
