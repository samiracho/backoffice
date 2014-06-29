Ext.define('BO.lib.overrides', {
	requires:['Ext.form.Panel'],
	statics: {
	
		vtypes: function(){		
			Ext.apply(Ext.form.field.VTypes, {
				doubleCheck: function (val, field)
				{
					var pwd = field.up('form').down('textfield[name=' + field.name.slice(0, -1)+']');
					return (val == pwd.getValue());

					return true;
				},
				doubleCheckText: 'Passwords do not match'
			});	
		},
		
		solveErrors : function(){
		
			// definir los overrides aquí dentro
		},
		
		htmlEditor: function(){
		
			return true;
			
			Ext.override(Ext.form.field.HtmlEditor, {
			  
				_getSelection : function() {
					var win = this.getWin();
					return win.getSelection ? win.getSelection() : win.document.selection;
				},
				_getNode: function(tagNode) {
					var selection = this._getSelection();
					var node      = (selection.focusNode.tagName == tagNode) ? selection.focusNode : (selection.focusNode.parentNode.tagName == tagNode ? selection.focusNode.parentNode : null ); 
					return node;
				},
				// We want to toggle on link button if a link is selected
				onEditorEvent: function(e) {
					var me    = this;
					var state = me._getNode('A') !== null ? true : false;
					var btns  = me.getToolbar().items.map;
					
					me.updateToolbar();
					
					if (me.readOnly || !me.activated) {
						return;
					}
					btns['createlink'].toggle(state);
					if(state)btns['underline'].toggle(!state);					
				},
				createLink: function() {
							
					var anchorNode = this._getNode('A');	
					
					// Check if the selection or the caret position is in a link node
					var linkValue = ( anchorNode != null ) ? anchorNode.getAttribute("href") : this.defaultLinkValue;		
					
					var url = prompt(this.createLinkText, linkValue);
					if (url && url !== 'http:/'+'/') {
						this.execCmd('createlink', url);
					}
					
					// If a link was selected and the user removes the http://... we unlink
					if(url == ''){				
						if (anchorNode !== null) {
							var selection = this._getSelection();
							var rangeToSelect = document.createRange();
							rangeToSelect.selectNode (selection.focusNode);
							selection.removeAllRanges ();
							selection.addRange (rangeToSelect);
						}
						this.execCmd('unlink');
					}
				}
			});  
		},
		// traducción de los textos del messagebox
		messageBoxTranslation: function()
		{
			Ext.MessageBox.buttonText = {
				ok     : 'Aceptar',
				cancel : 'Cancelar',
				yes    : 'Si',
				no     : 'No'
			};

		
		},
		load: function(){
			BO.lib.overrides.vtypes();
			BO.lib.overrides.messageBoxTranslation();
			BO.lib.overrides.htmlEditor();
			BO.lib.overrides.solveErrors();				
		}
	 }
 });
