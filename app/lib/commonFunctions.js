Ext.define('BO.lib.commonFunctions', {
	statics: {
		removeMask : function ()
		{
			var loadingMask = Ext.get('loading-mask');
			var loading = Ext.get('loading');
			if (loading && loadingMask)
			{
				loadingMask.fadeOut(
				{
					opacity: 0,
					//can be any value between 0 and 1 (e.g. .5)
					easing: 'easeOut',
					duration: 400,
					remove: true,
					useDisplay: true,
					callback: function ()
					{
						loading.fadeOut(
						{
							duration: 400,
							remove: true
						})
					}
				});
			}
		},
		hideMask: function(){
			Ext.fly('loading-mask').hide();
			Ext.fly('loading').hide();
		},
		confirmacion : function ()
		{
			Ext.MessageBox.show(
			{
				msg: 'Cambios guardados correctamente',
				icon: Ext.Msg.INFO,
				modal:false,
				closable: false
			});
			// cerramos automáticamente el mensaje de confirmación tras 1 seg			
			Ext.Function.defer(Ext.MessageBox.hide, 800, Ext.MessageBox);
		},
		checkLogin: function(viewport){
		
			Ext.Ajax.request(
			{
				url: 'data/user.php?action=checkLogin',
				method: 'POST',
				failure: function (o)
				{
					Ext.create('BO.view.user.Login'); 
				}
			});
			return true;
		}
	}
});