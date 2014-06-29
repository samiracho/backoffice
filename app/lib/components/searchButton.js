Ext.define('BO.lib.components.searchButton', {
    alias: 'widget.searchbutton',
    extend: 'Ext.button.Button',
	requires:['Ext.form.FieldSet','Ext.form.field.ComboBox'],
	enableToggle:true,
	initComponent: function () {
		var me = this;	
		
		me.on('click',me.onSearch, me);
		
		me.callParent(arguments);
	},
	removeFilters: function (grid)
	{
		var store = grid.getStore();
		
		store.getProxy().extraParams.filtros = '';
            // recargamos el store
            var lstOptions = store.lastOptions ? store.lastOptions =
            {
                'refrescar': true
            } : {
                params: {
                    refrescar: true
                }
            };
			
            store.load(lstOptions);
	},
	onSearch: function(button)
	{
		var grid = button.up('grid');
		
		if (!button.pressed)
		{
			this.removeFilters(grid);
			return true;
		}
		
		if (!grid.dialogoBusqueda)
		{
			grid.dialogoBusqueda = Ext.create('BO.lib.components.searchWindow');
		}
		var filtros = new Array();
		for (i = 0; i < grid.columns.length; i++)
		{
			// el 160 es el texto que tiene una columna de tipo checkbox, así la ignoramos
			if (grid.columns[i].searchable !== false && grid.columns[i].text != '&#160;')
			{
				var filtro =
				{
					'nombre': grid.columns[i].text,
					'valor': grid.columns[i].dataIndex
				};
				filtros.push(filtro);
			}
		}
		
		var store = Ext.create('Ext.data.Store', {
			fields: [
			{
				name: 'nombre',
				defaultValue: ''
			}, {
				name: 'valor',
				defaultValue: ''
			}],
			data: filtros
		});
		
		// guardamos una referencia al store del grid para poder hacer filtrarlo
		grid.dialogoBusqueda.storeBusqueda = grid.getStore();
		grid.dialogoBusqueda.definirCamposBusqueda(store);
		grid.dialogoBusqueda.botonBusqueda = button;
		grid.dialogoBusqueda.show();
	
	}
});

