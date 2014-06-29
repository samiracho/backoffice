Ext.define('BO.lib.components.uploadButton', {
    alias: 'widget.uploadbutton',
    extend: 'Ext.container.Container',
	requires:['Ext.form.field.File','Ext.form.field.Hidden','Ext.layout.container.Column','Ext.Img'],
    url:'',
	desc:'',
	saveFirst:false, // si es true, no dejará subir el archivo hasta que no se hayana guardado los cambios
	urlRel: null, // url que se añadirá al nombre del
	showPreview:true, // mostrar un botón para previsualizar la imagen
	initComponent: function () {
		var me = this;
		
		me.uploadButton = Ext.create('Ext.button.Button', {text: me.text,width:me.width});
		me.fileNameField = Ext.create('Ext.form.field.Hidden', {name: me.name});
		me.previewButton =  Ext.create('Ext.button.Button', {margin:'0 0 0 4', iconCls: 'iconPreview', disabled:true});
		
		me.setWidth(me.width+30);
		
		Ext.applyIf(me, {
			items: [
				{
					xtype: 'container',
					combineErrors: true,
					defaults: {
						hideLabel: true
					},
					items: [		  
						 me.uploadButton,
						 me.previewButton
					]
				},
					me.fileNameField
            ]
        });
		
		// eventos de los controles
		me.uploadWindow = Ext.widget('uploadwindow',{desc:me.desc});
		me.uploadWindow.on('upload',me.onUpload, me);		
		me.uploadButton.on('click',me.onClick, me);
		me.fileNameField.on('change',me.onFileChanged, me);
		me.previewButton.on('click',me.onPreviewClick, me);
		me.on('afterrender', me.onAfterRender, me,{single: true});
		
		me.callParent(arguments);
	},
	onAfterRender: function(comp)
	{
		var me = this;
		// si obligamos a guardar antes de poder adjuntar una imagen
		if(me.saveFirst){
			var bindedField = comp.up('form').down('[name='+me.bindedFieldName+']');
			if(bindedField.getValue() == 0 || bindedField.getValue() == "")me.uploadButton.setDisabled(true);
			bindedField.on('change',me.onBindedFieldChange, me);
		}
	},
	onBindedFieldChange: function(field)
	{
		 var me = this;
		 var docId = field.getValue();
		 if (docId != '' && docId > 0){
		 
			me.uploadButton.setDisabled(false)
		 } else me.uploadButton.setDisabled(true)
	},
	onClick: function(button){
		
		var me = this;
		
		if (me.saveFirst)
		{
			var docId = button.up('form').down('[name='+me.bindedFieldName+']').getValue();
			
			if (docId == '' || docId < 1){	
				 Ext.Msg.alert('¡Atención!', 'Debe guardar los cambios antes de poder subir un archivo');
				return;
			}else{	
				me.docId = docId;
				me.uploadWindow.show();
			}
		}
		else
		{
			me.docId = "";
			me.uploadWindow.show();
		}
	},
	onPreviewClick: function(button)
	{
		var me = this;
		
		var extension = me.fileNameField.getValue().split('.').pop();
		
		if( extension == 'jpg' || extension == 'jpeg' || extension == 'png' || extension == 'bmp' || extension == 'gif')
		{
			var win = new Ext.Window(
			{
				maxWidth:700,
				maxHeight:500,
				minWidth:100,
				minHeight:100,
				modal:true,
				title: 'Previsualizar Imagen',
				autoScroll:false,
				autoShow:true,
				items:{
					xtype:'image',
					src: me.urlRel ? me.urlRel+me.fileNameField.getValue() : me.fileNameField.getValue(),
					listeners: {
					   load : {
							element : 'el',  //the rendered img element
							fn : function(ev, image){
								
								win.setWidth(image.clientWidth+10);
								win.setHeight(image.clientHeight+40);
								
								if(image.clientWidth > win.maxWidth || image.clientHeight > win.maxHeight ){
									win.setAutoScroll(true);
								}
								win.center();
							}
						},single:true
					}
				}
			});
		}
		else
		{
			window.open(me.urlRel ? me.urlRel+me.fileNameField.getValue() : me.fileNameField.getValue(), "_blank");
		}
	},
	onFileChanged : function(field)
	{
		var me = this;
		var empty = Ext.isEmpty(field.getValue());
		me.previewButton.setDisabled(empty);

		
		return;
	},
	onUpload: function(win)
	{
		var me = this;
		var myForm = win.down('form').getForm();

		if (myForm.isValid())
        {
            myForm.submit(
            {
				url: me.url,
				params:{
					id:me.docId,
					nombre: me.fileNameField.getValue()
				},
                waitMsg: 'Espere mientras se envía el archivo...',
                success: function (fp, o)
                {
                    me.fileNameField.setValue(o.result.data['nombre']);
					me.urlRel = o.result.data['urlrel'];
                    win.close();
                    BO.lib.commonFunctions.confirmacion();
                },
                failure: function (form, action)
                {
                    // el manejador de excepciones mostrará un mensaje con la causa del error
					// por lo que no necesito mostrar ningún mensaje más
                    win.down('[name=file_path]').reset();
                }
            });
        }
    }
});

Ext.define('BO.lib.components.uploadWindow', {
    extend: 'Ext.window.Window',
    closeAction: 'hide',
    alias: 'widget.uploadwindow',
    resizable: false,
    modal: true,
    title: 'Subir archivo',
	desc:'',
	requires:['Ext.form.field.File'],
    initComponent: function ()
    {
        var me = this;
        
		me.items = [
        {
            xtype: 'form',
            bodyPadding: 10,
            items: [
            {
                xtype: 'filefield',
                hideLabel: true,
                name: 'file_path',
                value: '',
                buttonText: 'Buscar archivo...',
                anchor: '100%',
                allowBlank: false
            }, {
                xtype: 'container',
                html: 'La operación puede tardar varios minutos.<br>' + 'Archivos permitidos:'+me.desc+'.<br>' + 'Tamaño máximo permitido 10MB'
            }],
			dockedItems: [
				{
					xtype: 'toolbar',
					dock: 'bottom',
					items: [
						{
							xtype: 'component',
							flex: 1
						},{
							xtype: 'button',
							cls: 'botonFormulario',
							text: 'Aceptar',
							formBind: true,
							action: 'save',
							handler: function(){me.fireEvent('upload', me)}
						},{
							xtype: 'button',
							cls: 'botonFormulario',
							text: 'Cancelar',
							action: 'cancel',
							handler: me.close,
							scope:me
						}
					]
				}
			]
        }];
        me.callParent(arguments);
    }
});