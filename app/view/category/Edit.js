/*
 * File: app/view/category/Edit.js
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

Ext.define('BO.view.category.Edit', {
    extend: 'Ext.window.Window',
    alias: 'widget.categoryedit',
	closeAction:'hide',
    autoShow: false,
    width: 252,
    resizable: false,
	modal:true,
    title: 'Categoría',
	requires:[
		'Ext.form.field.Hidden'
	],
    initComponent: function() {
        var me = this;

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
                            xtype: 'hiddenfield',
                            name: 'categoria_id'
                        },
						{
                            xtype: 'textfield',
                            name: 'categoria_nombre',
                            fieldLabel: 'Nombre*',
                            allowBlank: false
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