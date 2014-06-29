Ext.define('BO.view.user.List' ,{
    extend: 'BO.lib.components.crudGrid',
    alias : 'widget.userlist',
    title : 'Usuarios',
	editWidget: 'useredit',
	model:'BO.model.User',
	glyph:0xf0c0,
	adminAccess: CONFIG.administar_usuarios,
	columns: [
		{header: 'Nombre',  dataIndex: 'usuario_nombre',  flex: 1},
        {header: 'Apellidos',  dataIndex: 'usuario_apellidos',  flex: 1},
        {header: 'login',  dataIndex: 'usuario_login',  flex: 1},
		{header: 'Email', dataIndex: 'usuario_email', flex: 1}
    ]
});