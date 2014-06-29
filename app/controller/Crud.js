Ext.define('BO.controller.Crud', {
    extend: 'Ext.app.Controller',
	models: [],
	views: [],
	refs: [
        {
            ref: 'list',
            selector: 'grid'
        },{
            ref: 'form',
            selector: 'form'
        }
    ],
	
	// Debo definir unos selectores distintos para evitar asociar este controlador a otras vistas,
	// porque los controladores se aplican a todas las vistas de la página.
	// http://www.sencha.com/forum/showthread.php?194345-Inherited-controller-issue
    init: function() {
		var me = this;
		var control = {};
		for(var i in me.views){
			var controlName = me.views[i].toLowerCase().replace(".","");
			
			if(controlName.indexOf("list") != -1){
				control[controlName] = {
					itemdblclick: me.edit,
					itemclick: me.enableToolbarButtons
				};
				
				control[controlName + ' toolbar button'] = {
					click: me.buttonClick
				};	
			}
			
			else if(controlName.indexOf("edit") != -1){
					control[controlName + ' form button[action=save]'] = {
					click: me.update
				};
				
					control[controlName + ' form button[action=cancel]'] = {
					click: me.cancel
				};
			}
		}
		
        me.control(control);
    },
	// función pensada para ser sobreescrita
	beforeUpdate: function(record, form, store){
		return true;
	},
	// función pensada para ser sobreescrita
	beforeLoad: function(record, form){
		return true;
	},
	// función pensada para ser sobreescrita
	afterLoad: function(record, form){
		return true;
	},
	update: function(button){	
		var me = this;
		var isNew = false;
		var win    = button.up('window'),
			form   = win.down('form'),
			myStore = win.storeList,
			record = form.getRecord(),
			values = form.getValues();
		
		record.set(values);
		
		if(!me.beforeUpdate(record, form, myStore))return;
		
		myStore.bindedForm = form;
        
		// si es un registro nuevo hay que crearlo.
		if (record.phantom)
		{
			if (myStore.insert) myStore.insert(0, record);
			isNew = true;
		}
		
		Ext.MessageBox.show({
			msg: 'Guardando cambios...',
			width:200,
			wait:true,
			waitConfig: {interval:200},
			icon:'iconSave32'
		});

		form.disable();
		
		myStore.sync({
			callback:function(batch,options){
				Ext.MessageBox.hide();
				form.enable();
				if(batch.hasException()){							
					myStore.remove(record); // Bug ExtJS 5 (no borra records nuevos)			
					myStore.rejectChanges();
					return false; 
				}
				me.syncCallBack(batch, options, win, myStore, record);
				
				// si es nuevo hay que recargar el store para que la paginación sea correcta
				if(isNew)myStore.load();
			}
		});
	},
	cancel: function(button){	
		var win    = button.up('window');
		win.close();
		
	},
	edit: function(gridView, record) {
		var me = this;
		var grid = gridView.panel;
		
		// control de acceso
		if(!gridView.panel.adminAccess) return;
		
		if(!grid.editForm){
			grid.editForm = Ext.widget(grid.editWidget);		
			//guardamos una referencia al store en el formulario de edición
			grid.editForm.storeList = grid.getStore();
		}
		
		// desmarcamos los campos que estén marcados como inválidos
		var fields = grid.editForm.down('form').getForm().getFields();
		
		fields.each(function (item)
		{
			item.reset();
		});
		
		grid.editForm.center();	
		var form = grid.editForm.down('form');
		if(!me.beforeLoad(record, form))return;
		form.loadRecord(record);
		grid.editForm.show();
		if(!me.afterLoad(record, form))return;
    },
	deleteRecord: function(gridView, record) {
		
		//var store = gridView.panel.getStore();

		Ext.Msg.show(
		{
			title: 'Confirmación',
			msg: '¿Está seguro de que desea eliminarlo?',
			buttons: Ext.Msg.YESNO,
			closable: false,
			fn: function (btn)
			{
				if (btn == 'yes')
				{
					if(record) {
						record.erase({
							failure : function() {
								// el manejador de excepciones mostrará un mensaje de error
							},
							success : function() {
								//store.remove(record);
								//store.sync();
								BO.lib.commonFunctions.confirmacion();
							}
						});
					}
				}
			}
		});
	},
	enableToolbarButtons: function(gridView){
	
		var selection = gridView.getSelectionModel().getSelection()[0];
		var toolbar = gridView.panel.down('toolbar');
		
		if(selection && toolbar){
			toolbar.down('button[action=edit]').setDisabled(false);
			toolbar.down('button[action=delete]').setDisabled(false);
		}
	},
	buttonClick: function(button){
		var grid = button.up('grid');
		var selection = grid ? grid.getView().getSelectionModel().getSelection()[0] : false;
		
		switch (button.action)
		{
			case 'add':	
				var record = Ext.create(grid.getStore().getModel().entityName);
				this.edit(grid.getView(),record);			
			break;
				
			case 'edit':
				if(selection)this.edit(grid.getView(),selection);
			break;
			
			case 'delete':
				if(selection)this.deleteRecord(grid.getView(),selection);
			break;
			
			default:
			break;
		}
	},
	syncCallBack: function(batch, options, win, myStore, record){
		// por defecto mostramos confirmación y cerramos la ventana
		BO.lib.commonFunctions.confirmacion();
		win.close();
	}
});