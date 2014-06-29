Ext.define('BO.view.article.List' ,{
    extend: 'BO.lib.components.crudGrid',
    alias : 'widget.articlelist',
    title : 'Noticias',
	editWidget: 'articleedit',
	model:'BO.model.Article',
	glyph:0xf044,
	adminAccess: CONFIG.administar_noticias,
	columns: [
		{header: 'Fecha',  dataIndex: 'noticia_fecha', renderer: Ext.util.Format.dateRenderer('d/m/Y')},
		{header: 'Título',  dataIndex: 'noticia_titulo',  flex: 1},
		{header: 'Categoría',  dataIndex: 'categoria_nombre'}
    ]
});