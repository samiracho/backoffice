Ext.define('BO.model.Role', {
    extend: 'Ext.data.Model',
    fields: [
    {
        name: 'rol_id',
        defaultValue: '',
		type: 'int'
    }, {
        name: 'rol_nombre',
        defaultValue: '',
		type: 'string'
    }, {
        name: 'rol_descripcion',
        defaultValue: '',
		type: 'string'
    }, {
        name: 'rol_basico',
        defaultValue: '',
		type: 'boolean'
    }, {
        name: 'rol_permisos',
        defaultValue: '',
		type: 'string'
    }],
	
	// proxy
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=rol&action=read','index.php?controller=rol&action=add','index.php?controller=rol&action=destroy')
});