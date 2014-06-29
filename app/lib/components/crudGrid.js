Ext.define('BO.lib.components.crudGrid' ,{
	extend: 'Ext.grid.Panel',
	editWidget: '',
	model:'',
	closable:true,
	groupField:'',
	adminAccess: false,
	requires:['BO.store.Default','BO.lib.components.searchButton','Ext.grid.*'],
    initComponent: function() {
        
		var me = this;
		var myStore = Ext.create('BO.store.Default',{model: me.model, groupField: me.groupField});
		
		Ext.applyIf(me, {
            store: myStore,
            viewConfig: {

            },
            dockedItems:[{
				dock:'top',
				xtype:'toolbar',
				items: [
				{
					text: 'Agregar',
					glyph:0xf196,
					action:'add',
					hidden: !me.adminAccess
				},
				{
					text: 'Editar',
					action:'edit',
					glyph:0xf040,
					disabled:true,
					hidden: !me.adminAccess
				},
				{
					text: 'Eliminar',
					action:'delete',
					glyph:0xf014,
					disabled:true,
					hidden: !me.adminAccess
				},
				
				'->',
				{
					xtype:'searchbutton',
					glyph:0xf002,
					text:'Buscar'
				}]
            },{
                xtype: 'pagingtoolbar',
                displayInfo: true,
                dock: 'bottom',
				store:myStore
			}]
        });

        me.callParent(arguments);
    }
});