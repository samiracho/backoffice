Ext.define('BO.model.Photo', {
    extend: 'Ext.data.Model',
    idProperty: 'foto_id',
    fields: [
    {
        name: 'foto_id',
        defaultValue: '',
		type:'int'
    }, {
        name: 'foto_titulo',
        defaultValue: '',
		type:'string'
    }, {
        name: 'foto_imagen',
        defaultValue: '',
		type:'string'
    }, {
        name: 'foto_miniatura',
        defaultValue: '',
		type:'string'
    }, {
        name: 'foto_proyecto_id',
        defaultValue: '',
		type:'int'
		
    }],
	// proxy
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=foto&action=getByProject','','')
});