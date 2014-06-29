Ext.define('BO.model.User', {
    extend: 'Ext.data.Model',
	//requires:['BO.model.Role'],
	idProperty:'usuario_id',
	// campos
	fields: [
    {
        name: 'usuario_id',
		defaultValue: 0,
		type: 'int'
    },{
        name: 'usuario_rol_id',
        defaultValue: 2,
		type: 'int'
    },{
        name: 'usuario_nombre',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_apellidos',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_login',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_email',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_password',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_password2',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_detalles',
        defaultValue: '',
		type: 'string'
    },{
        name: 'usuario_enviarmail',
        defaultValue: '',
		type: 'string'
    }],
	proxy: BO.lib.ajaxHandler.buildProxy('index.php?controller=usuario&action=read','index.php?controller=usuario&action=add','index.php?controller=usuario&action=destroy')
});
