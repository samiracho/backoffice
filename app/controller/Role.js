Ext.define('BO.controller.Role', {
    extend: 'BO.controller.Crud',
	views: [
        'role.Edit',
		'role.List'
    ],
    models: ['Role','Perm'],
	
	init: function() {
		var me = this;
		
		
        me.control({
            'documentedit form uploadbutton': {
                afterrender: this.setupUploadButton
            }
		
		});
		
		 this.callParent(arguments);
    },
	setupUploadButton: function(uploadbutton){
		
		
	},
	
	// función pensada para ser sobreescrita
	beforeUpdate: function(record, form){
		
		var permsGrid =  form.down('grid');
		var selectedPerms = permsGrid.getView().getSelectionModel().getSelection();
		var permsIds = " ";
		var selPermsLen = selectedPerms.length;
		if(selPermsLen){
			for(var i = 0; i < selPermsLen; i++)
			{
				permsIds += selectedPerms[i].get('permiso_id')+",";
			}
			record.set('rol_permisos',permsIds.slice(0, -1));
		}else record.set('rol_permisos','');
		return true;
	},
	// función pensada para ser sobreescrita
	// si cambio el store del grid antes de mostrar el formulario peta en iexplore
	afterLoad: function(record, form){
	
		var permsGrid =  form.down('grid');
		var permsStore = Ext.create('BO.store.Default',{model: 'BO.model.Perm'});
		
		//le asociamos el store al grid
		permsGrid.reconfigure(permsStore);
		
		var setSelectedPerms = function(){
		
			var records      = permsStore.getRange();		
			var checkedPerms = [];	
			var docPermisos  = record.get('rol_permisos').split(",");
			
			for (var j = 0, l = records.length; j < l; j++) {			
							
				for ( var i = 0, m = docPermisos.length; i < m; i++) {
					if (docPermisos[i] ==  records[j].get('permiso_id')) checkedPerms.push(records[j]);
				}
			}
			
			permsGrid.getView().getSelectionModel().select(checkedPerms,true, true);
		};
		

		// cargamos la lista de usuarios
		permsStore.pageSize = null;
		permsStore.load({callback:setSelectedPerms});
		
		return true;
	}
});