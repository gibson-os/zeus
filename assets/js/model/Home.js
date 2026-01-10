Ext.define('GibsonOS.module.zeus.model.Home', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'residents',
        type: 'int'
    },{
        name: 'size',
        type: 'int'
    }]
});