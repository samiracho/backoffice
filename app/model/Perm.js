Ext.define('BO.model.Perm', {
    extend: 'Ext.data.Model',
    idProperty: 'permiso_id',
    fields: [
    {
        name: 'permiso_id',
        defaultValue: '',
		type:'int'
    }, {
        name: 'permiso_nombreinterno',
        defaultValue: '',
		type:'string'
    }, {
        name: 'permiso_nombre',
        defaultValue: '',
		type:'string'
    }, {
        name: 'permiso_descripcion',
        defaultValue: '',
		type:'string'
		
    }],
	
	// proxy
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=rol&action=getPermList','','')
});