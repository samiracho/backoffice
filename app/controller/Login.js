Ext.define('BO.controller.Login', {
	extend: 'Ext.app.Controller',
	views: ['user.Login'],
	refs: [
		{
            ref: 'loginForm',
            selector: 'userlogin form'
        },
		{
            ref: 'loginUserTextBox',
            selector: 'userlogin textfield[name=loginUsername]'
        }
    ],
	init: function() {
		this.control({
			'userlogin button[action=userlogin]': {
				click: this.userLogin
			},
			'userlogin textfield[name=loginPassword]': {
				keyup: this.keyEnter
			},
			'userlogin textfield[name=loginUsername]': {
				afterrender: this.focusField
			}
		});
	},
	focusField: function(textfield){
	
		Ext.defer(function(){
			textfield.inputEl.dom.focus();
		}, 300);
	},
	keyEnter: function(textfield,e){
		if(e.getKey() === e.ENTER){
			e.stopEvent();
			this.userLogin();
		}
	},
	
	userLogin: function (button)
	{
		this.getLoginForm().getForm().submit(
		{
			method: 'POST',
			waitTitle: 'Conectando',
			url: 'index.php?controller=usuario&action=checkPassword',
			waitMsg: 'Enviando datos...',
			success: function ()
			{
				var redirect = 'index.php';
				window.location = redirect;
			},
			failure: function (form, action)
			{
				if (action.failureType == 'server')
				{
					obj = Ext.decode(action.response.responseText);
					Ext.Msg.alert('Identificación incorrecta', obj.errors.reason);
				}
				else
				{
					Ext.Msg.alert('¡Atención!', 'Fallo de conexión con el servidor de autenticación: ' + action.response.responseText);
				}
				form.reset();
			}
		});
	}
});