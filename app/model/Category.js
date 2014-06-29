Ext.define('BO.model.Category', {
    extend: 'Ext.data.Model',
	// campos
	fields: [
    {
        name: 'categoria_id',
		defaultValue: 0,
		type: 'int'
    },{
        name: 'categoria_nombre',
        defaultValue: '',
		type: 'string'
    }],
	
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=categoria&action=read','index.php?controller=categoria&action=add','index.php?controller=categoria&action=destroy')
	
});
