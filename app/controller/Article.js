Ext.define('BO.controller.Article', {
    extend: 'BO.controller.Crud',
	views: [
        'article.Edit',
		'article.List'
    ],
	models: ['Article', 'Category'],
    init: function() {
		var me = this;
		
		
        me.control({
			'articleedit form uploadbutton': {
                afterrender: this.setupUploadButton
            }
		
		});
		
		this.callParent(arguments);
    },
	
	setupUploadButton: function(uploadbutton){
		
		uploadbutton.url = 'index.php?controller=noticia&action=upload';
		uploadbutton.urlRel = CONFIG.UrlDocumentos;	
	}
});