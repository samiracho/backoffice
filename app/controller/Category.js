Ext.define('BO.controller.Category', {
    extend: 'BO.controller.Crud',
	views: [
        'category.Edit',
		'category.List'
    ],
    models: ['Category'],
	
	init: function() {
		this.callParent(arguments);
    }
});