Ext.define('BO.controller.Viewport', {
    extend: 'Ext.app.Controller',
	views: [
		'Viewport'		
	],
	refs: [
		{ref: 'panelMain', selector: '#myViewport > #panelMain'},
		{ref: 'toolbarMain', selector: '#toolbarMain'}
	],
    init: function() {
        this.control({
            '#panelMain':{
				tabchange: this.tabChange
			},
            '#panelMain tab':{
                beforeclose: this.beforeTabClose
            },
			'#toolbarMain':{
				beforerender: this.menuBuild,
				menuReady: this.setDefaultTab
			},
			'#toolbarMain menuitem, #toolbarMain button': {
                click: this.itemActivate
            }
        });
    },
	
    // Siempre dejamos al menos una pestaña abierta
    beforeTabClose: function(tab)
    {
        var tabsNumber = this.getPanelMain().items.getCount();       
        if(tabsNumber>1) return true;
        else return false;
    },
    
	// si hay alguna opción del menú activada por defecto la ejecutamos
	setDefaultTab: function(menu, item){
	
		var item = menu.down('[bydefault=true]');
		if(item)this.itemActivate(item);
	},
	
	// recarga el store cuando cambiamos la pestaña, así nos aseguramos de tener la información actualizada siempre
	tabChange: function(tabPanel, newTab, oldTab, options){
		
		if(newTab.getStore)
		{
			var store = newTab.getStore();
			if(store){
				//store.removeAll();
				store.load();
			}
		}
	},
	
	// construye el menú de opciones
	menuBuild: function(menu){
		
		var me = this;
		
		var body = Ext.getBody();	

		// mostramos el mensaje de cargando
		body.mask('Cargando...');
		
		// leemos el menú de opciones del servidor
		Ext.Ajax.request(
		{
		   url: 'archivos/menu.json.php',
		   timeout: 6000,
		   // success se ejecuta cuando el servidor responde algo.
		   // como controlo globalmente los errores ajax no hace falta que muestre mensajes de error desde aquí.
		   success: function(resp)
		   {
				if ( resp.decodedJson && resp.decodedJson.success ){
					menu.add(resp.decodedJson.data);
				}else{
					menu.hide();
				}
				body.unmask();
				
				BO.lib.commonFunctions.removeMask();
				
				menu.fireEvent('menuReady', menu);
		   }
		});	
		
		
	},
	
	// al activar una opción del menú principal
    itemActivate: function(item) {
		
		if(item.menu)return;
		
		var me = this;
		
		if(item.action){
			// Si el botón tiene una acción asociada la ejecutamos
			me.runItemAction(item.action);
			return;
		}
		
		var tab = me.getPanelMain().child(item.widgetName);
		
		// si no existe la pestaña la creamos
		if(tab == null)
		{	
			if(item.controllers)
			{	
				for (var j = 0, l = item.controllers.length; j < l; j++) {
		
					if(!me.application.controllers.get(item.controllers[j])){
						// Versiones de ExtJS previas a 4.2.1
						// me.getController(item.controllers[j]).init();
						me.getController(item.controllers[j]);
					}
				}
			}

			// agregamos la pestaña
			tab = Ext.widget(item.widgetName);
			me.getPanelMain().add(tab);
		}
		
		// activamos la pestaña
		me.getPanelMain().setActiveTab(tab);
    },	
	
	runItemAction: function(action)
	{
		var me = this;
		// si el botón tiene una acción asociada la ejecutamos
		switch (action)
		{
			case 'closesession':	
				me.onExit();
				return;			
			break;
				
			default:
			break;
		}
	},
	
	// funcion que se ejecuta al hacer click sobre el botón salir
    onExit: function ()
    {
        Ext.MessageBox.show(
        {
            title: 'Cerrar Sesión',
            msg: '¿Desea cerrar la sesión?',
            buttons: Ext.MessageBox.YESNO,
			icon: Ext.MessageBox.QUESTION,
            fn: function (buttonId)
            {
                switch (buttonId)
                {
                case 'no':
                    break;
                case 'yes':
                    window.location.href = 'index.php?controller=usuario&action=logout';
                    break;
                }
            },
            scope: this
        });
    }
});