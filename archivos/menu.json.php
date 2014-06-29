<?php
	require "../clases/inc.php";
?>
{
	success:true,
	data:[
		{
			text: "Menú Principal",
			glyph:0xf015,
			menu: {
				items: [
					
				<?php
					if ( Usuario::TienePermiso("administrar_usuarios") )
					{
						echo '
						{
							widgetName: "userlist",
							text: "Usuarios",
							controllers:["User"],
							glyph:0xf0c0
						}, ';
					}
				
					if ( Usuario::TienePermiso("administrar_roles") )
					{
						echo '
						{
							widgetName: "rolelist",
							text: "Roles",
							controllers: ["Role"],
							glyph:0xf132
						}, ';
					}
					
					if ( Usuario::TienePermiso("administrar_categorias") )
					{
						echo '
						{
							widgetName: "categorylist",
							text: "Categorías",
							controllers: ["Category"],
							glyph:0xf0e8
						}, ';
					}
					
					if ( Usuario::TienePermiso("administrar_noticias") )
					{
						echo '
						{
							widgetName: "articlelist",
							text: "Noticias",
							controllers: ["Article"],
							glyph:0xf044
						}, ';	
					}
					
					if ( Usuario::TienePermiso("administrar_proyectos") )
					{
						echo '
						{
							widgetName: "projectlist",
							text: "Proyectos",
							controllers: ["Project"],
							glyph:0xf0d1,
							bydefault:true
						}, ';	
					}						
				?>
					{
						xtype: "menuseparator"
					},
					{
						action: "closesession",
						text: "Cerrar Sesión",
						glyph:0xf011
					}
				]
			}
		}
	]
}