Ext.define('BO.lib.components.searchWindow', {
    extend: 'Ext.window.Window',
    closeAction: 'hide',
    alias: 'widget.searchWindow',
    width: 330,
    resizable: false,
	modal:true,
    layout: {
        type: 'fit'
    },
    title: 'Buscar',
    storeBusqueda: '',
    initComponent: function ()
    {
        var me = this;
        
		var comparadorStore = Ext.create('Ext.data.Store', {
			fields:['id','nombre'],
			data: [
			{
				'id': '1',
				'nombre': '='
			}, {
				'id': '2',
				'nombre': 'Distinto'
			}, {
				'id': '3',
				'nombre': 'Parecido'
			}, {
				'id': '4',
				'nombre': 'No parecido'
			}, {
				'id': '5',
				'nombre': '>'
			}, {
				'id': '6',
				'nombre': '<'
			}]
		});
		
		var operadorStore = Ext.create('Ext.data.Store', {
			fields:['id','nombre'],
			data: [
			{
				'id': '1',
				'nombre': 'Y'
			}, {
				'id': '2',
				'nombre': 'Y NO'
			}, {
				'id': '3',
				'nombre': 'O'
			}, {
				'id': '4',
				'nombre': 'O NO'
			}]
		});
		
		me.items = [
        {
            xtype: 'form',
            bodyPadding: 10,
			border:0,
            items: [
            {
                xtype: 'combobox',
                itemId: 'filtroBusqueda',
                queryMode: 'local',
                editable: false,
                allowBlank: false,
                fieldLabel: 'Filtrar por',
                emptyText: 'Seleccionar Filtro',
                anchor: '100%',
                store: operadorStore,
                displayField: 'nombre',
                valueField: 'valor'
            }, {
                xtype: 'combobox',
                itemId: 'filtroComparador',
                queryMode: 'local',
                editable: false,
                allowBlank: false,
                fieldLabel: 'Comparador',
                emptyText: 'Seleccionar Comparador',
                anchor: '100%',
                store: comparadorStore,
                displayField: 'nombre',
                valueField: 'id',
				value:'3'
            }, {
                xtype: 'textfield',
                itemId: 'textoBusqueda',
                fieldLabel: 'Texto',
                allowBlank: false,
                anchor: '100%',
                listeners: {
                    specialkey: function (f, e)
                    {
                        if (e.getKey() == e.ENTER)
                        {
                            me.onSearchClick();
                        }
                    }
                }
            }, {
                xtype: 'fieldset',
                title: 'Más Opciones',
                collapsible: true,
                layout: 'anchor',
                collapsed: true,
                items: [
                {
                    xtype: 'combobox',
                    itemId: 'filtroOperador2',
                    queryMode: 'local',
                    editable: false,
                    allowBlank: true,
                    fieldLabel: 'Operador',
                    anchor: '100%',
                    value: '1',
                    store: operadorStore,
                    displayField: 'nombre',
                    valueField: 'id'
                }, {
                    xtype: 'combobox',
                    itemId: 'filtroBusqueda2',
                    queryMode: 'local',
                    editable: false,
                    allowBlank: true,
                    fieldLabel: 'Filtrar por',
                    emptyText: 'Seleccionr Filtro',
                    anchor: '100%',
                    store: operadorStore,
                    displayField: 'nombre',
                    valueField: 'valor'
                }, {
					xtype: 'combobox',
					itemId: 'filtroComparador2',
					queryMode: 'local',
					editable: false,
					allowBlank: true,
					fieldLabel: 'Comparador',
					emptyText: 'Seleccionar Comparador',
					anchor: '100%',
					store: comparadorStore,
					displayField: 'nombre',
					valueField: 'id'
				}, {
                    xtype: 'textfield',
                    itemId: 'textoBusqueda2',
                    fieldLabel: 'Texto',
                    allowBlank: true,
                    anchor: '100%',
                    listeners: {
                        specialkey: function (f, e)
                        {
                            if (e.getKey() == e.ENTER)
                            {
                                me.onSearchClick();
                            }
                        }
                    }
                }, {
                    xtype: 'combobox',
                    itemId: 'filtroOperador3',
                    queryMode: 'local',
                    editable: false,
                    allowBlank: true,
                    fieldLabel: 'Operador',
                    anchor: '100%',
                    value: '1',
                    store: operadorStore,
                    displayField: 'nombre',
                    valueField: 'id'
                },{
                    xtype: 'combobox',
                    itemId: 'filtroBusqueda3',
                    queryMode: 'local',
                    editable: false,
                    allowBlank: true,
                    fieldLabel: 'Filtrar por',
                    emptyText: 'Seleccionar Filtro',
                    anchor: '100%',
                    store: operadorStore,
                    displayField: 'nombre',
                    valueField: 'valor'
                }, {
					xtype: 'combobox',
					itemId: 'filtroComparador3',
					queryMode: 'local',
					editable: false,
					allowBlank: true,
					fieldLabel: 'Comparador',
					emptyText: 'Seleccionar Comparador',
					anchor: '100%',
					store: comparadorStore,
					displayField: 'nombre',
					valueField: 'id'
				}, {
                    xtype: 'textfield',
                    itemId: 'textoBusqueda3',
                    fieldLabel: 'Texto',
                    allowBlank: true,
                    anchor: '100%',
                    listeners: {
                        specialkey: function (f, e)
                        {
                            if (e.getKey() == e.ENTER)
                            {
                                me.onSearchClick();
                            }
                        }
                    }
                }]
            }],
			dockedItems: [
				{
					xtype: 'toolbar',
					dock: 'bottom',
					items: [
						{
							xtype: 'button',
							text: 'Limpiar',
							handler: function ()
							{
								me.down('form').getForm().reset()
							}
						},{
							xtype: 'component',
							flex: 1
						},{
							xtype: 'button',
							cls: 'botonFormulario',
							text: 'Aceptar',
							formBind: true,
							handler: me.onSearchClick,
							scope: me
						}, {
							xtype: 'button',
							cls: 'botonFormulario',
							text: 'Cancelar',
							handler: me.close,
							scope: me
						}
					]
				}
			]
        }];
        me.keys = [
        {
            key: [Ext.EventObject.ENTER],
            handler: function ()
            {
                me.onSearchClick
            }
        }];
        me.callParent(arguments);
    },
    definirCamposBusqueda: function (store)
    {
        this.down('#filtroBusqueda').store = store;
        this.down('#filtroBusqueda2').store = store;
        this.down('#filtroBusqueda3').store = store;
    },
	
    onSearchClick: function (button)
    {
        var filtro = this.down('#filtroBusqueda').getValue();
		var comparador = this.down('#filtroComparador').getValue();
        var valor = this.down('#textoBusqueda').getValue();
        var filtro2 = this.down('#filtroBusqueda2').getValue();
		var comparador2 = this.down('#filtroComparador2').getValue();
        var valor2 = this.down('#textoBusqueda2').getValue();
        var operador2 = this.down('#filtroOperador2').getValue();
        var filtro3 = this.down('#filtroBusqueda3').getValue();
		var comparador3 = this.down('#filtroComparador3').getValue();
        var valor3 = this.down('#textoBusqueda3').getValue();
        var operador3 = this.down('#filtroOperador3').getValue();
        // le ponemos los filtros de búsqueda "operadores: 1 and, 2 and not, 3 or, 4 or not, 5 (, 6 ) "
        this.storeBusqueda.getProxy().extraParams.filtros = Ext.encode(
        {
            'filtros': [
            {
                'nombre': filtro,
                'valor': valor,
                'operador': '1',
				'comparador': comparador
            }, {
                'nombre': filtro2,
                'valor': valor2,
                'operador': operador2,
				'comparador': comparador2
            }, {
                'nombre': filtro3,
                'valor': valor3,
                'operador': operador3,
				'comparador': comparador3
            }]
        });
		
        // recargamos
        this.storeBusqueda.loadPage(1);
        // marcamos el botón de búsqueda para que el usuario sepa que hay un filtro de búsqueda activo
        this.botonBusqueda.toggle(true, true);
        this.close();
    }
});