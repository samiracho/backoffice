Ext.define('BO.lib.ajaxHandler', {
	requires:[
			'Ext.data.proxy.Ajax'
	],
	 statics: {
		 
		 // función generadora de Stores
		 createStore: function(modelName)
		 {
			var store = Ext.create('BO.store.Default', {model: 'BO.model.'+modelName});
			return store;
		 },
		 // función generadora de TreeStores
		 createTreeStore: function(modelName)
		 {
			var store = Ext.create('BO.store.DefaultTree', {model: 'BO.model.'+modelName});
			return store;
		 },
		 // función para crear la configuración del proxy ajax para los modelos
		 buildProxy: function(urlRead, urlAdd, urlDestroy){
			var proxy = new Ext.data.proxy.Ajax(
			{
				reader: {
					type: 'json',
					rootProperty: 'data',
					messageProperty: 'message'
				},
				writer: {
					type: 'json',
					writeAllFields: true,
					rootProperty: 'data'
				},
				api: {
					read: urlRead,
					update: urlAdd,
					create: urlAdd,
					destroy: urlDestroy
				}
			});		
			return proxy;
		},// función para crear la configuración del tree proxy ajax para los modelos
		buildTreeProxy: function(urlRead, urlAdd, urlDestroy){
			var proxy = new Ext.data.proxy.Ajax(
			{
				url: urlRead,
				urlAdd: urlAdd,
				urlDestroy: urlDestroy,
                reader: {
					type: 'json',
					// para que obtenga los datos del campo data de la respuesta json
					getResponseData: function(response) {
						var resp = Ext.decode(response.responseText);
						response.responseText = ( resp && ("data" in resp) ) ? Ext.encode(resp.data) : response;
						return Ext.data.reader.Json.prototype.getResponseData.call(this, response);	
					}
				}
			});		
			return proxy;
		},
		// mostrará messagebox al usuario informándole si ha habido algún error
		captureErrors: function() {
			
			BO.lib.ajaxHandler._jsonExceptions();
			
			var body = Ext.getBody();
			
			// Control de errores global para conexiones AJAX con el servidor
			Ext.util.Observable.observe(Ext.data.Connection);
			Ext.data.Connection.on('requestexception', function(dataconn, response, options){

				// desbloqueamos la interfaz si estuviera bloqueada
				body.unmask();
				// mostramos el mensaje de error
				Ext.MessageBox.show(
				{
					title: 'REMOTE EXCEPTION',
					msg: response.responseText ? response.responseText : 'No response from server',
					icon: Ext.MessageBox.ERROR,
					buttons: Ext.Msg.OK,
					modal:true,
					cls: 'x-msg-floating'
				});
				
			});
			
			Ext.Ajax.on('requestcomplete', function(dataconn, response, options){
				
				var json = Ext.decode(response.responseText);

				 // nos guardamos en el objeto respuesta el json decodificado para no tener que hacer decode otra vez
				 response.decodedJson = json;

				 if ( !Ext.typeOf(json.data) == 'array' || !json.success ){			
					
					json.success = false;
					
					var errMsg = "";
					if (typeof json.errors == 'string') errMsg += json.errors;
					else
					try
					{
						for (i = 0; i < json.errors.length; i++)
						{
							errMsg += json.errors[i]['msg'] + '<br />';
						}
					}
					catch (err)
					{
						errMsg += err.toString();
					};
					
					// hago un defer para evitar que la ventana aparezca detrás de diálogos modales al hacer submit de un formulario
					Ext.defer(function () {
					
						Ext.MessageBox.show(
						{
							title: 'REMOTE EXCEPTION',
							msg: '<b>'+( json.message || 'Error' )+':</b><br />'+ ( BO.lib.ajaxHandler._leerErrores(json) || response.responseText),
							icon: Ext.MessageBox.ERROR,
							fn:function(){
								// si es un mensaje de sesión expirada volvemos a la pantalla de login
								if( json.errors === "SESSION_ERROR") window.location.href=window.location.href;
							},
							buttons: Ext.Msg.OK,
							modal:true
						})
						Ext.MessageBox.toFront();
					
					}, 100, this);
				}
			});
		 },
		 
		 // función para procesar los errores de un mensaje devuelto por el servidor
		 _leerErrores: function(json)
		 {
			var errMsg = "";
			
			if(json.errors)
			{
				if (typeof json.errors == 'string')
				{ 
					errMsg += json.errors;	
				}
				else
				{
					try
					{
						for (i = 0; i < json.errors.length; i++)
						{
							errMsg += json.errors[i]['msg'] + '<br />';
						}
					}
					catch (err)
					{
						errMsg += err.toString();
					}
				}
			}
			else
			{
				errMsg = "Mensaje JSON corrupto.";
			}
			
			return errMsg;
		 },
		 
		 // En lugar de lanzar una excepción cuando el mensaje JSON esté mal creado
		 // devolveremos una respuesta de error
		 _jsonExceptions: function(){
			var oldDc = Ext.JSON.decode;
			Ext.JSON.decode = function (json, safe){
				
				var res = oldDc(json, true);
				if(res == null){
					// Quitamos la máscara si la hubiera
					Ext.getBody().unmask();
					// construimos un objeto con el error
					var respuesta =
					{
						message: 'REMOTE EXCEPTION',
						errors: 'Invalid JSON String' + '<br /><br />' + 'Debug Message' + ':<div style="width:300px;height:100px;overflow:auto">' + json + '</div>',
						success: false
					};
					return respuesta;
				}
				else return res;		
			};
			Ext.decode = Ext.JSON.decode;
		 }
	 }
 });