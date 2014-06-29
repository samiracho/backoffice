// store generico del que heredan todos los demás. He hecho cambios para poder controlar mejor los errores
Ext.define('BO.store.Default', {
    extend: 'Ext.data.Store',
	alias:'widget.defaultStore',
    remoteSort: true,
	autoLoad:false,
	autoSync: false,
	// Referencia al form.
	// Si asociamos un formulario al store, en caso de que haya campos incorrectos, estos se marcarán en rojo.
	bindedForm: null,
    constructor: function (config)
    {
        var me = this;	
        config = config || {};
			
		me.callParent([config]);

		me.proxy.on('exception',function (proxy, response, operation){
		
            // si la creación ha fallado descartamos los cambios en el store
            if (operation.action == 'create')
            {
                
				var r = me.getRange();
				 
				for (var j = 0, l = r.length; j < l; j++) {
					if (r[j].dirty) r[j].reject();
					if (r[j].phantom) me.remove(r[j]);
				}
            }
            
			// si el store tenia asociado un formulario, marcamos los campos con errores
			if(me.bindedForm != null)
			{
				var json = response.decodedJson ? response.decodedJson : Ext.decode(response.responseText);						
				if ( Ext.typeOf(json.errors) == 'array' ) me.bindedForm.getForm().markInvalid(json.errors);
			}
		});		
    }
});