Ext.define('GibsonOS.module.zeus.model.Price', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'total',
        type: 'float'
    },{
        name: 'energy',
        type: 'float'
    },{
        name: 'tax',
        type: 'float'
    },{
        name: 'startsAt',
        type: 'string'
    },{
        name: 'endsAt',
        type: 'string'
    }]
});