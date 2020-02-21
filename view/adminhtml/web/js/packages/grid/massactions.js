define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/grid/massactions',
    'Swissup_Marketplace/js/packages/helper',
    'Swissup_Marketplace/js/installer/helper'
], function (_, registry, Massactions, packageHelper, installer) {
    'use strict';

    return Massactions.extend({
        /**
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            action.index = action.type;
            action.href = action.url;

            if (!data.selected) {
                return;
            }

            if (!this.hasVisibleAction(data.selected, action)) {
                return;
            }

            if (action.index === 'install' && this.isAllDownloaded(data.selected)) {
                installer.render(data.selected);
            } else {
                this.source
                    .submit(action, data.selected)
                    .done(function () {
                        if (action.index === 'install') {
                            installer.render(data.selected);
                        }

                        setTimeout(function () {
                            this.selections().deselectAll();
                        }.bind(this), 300);
                    }.bind(this));
            }
        },

        /**
         * @param {Array} packages
         * @param {Object} action
         * @return {Boolean}
         */
        hasVisibleAction: function (packages, action) {
            return _.some(packages, function (packageName) {
                var packageData = this.findPackageData(packageName);

                return packageData && packageHelper.isActionVisible(packageData, action);
            }, this);
        },

        /**
         * @param {Array} packages
         * @return {Boolean}
         */
        isAllDownloaded: function (packages) {
            return _.every(packages, function (packageName) {
                var packageData = this.findPackageData(packageName);

                return packageData && packageData.downloaded;
            }, this);
        },

        /**
         * @param {String} packageName
         * @return {Object|null}
         */
        findPackageData: function (packageName) {
            var packageData = _.find(this.source.rows, function (row) {
                return row.name === packageName;
            });

            if (!packageData) {
                packageData = _.find(this.source.storage().data, function (row) {
                    return row.name === packageName;
                });
            }

            return packageData;
        }
    });
});
