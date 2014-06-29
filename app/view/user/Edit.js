Ext.define('BO.view.user.Edit', {
    extend: 'Ext.window.Window',
    alias: 'widget.useredit',
	requires:['BO.lib.components.remoteCombobox','Ext.form.field.Hidden'],
    autoShow: false,
    width: 270,
    resizable: false,
	modal:true,
	closeAction: 'hide',
    bodyPadding: '',
    title: 'Usuario',
    initComponent: function() {
        var me = this;

		var rolesStore = Ext.create('BO.store.Default',{model: 'BO.model.Role'});
		
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
					model: 'BO.model.User',
                    border: 0,
                    bodyPadding: 10,
                    width: '100%',
                    defaults: {
                        labelAlign: 'top',
                        msgTarget: 'side',
                        width: '100%'
                    },
                    items: [
                        {
                            xtype: 'hiddenfield',
                            name: 'usuario_id'
                        },
						{
                            xtype: 'textfield',
                            name: 'usuario_nombre',
                            fieldLabel: 'Nombre*',
							allowBlank:false
                        },
                        {
                            xtype: 'textfield',
                            name: 'usuario_apellidos',
                            fieldLabel: 'Apellidos'
                        },
						{
                            xtype: 'textfield',
                            name: 'usuario_email',
                            fieldLabel: 'E-mail*',
                            vtype:'email',
							allowBlank:false
                        },
                        {
                            xtype: 'textfield',
                            name: 'usuario_login',
                            fieldLabel: 'Login*'
                        },
                        {
							xtype: 'fieldcontainer',
							fieldLabel: 'Contraseña*',
							layout: 'hbox',

							fieldDefaults: {
								labelAlign: 'top'
							},

							items: [{
								flex: 0.6,
								name: 'usuario_password',
								xtype:'textfield',
                                inputType: 'password',
								allowBlank: false
							},{
								flex: 0.4,
								text: 'Generar',
                                glyph: 0xf013,
								xtype: 'button',
								itemId:'usuarioGenerarPass',
								margins: '0 0 0 8'
							}]
						},
                        {
                            xtype: 'remotecombobox',
							name:'usuario_rol_id',
							displayField: 'rol_nombre',
							valueField: 'rol_id',
							store: rolesStore,
                            fieldLabel: 'Rol*',
                            allowBlank: true
                        },{
                            xtype: 'checkboxfield',
                            name: 'usuario_enviarmail',
                            value: '',
                            boxLabel: 'Enviar e-mail de confirmación'
                        }
                    ],
                    dockedItems: [
                        {
                            xtype: 'toolbar',
                            dock: 'bottom',
                            items: [
                                {
                                    xtype: 'component',
                                    flex: 1
                                },
                                {
                                    xtype: 'button',
                                    action: 'save',
                                    text: 'Aceptar',
                                    formBind: true
                                },
                                {
                                    xtype: 'button',
                                    action: 'cancel',
                                    text: 'Cancelar'
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    }

});