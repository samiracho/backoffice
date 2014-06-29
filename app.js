Ext.application({
    name: 'BO',
    appFolder: 'app',
	requires: [
		'Ext.window.MessageBox',
		'BO.lib.ajaxHandler',
		'BO.lib.commonFunctions',
		'BO.lib.overrides',
		'BO.controller.Crud',
		'BO.controller.User',
		'BO.controller.Role',
		'BO.controller.Article',
		'BO.controller.Category',
		'BO.controller.Project'	
	],
	controllers: [
		'Viewport',
		'Login'
	],
	autoCreateViewport: false,
    launch: function() {
		BO.app = this;
		
		// Captura errores Ajax y proporciona varios métodos útiles
		BO.lib.ajaxHandler.captureErrors();
		
        //Qtips
        Ext.QuickTips.init(); 
        
		//FontAwesome
		Ext.setGlyphFontFamily('FontAwesome');
		
		// Cargar overrides
		BO.lib.overrides.load();	
		
		// comprobamos si el usuario ha hecho login
		Ext.Ajax.request(
		{
			url: 'index.php?controller=usuario&action=checkLogin',
			method: 'POST',
			success: function (o)
			{
				if (!o.decodedJson.data.logged) 
				{
					BO.lib.commonFunctions.hideMask();
					Ext.create('BO.view.user.Login').show();
				}
				else Ext.create('BO.view.Viewport').show();
			}
		});
    }
});
