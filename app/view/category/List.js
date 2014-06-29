Ext.define('BO.view.category.List' ,{
    extend: 'BO.lib.components.crudGrid',
    alias : 'widget.categorylist',
    title : 'Categorías',
	editWidget: 'categoryedit',
	model:'BO.model.Category',
	glyph:0xf0e8,
	adminAccess: CONFIG.administar_categorias,
	columns: [
		{header: 'Nombre',  dataIndex: 'categoria_nombre',  flex: 1}
    ]
});