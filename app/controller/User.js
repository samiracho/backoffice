Ext.define('BO.controller.User', {
    extend: 'BO.controller.Crud',
	views: [
        'user.Edit',
		'user.List'
    ],
    refs: [
		{ref: 'userEditForm', selector: 'useredit form'}
	],
	models: ['User','Role'],
    init: function() {
		var me = this;

        me.control({
            'useredit': {
                show: this.setupEditForm
            },
			'useredit #usuarioGenerarPass' : {
				click: this.generarPass
			},
            'useredit #usuarioVerPass' : {
				click: this.verPass
			},
            'useredit [name=usuario_password]':{
                render: this.agregarToolTipPass
            }
		});
		
		this.callParent(arguments);
    },
    
    verPass: function(){
        
        var textFieldPass = this.getUserEditForm().down('[name=usuario_password]').getValue();
        Ext.MessageBox.alert('Contraseña actual', '<div style="text-align:center; width:210px">'+textFieldPass+'</div>');
    
    },
    
	generarPass: function()
	{
		 var textFieldPass = this.getUserEditForm().down('[name=usuario_password]');
		 var randomstring = Math.random().toString(36).slice(-8);
		 textFieldPass.setValue(randomstring);
	},
    
	setupEditForm: function(win)
	{
		var me = this;
		
		if(!me.comboRole) me.comboRole = me.getUserEditForm().down('remotecombobox');
		
		if(!me.comboRole.getStore())
		{
			var rolesStore = Ext.create('BO.store.Default',{model: 'BO.model.Role'});
			me.comboRole.bindStore(rolesStore);
		}
		me.comboRole.getStore().load();
        
        // ponemos a true la opción enviar email
        me.getUserEditForm().down('[name=usuario_enviarmail]').setValue('true');
        
        // si es un usuario nuevo generamos la contraseña
        if(me.getUserEditForm().down('[name=usuario_id]').getValue() == 0){
            this.generarPass();
        }

	},
    
    agregarToolTipPass: function(textField){   

        textField.mon(textField.inputEl, 'mouseover', function(){
        
            textField.inputEl.set({
                title : textField.getValue(),
                qtip : textField.getValue()
            });
        }, textField);
    }
});