Ext.define('BO.view.role.List' ,{
    extend: 'BO.lib.components.crudGrid',
    alias : 'widget.rolelist',
    title : 'Roles',
	editWidget: 'roleedit',
	model:'BO.model.Role',
	glyph:0xf132,
	adminAccess: CONFIG.administar_roles,
	columns: [
		{header: 'Rol',  dataIndex: 'rol_nombre',  flex: 1},
		{header: 'Descripcion', dataIndex: 'rol_descripcion', flex: 1}
    ]
});