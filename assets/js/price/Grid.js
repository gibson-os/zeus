Ext.define('GibsonOS.module.zeus.price.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleZeusPriceGrid'],
    multiSelect: false,
    enableDrag: false,
    enableToolbar: false,
    homeId: null,
    initComponent(arguments) {
        let me = this;

        me.store = new GibsonOS.module.zeus.store.Price();
        me.store.getProxy().setExtraParam('homeId', me.homeId);

        me.callParent(arguments);
    },
    getColumns() {
        return [{
            header: 'Von',
            dataIndex: 'startsAt',
            flex: 1
        },{
            header: 'Bis',
            dataIndex: 'endsAt',
            flex: 1
        },{
            header: 'Preis Total',
            dataIndex: 'total',
            flex: 1,
            align: 'right',
            renderer: function(value) {
                return value + '€/kWh';
            }
        },{
            header: 'Preis Energie',
            dataIndex: 'energy',
            flex: 1,
            align: 'right',
            renderer: function(value) {
                return value + '€/kWh';
            }
        },{
            header: 'Preis Steuern',
            dataIndex: 'tax',
            flex: 1,
            align: 'right',
            renderer: function(value) {
                return value + '€/kWh';
            }
        }];
    }
});