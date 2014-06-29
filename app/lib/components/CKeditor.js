Ext.define('BO.lib.components.CKeditor', {    
	extend : 'Ext.form.field.TextArea',
    alias : 'widget.ckeditor',
    xtype: 'ckeditor',
    initComponent : function(){
        

		this.callParent(arguments);
        this.on('afterrender', function(){
            
			/*Ext.apply(this.CKConfig ,{
                    customConfig: '',
					height : this.getHeight(),
					language: 'fr',
					baseFloatZIndex: 20000
            });*/
			
			
			toolbarConf = [
				{ name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'PasteFromWord', '-' ] },
				{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic','-', 'RemoveFormat' ] },
				{ name: 'paragraph', groups: [ 'list', 'align'], items: [ 'NumberedList', 'BulletedList','Link', '-','JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
				'/',
				{ name: 'styles', items: [ 'Styles', 'Format'] },
				{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
				{ name: 'tools', items: [ 'Maximize']}
			];
			
			
			
            this.editor = CKEDITOR.replace(this.inputEl.id,{
                    customConfig: '',
					height : this.getHeight() -60,
					language: 'es',
					baseFloatZIndex: 20000,
					removePlugins: 'elementspath' ,
					resize_enabled: false,
					toolbar: toolbarConf
            });
            this.editorId =this.editor.id;
        },this);
    },
    onRender : function(ct, position){
        if(!this.el){
            this.defaultAutoCreate ={
                tag : 'textarea',
                autocomplete : 'off'
            };
        }
        this.callParent(arguments)
    }/*,
    setValue  : function(value){
        this.callParent(arguments);
        if(this.editor){
            this.editor.setData(value);
        }
    }*/,
    getRawValue: function(){
        if(this.editor){
            return this.editor.getData()
        }else{
            return''
        }
    }
});


CKEDITOR.on('instanceReady',function(e){
    var o = Ext.ComponentQuery.query('ckeditor[editorId="'+ e.editor.id +'"]'),
    comp = o[0];
    e.editor.resize(comp.getWidth(), comp.getHeight() -60)
    comp.on('resize',function(c,adjWidth,adjHeight){
        c.editor.resize(adjWidth, adjHeight)
    })
});