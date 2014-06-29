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
        defaultValue: 0,
		type:'int'
		
    }],
	// proxy
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=foto&action=getByProject','index.php?controller=foto&action=add','index.php?controller=foto&action=destroy')
});

Ext.define('BO.model.Project', {
	extend:'Ext.data.Model',
	idProperty:'proyecto_id',
	// campos
	fields: [
    {
        name: 'proyecto_id',
        defaultValue: 0,
		type: 'int'
    },{
        name: 'proyecto_usuario_id',
        defaultValue: 1,
		type: 'string'
    },{
        name: 'proyecto_titulo',
        defaultValue: '',
		type: 'string'
    },{
        name: 'proyecto_ficha',
        defaultValue: '',
		type: 'string'
    },{
        name: 'proyecto_fecha',
		type: 'date', 
		dateFormat: 'd/m/Y',
		defaultValue: new Date()
		
    },{
        name: 'sortproyecto_fecha',
        defaultValue: '',
		type: 'string'
    },{
        name: 'proyecto_cuerpo',
        defaultValue: '',
		type: 'string'
    },{
        name: 'proyecto_publicada',
        defaultValue: 1
    },{
        name: 'proyecto_categoria_id',
        defaultValue: 1,
		type: 'int'
    },{
        name: 'categoria_nombre',
        defaultValue: '',
		type: 'string'
    }],
	associations: [{
        model: 'BO.model.Photo',
		name:'getPhotos',
		foreignKey:'foto_proyecto_id',
		primaryKey:'proyecto_id',
        type: 'hasMany'
    }],
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=proyecto&action=read','index.php?controller=proyecto&action=add','index.php?controller=proyecto&action=destroy')
});