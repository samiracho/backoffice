/*
 * File: app/view/article/Edit.js
 *
 * This file was generated by Sencha Architect version 2.0.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.0.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('BO.view.article.Edit', {
    extend: 'Ext.window.Window',
    alias: 'widget.articleedit',
	requires:[
		'Ext.form.field.Date',
		'Ext.form.field.Checkbox',
		'Ext.form.field.HtmlEditor',
		'BO.lib.components.uploadButton',
		'Ext.form.field.Hidden'
	],
    autoShow: false,
	closeAction:'hide',
    width: 416,
    resizable: false,
	modal:true,
    title: 'Noticia',

    initComponent: function() {
        var me = this;

		var catStore = Ext.create('BO.store.Default',{model: 'BO.model.Category'});
		
        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    border: 0,
                    width: '100%',
                    defaults: {
                        labelAlign: 'top',
                        msgTarget: 'side',
                        width: '100%'
                    },
                    bodyPadding: 10,
                    items: [
                        {
							xtype:'hiddenfield',
							name:'noticia_id'
						},
						{
                            xtype: 'textfield',
                            name: 'noticia_titulo',
                            fieldLabel: 'Título*',
                            allowBlank: false
                        },
                        {
                            xtype: 'datefield',
                            fieldLabel: 'Fecha',
							name:'noticia_fecha',
							format: "d/m/Y",
							allowBlank:false,
							editable: false,
							enableKeyEvents: true,
							listeners: {
								keydown: function (combo, e){
									e.stopEvent();
								}
							}
                        },{
							margin:'8 0 0 0',
							xtype:'uploadbutton',
                            text: 'Seleccionar imagen...',
							bindedFieldName:'noticia_id',
							name:'noticia_imagen',
                            width:124,
							url:'data/noticia.php?action=uploadImage'
						},{
                            xtype: 'checkboxfield',
                            fieldLabel: 'Publicada',
                            boxLabel: 'Si',
							name:'noticia_publicada',
							uncheckedValue: 0
                        },{
                            xtype: 'remotecombobox',
							name:'noticia_categoria_id',
							displayField: 'categoria_nombre',
							valueField: 'categoria_id',
							store: catStore,
                            fieldLabel: 'Categoria'
                        },
                        {
                            xtype: 'htmleditor',
                            height: 200,
                            fieldLabel: 'Texto',
							name:'noticia_cuerpo',
							enableFont:false,
							enableFontSize:false,
							enableColors:false
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