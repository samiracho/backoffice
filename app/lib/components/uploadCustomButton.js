Ext.define('BO.lib.components.uploadCustomButton', {
    extend: 'Ext.form.Panel',
    alias: 'widget.uploadcustombutton',
	desc:'',
	requires:['Ext.form.field.File'],
	width:120,
	showConfirmation:false,
	text:'Seleccionar...',
    initComponent: function ()
    {
        var me = this; 

		Ext.applyIf(me, {
			items: [{
					xtype: 'fileuploadfield',
					buttonOnly: true,
					buttonText: me.text,
					margin:'0 0 0 0',
					name: 'file_path',
					buttonConfig: {
						glyph: me.glyph
					},
					listeners: {
						change: {
							fn: me.onUpload,
							scope: me
						} 
					}
				},
				{
					xtype:'hidden',
					name: me.name
				}
			]
        });
		me.glyph = '';
		
        me.callParent(arguments);
    },
	onUpload: function(button){
	
		var me = this;
		var myForm = me.getForm();

		if (myForm.isValid())
        {
            myForm.submit(
            {
				url: me.url,
				params:{
					id:'',
					nombre:''
				},
                waitMsg: 'Espere mientras se envía el archivo...',
                success: function (fp, response)
                {
					me.down('hidden').setValue(response.result.data['nombre']);
					me.fireEvent('success', me, fp, response);
                    if(me.ShowConfirmation)BO.lib.commonFunctions.confirmacion();				
                },
                failure: function (form, action)
                {
                    me.fireEvent('failure', me, form, action);
                }
            });
        }
	}
});