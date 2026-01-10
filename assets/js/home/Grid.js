Ext.define('GibsonOS.module.zeus.home.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleZeusHomeGrid'],
    multiSelect: false,
    enableDrag: false,
    initComponent(arguments) {
        let me = this;

        me.store = new GibsonOS.module.zeus.store.Home();

        me.callParent(arguments);
    },
    getColumns() {
        return [{
            header: 'Name',
            dataIndex: 'name',
            flex: 2
        },{
            header: 'Größe',
            dataIndex: 'size',
            align: 'right',
            flex: 1,
            renderer: function(value) {
                return value + 'm²';
            }
        },{
            header: 'Bewohner',
            dataIndex: 'residents',
            align: 'right',
            flex: 1
        }];
    },
    enterButton: {
        iconCls: 'icon_system system_show',
        text: 'Anzeigen'
    },
    enterFunction(record) {
        const homeApp = new GibsonOS.module.zeus.home.App({
            gos: {
                data: {
                    homeId: record.get('id'),
                }
            }
        });
        homeApp.setTitle(record.get('name'));
    }
});