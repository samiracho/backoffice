var glob = 0;
// combobox preconfigurado
Ext.define('BO.lib.components.remoteCombobox', {
    alias: 'widget.remotecombobox',
    extend: 'Ext.form.ComboBox',
    triggerAction: 'all',
    autoScroll: true,
	autoSelect: true,
    editable: false,
	forceSelection: true,
	emptyText:'Cargando...',
	trigger2Cls: Ext.baseCSSPrefix + 'form-reload-trigger',
    
    onTrigger1Click: function () {
    	this.onTriggerClick();
    },
    onTrigger2Click: function () {
		var me = this;
		me.expand();		
    	me.store.load();
    },

    // para que no vuelva a perdirle al servidor la lista una vez cargada
    queryMode: 'local',
    enableKeyEvents: true,


    setValue: function(value, doSelect) {
		var me = this;
		if(me.store.loading){
            me.store.on('load', function(){me.setValue(value,doSelect)}, me, {single:true});
            return;
        }
        this.callParent(arguments);
    },

	initComponent: function () {
		this.callParent(arguments);
		this.store.load();
	},

    listeners: {
        keydown: function (combo, e)
        {
            e.stopEvent();
        }
    }
});