Ext.define('BO.model.Article', {
	extend:'Ext.data.Model',
	idProperty:'noticia_id',
	// campos
	fields: [
    {
        name: 'noticia_id',
        defaultValue: 0,
		type: 'int'
    },{
        name: 'noticia_usuario_id',
        defaultValue: 1,
		type: 'string'
    },{
        name: 'noticia_titulo',
        defaultValue: '',
		type: 'string'
    },{
        name: 'noticia_imagen',
        defaultValue: '',
		type: 'string'
    },{
        name: 'noticia_fecha',
		type: 'date', 
		dateFormat: 'd/m/Y',
		defaultValue: new Date()
		
    },{
        name: 'sortnoticia_fecha',
        defaultValue: '',
		type: 'string'
    },{
        name: 'noticia_cuerpo',
        defaultValue: '',
		type: 'string'
    },{
        name: 'noticia_publicada',
        defaultValue: 1
    },{
        name: 'noticia_portada',
        defaultValue: 0
    },{
        name: 'noticia_categoria_id',
        defaultValue: 1,
		type: 'int'
    },{
        name: 'categoria_nombre',
        defaultValue: '',
		type: 'string'
    }],
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=noticia&action=read','index.php?controller=noticia&action=add','index.php?controller=noticia&action=destroy')
});