Ext.define('GibsonOS.module.zeus.home.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleZeusHomeApp'],
    title: 'Zeus',
    appIcon: 'icon_exe',
    width: 800,
    height: 400,
    requiredPermission: {
        module: 'zeus',
    },
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleZeusPriceGrid',
            homeId: me.gos.data.homeId
        }];

        me.callParent();
    }
});