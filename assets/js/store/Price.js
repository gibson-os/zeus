Ext.define('GibsonOS.module.zeus.store.Price', {
    extend: 'GibsonOS.data.Store',
    alias: ['hcZeusPriceStore'],
    autoLoad: true,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'zeus/index/prices',
        method: 'GET'
    },
    model: 'GibsonOS.module.zeus.model.Price'
});