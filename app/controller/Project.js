Ext.define('BO.controller.Project', {
    extend: 'BO.controller.Crud',
	views: [
        'project.Edit',
		'project.List'
    ],
	refs: [
        {
            ref: 'list',
            selector: 'grid'
        }
    ],
	models: ['Project', 'Category'],
    init: function() {
		var me = this;
		
		
        me.control({
			'projectedit form uploadbutton': {
                afterrender: this.setupUploadButton
            },
			'projectedit uploadcustombutton[action=addimage]': {
				success: this.uploadSucceed
			},
			'projectedit button[action=delete]': {
				click: this.deleteImage
			},
			'projectedit dataview': {
				select: this.selectImage,
				deselect: this.deselectImage
			},
			'projectedit htmleditor': {
				resize: this.resizeHtmlEditor
			}
		});
		
		this.callParent(arguments);
    },
	
	setupUploadButton: function(uploadbutton){
		
		uploadbutton.url = 'index.php?controller=proyecto&action=upload';
		uploadbutton.urlRel = CONFIG.UrlDocumentos;	
	},
	
	afterLoad: function(record, form){
		var tabPrincipal = form.down('tabpanel').setActiveTab(0);
		var fotosStore = record.getPhotos();
		
		fotosStore.load();
		
		this.dataviewImages = form.down('dataview');
		this.buttonDelete = form.down('button[action=delete]');		
		this.dataviewImages.setStore(fotosStore);
		this.buttonDelete.disable(true);	
		form.down('#tabImagenes').setDisabled(record.phantom); 
		
		return true;
	},
	
	uploadSucceed: function(uploadButton, fp, response){
		var me = this;
		var fotosStore = me.dataviewImages.getStore();
		var record = Ext.create('BO.model.Photo', {foto_imagen: response.result.data['nombre']});
		fotosStore.add(record);
	},
	
	selectImage: function(dataview){
		this.buttonDelete.enable(true);
	},
	
	deselectImage: function(dataview){
		this.buttonDelete.disable(true);
	},
	
	deleteImage: function(button){
		var record = this.dataviewImages.getSelectionModel().getSelection()[0];	
		if(record)this.deleteRecord(null, record);
	},
	
	syncCallBack: function(batch, options, win, store, record){
		// por defecto mostramos confirmación y cerramos la ventana
		var myStore = record.getPhotos();

		if( myStore.getModifiedRecords().length > 0 ){
			myStore.sync({
				callback:function(batch,options){
					Ext.MessageBox.hide();
					if(batch.hasException()){							
						myStore.remove(record); // Bug ExtJS 5 (no borra records nuevos)			
						myStore.rejectChanges();
						return false; 
					}
					else {
						BO.lib.commonFunctions.confirmacion();
						win.close();
					}
				}
			});
		} else {
		
			BO.lib.commonFunctions.confirmacion();
			win.close();
		}
	},
	resizeHtmlEditor: function (comp){
		var txtarea = comp.inputCmp.textareaEl;
		var iframeEl = comp.inputCmp.iframeEl;	
		var width = comp.getWidth();
		var height = comp.getSize().height - comp.getToolbar().getSize().height - 10;
		
		txtarea.setWidth( width );
		txtarea.setHeight( height );
		iframeEl.setWidth( width );
		iframeEl.setHeight( height );
	}
});