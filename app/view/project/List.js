Ext.define('BO.view.project.List' ,{
    extend: 'BO.lib.components.crudGrid',
    alias : 'widget.projectlist',
    title : 'Proyectos',
	editWidget: 'projectedit',
	model:'BO.model.Project',
	glyph: 0xf0d1,
	adminAccess: CONFIG.administar_noticias,
	columns: [
		{header: 'Fecha',  dataIndex: 'proyecto_fecha', renderer: Ext.util.Format.dateRenderer('d/m/Y')},
		{header: 'Título',  dataIndex: 'proyecto_titulo',  flex: 0.7},
		{header: 'Categoría',  dataIndex: 'categoria_nombre', flex: 0.3}
    ]
});