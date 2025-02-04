define([
    'Magento_Ui/js/grid/listing'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'Lachestry_CronMonitoring/ui/grid/listing'
        },

        getCellClass: function (row, col) {
            const handlerMap = {
                status: this._handleStatus,
            }

            const handler = handlerMap[col.index] || null;
            if (handler) {
                return handler(row, col);
            }
        },

        _handleStatus: function (row) {
            return row.status_class;
        },
    });
});

