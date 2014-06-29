Ext.define('BO.view.user.Login', {
  extend      : 'Ext.window.Window',
  alias       : 'widget.userlogin',
  layout      : 'fit',
  bodyStyle   : 'padding:10px;',
  title       : 'Login',
  autoShow    : true,
  labelAlign  : 'left',
  modal       : true,
  closable    : false,
  draggable   : false,
  constrain   : true,
  resizable   : false,
 requires     : ['Ext.form.Label'],
 
  initComponent: function() {
    
	var me = this;
	
	me.items = [
      {
        xtype          : 'form',
		baseCls        : 'x-plain',
        border         : false,
        bodyStyle      : "padding: 10px;",
        waitMsgTarget  : true,
        labelAlign     : "left",
        items: [
          {
            xtype            : 'textfield',
            name             : 'loginUsername',
            fieldLabel       : 'Usuario',
            blankText        : 'Escriba el usuario',
            msgTarget        : 'side',
			allowBlank       : false,
            selectOnFocus    : true,
			hasFocus         : true,
            enableKeyEvents  : true
          },{
            xtype            : 'textfield',
            inputType        : 'password', 
            fieldLabel       : 'Clave',
            name             : 'loginPassword',
            allowBlank       : false,
            blankText        : 'Escriba la contraseña',
            msgTarget        : 'side',
            selectOnFocus    : true,
            enableKeyEvents  : true,
			id: 'password'
          }
        ]
      }
    ];
    me.buttons = [
      {
        xtype  : 'label',
        style  : {color:'#ff0000'} ,
        id     : 'msgField',
        width  :200
      },{
        text: '<b>Entrar</b>',
        action: 'userlogin'
      }
    ];
    this.callParent(arguments);
  }
});