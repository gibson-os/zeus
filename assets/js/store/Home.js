Ext.define('GibsonOS.module.zeus.store.Home', {
    extend: 'GibsonOS.data.Store',
    alias: ['hcZeusHomeStore'],
    autoLoad: true,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'zeus/index/homes',
        method: 'GET'
    },
    model: 'GibsonOS.module.zeus.model.Home'
});