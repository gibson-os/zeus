Ext.define('GibsonOS.module.zeus.index.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleZeusIndexApp'],
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
            xtype: 'gosModuleZeusHomeGrid'
        }];
        me.tools = [{
            type: 'gear',
            tooltip: 'Einstellungen',
            handler() {
                const window = new GibsonOS.module.core.component.form.Window({
                    url: baseDir + 'zeus/index/form',
                }).show();

                window.down('form').getForm().on('actioncomplete', () => {
                    window.close();
                });
            }
        }];

        me.callParent();
    }
});